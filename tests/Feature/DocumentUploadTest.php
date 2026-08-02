<?php

namespace Tests\Feature;

use App\Models\Detainee;
use App\Models\Document;
use App\Models\Facility;
use App\Models\PenaltyReference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'security.documents.disk' => 'supabase',
        ]);
        Storage::fake('supabase');
    }

    public function test_admin_can_upload_document_to_durable_private_storage(): void
    {
        [$admin, $detainee] = $this->makeAdminAndDetainee();
        $file = UploadedFile::fake()->create('court order.pdf', 100, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('detainees.documents.store', $detainee), [
            'file' => $file,
            'doc_type' => 'court_record',
            'phase_number' => null,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        Storage::disk('supabase')->assertExists(
            'documents/'.$detainee->id.'/'.$file->hashName()
        );

        $this->assertDatabaseHas('documents', [
            'detainee_id' => $detainee->id,
            'storage_disk' => 'supabase',
            'original_name' => 'court order.pdf',
            'mime_type' => 'application/pdf',
            'doc_type' => 'court_record',
        ]);
    }

    public function test_authorized_user_downloads_document_through_laravel(): void
    {
        [$admin, $detainee] = $this->makeAdminAndDetainee();
        Storage::disk('supabase')->put('documents/1/court-order.pdf', 'private-content');

        $document = Document::create([
            'detainee_id' => $detainee->id,
            'file_path' => 'documents/1/court-order.pdf',
            'storage_disk' => 'supabase',
            'original_name' => 'court-order.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 15,
            'doc_type' => 'court_record',
            'uploaded_by' => $admin->id,
            'uploaded_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(
            route('detainees.documents.show', [$detainee, $document])
        );

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('court-order.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_deleting_document_removes_remote_object_and_database_record(): void
    {
        [$admin, $detainee] = $this->makeAdminAndDetainee();
        Storage::disk('supabase')->put('documents/1/to-delete.pdf', 'private-content');

        $document = Document::create([
            'detainee_id' => $detainee->id,
            'file_path' => 'documents/1/to-delete.pdf',
            'storage_disk' => 'supabase',
            'original_name' => 'to-delete.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 15,
            'doc_type' => 'court_record',
            'uploaded_by' => $admin->id,
            'uploaded_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete(
            route('detainees.documents.destroy', [$detainee, $document])
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');
        Storage::disk('supabase')->assertMissing($document->file_path);
        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    public function test_legacy_local_document_can_be_copied_to_supabase(): void
    {
        Storage::fake('local');
        [$admin, $detainee] = $this->makeAdminAndDetainee();
        $path = 'documents/'.$detainee->id.'/legacy.pdf';
        Storage::disk('local')->put($path, 'legacy-private-content');

        $document = Document::create([
            'detainee_id' => $detainee->id,
            'file_path' => $path,
            'storage_disk' => 'local',
            'original_name' => 'legacy.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 22,
            'doc_type' => 'court_record',
            'uploaded_by' => $admin->id,
            'uploaded_at' => now(),
        ]);

        $this->artisan('documents:migrate-to-supabase')->assertSuccessful();

        Storage::disk('supabase')->assertExists($path);
        Storage::disk('local')->assertExists($path);
        $this->assertSame('supabase', $document->refresh()->storage_disk);
    }

    private function makeAdminAndDetainee(): array
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $facility = Facility::create([
            'name' => 'Test Facility',
            'region' => 'Region X',
            'address' => '123 Test St',
            'capacity' => 10,
        ]);
        $penalty = PenaltyReference::create([
            'rpc_code' => 'TEST-DOC',
            'charge_name' => 'Test charge',
            'max_penalty_years' => 1,
            'law_source' => 'OTHER',
        ]);

        $detainee = Detainee::create([
            'full_name' => 'Upload Test Detainee',
            'charge_description' => 'Test charge',
            'charge_rpc_code' => $penalty->id,
            'commitment_date' => now()->subDays(2)->format('Y-m-d'),
            'facility_id' => $facility->id,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        return [$admin, $detainee];
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Models\Detainee;
use App\Models\Document;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DocumentController extends Controller
{
    public function index(Detainee $detainee)
    {
        $this->authorize('view', $detainee);
        $documents = $detainee->documents()->with('uploadedByUser')->latest()->get();

        return view('detainees.show', compact('detainee', 'documents'));
    }

    public function store(StoreDocumentRequest $request, Detainee $detainee)
    {
        $path = null;
        $disk = $this->configuredDocumentDisk();

        try {
            $file = $request->file('file');
            $path = $file->store("documents/{$detainee->id}", $disk);

            if (! is_string($path) || $path === '') {
                throw new RuntimeException('The document storage provider did not return an object path.');
            }

            $clientName = basename(str_replace('\\', '/', $file->getClientOriginalName()));
            $sanitizedName = preg_replace('/[\x00-\x1F\x7F]/u', '', $clientName);
            $originalName = Str::limit(
                is_string($sanitizedName) && $sanitizedName !== '' ? $sanitizedName : 'document',
                255,
                ''
            );

            DB::transaction(function () use ($request, $detainee, $file, $disk, $path, $originalName): void {
                Document::create([
                    'detainee_id' => $detainee->id,
                    'file_path' => $path,
                    'storage_disk' => $disk,
                    'original_name' => $originalName,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'doc_type' => $request->input('doc_type'),
                    'phase_number' => $request->input('phase_number'),
                    'uploaded_by' => $request->user()->id,
                    'uploaded_at' => now(),
                ]);

                AuditService::log(
                    'document_uploaded',
                    "Document ({$request->input('doc_type')}) uploaded for detainee {$detainee->full_name}",
                    $detainee->id
                );
            });

            return redirect()->back()->with('success', 'Document uploaded successfully.');
        } catch (\Throwable $e) {
            if (is_string($path) && $path !== '') {
                try {
                    Storage::disk($disk)->delete($path);
                } catch (\Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            report($e);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Document upload failed. Please try again or contact an administrator.');
        }
    }

    public function destroy(Detainee $detainee, Document $document)
    {
        if ($document->detainee_id !== $detainee->id) {
            abort(404);
        }
        $this->authorize('delete', $document);

        try {
            $deleted = Storage::disk($this->documentDisk($document))->delete($document->file_path);

            if (! $deleted) {
                throw new RuntimeException('The document could not be removed from object storage.');
            }

            $document->delete();

            AuditService::log(
                'document_deleted',
                "Document ({$document->doc_type}) deleted for detainee {$detainee->full_name}",
                $detainee->id
            );
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->with('error', 'Document deletion failed. Please try again or contact an administrator.');
        }

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }

    public function show(Detainee $detainee, Document $document)
    {
        if ($document->detainee_id !== $detainee->id) {
            abort(404);
        }
        $this->authorize('view', $document);

        $storage = Storage::disk($this->documentDisk($document));
        abort_unless($storage->exists($document->file_path), 404, 'Document file not found.');

        return $storage->download($document->file_path, $document->original_name, [
            'Cache-Control' => 'private, no-store',
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function configuredDocumentDisk(): string
    {
        $disk = (string) config('security.documents.disk');

        if (! in_array($disk, config('security.documents.allowed_disks', []), true)) {
            throw new RuntimeException('The configured document storage disk is not allowed.');
        }

        return $disk;
    }

    private function documentDisk(Document $document): string
    {
        $disk = $document->storage_disk ?: 'local';

        if (! in_array($disk, config('security.documents.allowed_disks', []), true)) {
            throw new RuntimeException('The document references an unsupported storage disk.');
        }

        return $disk;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MigrateDocumentsToSupabase extends Command
{
    protected $signature = 'documents:migrate-to-supabase
        {--delete-local : Delete each local source only after its remote copy is verified}';

    protected $description = 'Copy legacy private local documents to the configured durable document disk';

    public function handle(): int
    {
        $targetDisk = (string) config('security.documents.disk');

        if ($targetDisk === '' || $targetDisk === 'local') {
            $this->error('DOCUMENTS_DISK must reference the durable Supabase disk.');

            return self::INVALID;
        }

        if (! in_array($targetDisk, config('security.documents.allowed_disks', []), true)) {
            $this->error('The configured document disk is not allowed.');

            return self::INVALID;
        }

        $migrated = 0;
        $failed = 0;

        Document::query()
            ->where(function ($query): void {
                $query->whereNull('storage_disk')->orWhere('storage_disk', 'local');
            })
            ->orderBy('id')
            ->chunkById(100, function ($documents) use ($targetDisk, &$migrated, &$failed): void {
                foreach ($documents as $document) {
                    try {
                        $this->copyDocument($document, $targetDisk);
                        $migrated++;
                        $this->line("Migrated document {$document->id}.");
                    } catch (\Throwable $exception) {
                        report($exception);
                        $failed++;
                        $this->error("Document {$document->id} failed: {$exception->getMessage()}");
                    }
                }
            });

        $this->info("Migration complete: {$migrated} migrated, {$failed} failed.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function copyDocument(Document $document, string $targetDisk): void
    {
        $source = Storage::disk('local');
        $target = Storage::disk($targetDisk);

        if (! $target->exists($document->file_path)) {
            if (! $source->exists($document->file_path)) {
                throw new RuntimeException('The local source file does not exist.');
            }

            $stream = $source->readStream($document->file_path);
            if (! is_resource($stream)) {
                throw new RuntimeException('The local source file could not be opened.');
            }

            try {
                $options = $document->mime_type ? ['ContentType' => $document->mime_type] : [];
                if (! $target->put($document->file_path, $stream, $options)) {
                    throw new RuntimeException('The remote storage provider rejected the file.');
                }
            } finally {
                fclose($stream);
            }
        }

        if (! $target->exists($document->file_path)) {
            throw new RuntimeException('The remote copy could not be verified.');
        }

        $document->forceFill(['storage_disk' => $targetDisk])->save();

        if ($this->option('delete-local') && $source->exists($document->file_path)) {
            if (! $source->delete($document->file_path)) {
                $this->warn("Remote copy verified, but local cleanup failed for document {$document->id}.");
            }
        }
    }
}

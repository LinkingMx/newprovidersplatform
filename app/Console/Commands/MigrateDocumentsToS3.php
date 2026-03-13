<?php

namespace App\Console\Commands;

use App\Models\SupplierDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateDocumentsToS3 extends Command
{
    protected $signature = 'app:migrate-documents-to-s3
                            {--dry-run : Mostrar qué se migraría sin ejecutar}';

    protected $description = 'Migra documentos de proveedores del disco local a S3 (Supabase)';

    public function handle(): int
    {
        $local = Storage::disk('local');
        $s3 = Storage::disk('s3');
        $dryRun = $this->option('dry-run');

        $documents = SupplierDocument::whereNotNull('archivo_path')->get();

        if ($documents->isEmpty()) {
            $this->info('No hay documentos con archivo para migrar.');

            return self::SUCCESS;
        }

        $this->info("Documentos encontrados: {$documents->count()}");

        if ($dryRun) {
            $this->warn('Modo dry-run: no se realizarán cambios.');
        }

        $migrated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($documents as $doc) {
            $path = $doc->archivo_path;

            if (! $local->exists($path)) {
                $this->warn("  SKIP [{$doc->id}] Archivo no existe en local: {$path}");
                $skipped++;

                continue;
            }

            if ($s3->exists($path)) {
                $this->line("  SKIP [{$doc->id}] Ya existe en S3: {$path}");
                $skipped++;

                continue;
            }

            if ($dryRun) {
                $size = number_format($local->size($path) / 1024, 1);
                $this->line("  WOULD MIGRATE [{$doc->id}] {$path} ({$size} KB)");
                $migrated++;

                continue;
            }

            try {
                $s3->put($path, $local->get($path));
                $migrated++;
                $this->info("  OK [{$doc->id}] {$path}");
            } catch (\Throwable $e) {
                $failed++;
                $this->error("  FAIL [{$doc->id}] {$path}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->table(
            ['Migrados', 'Omitidos', 'Fallidos'],
            [[$migrated, $skipped, $failed]]
        );

        if (! $dryRun && $migrated > 0) {
            if ($this->confirm('¿Eliminar los archivos locales ya migrados?', false)) {
                $deleted = 0;

                foreach ($documents as $doc) {
                    if ($local->exists($doc->archivo_path) && $s3->exists($doc->archivo_path)) {
                        $local->delete($doc->archivo_path);
                        $deleted++;
                    }
                }

                $this->info("Archivos locales eliminados: {$deleted}");
            }
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

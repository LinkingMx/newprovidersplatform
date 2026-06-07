@php
    /** @var \App\Models\SupplierDocument $record */
    $hasFile = filled($record->archivo_path);
    $tempUrl = null;
    $extension = null;
    $kind = 'unknown';

    if ($hasFile) {
        try {
            $tempUrl = \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl(
                $record->archivo_path,
                now()->addMinutes(15)
            );
        } catch (\Throwable) {
            $tempUrl = null;
        }
        $extension = strtolower(pathinfo($record->archivo_nombre ?? $record->archivo_path, PATHINFO_EXTENSION));
        $kind = match ($extension) {
            'pdf' => 'pdf',
            'png', 'jpg', 'jpeg', 'webp', 'gif' => 'image',
            default => 'other',
        };
    }
@endphp

<div class="fi-section flex flex-col gap-3">
    @if (! $hasFile)
        <div class="flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed border-gray-300 py-12 text-center dark:border-white/10">
            <x-filament::icon icon="heroicon-o-document" class="size-10 text-gray-400" />
            <p class="text-base font-semibold text-gray-900 dark:text-white">Sin archivo</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">El proveedor aún no ha subido este documento.</p>
        </div>
    @elseif (! $tempUrl)
        <div class="flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed border-red-300 py-12 text-center">
            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="size-10 text-red-400" />
            <p class="text-base font-semibold">No se pudo generar la URL temporal</p>
            <p class="text-sm text-gray-500">Revisa la configuración de S3.</p>
        </div>
    @else
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-mono text-xs">{{ $record->archivo_nombre }}</span>
            </p>
            <x-filament::link
                :href="$tempUrl"
                target="_blank"
                icon="heroicon-o-arrow-top-right-on-square"
                icon-position="after"
            >
                Abrir en pestaña nueva
            </x-filament::link>
        </div>

        @if ($kind === 'pdf')
            <iframe
                src="{{ $tempUrl }}"
                class="h-[70vh] w-full rounded-lg border border-gray-200 dark:border-white/10"
                title="Preview del documento"
            ></iframe>
        @elseif ($kind === 'image')
            <div class="flex justify-center rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-white/10 dark:bg-white/5">
                <img
                    src="{{ $tempUrl }}"
                    alt="Documento"
                    class="max-h-[70vh] w-auto rounded"
                />
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed border-gray-300 py-12 text-center dark:border-white/10">
                <x-filament::icon icon="heroicon-o-document-arrow-down" class="size-10 text-gray-400" />
                <p class="text-base font-semibold text-gray-900 dark:text-white">Vista previa no disponible</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    El formato <span class="font-mono">.{{ $extension ?: '?' }}</span> no se puede previsualizar aquí.
                </p>
                <x-filament::link
                    :href="$tempUrl"
                    target="_blank"
                    icon="heroicon-o-arrow-down-tray"
                    icon-position="after"
                >
                    Descargar archivo
                </x-filament::link>
            </div>
        @endif
    @endif
</div>

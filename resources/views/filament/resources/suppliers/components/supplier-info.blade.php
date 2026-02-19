@php
    $record = $getRecord();

    $addressFields = [
        ['label' => 'Calle', 'value' => $record->address_street],
        ['label' => 'Número', 'value' => $record->address_number],
        ['label' => 'Colonia', 'value' => $record->address_neighborhood],
        ['label' => 'Ciudad', 'value' => $record->address_city],
        ['label' => 'Estado', 'value' => $record->address_state ?? null],
        ['label' => 'C.P.', 'value' => $record->address_zip],
    ];

    $hasAddress = collect($addressFields)->contains(fn ($f) => filled($f['value']));
@endphp

<div style="display: flex; flex-direction: column; gap: 1rem;">
    {{-- Address card --}}
    <div style="border-radius: 0.75rem; overflow: hidden;" class="ring-1 ring-gray-950/5 dark:ring-white/10">
        <div style="padding: 0.625rem 1rem; display: flex; align-items: center; gap: 0.5rem;" class="border-b border-gray-950/5 bg-gray-50 dark:border-white/10 dark:bg-white/5">
            <x-filament::icon icon="heroicon-o-map-pin" style="width: 16px; height: 16px;" class="text-gray-400 dark:text-gray-500" />
            <span style="font-size: 0.8125rem; font-weight: 600;" class="text-gray-600 dark:text-gray-400">Dirección</span>
        </div>

        @if($hasAddress)
            <div style="padding: 1rem; display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                @foreach($addressFields as $field)
                    @if(filled($field['value']))
                        <div>
                            <div style="font-size: 0.6875rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.125rem;" class="text-gray-400 dark:text-gray-500">{{ $field['label'] }}</div>
                            <div style="font-size: 0.875rem; font-weight: 500;" class="text-gray-950 dark:text-white">{{ $field['value'] }}</div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div style="padding: 1rem;">
                <span style="font-size: 0.8125rem; font-style: italic;" class="text-gray-400 dark:text-gray-500">Sin dirección capturada</span>
            </div>
        @endif
    </div>

    {{-- Banking card --}}
    <div style="border-radius: 0.75rem; overflow: hidden;" class="ring-1 ring-gray-950/5 dark:ring-white/10">
        <div style="padding: 0.625rem 1rem; display: flex; align-items: center; gap: 0.5rem;" class="border-b border-gray-950/5 bg-gray-50 dark:border-white/10 dark:bg-white/5">
            <x-filament::icon icon="heroicon-o-credit-card" style="width: 16px; height: 16px;" class="text-gray-400 dark:text-gray-500" />
            <span style="font-size: 0.8125rem; font-weight: 600;" class="text-gray-600 dark:text-gray-400">Datos Bancarios</span>
        </div>

        <div style="padding: 1rem;">
            @if(filled($record->clabe_interbancaria))
                <div>
                    <div style="font-size: 0.6875rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.125rem;" class="text-gray-400 dark:text-gray-500">CLABE Interbancaria</div>
                    <div style="font-size: 0.9375rem; font-weight: 600; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; letter-spacing: 0.05em;" class="text-gray-950 dark:text-white">{{ $record->clabe_interbancaria }}</div>
                </div>
            @else
                <span style="font-size: 0.8125rem; font-style: italic;" class="text-gray-400 dark:text-gray-500">Sin datos bancarios capturados</span>
            @endif
        </div>
    </div>

    {{-- Country --}}
    @if(filled($record->address_country))
        <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0 0.25rem;">
            <x-filament::icon icon="heroicon-o-globe-americas" style="width: 14px; height: 14px;" class="text-gray-400 dark:text-gray-500" />
            <span style="font-size: 0.8125rem;" class="text-gray-500 dark:text-gray-400">País: <strong class="text-gray-700 dark:text-gray-300">{{ $record->address_country }}</strong></span>
        </div>
    @endif
</div>

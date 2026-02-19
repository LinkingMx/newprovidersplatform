<x-filament-widgets::widget>
    <x-filament::section
        icon="heroicon-o-clipboard-document-list"
        heading="Log de Eventos"
        description="Seguimiento del proceso de onboarding del proveedor"
        collapsible
        collapsed
    >
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">

            {{-- LEFT COLUMN --}}
            <div style="border-radius: 0.75rem; padding: 1.25rem;" class="ring-1 ring-gray-950/5 dark:ring-white/10">

                {{-- Header row --}}
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <x-filament::icon icon="heroicon-o-chart-bar" style="width: 18px; height: 18px;" class="text-gray-500 dark:text-gray-400" />
                        <span style="font-size: 0.875rem; font-weight: 600;" class="text-gray-700 dark:text-gray-300">Progreso general</span>
                    </div>
                    <span style="font-size: 0.8rem; font-weight: 500;" class="text-gray-500 dark:text-gray-400">{{ $completedCount }}/{{ $totalSteps }} pasos</span>
                </div>

                {{-- Status badge centered --}}
                <div style="display: flex; justify-content: center; margin-bottom: 0.75rem;">
                    <x-filament::badge :color="$status->color()" size="lg" style="font-size: 0.9rem; padding: 0.4rem 1.5rem;">
                        {{ $status->label() }}
                    </x-filament::badge>
                </div>

                {{-- Progress bar --}}
                <div style="height: 6px; width: 100%; border-radius: 9999px; overflow: hidden; margin-bottom: 1.25rem;" class="bg-gray-200 dark:bg-white/10">
                    <div style="height: 100%; width: {{ $progressPercent }}%; border-radius: 9999px; background: #f59e0b;"></div>
                </div>

                {{-- Invitation card --}}
                <div style="border-radius: 0.75rem; padding: 1rem;" class="ring-1 ring-gray-950/5 dark:ring-white/10">
                    <div style="display: flex; align-items: center; gap: 0.625rem; margin-bottom: 0.75rem;">
                        <div style="width: 36px; height: 36px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; background: #fef3c7;">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 20px; height: 20px; color: #d97706;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                        </div>
                        <div>
                            <div style="font-size: 0.875rem; font-weight: 600;" class="text-gray-900 dark:text-white">Detalles de Invitación</div>
                            <div style="font-size: 0.75rem;" class="text-gray-400 dark:text-gray-500">invitación</div>
                        </div>
                    </div>

                    @if($invitation)
                        <div style="font-size: 0.8125rem; display: flex; flex-direction: column; gap: 0.375rem;">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Enviada:</span>
                                <span style="font-weight: 600;" class="text-gray-900 dark:text-white">{{ $invitation['sent'] }}</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.375rem; flex-wrap: wrap;">
                                <span class="text-gray-500 dark:text-gray-400">Expira:</span>
                                <span style="font-weight: 600;" class="text-gray-900 dark:text-white">{{ $invitation['expires'] }}</span>
                                @if($invitation['is_expired'])
                                    <x-filament::badge color="danger" size="sm">Expirada</x-filament::badge>
                                @else
                                    <x-filament::badge color="warning" size="sm">Vigente</x-filament::badge>
                                @endif
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.375rem;">
                                <span class="text-gray-500 dark:text-gray-400">Estado:</span>
                                @if($invitation['is_accepted'])
                                    <x-filament::badge color="warning" size="sm">Aceptada {{ $invitation['accepted_at'] }}</x-filament::badge>
                                @else
                                    <x-filament::badge color="warning" size="sm">Pendiente</x-filament::badge>
                                @endif
                            </div>
                        </div>
                    @else
                        <p style="font-size: 0.8125rem;" class="text-gray-400 dark:text-gray-500">Sin invitación enviada</p>
                    @endif
                </div>
            </div>

            {{-- RIGHT COLUMN - Steps --}}
            <div style="border-radius: 0.75rem; padding: 1.25rem;" class="ring-1 ring-gray-950/5 dark:ring-white/10">
                <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 1.25rem;" class="text-gray-700 dark:text-gray-300">
                    Pasos de Onboarding
                </div>

                <div style="display: flex; flex-direction: column; gap: 0;">
                    @foreach($checks as $index => $check)
                        {{-- Step row --}}
                        <div style="display: flex; align-items: center; gap: 0.875rem; padding: 0.5rem 0; position: relative;">
                            {{-- Circle --}}
                            @if($check['done'])
                                <div style="width: 32px; height: 32px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #fef3c7; border: 2px solid #f59e0b;">
                                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px; color: #d97706;" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                                </div>
                            @else
                                <div style="width: 32px; height: 32px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" class="ring-2 ring-gray-300 bg-white dark:ring-white/20 dark:bg-white/5">
                                    <x-filament::icon :icon="$check['icon']" style="width: 16px; height: 16px;" class="text-gray-400 dark:text-gray-500" />
                                </div>
                            @endif

                            {{-- Label --}}
                            <span style="font-size: 0.875rem; {{ $check['done'] ? 'font-weight: 500;' : '' }}" @if($check['done']) class="text-gray-900 dark:text-white" @else class="text-gray-400 dark:text-gray-500" @endif>
                                {{ $check['label'] }}
                            </span>
                        </div>

                        {{-- Connector line --}}
                        @if(!$loop->last)
                            <div style="margin-left: 15px; height: 12px; width: 2px; background: {{ $check['done'] && ($checks[$index + 1]['done'] ?? false) ? '#f59e0b' : '' }};" @if(!($check['done'] && ($checks[$index + 1]['done'] ?? false))) class="bg-gray-200 dark:bg-white/10" @endif></div>
                        @endif
                    @endforeach
                </div>
            </div>

        </div>
    </x-filament::section>
</x-filament-widgets::widget>

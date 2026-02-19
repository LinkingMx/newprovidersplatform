<x-filament-widgets::widget>
    <x-filament::section
        icon="heroicon-o-clipboard-document-list"
        heading="Log de Eventos"
        description="Seguimiento del proceso de onboarding del proveedor"
    >
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            {{-- Estado actual --}}
            <div class="space-y-2">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado Actual</h4>
                <x-filament::badge :color="$status->color()">
                    {{ $status->label() }}
                </x-filament::badge>
            </div>

            {{-- Invitacion --}}
            <div class="space-y-2">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Invitacion</h4>
                @if($invitation)
                    <div class="space-y-1 text-sm">
                        <div><span class="font-medium">Enviada:</span> {{ $invitation['sent'] }}</div>
                        <div>
                            <span class="font-medium">Expira:</span> {{ $invitation['expires'] }}
                            @if($invitation['is_expired'])
                                <x-filament::badge color="danger" size="sm" class="ml-1">Expirada</x-filament::badge>
                            @else
                                <x-filament::badge color="success" size="sm" class="ml-1">Vigente</x-filament::badge>
                            @endif
                        </div>
                        <div>
                            <span class="font-medium">Estado:</span>
                            @if($invitation['is_accepted'])
                                <span class="text-success-600 dark:text-success-400">Aceptada el {{ $invitation['accepted_at'] }}</span>
                            @else
                                <span class="text-warning-600 dark:text-warning-400">Pendiente</span>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-400">Sin invitacion enviada</p>
                @endif
            </div>

            {{-- Progreso --}}
            <div class="space-y-2">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Progreso</h4>
                <div class="space-y-1 text-sm">
                    @foreach($checks as $check)
                        <div class="flex items-center gap-2">
                            @if($check['done'])
                                <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4 text-success-500" />
                            @else
                                <x-filament::icon icon="heroicon-o-ellipsis-horizontal-circle" class="h-4 w-4 text-gray-400" />
                            @endif
                            <span @class(['text-gray-400' => !$check['done']])>{{ $check['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

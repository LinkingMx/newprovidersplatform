@php
    $isApproved = $branchRequest->isApproved();
    $statusColor = $isApproved ? '#16a34a' : '#dc2626';
    $statusBg = $isApproved ? '#f0fdf4' : '#fef2f2';
    $statusBorder = $isApproved ? '#bbf7d0' : '#fecaca';
@endphp
<x-omail.layout preview="Tu solicitud de sucursal {{ $branch->name }} fue {{ $branchRequest->status->label() }}">
    <h1 style="margin:0 0 8px 0;font-size:22px;font-weight:600;letter-spacing:-0.3px;color:#0a0a0a;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
        Hola, {{ $supplier->name }}
    </h1>
    <p style="margin:0 0 14px 0;font-size:14.5px;line-height:1.6;color:#374151;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
        Tu solicitud para la sucursal <strong>{{ $branch->name }}</strong> fue revisada.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:6px 0 14px 0;">
        <tr>
            <td>
                <span style="display:inline-block;padding:6px 14px;border-radius:999px;background:{{ $statusBg }};border:1px solid {{ $statusBorder }};color:{{ $statusColor }};font-size:13px;font-weight:600;letter-spacing:0.3px;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
                    {{ $branchRequest->status->label() }}
                </span>
            </td>
        </tr>
    </table>

    @if ($isApproved)
        <p style="margin:0 0 4px 0;font-size:14px;line-height:1.6;color:#374151;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
            La sucursal ya está asignada a tu cuenta. Puedes verla en tu panel de control.
        </p>
    @else
        <p style="margin:0 0 4px 0;font-size:14px;line-height:1.6;color:#374151;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
            Si tienes dudas, puedes enviar una nueva solicitud o contactar al administrador.
        </p>

        @if ($branchRequest->notas_admin)
            <x-omail.section title="Motivo del rechazo">
                <p style="margin:0;font-size:13.5px;line-height:1.6;color:#374151;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
                    {{ $branchRequest->notas_admin }}
                </p>
            </x-omail.section>
        @endif
    @endif

    <x-omail.button :href="$dashboardUrl">Ir a mi panel</x-omail.button>
</x-omail.layout>

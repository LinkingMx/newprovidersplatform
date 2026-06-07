@php
    $isApproved = $state->nombre === 'Aprobado';
    $statusColor = $isApproved ? '#16a34a' : '#dc2626';
    $statusBg = $isApproved ? '#f0fdf4' : '#fef2f2';
    $statusBorder = $isApproved ? '#bbf7d0' : '#fecaca';
@endphp
<x-omail.layout preview="Estado de tu documento {{ $document->documentType->nombre }}">
    <h1 style="margin:0 0 8px 0;font-size:22px;font-weight:600;letter-spacing:-0.3px;color:#0a0a0a;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
        Hola, {{ $supplier->name }}
    </h1>
    <p style="margin:0 0 14px 0;font-size:14.5px;line-height:1.6;color:#374151;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
        Tu documento <strong>{{ $document->documentType->nombre }}</strong> fue revisado.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:6px 0 14px 0;">
        <tr>
            <td>
                <span style="display:inline-block;padding:6px 14px;border-radius:999px;background:{{ $statusBg }};border:1px solid {{ $statusBorder }};color:{{ $statusColor }};font-size:13px;font-weight:600;letter-spacing:0.3px;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
                    {{ $state->etiqueta }}
                </span>
            </td>
        </tr>
    </table>

    @if ($isApproved)
        <p style="margin:0 0 4px 0;font-size:14px;line-height:1.6;color:#374151;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
            No es necesario que realices ninguna acción adicional para este documento.
        </p>
    @else
        <p style="margin:0 0 4px 0;font-size:14px;line-height:1.6;color:#374151;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
            Por favor, sube nuevamente el documento corregido desde tu panel de proveedor.
        </p>

        @if ($document->notas)
            <x-omail.section title="Motivo del rechazo">
                <p style="margin:0;font-size:13.5px;line-height:1.6;color:#374151;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
                    {{ $document->notas }}
                </p>
            </x-omail.section>
        @endif
    @endif

    <x-omail.button :href="$dashboardUrl">Ir a mi panel</x-omail.button>

    <p style="margin:18px 0 0 0;font-size:13px;line-height:1.6;color:#6b7280;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
        Si tienes preguntas, contacta a nuestro equipo de soporte.
    </p>
</x-omail.layout>

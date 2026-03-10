<x-mail::message>
# Hola, {{ $supplier->name }}

Tu solicitud para la sucursal **{{ $branch->name }}** ha sido **{{ $branchRequest->status->label() }}**.

@if($branchRequest->isApproved())
La sucursal ya está asignada a tu cuenta. Puedes verla en tu panel de control.
@else
**Motivo del rechazo:**

> {{ $branchRequest->notas_admin }}

Si tienes dudas, puedes enviar una nueva solicitud o contactar al administrador.
@endif

<x-mail::button :url="$dashboardUrl">
Ir a mi Panel
</x-mail::button>

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>

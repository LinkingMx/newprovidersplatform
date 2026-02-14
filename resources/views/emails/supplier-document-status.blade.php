<x-mail::message>
# <span style="color: #191731;">Hola, {{ $supplier->name }}</span>

@if($state->nombre === 'Aprobado')
Tu documento **<span style="color: #191731;">{{ $document->documentType->nombre }}</span>** ha sido **<span style="color: #191731;">aprobado</span>** por nuestro equipo.

No es necesario que realices ninguna acción adicional para este documento.
@elseif($state->nombre === 'Rechazado')
Tu documento **<span style="color: #191731;">{{ $document->documentType->nombre }}</span>** ha sido **<span style="color: #191731;">rechazado</span>**.

@if($document->notas)
**<span style="color: #191731;">Motivo del rechazo:</span>**

> {{ $document->notas }}
@endif

Por favor, sube nuevamente el documento corregido desde tu panel de proveedor.
@endif

<x-mail::button :url="$dashboardUrl">
Ir a mi Panel
</x-mail::button>

Si tienes preguntas o necesitas asistencia, por favor contacta a nuestro equipo de soporte.

---

Gracias por ser parte de nuestros proveedores.

El equipo de Costeño Group
</x-mail::message>

<x-mail::message>
# <span style="color: #191731;">Hola, {{ $supplier->name }}</span>

Se te han asignado **<span style="color: #191731;">{{ $documentCount }} documento(s)</span>** que necesitas subir para completar tu expediente de proveedor.

**<span style="color: #191731;">Pasos a seguir:</span>**
1. Ingresa a tu panel de proveedor
2. Dirígete a la sección de **Documentos**
3. Sube cada uno de los documentos solicitados
4. Espera la revisión y aprobación de nuestro equipo

<x-mail::button :url="$dashboardUrl">
Ir a mi Panel
</x-mail::button>

Es importante que subas tus documentos lo antes posible para que podamos completar la validación de tu expediente.

Si tienes preguntas o necesitas asistencia, por favor contacta a nuestro equipo de soporte.

---

Gracias por ser parte de nuestros proveedores.

El equipo de Costeño Group
</x-mail::message>

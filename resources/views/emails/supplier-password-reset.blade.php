<x-mail::message>
# <span style="color: #191731;">Hola, {{ $supplier->name }}</span>

Recibimos una solicitud para restablecer la contraseña de tu cuenta de proveedor.

<x-mail::button :url="$resetUrl">
Restablecer Contraseña
</x-mail::button>

**<span style="color: #191731;">Información importante:</span>**
- Este enlace será válido durante **<span style="color: #191731;">60 minutos</span>**
- Si no solicitaste este cambio, puedes ignorar este correo
- Tu contraseña actual no será modificada hasta que completes el proceso

Si tienes problemas para hacer clic en el botón, copia y pega la siguiente URL en tu navegador:

<span class="break-all">{{ $resetUrl }}</span>

---

Gracias por ser parte de nuestros proveedores.

El equipo de Costeño Group
</x-mail::message>

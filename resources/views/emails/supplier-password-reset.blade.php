<x-mail::message>
# Hola, {{ $supplier->name }}

Recibimos una solicitud para restablecer la contraseña de tu cuenta de proveedor.

<x-mail::button :url="$resetUrl">
Restablecer Contraseña
</x-mail::button>

**Información importante:**
- Este enlace será válido durante **60 minutos**
- Si no solicitaste este cambio, puedes ignorar este correo
- Tu contraseña actual no será modificada hasta que completes el proceso

Si tienes problemas con el enlace, copia y pega la siguiente URL en tu navegador:

{{ $resetUrl }}

---

El equipo de NewProvidersPlatform
</x-mail::message>

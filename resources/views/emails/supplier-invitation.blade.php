<x-mail::message>
# Bienvenido, {{ $supplier->name }}

Hemos creado tu cuenta como proveedor en nuestro sistema. Para completar el registro y acceder a tu panel, necesitas establecer una contraseña.

<x-mail::button :url="$invitationUrl">
Establecer Contraseña
</x-mail::button>

**Información importante:**
- Este enlace será válido durante **7 días**
- Si no estableces tu contraseña dentro de este tiempo, deberás solicitar una nueva invitación
- Este enlace es personal y no debe ser compartido con otros

**Después de establecer tu contraseña:**
1. Completa tu información de perfil
2. Proporciona tus datos bancarios (CLABE interbancaria)
3. Acepta los términos y condiciones
4. ¡Tu cuenta estará lista para usar!

Si tienes problemas para acceder o no solicitaste esta invitación, por favor contacta a nuestro equipo de soporte.

---

Gracias por ser parte de nuestros proveedores.

El equipo de NewProvidersPlatform
</x-mail::message>

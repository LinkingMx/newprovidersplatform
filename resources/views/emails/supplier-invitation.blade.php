<x-mail::message>
# <span style="color: #191731;">Bienvenido, {{ $supplier->name }}</span>

Hemos creado tu cuenta como proveedor en nuestro sistema. Para completar el registro y acceder a tu panel, necesitas establecer una contraseña.

<x-mail::button :url="$invitationUrl">
Establecer Contraseña
</x-mail::button>

**<span style="color: #191731;">Información importante:</span>**
- Este enlace será válido durante **<span style="color: #191731;">7 días</span>**
- Si no estableces tu contraseña dentro de este tiempo, deberás solicitar una nueva invitación
- Este enlace es personal y no debe ser compartido con otros

**<span style="color: #191731;">Después de establecer tu contraseña:</span>**
1. Completa tu información de perfil
2. Proporciona tus datos bancarios (CLABE interbancaria)
3. Acepta los términos y condiciones
4. ¡Tu cuenta estará lista para usar!

Si tienes problemas para acceder o no solicitaste esta invitación, por favor contacta a nuestro equipo de soporte.

---

Gracias por ser parte de nuestros proveedores.

El equipo de Costeño Group
</x-mail::message>

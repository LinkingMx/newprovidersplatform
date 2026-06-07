<x-omail.layout preview="Activa tu cuenta de proveedor en Costeño Providers">
    <h1 style="margin:0 0 8px 0;font-size:22px;font-weight:600;letter-spacing:-0.3px;color:#0a0a0a;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
        Bienvenido, {{ $supplier->name }}
    </h1>
    <p style="margin:0 0 18px 0;font-size:14.5px;line-height:1.6;color:#374151;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
        Hemos creado tu cuenta como proveedor en <strong>Costeño Providers</strong>. Para completar el registro y acceder a tu panel, necesitas establecer una contraseña.
    </p>

    <x-omail.button :href="$invitationUrl">Establecer contraseña</x-omail.button>

    <x-omail.section title="Información importante" subtitle="Lo que debes saber antes de continuar">
        <ul style="margin:0;padding-left:20px;font-size:13.5px;line-height:1.7;color:#374151;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
            <li>Este enlace es válido durante <strong>7 días</strong>.</li>
            <li>Si no estableces tu contraseña en ese tiempo, deberás solicitar una nueva invitación.</li>
            <li>El enlace es personal — no lo compartas.</li>
        </ul>
    </x-omail.section>

    <x-omail.section title="Después de establecer tu contraseña">
        <ol style="margin:0;padding-left:20px;font-size:13.5px;line-height:1.7;color:#374151;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
            <li>Completa tu información de perfil.</li>
            <li>Proporciona tus datos bancarios (CLABE interbancaria).</li>
            <li>Acepta los términos y condiciones.</li>
            <li>¡Tu cuenta estará lista para usar!</li>
        </ol>
    </x-omail.section>

    <p style="margin:24px 0 0 0;font-size:13px;line-height:1.6;color:#6b7280;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
        Si no solicitaste esta invitación o tienes problemas para acceder, contacta a nuestro equipo de soporte.
    </p>
</x-omail.layout>

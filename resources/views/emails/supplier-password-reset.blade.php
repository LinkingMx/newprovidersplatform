<x-omail.layout preview="Restablece la contraseña de tu cuenta de proveedor">
    <h1 style="margin:0 0 8px 0;font-size:22px;font-weight:600;letter-spacing:-0.3px;color:#0a0a0a;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
        Hola, {{ $supplier->name }}
    </h1>
    <p style="margin:0 0 18px 0;font-size:14.5px;line-height:1.6;color:#374151;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
        Recibimos una solicitud para restablecer la contraseña de tu cuenta de proveedor.
    </p>

    <x-omail.button :href="$resetUrl">Restablecer contraseña</x-omail.button>

    <x-omail.section title="Información importante">
        <ul style="margin:0;padding-left:20px;font-size:13.5px;line-height:1.7;color:#374151;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
            <li>Este enlace es válido durante <strong>60 minutos</strong>.</li>
            <li>Si no solicitaste este cambio, puedes ignorar este correo.</li>
            <li>Tu contraseña actual no cambia hasta que completes el proceso.</li>
        </ul>
    </x-omail.section>

    <p style="margin:18px 0 0 0;font-size:12.5px;line-height:1.6;color:#6b7280;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
        ¿Problemas con el botón? Copia esta URL en tu navegador:
    </p>
    <p style="margin:6px 0 0 0;font-size:11.5px;line-height:1.5;color:#0a0a0a;word-break:break-all;font-family:'Geist Mono','SFMono-Regular',Consolas,monospace;">
        {{ $resetUrl }}
    </p>
</x-omail.layout>

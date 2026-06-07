<x-omail.layout preview="Tienes documentos pendientes por subir en Costeño Providers">
    <h1 style="margin:0 0 8px 0;font-size:22px;font-weight:600;letter-spacing:-0.3px;color:#0a0a0a;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
        Hola, {{ $supplier->name }}
    </h1>
    <p style="margin:0 0 18px 0;font-size:14.5px;line-height:1.6;color:#374151;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
        Se te han asignado <strong>{{ $documentCount }} documento(s)</strong> que necesitas subir para completar tu expediente de proveedor.
    </p>

    <x-omail.button :href="$dashboardUrl">Ir a mi panel</x-omail.button>

    <x-omail.section title="Pasos a seguir">
        <ol style="margin:0;padding-left:20px;font-size:13.5px;line-height:1.7;color:#374151;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
            <li>Ingresa a tu panel de proveedor.</li>
            <li>Dirígete a la sección <strong>Documentos</strong>.</li>
            <li>Sube cada uno de los documentos solicitados.</li>
            <li>Espera la revisión y aprobación de nuestro equipo.</li>
        </ol>
    </x-omail.section>

    <p style="margin:18px 0 0 0;font-size:13px;line-height:1.6;color:#6b7280;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
        Sube tus documentos lo antes posible para que podamos completar la validación de tu expediente.
    </p>
</x-omail.layout>

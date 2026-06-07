@props([
    'preview' => null,
    'attachment' => null,
])
@php
    $contact = config('mail.from.address') ?: 'soporte@grupocosteno.com';
    $year = date('Y');
@endphp
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light only">
    <title>Costeño Providers</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; background: #f3f4f6; }
        table { border-collapse: collapse; }
        img { border: 0; line-height: 100%; outline: none; text-decoration: none; }
        a { color: #0a0a0a; }
        @media only screen and (max-width: 620px) {
            .o-container { width: 100% !important; }
            .o-px { padding-left: 20px !important; padding-right: 20px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;-webkit-font-smoothing:antialiased;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
    @if ($preview)
        <div style="display:none;max-height:0;overflow:hidden;opacity:0;mso-hide:all;font-size:1px;line-height:1px;color:#f3f4f6;">{{ $preview }}</div>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;">
        <tr>
            <td align="center" style="padding:40px 16px 64px;">

                {{-- Card --}}
                <table role="presentation" class="o-container" width="600" cellpadding="0" cellspacing="0" style="width:600px;max-width:600px;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;">

                    {{-- Brandbar --}}
                    <tr>
                        <td class="o-px" style="padding:18px 28px;border-bottom:1px solid #eef0f3;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="left" style="vertical-align:middle;">
                                        <span style="font-family:Georgia,'Times New Roman',serif;font-style:italic;font-size:22px;color:#0a0a0a;">Costeño</span>
                                        <span style="font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;font-size:11px;font-weight:600;letter-spacing:1.6px;text-transform:uppercase;color:#6b7280;border-left:1px solid #e5e7eb;padding-left:8px;margin-left:6px;">Costeño Providers</span>
                                    </td>
                                    @if ($attachment)
                                        <td align="right" style="vertical-align:middle;">
                                            <span style="display:inline-block;border:1px solid #e5e7eb;border-radius:999px;padding:5px 11px;background:#fafafa;font-size:12px;color:#1f2937;">&#128206;&nbsp;{{ $attachment }}</span>
                                        </td>
                                    @endif
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td class="o-px" style="padding:28px;">
                            {{ $slot }}
                        </td>
                    </tr>
                </table>

                {{-- Footnote --}}
                <table role="presentation" class="o-container" width="600" cellpadding="0" cellspacing="0" style="width:600px;max-width:600px;">
                    <tr>
                        <td style="padding:18px 8px 0;text-align:center;font-size:11.5px;color:#9ca3af;line-height:1.6;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">
                            Este correo fue generado automáticamente por <strong style="color:#6b7280;font-weight:600;">Costeño Providers</strong>.<br>
                            ¿Dudas? Responde directamente o escribe a
                            <a href="mailto:{{ $contact }}" style="color:#6b7280;text-decoration:underline;">{{ $contact }}</a>.<br>
                            <span style="color:#c4c8cf;">© {{ $year }} Grupo Costeño</span>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>
</html>

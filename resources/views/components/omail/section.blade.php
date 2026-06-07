@props([
    'title',
    'subtitle' => null,
])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:18px;border:1px solid #e5e7eb;border-radius:16px;background:#ffffff;">
    <tr>
        <td style="padding:22px;">
            <div style="font-size:15.5px;font-weight:600;letter-spacing:-0.2px;color:#0a0a0a;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">{{ $title }}</div>
            @if ($subtitle)
                <div style="font-size:13px;color:#6b7280;margin-top:6px;line-height:1.5;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;">{{ $subtitle }}</div>
            @endif
            <div style="margin-top:14px;">{{ $slot }}</div>
        </td>
    </tr>
</table>

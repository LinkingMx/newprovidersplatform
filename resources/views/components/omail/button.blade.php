@props([
    'href',
    'color' => '#0a0a0a',
])
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:18px 0;">
    <tr>
        <td align="center" bgcolor="{{ $color }}" style="border-radius:10px;">
            <a href="{{ $href }}" target="_blank" rel="noopener" style="display:inline-block;padding:12px 24px;font-family:'Geist','Helvetica Neue',Helvetica,Arial,sans-serif;font-size:14px;font-weight:600;line-height:1;color:#ffffff;text-decoration:none;border-radius:10px;">{{ $slot }}</a>
        </td>
    </tr>
</table>

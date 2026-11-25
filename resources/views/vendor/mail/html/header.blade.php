@props(['url'])
{{-- En-tête des e-mails : logo Bénin Codeurs, fin trait vertical, puis « Talent Board ». --}}
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
<table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
<tr>
<td style="vertical-align: middle;"><img src="{{ asset('images/logo-benincodeurs-dark.png') }}" alt="Bénin Codeurs" width="136" height="36" style="display: block; height: 36px; width: auto; max-height: 36px; border: 0;"></td>
<td style="vertical-align: middle; padding: 0 12px;"><div style="width: 1px; height: 30px; background-color: #d9d0e6;"></div></td>
<td style="vertical-align: middle; font-size: 18px; font-weight: 800; color: #280050; white-space: nowrap;">Talent Board</td>
</tr>
</table>
</a>
</td>
</tr>

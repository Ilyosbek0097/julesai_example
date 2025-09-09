<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
@foreach($templates as $template)
    <table style="width: 100%;">
        {{-- Empty Row for spacing --}}
        <tr><td colspan="16"></td></tr>

        {{-- E'LON Header --}}
        <tr>
            <td colspan="16" style="text-align: center; font-weight: bold; font-size: 14px;">E'LON</td>
        </tr>
        <tr>
            <td colspan="16" style="text-align: center; font-weight: bold; font-size: 12px;">Naqd pul topshirish uchun</td>
        </tr>

        {{-- Bank & Doc Info --}}
        <tr>
            <td colspan="3">Bank nomi:</td>
            <td colspan="5" style="background-color: #FFFF00;">{{-- Bank Name Placeholder --}}</td>
            <td colspan="4"></td>
            <td>Sana:</td>
            <td colspan="2" style="background-color: #FFFF00;">{{ $template->currday ? (new DateTime($template->currday))->format('d.m.Y') : '' }}</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td colspan="5"></td>
            <td colspan="4"></td>
            <td>Hujjat №:</td>
            <td colspan="2" style="background-color: #FFFF00;">{{ $template->docnumb }}</td>
        </tr>

        {{-- Spacer Row --}}
        <tr><td colspan="16" style="height: 10px;"></td></tr>

        {{-- Main Content Table --}}
        <tr>
            <td colspan="2" style="font-weight: bold; text-align: center;">Debet</td>
            <td colspan="6" style="font-weight: bold; text-align: center;">Kredit</td>
            <td rowspan="2" colspan="2" style="font-weight: bold; text-align: center;">To'lov maqsadi</td>
            <td rowspan="2" colspan="3" style="font-weight: bold; text-align: center;">Summa</td>
            <td rowspan="2" colspan="3" style="font-weight: bold; text-align: center;">Valyuta kodi</td>
        </tr>
        <tr>
            <td style="font-weight: bold; text-align: center;">Hisob</td>
            <td style="font-weight: bold; text-align: center;">MFO</td>
            <td style="font-weight: bold; text-align: center;">INN</td>
            <td colspan="4" style="font-weight: bold; text-align: center;">Oluvchining nomi</td>
            <td style="font-weight: bold; text-align: center;">Hisob</td>
        </tr>
        <tr>
            <td style="background-color: #FFFF00;">{{ $template->coacc }}</td>
            <td></td>
            <td style="background-color: #FFFF00;">{{-- Oluvchi INN Placeholder --}}</td>
            <td colspan="4" style="background-color: #FFFF00;">{{ $template->coname }}</td>
            <td style="background-color: #FFFF00;">{{ $template->clacc }}</td>
            <td colspan="2" style="background-color: #FFFF00;">{{ $template->paypurpose }}</td>
            <td colspan="3" style="background-color: #FFFF00;">{{ number_format($template->sumpay, 2, ',', ' ') }}</td>
            <td colspan="3" style="background-color: #FFFF00;">{{-- Currency Code Placeholder --}}</td>
        </tr>

        {{-- Spacer Row --}}
        <tr><td colspan="16" style="height: 10px;"></td></tr>

        {{-- Summa so'z bilan --}}
        <tr>
            <td colspan="3">Summa so'z bilan:</td>
            <td colspan="13" style="background-color: #FFFF00;">{{-- Amount in words placeholder --}}</td>
        </tr>

        {{-- Spacer Row --}}
        <tr><td colspan="16" style="height: 10px;"></td></tr>

        {{-- Signatures --}}
        <tr>
            <td colspan="3">Bosh buxgalter:</td>
            <td colspan="5">_________________</td>
            <td colspan="3">Ijrochi:</td>
            <td colspan="5">_________________</td>
        </tr>

        {{-- Spacer Row --}}
        <tr><td colspan="16" style="height: 10px;"></td></tr>
        <tr><td colspan="16" style="border-bottom: 1px dashed #000;"></td></tr>
        <tr><td colspan="16" style="height: 10px;"></td></tr>

        {{-- Kvitansiya Section --}}
        <tr>
            <td colspan="16" style="text-align: center; font-weight: bold; font-size: 14px;">KVITANSIYA</td>
        </tr>
        <tr>
            <td colspan="3">Bank nomi:</td>
            <td colspan="5" style="background-color: #FFFF00;">{{-- Bank Name Placeholder --}}</td>
            <td colspan="4"></td>
            <td>Sana:</td>
            <td colspan="2" style="background-color: #FFFF00;">{{ $template->currday ? (new DateTime($template->currday))->format('d.m.Y') : '' }}</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td colspan="5"></td>
            <td colspan="4"></td>
            <td>Hujjat №:</td>
            <td colspan="2" style="background-color: #FFFF00;">{{ $template->docnumb }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold; text-align: center;">Debet</td>
            <td colspan="6" style="font-weight: bold; text-align: center;">Kredit</td>
            <td rowspan="2" colspan="2" style="font-weight: bold; text-align: center;">To'lov maqsadi</td>
            <td rowspan="2" colspan="3" style="font-weight: bold; text-align: center;">Summa</td>
            <td rowspan="2" colspan="3" style="font-weight: bold; text-align: center;">Valyuta kodi</td>
        </tr>
        <tr>
            <td style="font-weight: bold; text-align: center;">Hisob</td>
            <td style="font-weight: bold; text-align: center;">MFO</td>
            <td style="font-weight: bold; text-align: center;">INN</td>
            <td colspan="4" style="font-weight: bold; text-align: center;">Oluvchining nomi</td>
            <td style="font-weight: bold; text-align: center;">Hisob</td>
        </tr>
        <tr>
            <td style="background-color: #FFFF00;">{{ $template->coacc }}</td>
            <td></td>
            <td style="background-color: #FFFF00;">{{-- Oluvchi INN Placeholder --}}</td>
            <td colspan="4" style="background-color: #FFFF00;">{{ $template->coname }}</td>
            <td style="background-color: #FFFF00;">{{ $template->clacc }}</td>
            <td colspan="2" style="background-color: #FFFF00;">{{ $template->paypurpose }}</td>
            <td colspan="3" style="background-color: #FFFF00;">{{ number_format($template->sumpay, 2, ',', ' ') }}</td>
            <td colspan="3" style="background-color: #FFFF00;">{{-- Currency Code Placeholder --}}</td>
        </tr>
        <tr>
            <td colspan="3">Summa so'z bilan:</td>
            <td colspan="13" style="background-color: #FFFF00;">{{-- Amount in words placeholder --}}</td>
        </tr>
        <tr>
            <td colspan="3">Bosh buxgalter:</td>
            <td colspan="5">_________________</td>
            <td colspan="3">Kassir:</td>
            <td colspan="5">_________________</td>
        </tr>

        {{-- Spacer Row --}}
        <tr><td colspan="16" style="height: 10px;"></td></tr>
        <tr><td colspan="16" style="border-bottom: 1px dashed #000;"></td></tr>
        <tr><td colspan="16" style="height: 10px;"></td></tr>

        {{-- Order Section --}}
        <tr>
            <td colspan="16" style="text-align: center; font-weight: bold; font-size: 14px;">ORDER</td>
        </tr>
        <tr>
            <td colspan="3">Bank nomi:</td>
            <td colspan="5" style="background-color: #FFFF00;">{{-- Bank Name Placeholder --}}</td>
            <td colspan="4"></td>
            <td>Sana:</td>
            <td colspan="2" style="background-color: #FFFF00;">{{ $template->currday ? (new DateTime($template->currday))->format('d.m.Y') : '' }}</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td colspan="5"></td>
            <td colspan="4"></td>
            <td>Hujjat №:</td>
            <td colspan="2" style="background-color: #FFFF00;">{{ $template->docnumb }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold; text-align: center;">Debet</td>
            <td colspan="6" style="font-weight: bold; text-align: center;">Kredit</td>
            <td rowspan="2" colspan="2" style="font-weight: bold; text-align: center;">To'lov maqsadi</td>
            <td rowspan="2" colspan="3" style="font-weight: bold; text-align: center;">Summa</td>
            <td rowspan="2" colspan="3" style="font-weight: bold; text-align: center;">Valyuta kodi</td>
        </tr>
        <tr>
            <td style="font-weight: bold; text-align: center;">Hisob</td>
            <td style="font-weight: bold; text-align: center;">MFO</td>
            <td style="font-weight: bold; text-align: center;">INN</td>
            <td colspan="4" style="font-weight: bold; text-align: center;">Oluvchining nomi</td>
            <td style="font-weight: bold; text-align: center;">Hisob</td>
        </tr>
        <tr>
            <td style="background-color: #FFFF00;">{{ $template->coacc }}</td>
            <td></td>
            <td style="background-color: #FFFF00;">{{-- Oluvchi INN Placeholder --}}</td>
            <td colspan="4" style="background-color: #FFFF00;">{{ $template->coname }}</td>
            <td style="background-color: #FFFF00;">{{ $template->clacc }}</td>
            <td colspan="2" style="background-color: #FFFF00;">{{ $template->paypurpose }}</td>
            <td colspan="3" style="background-color: #FFFF00;">{{ number_format($template->sumpay, 2, ',', ' ') }}</td>
            <td colspan="3" style="background-color: #FFFF00;">{{-- Currency Code Placeholder --}}</td>
        </tr>
        <tr>
            <td colspan="3">Summa so'z bilan:</td>
            <td colspan="13" style="background-color: #FFFF00;">{{-- Amount in words placeholder --}}</td>
        </tr>
        <tr>
            <td colspan="3">Bosh buxgalter:</td>
            <td colspan="5">_________________</td>
            <td colspan="3">Kassir:</td>
            <td colspan="5">_________________</td>
        </tr>

        {{-- Page break if not the last record --}}
        @if (!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>
</html>

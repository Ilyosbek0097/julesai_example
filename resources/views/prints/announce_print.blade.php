<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Chop etish uchun hujjat</title>
    <style>
        @media print {
            body { font-family: Times New Roman, serif; }
            table { width: 100%; border-collapse: collapse; }
            td { border: 1px solid black; padding: 2px 4px; font-size: 10pt; vertical-align: middle; }
            .page-break { page-break-after: always; }
        }
        /* Screen-view styles for better preview */
        body { font-family: Times New Roman, serif; }
        table { width: 100%; border-collapse: collapse; }
        td { border: 1px solid black; padding: 2px 4px; font-size: 10pt; vertical-align: middle; }
    </style>
</head>
<body>
@foreach($templates as $template)
    <table style="width: 100%; border-collapse: collapse;">
        {{-- E'lon Section --}}
        {{-- Row 1: Empty --}}
        <tr>
            <td colspan="16" style="border: none;"></td>
        </tr>
        {{-- Row 2: Headers (with F2:H2 merge fix) --}}
        <tr>
            <td colspan="5" style="border: none;">Naqd pullarni topshirish</td>
            <td colspan="3" style="border: none; font-weight: bold;">E'LON №</td>
            <td colspan="2" style="text-align: center;">{{ $template->docnumb }}</td>
            <td style="border: none;"></td>
            <td colspan="5" style="text-align: center;">2-ilova</td>
        </tr>
        {{-- Row 3: SUMMA Header --}}
        <tr>
            <td colspan="10" style="border: none;"></td>
            <td colspan="6" style="text-align: center;">SUMMA</td>
        </tr>
        {{-- Row 4: Date and Amount --}}
        <tr>
            <td colspan="6" style="text-align: center;">{{ $template->currday ? (new DateTime($template->currday))->format('d.m.Y') : '' }}</td>
            <td colspan="4" style="border: none;"></td>
            <td colspan="6" style="text-align: center;">{{ number_format($template->sumpay, 2, ',', ' ') }}</td>
        </tr>
        {{-- Row 5: Payer --}}
        <tr>
            <td colspan="2" style="border: none;">Naqd pul topshiruvchi:</td>
            <td colspan="9" style="text-align: center; border-bottom: 1px solid black;">{{ $template->payer_name }}</td>
            <td colspan="5" style="border: none; text-align: center;">Дебет</td>
        </tr>
        {{-- Row 6: COA Account --}}
        <tr>
            <td colspan="10" style="border: none;"></td>
            <td colspan="6">{{ $template->clacc }}</td>
        </tr>
        {{-- Row 7: Bank Name --}}
        <tr>
            <td colspan="3" style="border: none;">Qabul qiluvchi bank nomi</td>
            <td colspan="8">{{ $template->cashbox->label ?? '' }}</td>
            <td colspan="5" style="border: none;"></td>
        </tr>
        {{-- Row 8: Bank/BXM Codes --}}
        <tr>
            <td style="border: none;"></td>
            <td style="text-align: center;">Bank kodi</td>
            <td colspan="2"></td>
            <td colspan="4" style="font-weight: bold;">00083</td>
            <td colspan="2">BXM kodi</td>
            <td>{{ $template->branchid }}</td>
            <td colspan="5" style="text-align: center;">KREDIT</td>
        </tr>
        {{-- Row 9: Payer Name and Account --}}
        <tr>
            <td colspan="2">Pul kirim qilinadigan xoʻjalik subyekti nomi va x/r</td>
            <td colspan="8">{{ $template->coname }}</td>
            <td colspan="6">{{ $template->coacc }}</td>
        </tr>
        {{-- Row 10: Amount in Words --}}
        <tr>
            <td colspan="3">Summa soʻz bilan -</td>
            <td colspan="13">{{ $template->sumpay_in_words }}</td>
        </tr>
        {{-- Row 11: Payment Purpose --}}
        <tr>
            <td colspan="2">To'lov maqsadi</td>
            <td colspan="14">{{ $template->paypurpose }}</td>
        </tr>
        {{-- Row 12: Empty with underline --}}
        <tr>
            <td colspan="7" style="border: none; border-bottom: 1px solid black;"></td>
            <td colspan="9" style="border: none;"></td>
        </tr>
        {{-- Row 13 & 14: Signatures --}}
        <tr>
            <td colspan="2" rowspan="2" style="font-size: 9pt; text-align: center; vertical-align: middle;">naqd pulni topshiruvchi shaxs imzosi:</td>
            <td colspan="3" rowspan="2" style="border: none;"></td>
            <td colspan="2" style="border: none;">Buxgalter:</td>
            <td colspan="4" style="border-bottom: 1px solid black;"></td>
            <td>Kassir:</td>
            <td colspan="4" style="border-bottom: 1px solid black;"></td>
        </tr>
        <tr>
            <td colspan="2" style="border: none;"></td>
            <td colspan="4" style="border: none;"></td>
            <td></td>
            <td colspan="4" style="border: none;"></td>
        </tr>

        {{-- Kvitansiya Section (starts immediately at row 15) --}}
        {{-- Row 15 (Kvitansiya's Row 1): Empty --}}
        <tr>
            <td colspan="16" style="border: none;"></td>
        </tr>
        {{-- Row 16 (Kvitansiya's Row 2): Headers --}}
        <tr>
            <td colspan="5" style="border: none;">Naqd pullar topshirilganligi toʻgʻrisida</td>
            <td colspan="3" style="border: none; font-weight: bold;">KVITANSIYA №</td>
            <td colspan="2" style="text-align: center;">{{ $template->docnumb }}</td>
            <td style="border: none;"></td>
            <td colspan="5" style="text-align: center; border: none;"></td>
        </tr>
        {{-- ... (rest of Kvitansiya section) ... --}}
        <tr>
            <td colspan="2" rowspan="2">MO'</td>
            <td colspan="3" rowspan="2" style="border: none;"></td>
            <td colspan="2" style="border: none;">Buxgalter:</td>
            <td colspan="4" style="border-bottom: 1px solid black;"></td>
            <td>Kassir:</td>
            <td colspan="4" style="border-bottom: 1px solid black;"></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td colspan="4" style="border: none;"></td>
            <td></td>
            <td colspan="4" style="border: none;"></td>
        </tr>

        {{-- Order Section (starts immediately at row 29) --}}
        {{-- Row 29 (Order's Row 1): Empty --}}
        <tr>
            <td colspan="16" style="border: none;"></td>
        </tr>
        {{-- Row 30 (Order's Row 2): Headers --}}
        <tr>
            <td colspan="5" style="border: none;">Naqd pullarni topshirish</td>
            <td colspan="3" style="border: none; font-weight: bold;">ORDER №</td>
            <td colspan="2" style="text-align: center;">{{ $template->docnumb }}</td>
            <td style="border: none;"></td>
            <td colspan="5" style="text-align: center; border: none;"></td>
        </tr>
        {{-- ... (rest of Order section) ... --}}
        <tr>
            <td colspan="2" rowspan="2" style="font-size: 9pt; text-align: center; vertical-align: middle;">naqd pulni topshiruvchi shaxs imzosi:</td>
            <td colspan="3" rowspan="2" style="border: none;"></td>
            <td colspan="2" style="border: none;">Buxgalter:</td>
            <td colspan="4" style="border-bottom: 1px solid black;"></td>
            <td>Kassir:</td>
            <td colspan="4" style="border-bottom: 1px solid black;"></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td colspan="4" style="border: none;"></td>
            <td></td>
            <td colspan="4" style="border: none;"></td>
        </tr>
    </table>

    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach
</body>
</html>

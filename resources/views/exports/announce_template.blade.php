<!DOCTYPE html>
<html>
<body>
@foreach($templates as $template)
    <table style="width: 100%; border-collapse: collapse;">
        {{-- E'lon Section --}}
        {{-- Row 1: Empty --}}
        <tr>
            <td colspan="16"></td>
        </tr>
        {{-- Row 2: Headers (with F2:H2 merge fix) --}}
        <tr>
            <td colspan="5">Naqd pullarni topshirish</td>
            <td colspan="3">E'LON №</td>
            <td colspan="2">{{ $template->docnumb }}</td>
            <td></td>
            <td colspan="5">2-ilova</td>
        </tr>
        {{-- Row 3: SUMMA Header --}}
        <tr>
            <td colspan="10"></td>
            <td colspan="6">SUMMA</td>
        </tr>
        {{-- Row 4: Date and Amount --}}
        <tr>
            <td colspan="6">{{ $template->currday ? (new DateTime($template->currday))->format('d.m.Y') : '' }}</td>
            <td colspan="4"></td>
            <td colspan="6">{{ number_format($template->sumpay, 2, ',', ' ') }}</td>
        </tr>
        {{-- Row 5: Payer --}}
        <tr>
            <td colspan="2">Naqd pul topshiruvchi:</td>
            <td colspan="9">{{ $template->clname }}</td>
            <td colspan="5">Дебет</td>
        </tr>
        {{-- Row 6: COA Account --}}
        <tr>
            <td colspan="10"></td>
            <td colspan="6">{{ $template->coacc }}</td>
        </tr>
        {{-- Row 7: Bank Name --}}
        <tr>
            <td colspan="3">Qabul qiluvchi bank nomi</td>
            <td colspan="8">{{ $template->cashbox->label ?? '' }}</td>
            <td colspan="5"></td>
        </tr>
        {{-- Row 8: Bank/BXM Codes --}}
        <tr>
            <td></td>
            <td>Bank kodi</td>
            <td colspan="2"></td>
            <td colspan="4">00083</td>
            <td colspan="2">BXM kodi</td>
            <td>{{ $template->branchid }}</td>
            <td colspan="5">KREDIT</td>
        </tr>
        {{-- Row 9: Payer Name and Account --}}
        <tr>
            <td colspan="2">Pul kirim qilinadigan xoʻjalik subyekti nomi va x/r</td>
            <td colspan="8">{{ $template->clname }}</td>
            <td colspan="6">{{ $template->clacc }}</td>
        </tr>
        {{-- Row 10: Amount in Words --}}
        <tr>
            <td colspan="3">Summa soʻz bilan -</td>
            <td colspan="13">summa so'z bilan - {{ number_format($template->sumpay, 2, ',', ' ') }}</td>
        </tr>
        {{-- Row 11: Payment Purpose --}}
        <tr>
            <td colspan="2">To'lov maqsadi</td>
            <td colspan="14">{{ $template->paypurpose }}</td>
        </tr>
        {{-- Row 12: Empty with underline --}}
        <tr>
            <td colspan="7"></td>
            <td colspan="9"></td>
        </tr>
        {{-- Row 13 & 14: Signatures --}}
        <tr>
            <td colspan="2" rowspan="2">naqd pulni topshiruvchi shaxs imzosi:</td>
            <td colspan="3" rowspan="2"></td>
            <td colspan="2">Buxgalter:</td>
            <td colspan="4"></td>
            <td>Kassir:</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td colspan="4"></td>
            <td></td>
            <td colspan="4"></td>
        </tr>

        {{-- Kvitansiya Section (starts immediately at row 15) --}}
        {{-- Row 15 (Kvitansiya's Row 1): Empty --}}
        <tr>
            <td colspan="16"></td>
        </tr>
        {{-- Row 16 (Kvitansiya's Row 2): Headers --}}
        <tr>
            <td colspan="5">Naqd pullar topshirilganligi toʻgʻrisida</td>
            <td colspan="3">KVITANSIYA №</td>
            <td colspan="2">{{ $template->docnumb }}</td>
            <td></td>
            <td colspan="5">2-ilova</td>
        </tr>
        {{-- Row 17 (Kvitansiya's Row 3): SUMMA Header --}}
        <tr>
            <td colspan="10"></td>
            <td colspan="6">SUMMA</td>
        </tr>
        {{-- Row 18 (Kvitansiya's Row 4): Date and Amount --}}
        <tr>
            <td colspan="6">{{ $template->currday ? (new DateTime($template->currday))->format('d.m.Y') : '' }}</td>
            <td colspan="4"></td>
            <td colspan="6">{{ number_format($template->sumpay, 2, ',', ' ') }}</td>
        </tr>
        {{-- Row 19 (Kvitansiya's Row 5): Payer --}}
        <tr>
            <td colspan="2">Naqd pul topshiruvchi:</td>
            <td colspan="9">{{ $template->clname }}</td>
            <td colspan="5">Дебет</td>
        </tr>
        {{-- Row 20 (Kvitansiya's Row 6): COA Account --}}
        <tr>
            <td colspan="10"></td>
            <td colspan="6">{{ $template->coacc }}</td>
        </tr>
        {{-- Row 21 (Kvitansiya's Row 7): Bank Name --}}
        <tr>
            <td colspan="3">Qabul qiluvchi bank nomi</td>
            <td colspan="8">{{ $template->cashbox->label ?? '' }}</td>
            <td colspan="5"></td>
        </tr>
        {{-- Row 22 (Kvitansiya's Row 8): Bank/BXM Codes --}}
        <tr>
            <td></td>
            <td>Bank kodi</td>
            <td colspan="2"></td>
            <td colspan="4">00083</td>
            <td colspan="2">BXM kodi</td>
            <td>{{ $template->branchid }}</td>
            <td colspan="5">KREDIT</td>
        </tr>
        {{-- Row 23 (Kvitansiya's Row 9): Payer Name and Account --}}
        <tr>
            <td colspan="2">Pul kirim qilinadigan xoʻjalik subyekti nomi va x/r</td>
            <td colspan="8">{{ $template->clname }}</td>
            <td colspan="6">{{ $template->clacc }}</td>
        </tr>
        {{-- Row 24 (Kvitansiya's Row 10): Amount in Words --}}
        <tr>
            <td colspan="3">Summa soʻz bilan -</td>
            <td colspan="13">summa so'z bilan - {{ number_format($template->sumpay, 2, ',', ' ') }}</td>
        </tr>
        {{-- Row 25 (Kvitansiya's Row 11): Payment Purpose --}}
        <tr>
            <td colspan="2">To'lov maqsadi</td>
            <td colspan="14">{{ $template->paypurpose }}</td>
        </tr>
        {{-- Row 26 (Kvitansiya's Row 12): Empty with underline --}}
        <tr>
            <td colspan="7"></td>
            <td colspan="9"></td>
        </tr>
        {{-- Row 27 & 28 (Kvitansiya's Row 13 & 14): Signatures --}}
        <tr>
            <td colspan="2" rowspan="2">MO'</td>
            <td colspan="3" rowspan="2"></td>
            <td colspan="2">Buxgalter:</td>
            <td colspan="4"></td>
            <td>Kassir:</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td colspan="4"></td>
            <td></td>
            <td colspan="4"></td>
        </tr>
    </table>

    {{-- Page break for multi-record exports --}}
    @if (!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif
@endforeach
</body>
</html>

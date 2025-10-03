<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hamkorbank</title>
    <style>
        @media print {
            body {
                font-family: Times New Roman, serif;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                padding-left: 2cm;
                padding-top: 1cm;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            td, th {
                border: 1px solid black;
                padding: 2px 4px;
                font-size: 10pt;
                vertical-align: middle;
            }

            .page-break {
                page-break-after: always;
            }
        }
        body {
            font-family: Times New Roman, serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            border: 1px solid black;
            padding: 2px 4px;
            font-size: 10pt;
            vertical-align: middle;
        }
    </style>
</head>
<body>
@foreach($templates as $template)
    <div style="max-height: 14.5cm; overflow: hidden; margin-bottom: 1cm;">
        <table style="border: 2px solid black;">
            {{-- E'lon Section --}}
            <tr>
                <td colspan="16" style="height:2px; border: none;"></td>
            </tr>
            <tr>
                <td colspan="5" style="border: none; width: 30%;">Naqd pullarni topshirish</td>
                <td colspan="3" style="border: none; font-weight: bold; text-align: center; ">E'LON №</td>
                <td colspan="2" style="text-align: center;">{{ $template->docnumb }}</td>
                <td style="border: none;"></td>
                <td colspan="5" style="text-align: center;">2-ilova</td>
            </tr>
            <tr>
                <td colspan="10" style="border: none;"></td>
                <td colspan="6" style="border: none; text-align: center;">SUMMA</td>
            </tr>
            <tr>
                <td colspan="6"
                    style="border:none; text-align: center;">{{ $template->currday ? (new DateTime($template->currday))->format('d.m.Y') : '' }}</td>
                <td colspan="4" style="border: none;"></td>
                <td colspan="6" style="text-align: center;">{{ number_format($template->sumpay, 2, ',', ' ') }}</td>
            </tr>
            <tr>
                <td colspan="2"
                    style="border-bottom: 1px solid black; border-top: none; border-left: none; border-right: none; text-align: center;">
                    Naqd pul topshiruvchi:
                </td>
                <td colspan="9"
                    style="border-bottom: 1px solid black; border-top: none; border-left: none; border-right: none; text-align: center;">
                    {{ $template->payer_name }}
                </td>
                <td colspan="5" style="border: none; text-align: center;">Дебет</td>
            </tr>
            <tr>
                <td colspan="10" style="border: none;"></td>
                <td colspan="6">{{ $template->clacc }}</td>
            </tr>
            <tr>
                <td colspan="3" style="border: none;">Qabul qiluvchi bank nomi</td>
                <td colspan="8" style="border: none;font-weight: 800;">{{ $template->cashbox->label ?? '' }}</td>
                <td colspan="5" style="border: none;"></td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align: center;">Bank kodi</td>
                <td colspan="2"></td>
                <td colspan="4" style="font-weight: bold;">00083</td>
                <td colspan="2">BXM kodi</td>
                <td style="font-weight: bold; border: none;">{{ $template->branchid }}</td>
                <td colspan="5" style="text-align: center; border: none;">Кредит</td>
            </tr>
            <tr>
                <td colspan="2" style="font-size: 9pt; font-weight: bold; vertical-align: middle; text-align: center; border: none;">
                    Pul kirim qilinadigan
                    xoʻjalik subyekti nomi va x/r
                </td>
                <td colspan="8" style="text-align: center; border: none; ">{{ $template->coname }}</td>
                <td colspan="6">{{ $template->coacc }}</td>
            </tr>
            <tr>
                <td colspan="2">Summa soʻz bilan</td>
                <td colspan="14">{{ $template->sumpay_in_words }} </td>
            </tr>
            <tr>
                <td colspan="2">To'lov maqsadi</td>
                <td colspan="14">{{ $template->paypurpose }}</td>
            </tr>
            <tr>
                <td colspan="7" style="border-bottom: 1px solid black; border-top: none; border-right: none; border-left: none;"></td>
                <td colspan="9" style="border: none;"></td>
            </tr>
            <tr>
                <td colspan="2" rowspan="2" style="font-size: 9pt; text-align: center; vertical-align: top; border: none;">
                    naqd pulni
                    topshiruvchi shaxs imzosi:
                </td>
                <td colspan="3" rowspan="2" style="border: none;"></td>
                <td colspan="2" style="border: none;">Buxgalter:</td>
                <td colspan="3" style="border: none;"></td>
                <td style="border: none;">Kassir:</td>
                <td colspan="5" style="border:none;"></td>
            </tr>
            <tr >
                <td colspan="2" style="border: none;"></td>
                <td colspan="4" style="border: none;"></td>
                <td style="border: none;"></td>
                <td colspan="4" style="border: none;"></td>
            </tr>

            {{-- Kvitansiya Section --}}
            <tr style="border-top: 2px solid black;  border-bottom: none; border-right: none; border-left: none;">
                <td colspan="16" style="height: 2px; border: none;"></td>
            </tr>
            <tr>
                <td colspan="5" style="border: none; width: 30%;">Naqd pullar topshirilganligi toʻgʻrisida</td>
                <td colspan="3" style="border: none; font-weight: bold; text-align: center; ">KVITANSIYA №</td>
                <td colspan="2" style="text-align: center;">{{ $template->docnumb }}</td>
                <td style="border: none;"></td>
                <td colspan="5" style="text-align: center; border: none;"></td>
            </tr>
            <tr>
                <td colspan="10" style="border: none;"></td>
                <td colspan="6" style="text-align: center; border: none;">SUMMA</td>
            </tr>
             <tr>
                <td colspan="6"
                    style="border:none; text-align: center;">{{ $template->currday ? (new DateTime($template->currday))->format('d.m.Y') : '' }}</td>
                <td colspan="4" style="border: none;"></td>
                <td colspan="6" style="text-align: center;">{{ number_format($template->sumpay, 2, ',', ' ') }}</td>
            </tr>
            <tr>
                <td colspan="2"
                    style="border-bottom: 1px solid black; border-top: none; border-left: none; border-right: none; text-align: center;">
                    Naqd pul topshiruvchi:
                </td>
                <td colspan="9"
                    style="border-bottom: 1px solid black; border-top: none; border-left: none; border-right: none; text-align: center;">
                    {{ $template->payer_name }}
                </td>
                <td colspan="5" style="border: none; text-align: center;">Дебет</td>
            </tr>
            <tr>
                <td colspan="10" style="border: none;"></td>
                <td colspan="6">{{ $template->clacc }}</td>
            </tr>
            <tr>
                <td colspan="3" style="border: none;">Qabul qiluvchi bank nomi</td>
                <td colspan="8" style="border: none;font-weight: 800;">{{ $template->cashbox->label ?? '' }}</td>
                <td colspan="5" style="border: none;"></td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align: center;">Bank kodi</td>
                <td colspan="2"></td>
                <td colspan="4" style="font-weight: bold;">00083</td>
                <td colspan="2">BXM kodi</td>
                <td style="font-weight: bold; border: none;">{{ $template->branchid }}</td>
                <td colspan="5" style="text-align: center; border: none;">Кредит</td>
            </tr>
            <tr>
                <td colspan="2" style="font-size: 9pt; font-weight: bold; vertical-align: middle; text-align: center; border: none;">
                    Pul kirim qilinadigan
                    xoʻjalik subyekti nomi va x/r
                </td>
                <td colspan="8" style="text-align: center; border: none; ">{{ $template->coname }}</td>
                <td colspan="6">{{ $template->coacc }}</td>
            </tr>
            <tr>
                <td colspan="2">Summa soʻz bilan</td>
                <td colspan="14">{{ $template->sumpay_in_words }}</td>
            </tr>
            <tr>
                <td colspan="2">To'lov maqsadi</td>
                <td colspan="14">{{ $template->paypurpose }}</td>
            </tr>
            <tr>
                <td colspan="7" style="border-bottom: 1px solid black; border-top: none; border-right: none; border-left: none;"></td>
                <td colspan="9" style="border: none;"></td>
            </tr>
            <tr>
                <td colspan="2" rowspan="2" style="font-weight:bold; text-align: center; vertical-align: middle; border: none;">
                    MO'
                </td>
                <td colspan="3" rowspan="2" style="border: none;"></td>
                <td colspan="2" style="border: none;">Buxgalter:</td>
                <td colspan="3" style="border: none;"></td>
                <td style="border: none;">Kassir:</td>
                <td colspan="5" style="border:none;"></td>
            </tr>
            <tr >
                <td colspan="2" style="border: none;"></td>
                <td colspan="4" style="border: none;"></td>
                <td style="border: none;"></td>
                <td colspan="4" style="border: none;"></td>
            </tr>
        </table>
    </div>
    @if ($loop->iteration % 2 == 0 && !$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach
</body>
</html>

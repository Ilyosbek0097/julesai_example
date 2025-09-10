<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Chop etish uchun hujjat</title>
    <style>
        @media print {
            body { font-family: Times New Roman, serif; -webkit-print-color-adjust: exact; color-adjust: exact; }
            table { width: 100%; border-collapse: collapse; }
            td, th { border: 1px solid black !important; padding: 2px 4px; font-size: 10pt; vertical-align: middle; }
            .page-break { page-break-after: always; }
        }
        body { font-family: Times New Roman, serif; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid black !important; padding: 2px 4px; font-size: 10pt; vertical-align: middle; }
    </style>
</head>
<body>
@foreach($templates as $template)
    <table style="width: 100%; border-collapse: collapse;">
        {{-- ... Omitted for brevity ... --}}
        {{-- Row 13 & 14: Signatures --}}
        <tr>
            <td colspan="2" rowspan="2" style="font-size: 9pt; text-align: center; vertical-align: middle;">naqd pulni topshiruvchi shaxs imzosi:</td>
            <td colspan="3" rowspan="2" style="border: none;"></td>
            <td colspan="2" style="border: none;">Buxgalter:</td>
            <td colspan="4" style="border-bottom: 1px solid black !important;"></td>
            <td colspan="2" style="border: none;">Kassir:</td>
            <td colspan="3" style="border-bottom: 1px solid black !important;"></td>
        </tr>
        <tr>
            <td colspan="2" style="border: none;"></td>
            <td colspan="4" style="border: none;"></td>
            <td colspan="2" style="border: none;"></td>
            <td colspan="3" style="border: none;"></td>
        </tr>

        {{-- Kvitansiya Section Signatures --}}
        {{-- ... Omitted for brevity ... --}}
        <tr>
            <td colspan="2" rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle;">MO'</td>
            <td colspan="3" rowspan="2" style="border: none;"></td>
            <td colspan="2" style="border: none;">Buxgalter:</td>
            <td colspan="4" style="border-bottom: 1px solid black !important;"></td>
            <td colspan="2" style="border: none;">Kassir:</td>
            <td colspan="3" style="border-bottom: 1px solid black !important;"></td>
        </tr>
        <tr>
            <td colspan="2" style="border: none;"></td>
            <td colspan="4" style="border: none;"></td>
            <td colspan="2" style="border: none;"></td>
            <td colspan="3" style="border: none;"></td>
        </tr>

        {{-- Order Section Signatures --}}
        {{-- ... Omitted for brevity ... --}}
        <tr>
            <td colspan="2" rowspan="2" style="font-size: 9pt; text-align: center; vertical-align: middle;">naqd pulni topshiruvchi shaxs imzosi:</td>
            <td colspan="3" rowspan="2" style="border: none;"></td>
            <td colspan="2" style="border: none;">Buxgalter:</td>
            <td colspan="4" style="border-bottom: 1px solid black !important;"></td>
            <td colspan="2" style="border: none;">Kassir:</td>
            <td colspan="3" style="border-bottom: 1px solid black !important;"></td>
        </tr>
        <tr>
            <td colspan="2" style="border: none;"></td>
            <td colspan="4" style="border: none;"></td>
            <td colspan="2" style="border: none;"></td>
            <td colspan="3" style="border: none;"></td>
        </tr>
    </table>

    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach
</body>
</html>

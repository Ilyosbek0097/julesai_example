<!DOCTYPE html>
<html>
<body>
@foreach($templates as $template)
    <table style="width: 100%; border-collapse: collapse;">
        {{-- ... Omitted for brevity ... --}}
        {{-- Row 13 & 14: Signatures --}}
        <tr>
            <td colspan="2" rowspan="2">naqd pulni topshiruvchi shaxs imzosi:</td>
            <td colspan="3" rowspan="2"></td>
            <td colspan="2">Buxgalter:</td>
            <td colspan="4"></td>
            <td colspan="2">Kassir:</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td colspan="4"></td>
            <td colspan="2"></td>
            <td colspan="3"></td>
        </tr>
        {{-- ... Omitted for brevity ... --}}
        {{-- Kvitansiya Section Signatures --}}
        <tr>
            <td colspan="2" rowspan="2">MO'</td>
            <td colspan="3" rowspan="2"></td>
            <td colspan="2">Buxgalter:</td>
            <td colspan="4"></td>
            <td colspan="2">Kassir:</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td colspan="4"></td>
            <td colspan="2"></td>
            <td colspan="3"></td>
        </tr>
        {{-- ... Omitted for brevity ... --}}
        {{-- Order Section Signatures --}}
        <tr>
            <td colspan="2" rowspan="2">naqd pulni topshiruvchi shaxs imzosi:</td>
            <td colspan="3" rowspan="2"></td>
            <td colspan="2">Buxgalter:</td>
            <td colspan="4"></td>
            <td colspan="2">Kassir:</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td colspan="4"></td>
            <td colspan="2"></td>
            <td colspan="3"></td>
        </tr>
    </table>

    @if (!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif
@endforeach
</body>
</html>

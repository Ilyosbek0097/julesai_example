<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    @foreach($templates as $template)
        {{-- E'lon Section --}}
        <table>
            <thead>
                <tr>
                    <th colspan="4">E'LON</th>
                </tr>
                <tr>
                    <th colspan="4">Naqd pul topshirish uchun</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2">Bank nomi:</td>
                    <td colspan="2">{{-- Bank Name Placeholder --}}</td>
                </tr>
                <tr>
                    <td>Sana:</td>
                    <td>{{ $template->currday ? (new DateTime($template->currday))->format('d.m.Y') : '' }}</td>
                    <td>Hujjat Raqami:</td>
                    <td>{{ $template->docnumb }}</td>
                </tr>
                <tr><td colspan="4" style="height: 15px;"></td></tr>
                <tr>
                    <td colspan="2">To'lovchi (Mijoz):</td>
                    <td colspan="2">{{ $template->clname }}</td>
                </tr>
                <tr>
                    <td colspan="2">To'lovchining hisob raqami:</td>
                    <td colspan="2">{{ $template->clacc }}</td>
                </tr>
                <tr>
                    <td colspan="2">To'lov maqsadi:</td>
                    <td colspan="2">{{ $template->paypurpose }}</td>
                </tr>
                <tr><td colspan="4" style="height: 15px;"></td></tr>
                <tr>
                    <td colspan="2">Oluvchi:</td>
                    <td colspan="2">{{ $template->coname }}</td>
                </tr>
                <tr>
                    <td colspan="2">Oluvchining hisob raqami:</td>
                    <td colspan="2">{{ $template->coacc }}</td>
                </tr>
                <tr><td colspan="4" style="height: 15px;"></td></tr>
                <tr>
                    <td>Summa (so'z bilan):</td>
                    <td colspan="3">{{-- Amount in words placeholder --}}</td>
                </tr>
                <tr>
                    <td>Summa (raqam bilan):</td>
                    <td colspan="3">{{ number_format($template->sumpay, 2, ',', ' ') }}</td>
                </tr>
                <tr><td colspan="4" style="height: 15px;"></td></tr>
                <tr>
                    <td>Bosh buxgalter:</td>
                    <td>_________________</td>
                    <td>Kassir:</td>
                    <td>_________________</td>
                </tr>
            </tbody>
        </table>

        {{-- Spacer between sections --}}
        <table>
            <tr><td style="height: 30px;"></td></tr>
        </table>

        {{-- Kvitansiya Section --}}
        <table>
            <thead>
                <tr>
                    <th colspan="4">KVITANSIYA</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2">Bank nomi:</td>
                    <td colspan="2">{{-- Bank Name Placeholder --}}</td>
                </tr>
                <tr>
                    <td>Sana:</td>
                    <td>{{ $template->currday ? (new DateTime($template->currday))->format('d.m.Y') : '' }}</td>
                    <td>Hujjat Raqami:</td>
                    <td>{{ $template->docnumb }}</td>
                </tr>
                <tr><td colspan="4" style="height: 15px;"></td></tr>
                <tr>
                    <td colspan="2">To'lovchi (Mijoz):</td>
                    <td colspan="2">{{ $template->clname }}</td>
                </tr>
                <tr>
                    <td colspan="2">To'lovchining hisob raqami:</td>
                    <td colspan="2">{{ $template->clacc }}</td>
                </tr>
                <tr>
                    <td colspan="2">To'lov maqsadi:</td>
                    <td colspan="2">{{ $template->paypurpose }}</td>
                </tr>
                <tr><td colspan="4" style="height: 15px;"></td></tr>
                <tr>
                    <td colspan="2">Oluvchi:</td>
                    <td colspan="2">{{ $template->coname }}</td>
                </tr>
                <tr>
                    <td colspan="2">Oluvchining hisob raqami:</td>
                    <td colspan="2">{{ $template->coacc }}</td>
                </tr>
                <tr><td colspan="4" style="height: 15px;"></td></tr>
                <tr>
                    <td>Summa (so'z bilan):</td>
                    <td colspan="3">{{-- Amount in words placeholder --}}</td>
                </tr>
                <tr>
                    <td>Summa (raqam bilan):</td>
                    <td colspan="3">{{ number_format($template->sumpay, 2, ',', ' ') }}</td>
                </tr>
                <tr><td colspan="4" style="height: 15px;"></td></tr>
                <tr>
                    <td>Bosh buxgalter:</td>
                    <td>_________________</td>
                    <td>Kassir:</td>
                    <td>_________________</td>
                </tr>
            </tbody>
        </table>

        {{-- Spacer between sections --}}
        <table>
            <tr><td style="height: 30px;"></td></tr>
        </table>

        {{-- Order Section --}}
        <table>
            <thead>
                <tr>
                    <th colspan="4">ORDER</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2">Bank nomi:</td>
                    <td colspan="2">{{-- Bank Name Placeholder --}}</td>
                </tr>
                <tr>
                    <td>Sana:</td>
                    <td>{{ $template->currday ? (new DateTime($template->currday))->format('d.m.Y') : '' }}</td>
                    <td>Hujjat Raqami:</td>
                    <td>{{ $template->docnumb }}</td>
                </tr>
                <tr><td colspan="4" style="height: 15px;"></td></tr>
                <tr>
                    <td colspan="2">To'lovchi (Mijoz):</td>
                    <td colspan="2">{{ $template->clname }}</td>
                </tr>
                <tr>
                    <td colspan="2">To'lovchining hisob raqami:</td>
                    <td colspan="2">{{ $template->clacc }}</td>
                </tr>
                <tr>
                    <td colspan="2">To'lov maqsadi:</td>
                    <td colspan="2">{{ $template->paypurpose }}</td>
                </tr>
                <tr><td colspan="4" style="height: 15px;"></td></tr>
                <tr>
                    <td colspan="2">Oluvchi:</td>
                    <td colspan="2">{{ $template->coname }}</td>
                </tr>
                <tr>
                    <td colspan="2">Oluvchining hisob raqami:</td>
                    <td colspan="2">{{ $template->coacc }}</td>
                </tr>
                <tr><td colspan="4" style="height: 15px;"></td></tr>
                <tr>
                    <td>Summa (so'z bilan):</td>
                    <td colspan="3">{{-- Amount in words placeholder --}}</td>
                </tr>
                <tr>
                    <td>Summa (raqam bilan):</td>
                    <td colspan="3">{{ number_format($template->sumpay, 2, ',', ' ') }}</td>
                </tr>
                <tr><td colspan="4" style="height: 15px;"></td></tr>
                <tr>
                    <td>Bosh buxgalter:</td>
                    <td>_________________</td>
                    <td>Kassir:</td>
                    <td>_________________</td>
                </tr>
            </tbody>
        </table>

        {{-- Page break if not the last record --}}
        @if (!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>
</html>

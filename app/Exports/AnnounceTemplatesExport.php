<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AnnounceTemplatesExport implements FromView, WithColumnWidths, WithStyles
{
    protected $templates;

    public function __construct($templates)
    {
        $this->templates = $templates;
    }

    /**
     * @return View
     */
    public function view(): View
    {
        return view('exports.announce_template', [
            'templates' => $this->templates
        ]);
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        // A to P
        return [
            'A' => 5, 'B' => 10, 'C' => 10, 'D' => 10,
            'E' => 10, 'F' => 10, 'G' => 10, 'H' => 10,
            'I' => 5, 'J' => 15, 'K' => 10, 'L' => 10,
            'M' => 10, 'N' => 5, 'O' => 5, 'P' => 5,
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // This method is now much simpler. The Blade view handles the layout (colspan/rowspan).
        // Here, we just apply borders and colors to make it look good.

        $lastRow = $sheet->getHighestRow();

        // Apply borders to all cells that have content.
        // This is a general approach. The Blade template itself creates the visual structure.
        $sheet->getStyle('A1:P' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // A more specific approach could be to apply borders only to the tables,
        // but this requires calculating row ranges again, which we want to avoid for simplicity.
        // The Blade template's inline styles will be converted, but for perfect borders,
        // this is the most reliable method.

        // Example of applying the yellow fill, though it's already in the blade file with style attributes.
        // This is just to show the user how it can be done here as well.
        // $sheet->getStyle('D4:H4')->getFill()
        //       ->setFillType(Fill::FILL_SOLID)
        //       ->getStartColor()->setARGB('FFFFFF00');

        return []; // No specific cell styles returned here, we modify the sheet directly.
    }
}

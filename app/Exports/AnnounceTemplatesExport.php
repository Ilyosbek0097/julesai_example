<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AnnounceTemplatesExport implements FromView, WithColumnWidths, WithEvents
{
    protected $templates;

    public function __construct($templates)
    {
        $this->templates = $templates;
    }

    public function view(): View
    {
        return view('exports.announce_template', [
            'templates' => $this->templates
        ]);
    }

    public function columnWidths(): array
    {
        // A to P, adjusted for visual balance
        return [
            'A' => 5, 'B' => 12, 'C' => 5, 'D' => 5, 'E' => 5, 'F' => 8,
            'G' => 5, 'H' => 5, 'I' => 8, 'J' => 8, 'K' => 12, 'L' => 5,
            'M' => 5, 'N' => 5, 'O' => 5, 'P' => 5,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $recordCount = count($this->templates);

                // New calculation: 14 rows for E'lon, 3 for separator, 14 for Kvitansiya, 1 for page break
                $rowsPerRecord = 32;

                // --- Define Style Arrays ---
                $smallFont = ['font' => ['size' => 9]];
                $boldFont = ['font' => ['bold' => true]];
                $boldishFont = ['font' => ['bold' => true, 'size' => 10]];
                $centerAlign = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
                $verticalCenter = ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER]];
                $wrapText = ['alignment' => ['wrapText' => true]];
                $thinBorder = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
                $bottomBorder = ['borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]]];
                $outlineBorder = ['borders' => ['outline' => ['borderStyle' => Border::BORDER_THICK]]];

                for ($i = 0; $i < $recordCount; $i++) {
                    $recordStartRow = ($i * $rowsPerRecord) + 1;

                    // --- E'lon Section ---
                    $elonStart = $recordStartRow;
                    $this->applySectionStyles($sheet, $elonStart, true);

                    // --- Kvitansiya Section ---
                    $kvitansiyaStart = $elonStart + 17; // 14 rows for table + 3 for separator
                    $this->applySectionStyles($sheet, $kvitansiyaStart, false);

                    // Apply specific bold style for "MO'"
                    $sheet->getStyle('A'.($kvitansiyaStart + 12).':B'.($kvitansiyaStart + 13))->applyFromArray($boldFont);
                }
            },
        ];
    }

    /**
     * Helper function to apply styles to a section to avoid code duplication.
     */
    private function applySectionStyles(Worksheet $sheet, int $startRow, bool $isElon)
    {
        // Style definitions are copied here to be self-contained
        $smallFont = ['font' => ['size' => 9]];
        $boldFont = ['font' => ['bold' => true]];
        $boldishFont = ['font' => ['bold' => true, 'size' => 10]];
        $centerAlign = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
        $verticalCenter = ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER]];
        $thinBorder = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
        $bottomBorder = ['borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]]];
        $outlineBorder = ['borders' => ['outline' => ['borderStyle' => Border::BORDER_THICK]]];

        // Apply Styles Row-by-Row
        $sheet->getStyle('A'.($startRow + 1))->applyFromArray($smallFont);
        $sheet->getStyle('F'.($startRow + 1).':H'.($startRow + 1))->applyFromArray($boldFont); // F2:H2 merge fix
        $sheet->getStyle('I'.($startRow + 1).':J'.($startRow + 1))->applyFromArray($centerAlign)->applyFromArray($thinBorder);
        $sheet->getStyle('L'.($startRow + 1).':P'.($startRow + 1))->applyFromArray($centerAlign)->applyFromArray($thinBorder);

        $sheet->getStyle('K'.($startRow + 2).':P'.($startRow + 2))->applyFromArray($centerAlign);

        $sheet->getStyle('A'.($startRow + 3).':F'.($startRow + 3))->applyFromArray($centerAlign);
        $sheet->getStyle('K'.($startRow + 3).':P'.($startRow + 3))->applyFromArray($centerAlign)->applyFromArray($thinBorder);

        $sheet->getStyle('A'.($startRow + 4))->applyFromArray($smallFont);
        $sheet->getStyle('A'.($startRow + 4).':I'.($startRow + 4))->applyFromArray($bottomBorder);
        $sheet->getStyle('C'.($startRow + 4).':K'.($startRow + 4))->applyFromArray($centerAlign);
        $sheet->getStyle('L'.($startRow + 4).':P'.($startRow + 4))->applyFromArray($smallFont)->applyFromArray($centerAlign);

        $sheet->getStyle('K'.($startRow + 5).':P'.($startRow + 5))->applyFromArray($thinBorder);

        $sheet->getRowDimension($startRow + 6)->setRowHeight(25);
        $sheet->getStyle('A'.($startRow + 6))->applyFromArray($smallFont);
        $sheet->getStyle('A'.($startRow + 6).':P'.($startRow + 13))->applyFromArray($verticalCenter);

        $sheet->getStyle('A'.($startRow + 7))->applyFromArray($thinBorder);
        $sheet->getStyle('B'.($startRow + 7))->applyFromArray($centerAlign)->applyFromArray($thinBorder);
        $sheet->getStyle('C'.($startRow + 7).':D'.($startRow + 7))->applyFromArray($thinBorder);
        $sheet->getStyle('E'.($startRow + 7).':H'.($startRow + 7))->applyFromArray($boldishFont)->applyFromArray($thinBorder);
        $sheet->getStyle('I'.($startRow + 7).':J'.($startRow + 7))->applyFromArray($thinBorder);
        $sheet->getStyle('K'.($startRow + 7))->applyFromArray($boldFont);
        $sheet->getStyle('L'.($startRow + 7).':P'.($startRow + 7))->applyFromArray($smallFont)->applyFromArray($centerAlign);

        $sheet->getRowDimension($startRow + 8)->setRowHeight(25);
        $sheet->getStyle('A'.($startRow + 8).':B'.($startRow + 8))->applyFromArray($smallFont)->applyFromArray($boldishFont)->getAlignment()->setWrapText(true);
        $sheet->getStyle('C'.($startRow + 8).':J'.($startRow + 8))->applyFromArray($centerAlign);
        $sheet->getStyle('K'.($startRow + 8).':P'.($startRow + 8))->applyFromArray($thinBorder);

        $sheet->getRowDimension($startRow + 9)->setRowHeight(30);
        $sheet->getStyle('A'.($startRow + 9).':C'.($startRow + 9))->applyFromArray($thinBorder);
        $sheet->getStyle('D'.($startRow + 9).':P'.($startRow + 9))->applyFromArray($thinBorder);

        $sheet->getStyle('A'.($startRow + 10).':B'.($startRow + 10))->applyFromArray($thinBorder);
        $sheet->getStyle('C'.($startRow + 10).':P'.($startRow + 10))->applyFromArray($thinBorder);

        $sheet->getStyle('A'.($startRow + 11).':G'.($startRow + 11))->applyFromArray($bottomBorder);

        $sheet->getRowDimension($startRow + 12)->setRowHeight(25);
        $sheet->getRowDimension($startRow + 13)->setRowHeight(25);
        $sheet->getStyle('A'.($startRow + 12).':B'.($startRow + 13))->applyFromArray($smallFont)->applyFromArray($centerAlign)->getAlignment()->setWrapText(true);
        $sheet->getStyle('F'.($startRow + 12).':G'.($startRow + 12))->applyFromArray($smallFont);
        $sheet->getStyle('K'.($startRow + 12))->applyFromArray($smallFont);

        $sheet->getStyle('A'.($startRow).':P'.($startRow + 13))->applyFromArray($outlineBorder);
    }
}

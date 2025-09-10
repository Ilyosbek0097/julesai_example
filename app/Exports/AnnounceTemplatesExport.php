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

                // New calculation: 14 rows for E'lon, 14 for Kvitansiya, 1 for page break div
                $rowsPerRecord = 29;

                for ($i = 0; $i < $recordCount; $i++) {
                    $recordStartRow = ($i * $rowsPerRecord) + 1;

                    // --- E'lon Section ---
                    $elonStart = $recordStartRow;
                    $this->applySectionStyles($sheet, $elonStart);

                    // --- Kvitansiya Section ---
                    $kvitansiyaStart = $elonStart + 14; // Starts immediately after E'lon's 14 rows
                    $this->applySectionStyles($sheet, $kvitansiyaStart);

                    // Apply specific bold style for "MO'" in Kvitansiya
                    $boldFont = ['font' => ['bold' => true]];
                    $sheet->getStyle('A'.($kvitansiyaStart + 12).':B'.($kvitansiyaStart + 13))->applyFromArray($boldFont);
                }
            },
        ];
    }

    private function applySectionStyles(Worksheet $sheet, int $startRow)
    {
        $centerAlign = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
        $sheet->getStyle('A'.$startRow.':P'.($startRow + 13))->applyFromArray($centerAlign);

        $smallFont = ['font' => ['size' => 9]];
        $boldFont = ['font' => ['bold' => true]];
        $boldishFont = ['font' => ['bold' => true, 'size' => 10]];
        $thinBorder = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
        $bottomBorder = ['borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]]];
        $outlineBorder = ['borders' => ['outline' => ['borderStyle' => Border::BORDER_THICK]]];

        $sheet->getStyle('A'.($startRow + 1))->applyFromArray($smallFont);
        $sheet->getStyle('F'.($startRow + 1).':H'.($startRow + 1))->applyFromArray($boldFont);
        $sheet->getStyle('I'.($startRow + 1).':J'.($startRow + 1))->applyFromArray($thinBorder);
        $sheet->getStyle('L'.($startRow + 1).':P'.($startRow + 1))->applyFromArray($thinBorder);

        $sheet->getStyle('K'.($startRow + 3).':P'.($startRow + 3))->applyFromArray($thinBorder);

        $sheet->getStyle('A'.($startRow + 4))->applyFromArray($smallFont);
        $sheet->getStyle('A'.($startRow + 4).':I'.($startRow + 4))->applyFromArray($bottomBorder);

        $sheet->getStyle('K'.($startRow + 5).':P'.($startRow + 5))->applyFromArray($thinBorder);

        $sheet->getRowDimension($startRow + 6)->setRowHeight(25);
        $sheet->getStyle('A'.($startRow + 6))->applyFromArray($smallFont);

        $sheet->getStyle('A'.($startRow + 7))->applyFromArray($thinBorder);
        $sheet->getStyle('B'.($startRow + 7))->applyFromArray($thinBorder);
        $sheet->getStyle('C'.($startRow + 7).':D'.($startRow + 7))->applyFromArray($thinBorder);
        $sheet->getStyle('E'.($startRow + 7).':H'.($startRow + 7))->applyFromArray($boldishFont)->applyFromArray($thinBorder);
        $sheet->getStyle('I'.($startRow + 7).':J'.($startRow + 7))->applyFromArray($thinBorder);
        $sheet->getStyle('K'.($startRow + 7))->applyFromArray($boldFont);
        $sheet->getStyle('L'.($startRow + 7).':P'.($startRow + 7))->applyFromArray($smallFont);

        $sheet->getRowDimension($startRow + 8)->setRowHeight(30);
        $sheet->getStyle('A'.($startRow + 8).':B'.($startRow + 8))->applyFromArray($smallFont)->applyFromArray($boldishFont)->getAlignment()->setWrapText(true);
        $sheet->getStyle('K'.($startRow + 8).':P'.($startRow + 8))->applyFromArray($thinBorder);

        $sheet->getRowDimension($startRow + 9)->setRowHeight(30);
        $sheet->getStyle('A'.($startRow + 9).':C'.($startRow + 9))->applyFromArray($thinBorder);
        $sheet->getStyle('D'.($startRow + 9).':P'.($startRow + 9))->applyFromArray($thinBorder);

        $sheet->getStyle('A'.($startRow + 10).':B'.($startRow + 10))->applyFromArray($thinBorder);
        $sheet->getStyle('C'.($startRow + 10).':P'.($startRow + 10))->applyFromArray($thinBorder);

        $sheet->getStyle('A'.($startRow + 11).':G'.($startRow + 11))->applyFromArray($bottomBorder);

        $sheet->getRowDimension($startRow + 12)->setRowHeight(30);
        $sheet->getRowDimension($startRow + 13)->setRowHeight(30);
        $sheet->getStyle('A'.($startRow + 12).':B'.($startRow + 13))->applyFromArray($smallFont)->getAlignment()->setWrapText(true);
        $sheet->getStyle('F'.($startRow + 12).':G'.($startRow + 12))->applyFromArray($smallFont);
        $sheet->getStyle('K'.($startRow + 12))->applyFromArray($smallFont);

        $sheet->getStyle('A'.($startRow).':P'.($startRow + 13))->applyFromArray($outlineBorder);
    }
}

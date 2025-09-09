<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnnounceTemplatesExport implements FromView, WithColumnWidths, WithEvents
{
    protected $templates;

    // Precise row counts for each section
    protected const ELON_ROWS = 16;
    protected const KVITANSIYA_ROWS = 15;
    protected const ORDER_ROWS = 15;
    protected const SPACER_ROWS = 1;
    protected const MARGIN_BOTTOM = 2; // User requested margin between records

    // Correct total height for one record's entire block including margin
    protected const TOTAL_BLOCK_HEIGHT = self::ELON_ROWS + self::SPACER_ROWS + self::KVITANSIYA_ROWS + self::SPACER_ROWS + self::ORDER_ROWS + self::MARGIN_BOTTOM;

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
        return [
            'A' => 15,
            'B' => 30,
            'C' => 15,
            'D' => 30,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $recordCount = count($this->templates);

                // Define styles for reuse
                $headerStyle = [
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ];
                $subHeaderStyle = [
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ];
                $labelStyle = [
                    'font' => ['bold' => true],
                ];
                $valueStyle = [
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'wrapText' => true],
                ];
                $amountStyle = [
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ];
                $bottomBorderStyle = [
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]],
                ];

                for ($i = 0; $i < $recordCount; $i++) {
                    $startRow = ($i * self::TOTAL_BLOCK_HEIGHT) + 1;

                    // Helper function to apply styles to a section
                    $applySectionStyles = function(int $sectionStart, int $sectionHeight) use ($sheet, $headerStyle, $subHeaderStyle, $labelStyle, $valueStyle, $amountStyle, $bottomBorderStyle) {
                        // Headers
                        $sheet->getStyle("A{$sectionStart}")->applyFromArray($headerStyle);
                        if ($sectionHeight === self::ELON_ROWS) { // Only E'lon has sub-header
                             $sheet->getStyle("A".($sectionStart + 1))->applyFromArray($subHeaderStyle);
                        }

                        // Labels
                        $sheet->getStyle("A".($sectionStart + 2))->applyFromArray($labelStyle);
                        $sheet->getStyle("A".($sectionStart + 3))->applyFromArray($labelStyle);
                        $sheet->getStyle("C".($sectionStart + 3))->applyFromArray($labelStyle);
                        $sheet->getStyle("A".($sectionStart + 5))->applyFromArray($labelStyle);
                        $sheet->getStyle("A".($sectionStart + 6))->applyFromArray($labelStyle);
                        $sheet->getStyle("A".($sectionStart + 7))->applyFromArray($labelStyle);
                        $sheet->getStyle("A".($sectionStart + 9))->applyFromArray($labelStyle);
                        $sheet->getStyle("A".($sectionStart + 10))->applyFromArray($labelStyle);
                        $sheet->getStyle("A".($sectionStart + 12))->applyFromArray($labelStyle);
                        $sheet->getStyle("A".($sectionStart + 13))->applyFromArray($labelStyle);
                        $sheet->getStyle("A".($sectionStart + 15))->applyFromArray($labelStyle);
                        $sheet->getStyle("C".($sectionStart + 15))->applyFromArray($labelStyle);

                        // Values with bottom borders
                        $sheet->getStyle("C".($sectionStart + 2).":D".($sectionStart + 2))->applyFromArray($bottomBorderStyle);
                        $sheet->getStyle("B".($sectionStart + 3))->applyFromArray($bottomBorderStyle);
                        $sheet->getStyle("D".($sectionStart + 3))->applyFromArray($bottomBorderStyle);
                        $sheet->getStyle("C".($sectionStart + 5).":D".($sectionStart + 5))->applyFromArray($bottomBorderStyle);
                        $sheet->getStyle("C".($sectionStart + 6).":D".($sectionStart + 6))->applyFromArray($bottomBorderStyle);

                        // FIX: Separated the chained calls
                        $maqsadStyle = $sheet->getStyle("C".($sectionStart + 7).":D".($sectionStart + 7));
                        $maqsadStyle->applyFromArray($valueStyle);
                        $maqsadStyle->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

                        $sheet->getStyle("C".($sectionStart + 9).":D".($sectionStart + 9))->applyFromArray($bottomBorderStyle);
                        $sheet->getStyle("C".($sectionStart + 10).":D".($sectionStart + 10))->applyFromArray($bottomBorderStyle);
                        $sheet->getStyle("B".($sectionStart + 12).":D".($sectionStart + 12))->applyFromArray($bottomBorderStyle);

                        // FIX: Separated the chained calls
                        $summaStyle = $sheet->getStyle("B".($sectionStart + 13).":D".($sectionStart + 13));
                        $summaStyle->applyFromArray($amountStyle);
                        $summaStyle->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

                        $sheet->getStyle("B".($sectionStart + 15))->applyFromArray($bottomBorderStyle);
                        $sheet->getStyle("D".($sectionStart + 15))->applyFromArray($bottomBorderStyle);

                        // Set row heights
                        for ($r = 0; $r < $sectionHeight; $r++) {
                            $sheet->getRowDimension($sectionStart + $r)->setRowHeight(20);
                        }
                    };

                    // Apply styles to each section
                    $elonStart = $startRow;
                    $applySectionStyles($elonStart, self::ELON_ROWS);

                    $kvitansiyaStart = $elonStart + self::ELON_ROWS + self::SPACER_ROWS;
                    $applySectionStyles($kvitansiyaStart, self::KVITANSIYA_ROWS);

                    $orderStart = $kvitansiyaStart + self::KVITANSIYA_ROWS + self::SPACER_ROWS;
                    $applySectionStyles($orderStart, self::ORDER_ROWS);
                }
            },
        ];
    }
}

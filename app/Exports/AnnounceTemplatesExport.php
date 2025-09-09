<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnnounceTemplatesExport implements FromView, WithColumnWidths, WithEvents
{
    protected $templates;

    protected const ELON_ROWS = 16;
    protected const KVITANSIYA_ROWS = 15;
    protected const ORDER_ROWS = 15;
    protected const SPACER_ROWS = 1;
    protected const MARGIN_BOTTOM = 2;
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

                $headerStyle = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
                $subHeaderStyle = ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
                $labelStyle = ['font' => ['bold' => true, 'size' => 10], 'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]];
                $valueStyle = ['font' => ['size' => 10], 'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]];
                $amountStyle = ['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER]];
                $bottomBorderStyle = ['borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];

                for ($i = 0; $i < $recordCount; $i++) {
                    $startRow = ($i * self::TOTAL_BLOCK_HEIGHT) + 1;

                    $applyStyles = function($sectionStart, $isElon = false) use ($sheet, $headerStyle, $subHeaderStyle, $labelStyle, $valueStyle, $amountStyle, $bottomBorderStyle) {
                        // Reset all borders first to remove table borders from view
                        $endRow = $sectionStart + ($isElon ? self::ELON_ROWS : self::KVITANSIYA_ROWS) -1;
                        $sheet->getStyle("A{$sectionStart}:D{$endRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);

                        // Headers
                        $sheet->getStyle("A{$sectionStart}")->applyFromArray($headerStyle);
                        if ($isElon) {
                            $sheet->getStyle("A".($sectionStart + 1))->applyFromArray($subHeaderStyle);
                        }

                        // Set Row Heights and apply styles row-by-row
                        $sheet->getRowDimension($sectionStart)->setRowHeight(22);
                        $sheet->getRowDimension($sectionStart + 1)->setRowHeight(20);

                        // Bank Nomi
                        $sheet->getRowDimension($sectionStart + 2)->setRowHeight(20);
                        $sheet->getStyle("A".($sectionStart + 2))->applyFromArray($labelStyle);
                        $sheet->getStyle("C".($sectionStart + 2).":D".($sectionStart + 2))->applyFromArray($bottomBorderStyle);

                        // Sana & Hujjat
                        $sheet->getRowDimension($sectionStart + 3)->setRowHeight(20);
                        $sheet->getStyle("A".($sectionStart + 3))->applyFromArray($labelStyle);
                        $sheet->getStyle("B".($sectionStart + 3))->applyFromArray($bottomBorderStyle);
                        $sheet->getStyle("C".($sectionStart + 3))->applyFromArray($labelStyle);
                        $sheet->getStyle("D".($sectionStart + 3))->applyFromArray($bottomBorderStyle);

                        // Spacer row
                        $sheet->getRowDimension($sectionStart + 4)->setRowHeight(10);

                        // To'lovchi
                        $sheet->getRowDimension($sectionStart + 5)->setRowHeight(20);
                        $sheet->getStyle("A".($sectionStart + 5).":B".($sectionStart + 5))->applyFromArray($labelStyle);
                        $sheet->getStyle("C".($sectionStart + 5).":D".($sectionStart + 5))->applyFromArray($bottomBorderStyle);

                        // To'lovchi hisobi
                        $sheet->getRowDimension($sectionStart + 6)->setRowHeight(20);
                        $sheet->getStyle("A".($sectionStart + 6).":B".($sectionStart + 6))->applyFromArray($labelStyle);
                        $sheet->getStyle("C".($sectionStart + 6).":D".($sectionStart + 6))->applyFromArray($bottomBorderStyle);

                        // Maqsad
                        $sheet->getRowDimension($sectionStart + 7)->setRowHeight(20);
                        $sheet->getStyle("A".($sectionStart + 7).":B".($sectionStart + 7))->applyFromArray($labelStyle);
                        $sheet->getStyle("C".($sectionStart + 7).":D".($sectionStart + 7))->applyFromArray($valueStyle)->getStyle()->applyFromArray($bottomBorderStyle);

                        // Spacer row
                        $sheet->getRowDimension($sectionStart + 8)->setRowHeight(10);

                        // Oluvchi & hisobi
                        $sheet->getRowDimension($sectionStart + 9)->setRowHeight(20);
                        $sheet->getStyle("A".($sectionStart + 9).":B".($sectionStart + 9))->applyFromArray($labelStyle);
                        $sheet->getStyle("C".($sectionStart + 9).":D".($sectionStart + 9))->applyFromArray($bottomBorderStyle);
                        $sheet->getRowDimension($sectionStart + 10)->setRowHeight(20);
                        $sheet->getStyle("A".($sectionStart + 10).":B".($sectionStart + 10))->applyFromArray($labelStyle);
                        $sheet->getStyle("C".($sectionStart + 10).":D".($sectionStart + 10))->applyFromArray($bottomBorderStyle);

                        // Spacer row
                        $sheet->getRowDimension($sectionStart + 11)->setRowHeight(10);

                        // Summa
                        $sheet->getRowDimension($sectionStart + 12)->setRowHeight(20);
                        $sheet->getStyle("A".($sectionStart + 12))->applyFromArray($labelStyle);
                        $sheet->getStyle("B".($sectionStart + 12).":D".($sectionStart + 12))->applyFromArray($bottomBorderStyle);
                        $sheet->getRowDimension($sectionStart + 13)->setRowHeight(20);
                        $sheet->getStyle("A".($sectionStart + 13))->applyFromArray($labelStyle);
                        $sheet->getStyle("B".($sectionStart + 13).":D".($sectionStart + 13))->applyFromArray($amountStyle)->getStyle()->applyFromArray($bottomBorderStyle);

                        // Spacer row
                        $sheet->getRowDimension($sectionStart + 14)->setRowHeight(10);

                        // Signatures
                        $sheet->getRowDimension($sectionStart + 15)->setRowHeight(20);
                        $sheet->getStyle("A".($sectionStart + 15))->applyFromArray($labelStyle);
                        $sheet->getStyle("B".($sectionStart + 15))->applyFromArray($bottomBorderStyle);
                        $sheet->getStyle("C".($sectionStart + 15))->applyFromArray($labelStyle);
                        $sheet->getStyle("D".($sectionStart + 15))->applyFromArray($bottomBorderStyle);
                    };

                    $elonStart = $startRow;
                    $applyStyles($elonStart, true);

                    $kvitansiyaStart = $elonStart + self::ELON_ROWS + self::SPACER_ROWS;
                    $applyStyles($kvitansiyaStart);

                    $orderStart = $kvitansiyaStart + self::KVITANSIYA_ROWS + self::SPACER_ROWS;
                    $applyStyles($orderStart);
                }
            },
        ];
    }
}

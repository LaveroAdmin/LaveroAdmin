<?php

namespace LaveroAdmin\Admin\Grid\Exporters;

use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PhpSpreadsheetExporter extends AbstractExporter
{
    /**
     * @var string
     */
    protected $fileName;

    /**
     * Get or set export filename.
     *
     * @param string|null $name
     * @return $this|string
     */
    public function fileName(?string $name = null)
    {
        if (is_null($name)) {
            $table = $this->getTable();
            return $this->fileName ?: $table.'-'.date('Y-m-d_H-i-s').'.xlsx';
        }

        $this->fileName = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        if (!class_exists(Spreadsheet::class)) {
            throw new \RuntimeException(
                'PhpSpreadsheet is not installed. Please run "composer require phpoffice/phpspreadsheet" to export to Excel format.'
            );
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(Str::limit($this->getTable(), 30, ''));

        // Prepare visible columns and their labels
        $titles = [];
        $columns = [];
        foreach ($this->grid->visibleColumns() as $column) {
            $name = $column->getName();
            if (in_array($name, ['__actions__', '__row_selector__'])) {
                continue;
            }
            $columns[] = $name;
            $titles[] = strip_tags($column->getLabel());
        }

        if (empty($titles)) {
            $titles = ['ID'];
            $columns = ['id'];
        }

        // Set Headers (Row 1)
        $colIndex = 1;
        foreach ($titles as $title) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValueExplicit("{$colLetter}1", $title, DataType::TYPE_STRING);
            $colIndex++;
        }

        $lastColLetter = Coordinate::stringFromColumnIndex(count($titles));

        // Header Styling
        $headerRange = "A1:{$lastColLetter}1";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => '12161F'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FAFF69'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Fill Data Rows
        $rowIndex = 2;
        $this->chunk(function ($collection) use ($sheet, $columns, &$rowIndex) {
            foreach ($collection as $model) {
                $colIndex = 1;
                foreach ($columns as $name) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                    $val = data_get($model, $name);

                    if (is_array($val) || is_object($val)) {
                        $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                    }

                    if (is_numeric($val) && !str_starts_with((string)$val, '0')) {
                        $sheet->setCellValue("{$colLetter}{$rowIndex}", $val);
                    } else {
                        $sheet->setCellValueExplicit("{$colLetter}{$rowIndex}", (string)$val, DataType::TYPE_STRING);
                    }

                    $colIndex++;
                }
                $rowIndex++;
            }
        });

        // Auto-fit column widths
        for ($i = 1; $i <= count($titles); $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Borders
        $totalRows = max(1, $rowIndex - 1);
        $sheet->getStyle("A1:{$lastColLetter}{$totalRows}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'E2E8F0'],
                ],
            ],
        ]);

        $fileName = $this->fileName();

        // Send download headers
        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}

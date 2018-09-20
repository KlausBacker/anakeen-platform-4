<?php
namespace Anakeen\Components\Grid\Routes;

use Anakeen\Core\Utils\FileMime;
use Anakeen\Router\Exception;

require_once "vendor/Anakeen/Ui/PhpLib/vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GridExport extends GridContent {

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        parent::__invoke($request, $response, $args);
        $data = $this->getData();
        $spreadSheet = new Spreadsheet();
        $sheet = $spreadSheet->getActiveSheet();

        $this->writeHeaders($sheet);
        $this->writeValues($sheet, $data["smartElements"]);
        $this->writeFile($spreadSheet);


        return self::withFile($response, "./export.xlsx", "export.xlsx");
    }

    private function writeHeaders(Worksheet $sheet) {
        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_PATTERN_DARKGRAY,
            ],
        ];

        $column = "A";
        for ($i = 0; $i < count($this->returnFields); $i++) {
            $sheet->getStyle($column."1")->applyFromArray($styleArray);
            $sheet->getColumnDimension($column++)->setAutoSize('true');
        }
        $sheet->fromArray(array_map(function ($item) {
            return explode(".", $item)[2];
        }, $this->returnFields), NULL, "A1");
    }

    private function writeValues(Worksheet $sheet, $values) {
        $i = 2;
        $defaultHeight = 20.0;
        foreach ($values as $datum) {
            $maxHeightCoeff = 1;
            $row = [];
            foreach ($this->returnFields as $field) {
                $tokens = explode(".", $field);
                $fieldType = $tokens[1];
                $fieldId = $tokens[2];
                if ($fieldType === "attributes") {
                    if (is_object($datum[$fieldType][$fieldId])) {
                        $row[] = $datum[$fieldType][$fieldId]->value;
                    } else if (is_array($datum[$fieldType][$fieldId])) {
                        if (count($datum[$fieldType][$fieldId]) > $maxHeightCoeff) {
                            $maxHeightCoeff = count($datum[$fieldType][$fieldId]);
                        }
                        $row[] = implode("\n", array_map(function ($item) {
                            return $item->value;
                        },$datum[$fieldType][$fieldId]));
                    }
                } else {
                    $row[] = $datum[$fieldType][$fieldId];
                }
            }
            $sheet->getRowDimension($i)->setRowHeight(($maxHeightCoeff * $defaultHeight));
            $sheet->fromArray($row, NULL, "A".$i++);
        }
    }

    private function writeFile(Spreadsheet $spreadsheet) {
        $writer = new Xlsx($spreadsheet);
        $writer->save('export.xlsx');
    }

    private static function withFile(
        \Slim\Http\response $response,
        $filePath,
        $fileName = "",
        $mime = ""
    ) {
        if (!$fileName) {
            $fileName = basename($filePath);
        }
        if (!file_exists($filePath)) {
            throw new Exception("GRID0003", basename($filePath));
        }
        if (!$mime) {
            $mime = FileMime::getSysMimeFile(realpath($filePath), $fileName);
        }

        if ($mime) {
            $response = $response->withHeader("Content-type", $mime);
        }

        return $response->write(file_get_contents($filePath));
    }


}
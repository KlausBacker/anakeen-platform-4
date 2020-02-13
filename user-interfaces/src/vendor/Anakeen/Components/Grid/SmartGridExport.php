<?php

namespace Anakeen\Components\Grid;

require_once "vendor/Anakeen/Ui/PhpLib/vendor/autoload.php";
use Anakeen\Core\ContextManager;
use Anakeen\Core\Utils\FileMime;
use Anakeen\Router\Exception;
use Anakeen\Routes\Ui\Transaction\TransactionManager;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class GridExport
 *
 * @package Anakeen\Components\Grid\Routes
 * @note    use by route /api/v2/grid/export/{collectionId}
 */
class SmartGridExport
{
    public $clientColumnsConfig = [];
    public $selectedRows = [];
    public $unselectedRows = [];
    protected $returnFields = [];
    public $transactionId;
    public $onlySelect = false;

    public function doExport(\Slim\Http\response $response, $data)
    {
        $spreadSheet = new Spreadsheet();
        $sheet = $spreadSheet->getActiveSheet();

        $this->writeHeaders($sheet);
        $this->writeValues($sheet, $data["content"]);
        $filename = $this->writeFile($spreadSheet);
        return self::withFile($response, $filename);
    }

    private function writeHeaders(Worksheet $sheet)
    {
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
        for ($i = 0; $i < count($this->clientColumnsConfig); $i++) {
            if (!isset($this->clientColumnsConfig[$i]["abstract"]) || $this->clientColumnsConfig[$i]["abstract"] === false) {
                $sheet->getStyle($column . "1")->applyFromArray($styleArray);
                $sheet->getColumnDimension($column++)->setAutoSize('true');
            }
        }
        $columnsNames = array_map(function ($config) {
            if (!isset($config["abstract"]) || $config["abstract"] === false) {
                return isset($config["title"]) ? $config["title"] : $config["field"];
            } else {
                return "";
            }
        }, $this->clientColumnsConfig);

        $sheet->fromArray($columnsNames, null, "A1");
    }

    private function getFieldConfig($fieldId)
    {
        if (!empty($this->clientColumnsConfig)) {
            $fieldConfig = array_filter($this->clientColumnsConfig, function ($config) use ($fieldId) {
                return $config["field"] === $fieldId;
            });
        }
        if (!empty($fieldConfig) && count($fieldConfig) > 0) {
            return array_shift($fieldConfig);
        }
        return null;
    }

    private function writeValues(Worksheet $sheet, $values)
    {
        $rowIndex = 2;
        $defaultHeight = 20.0;
        $totalValues = count($values);
        $modulo = intval($totalValues / 100) || 1;
        \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder());
        TransactionManager::updateProgression($this->transactionId, [
            "exportedRows" => 0,
            "totalRows" => $totalValues
        ]);
        foreach ($values as $datum) {
            $maxHeightCoeff = 1;
            $row = [];
            $columnIndex = "A";
            if (!$this->onlySelect || in_array($datum["properties"]["id"], $this->selectedRows)) {
                foreach ($this->clientColumnsConfig as $field) {
                    $fieldType = isset($field["property"]) ? "properties" : "attributes";
                    $fieldId = $field["field"];
                    $fieldConfig = $this->getFieldConfig($fieldId);
                    if ($fieldType === "attributes" && !isset($field["abstract"])) {
                        if (is_object($datum[$fieldType][$fieldId])) {
                            $row[] = $this->getCellFieldValue($datum[$fieldType][$fieldId], $fieldConfig);
                        } elseif (is_array($datum[$fieldType][$fieldId])) {
                            if (count($datum[$fieldType][$fieldId]) > $maxHeightCoeff) {
                                $maxHeightCoeff = count($datum[$fieldType][$fieldId]);
                            }
                            $row[] = implode("\n", array_map(function ($item) use ($fieldConfig) {
                                return $this->getCellFieldValue($item, $fieldConfig);
                            }, $datum[$fieldType][$fieldId]));
                        }
                    } elseif ($fieldType === "properties" && !isset($field["abstract"])) {
                        $row[] = $this->getCellPropertyValue($datum[$fieldType][$fieldId], $fieldId);
                    }
                    $this->setCellFormat(
                        $sheet,
                        $columnIndex . $rowIndex,
                        $fieldConfig["smartType"]
                    );
                    $columnIndex++;
                }
                $sheet->getRowDimension($rowIndex)->setRowHeight(($maxHeightCoeff * $defaultHeight));
                $sheet->fromArray($row, null, "A" . $rowIndex++);

                if (($rowIndex - 2) % $modulo === 0) {
                    TransactionManager::updateProgression($this->transactionId, [
                        "exportedRows" => $rowIndex - 2,
                        "totalRows" => $totalValues
                    ]);
                }
            }
        }
    }

    private function getCellPropertyValue($data, $propId)
    {
        if (is_array($data)) {
            switch ($propId) {
                case "cdate":
                case "mdate":
                    return $data["value"];
                default:
                    return $data["displayValue"];
            }
        }
        if (is_a($data, \Anakeen\Core\Internal\Format\StatePropertyValue::class)) {
            return $data->displayValue;
        }
        return $data;
    }

    private function getCellFieldValue($data, $dataConfig)
    {
        if (isset($dataConfig["smartType"])) {
            switch ($dataConfig["smartType"]) {
                case "date":
                case "int":
                case "double":
                case "money":
                    return $data->value;
                    break;
                default:
                    return $data->displayValue;
            }
        } else {
            return isset($data->displayValue) ? $data->displayValue : $data->value;
        }
    }

    private function setCellFormat(Worksheet $sheet, $cellCoordinate, $format)
    {
        switch ($format) {
            case "date":
                $sheet->getStyle($cellCoordinate)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);
                break;
            case "int":
            case "double":
            case "money":
                break;
        }
    }


    private function writeFile(Spreadsheet $spreadsheet)
    {
        $outputFileName = sprintf("%s/%s.xlsx", ContextManager::getTmpDir(), uniqid("export"));
        $writer = new Xlsx($spreadsheet);
        $writer->save($outputFileName);
        return $outputFileName;
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

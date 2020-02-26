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

class SmartGridExport
{
    public $clientColumnsConfig = [];
    public $selectedRows = [];
    public $unselectedRows = [];
    protected $returnFields = [];
    public $transactionId;
    public $onlySelect = false;

    /**
     * @param \Slim\Http\response $response
     * @param $data
     * @return \Slim\Http\Response
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function doExport(\Slim\Http\response $response, $data)
    {
        $spreadSheet = new Spreadsheet();
        $sheet = $spreadSheet->getActiveSheet();

        $this->writeHeaders($sheet);
        $this->writeValues($sheet, $data["content"]);
        $filename = $this->writeFile($spreadSheet);
        return self::withFile($response, $filename);
    }

    /**
     * @param Worksheet $sheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
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

    /**
     * @param $fieldId
     * @return mixed|null
     */
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

    /**
     * @param Worksheet $sheet
     * @param $values
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
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

    /**
     * @param $data
     * @param $propId
     * @return mixed
     */
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

    /**
     * @param $data
     * @param $dataConfig
     * @return mixed
     */
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
                    if (is_array($data)) {
                        foreach ($data as $datum) {
                            return isset($datum->displayValue) ? $datum->displayValue : $datum->value;
                        }
                    }
                    return isset($data->displayValue) ? $data->displayValue : $data->value;
            }
        } else {
            return isset($data->displayValue) ? $data->displayValue : $data->value;
        }
    }

    /**
     * @param Worksheet $sheet
     * @param $cellCoordinate
     * @param $format
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
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


    /**
     * @param Spreadsheet $spreadsheet
     * @return string
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    private function writeFile(Spreadsheet $spreadsheet)
    {
        $outputFileName = sprintf("%s/%s.xlsx", ContextManager::getTmpDir(), uniqid("export"));
        $writer = new Xlsx($spreadsheet);
        $writer->save($outputFileName);
        return $outputFileName;
    }

    /**
     * @param \Slim\Http\response $response
     * @param $filePath - path to the writable file
     * @param string $fileName - name of the file to return
     * @param string $mime
     * @return \Slim\Http\Response
     * @throws Exception
     */
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

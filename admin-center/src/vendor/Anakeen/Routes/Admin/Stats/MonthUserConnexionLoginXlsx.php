<?php

namespace Anakeen\Routes\Admin\Stats;

require_once "vendor/Anakeen/Ui/PhpLib/vendor/autoload.php";

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\StatLogConnectionManager;
use Anakeen\Core\Settings;
use Anakeen\Router\ApiV2Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/*
 * @note used by GET /api/v2/stats/connexions/login/months/{from}/{to}.xlsx
 */

class MonthUserConnexionLoginXlsx
{
    /**  @var string */
    protected $fromDate;
    /** @var string */
    protected $toDate;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        $file = $this->doRequest();
        $filename = sprintf(___("active users %s %s", "adminstats").".xlsx", $this->fromDate, $this->toDate);
        $response = ApiV2Response::withFile($response, $file, $filename);
        return $response;
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        if (!empty($args["from"])) {
            $this->fromDate = $args["from"] . "-01";
        }
        if (!empty($args["to"])) {
            $this->toDate = $args["to"] . "-01";
        }
    }

    protected function doRequest()
    {


        return $this->exportXlsx();
    }


    protected function exportXlsx()
    {
        $logins = StatLogConnectionManager::getMonthsLogin($this->fromDate, $this->toDate);
        $spreadSheet = new Spreadsheet();
        $sheet = $spreadSheet->getActiveSheet();
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
        $cellTitle = sprintf(
            ___("%d active users : period from %s to %s", "adminstats"),
            count($logins),
            strftime("%B %Y", strtotime($this->fromDate)),
            strftime("%B %Y", strtotime($this->toDate))
        );
        $propTitle = sprintf(
            ___("Active users : %s to %s", "adminstats"),
            strftime("%B %Y", strtotime($this->fromDate)),
            strftime("%B %Y", strtotime($this->toDate))
        );
        $spreadSheet->getProperties()->setCompany("Anakeen");
        $spreadSheet->getProperties()->setSubject(ContextManager::getParameterValue(Settings::NsSde, "CORE_CLIENT"));
        $spreadSheet->getProperties()->setDescription($cellTitle);
        $sheet->setTitle(___("Active users", "adminstats"));
        $sheet->getStyle("A1")->applyFromArray($styleArray);

        $spreadSheet->getProperties()->setTitle($propTitle);
        $sheet->getCell("A1")->setValue(
            $cellTitle
        );
        $sheet->getColumnDimension("A")->setAutoSize('true');

        $c = 2;
        foreach ($logins as $login) {
            $sheet->getCell("A" . $c)->setValue($login);
            $c++;
        }

        $outputFileName = sprintf("%s/%s.xlsx", ContextManager::getTmpDir(), uniqid("export"));
        $writer = new Xlsx($spreadSheet);
        $writer->save($outputFileName);

        return $outputFileName;
    }
}

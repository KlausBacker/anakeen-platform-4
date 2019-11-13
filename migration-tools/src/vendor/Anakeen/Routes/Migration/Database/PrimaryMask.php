<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Search\SearchElements;
use SmartStructure\Fields\Mask as MaskField;
use SmartStructure\Fields\Cvdoc as CvField;

/**
 * Create primary mask based on default visibilities
 */
class PrimaryMask
{
    protected $structureName;
    /**
     * @var \Anakeen\Core\SmartStructure
     */
    protected $structure;


    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($args);
        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }

    protected function initParameters($args)
    {
        $this->structureName = $args["structure"];
        $this->structure = SEManager::getFamily($this->structureName);
        if (!$this->structure) {
            throw new Exception(sprintf("Structure \"%s\" not exists", $this->structureName));
        }
    }

    protected function doRequest()
    {
        $data = [];

        $sql = sprintf("select * from dynacase.docattr where docid=%d", $this->structure->id);
        DbManager::query($sql, $results);

        $name = sprintf("PRIMARYMASK_%s", $this->structure->name);
        /** @var \SmartStructure\Mask $mask */
        $mask = SEManager::getDocument($name);
        if (!$mask) {
            $mask = SEManager::createDocument("MASK");
        }
        $mask->setValue(MaskField::msk_famid, $this->structure->id);
        $mask->setValue(MaskField::ba_title, sprintf("Primary Mask for %s", $this->structure->getTitle()));
        $mask->clearArrayValues(MaskField::msk_t_contain);
        foreach ($results as $result) {
            $vis = $result["visibility"];
            $need = $result["needed"];
            if ($vis !== "W" || $need === "Y") {
                $mask->addArrayRow(
                    MaskField::msk_t_contain,
                    [
                        MaskField::msk_attrids => $result["id"],
                        MaskField::msk_visibilities => $vis,
                        MaskField::msk_needeeds => ($need === "Y" ? $need : "-")
                    ]
                );
            }
        }
        $mask->store();
        $mask->setLogicalName($name);

        $data["cvdoc"] = $this->setCVid($mask->id);
        return $data;
    }

    protected function setCVid($mskId)
    {
        $data = [];
        if (!$this->structure->ccvid) {
            $cv = SEManager::createDocument("CVDOC");
            $cv->setValue(CvField::ba_title, sprintf("Simple view for %s", $this->structure->getTitle()));
            $cv->setValue(CvField::ba_desc, sprintf("Only to add default mask"));

            $cv->setValue(CvField::cv_famid, $this->structure->id);
            $cv->store();
            $name = sprintf("CVMASK_%s", $this->structure->name);
            $cv->setLogicalName($name);

            $this->structure->ccvid = $cv->id;
            $this->structure->modify();

            DbManager::query(sprintf("update doc%d set cvid='%d' where cvid is null and fromid = %d", $this->structure->id, $this->structure->ccvid, $this->structure->id));
        }

        $searcCv = new SearchElements("CVDOC");
        $searcCv->addFilter("%s = '%d'", CvField::cv_famid, $this->structure->id);
        $cvList = $searcCv->getResults();
        foreach ($cvList as $cv) {
            $cv->setValue(CvField::cv_primarymask, $mskId);
            $cv->store();
            $data[] = ["id" => $cv->id, "title" => $cv->getTitle()];
        }
        return $data;
    }
}

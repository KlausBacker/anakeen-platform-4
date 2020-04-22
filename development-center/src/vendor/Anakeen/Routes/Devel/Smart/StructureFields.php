<?php

namespace Anakeen\Routes\Devel\Smart;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\Settings;
use Anakeen\Core\SmartStructure;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Router\URLUtils;
use Anakeen\Routes\Core\Lib\DocumentDataFormatter;

/**
 * Get Structure info
 *
 * @note Used by route : GET /api/v2/devel/smart/structures/{structure}/fields/
 */
class StructureFields
{
    protected $structureName = "";
    protected $sqlFilter = "(usefor != 'Q' or usefor is null)";

    /**
     * @var SmartStructure $structure
     */
    protected $structure = null;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters($args)
    {
        $this->structureName = $args["structure"];
        $this->structure = SEManager::getFamily($this->structureName);
        if (empty($this->structure)) {
            $exception = new Exception("DEV0101", $this->structureName);
            $exception->setHttpStatus(404, "Structure not found");
            throw $exception;
        }
    }

    public function doRequest()
    {
        $df = new DocumentDataFormatter($this->structure);
        $df->useDefaultProperties();
        $df->addProperty("profid");

        $data = $df->getData();

        $data["properties"]["parents"] = StructureInfo::getParents($this->structure);
        $data["fields"] = $this->getFieldsConfig($this->structure);
        $data["uri"] = URLUtils::generateURL(Settings::ApiV2 .
            sprintf(
                "devel/smart/structures/%s/fields/",
                $this->structureName
            ));
        return $data;
    }

    protected function getFieldsConfig(SmartStructure $family)
    {
        $fromids = $family->getFromDoc();

        $parents = $ancestrors = [];
        foreach ($fromids as $parentId) {
            $docParent = SEManager::getFamily($parentId);
            if ($docParent->id != $family->id) {
                $parents[$docParent->id] = array(
                    'id' => $docParent->id,
                    "name" => $docParent->name,
                    "title" => $docParent->getTitle(),
                    "icon" => $docParent->getIcon("", 16)
                );
                $ancestrors[$docParent->name] = $docParent;
            }
        }
        $parentStructure = null;
        if ($family->fromid) {
            $parentStructure = SEManager::getFamily($family->fromid);
        }

        $ancestrors = array_reverse($ancestrors, true);
        $sql = sprintf(
            "select * from docattr where docid in (%s) and {$this->sqlFilter} and type != 'menu' and id !~ '^:' order by ordered",
            implode(',', $fromids)
        );
        $dbAttrs = [];
        DbManager::query($sql, $dbAttrs);
        $sql = sprintf(
            "select * from docattr where docid in (%s) and {$this->sqlFilter} and id ~ '^:' order by ordered",
            implode(',', $fromids)
        );
        DbManager::query($sql, $dbModAttr);

        foreach ($dbAttrs as $k => $v) {
            $dbAttrs[$v["id"]] = $v;
            unset($dbAttrs[$k]);
        }
        $oDocAttr = new SmartStructure\DocAttr();
        $oAttrs = $family->getAttributes();
        $family->attributes->orderAttributes(true);

        $relativeOrder = 0;
        /**
         * @var SmartStructure\NormalAttribute $oa
         */
        foreach ($oAttrs as $oa) {
            if (!$oa) {
                continue;
            }
            if ($this->checkAttribute($oa) === false) {
                continue;
            }
            if ($oa->getOption("relativeOrder")) {
                $oa->ordered = $oa->getOption("relativeOrder");
                $oa->options = preg_replace("/(relativeOrder=[a-zA-Z0-9_:]+)/", "", $oa->options);
            }


            if ($oa->id === SmartStructure\Attributes::HIDDENFIELD) {
                continue;
            }


            $attrDatum = [
                "id" => $oa->id,
                "parentId" => ($oa->fieldSet && $oa->fieldSet->id !== SmartStructure\Attributes::HIDDENFIELD) ? $oa->fieldSet->id : null,
                "labeltext" => $oa->getLabel(),
                "type" => $oa->type,
                "format" => $oa->format,
                "ordered" => $oa->ordered,
                "accessibility" => SmartStructure\FieldAccessManager::getTextAccess($oa->access),
                "declaration" => $oa->getOption("autotitle") ? "bycode" : "static",
                "displayOrder" => $oa->ordered,
                "isAbstract" => $oa->isInAbstract,
                "isNeeded" => $oa->needed,
                "isTitle" => $oa->isInTitle,
                "link" => $oa->link,
                "optionValues" => $oa->getOptions(),
                "phpconstraint" => $oa->phpconstraint,
                "phpfile" => $oa->phpfile,

                "phpfunc" => $oa->phpfunc,
                "properties" => $oa->properties ? json_decode(json_encode($oa->properties), true) : null,
                "simpletype" => $oa->type,
                "structure" => SEManager::getNameFromId($oa->structureId),
            ];
            if (!empty($attrDatum["properties"]["autocomplete"])) {
                $attrDatum["autocomplete"] = $attrDatum["properties"]["autocomplete"];
                unset($attrDatum["properties"]["autocomplete"]);
            }
            if ($attrDatum["format"]) {
                $attrDatum["type"] .= "(\"" . $attrDatum["format"] . "\")";
            }
            unset($attrDatum["optionValues"]["relativeOrder"]);


            $attrData[] = $attrDatum;

            /*
             * @TODO overrides
                        $attrDatum["overrides"]["isAbstract"] = [
                            "before" => $dbAttr["overrides"]["abstract"]["before"] === "Y",
                            "after" => $dbAttr["overrides"]["abstract"]["after"] === "Y"
                        ];
                         */
        }
        return $attrData;
    }

    protected function checkAttribute(SmartStructure\BasicAttribute $oa)
    {
        return $oa->usefor !== "Q" && $oa->type !== "menu";
    }
}

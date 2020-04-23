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

        $attrData = [];
        $oAttrs = $family->getAttributes();
        $family->attributes->orderAttributes(true);

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
                "format" => null,
                "ordered" => $oa->ordered,
                "accessibility" => SmartStructure\FieldAccessManager::getTextAccess($oa->access),
                "declaration" => $oa->getOption("autotitle") ? "bycode" : "static",
                "displayOrder" => $oa->ordered,
                "isAbstract" => null,
                "isNeeded" => null,
                "isTitle" => null,
                "link" => null,
                "optionValues" => $oa->getOptions(),
                "phpconstraint" => null,
                "phpfile" => null,

                "phpfunc" => null,
                "properties" => $oa->properties ? json_decode(json_encode($oa->properties), true) : null,
                "simpletype" => $oa->type,
                "structure" => SEManager::getNameFromId($oa->structureId),
            ];
            if ($oa->isNormal) {
                $attrDatum  ["format"] = $oa->format;
                $attrDatum  ["isAbstract"] = $oa->isInAbstract;
                $attrDatum  ["isNeeded"] = $oa->needed;
                $attrDatum  ["isTitle"] = $oa->isInTitle;
                $attrDatum  ["link"] = $oa->link;
                $attrDatum  ["phpconstraint"] = $oa->phpconstraint;
                $attrDatum  ["phpfile"] = $oa->phpfile;
                $attrDatum  ["phpfunc"] = $oa->phpfunc;
            }

            if (!empty($attrDatum["properties"]["autocomplete"])) {
                $attrDatum["autocomplete"] = $attrDatum["properties"]["autocomplete"];
                unset($attrDatum["properties"]["autocomplete"]);
            }
            if ($attrDatum["format"]) {
                $attrDatum["type"] .= "(\"" . $attrDatum["format"] . "\")";
            }
            if (strlen($attrDatum["phpfunc"]) > 2) {
                $attrDatum["computed"] = $attrDatum["phpfunc"];
            }
            unset($attrDatum["optionValues"]["relativeOrder"]);


            $attrData[$oa->id] = $attrDatum;
        }

        /**
         * =================
         * Add override part
         */
        $sql = sprintf(
            "select * from docattr where docid = %d and {$this->sqlFilter} and id ~ '^:' order by ordered",
            $family->id
        );
        DbManager::query($sql, $dbModAttr);
        $sql = sprintf(
            "select * from docattr where docid in (%s) and {$this->sqlFilter} and type != 'menu' and id !~ '^:' order by docid",
            implode(',', $fromids)
        );
        DbManager::query($sql, $originAllAttr);
        $originValues = [];
        foreach ($originAllAttr as $originalAttr) {
            $originValues[$originalAttr["id"]] = $originalAttr;
        }
        foreach ($dbModAttr as $modAttrRow) {
            $fieldId = substr($modAttrRow["id"], 1);
            foreach ($modAttrRow as $col => $modValue) {
                if ($modValue) {
                    $attrData[$fieldId]["declaration"] = "overrided";
                    switch ($col) {
                        case "title":
                            $attrData[$fieldId]["overrides"]["isTitle"] = [];
                            break;
                        case "abstract":
                            $attrData[$fieldId]["overrides"]["isAbstract"] = [];
                            break;
                        case "needed":
                            $attrData[$fieldId]["overrides"]["isNeeded"] = [];
                            break;
                        case "frameid":
                            $attrData[$fieldId]["overrides"]["parentId"] = [
                                "before" => $originValues[$fieldId][$col],
                                "after" => $attrData[$fieldId]["parentId"]
                            ];
                            break;
                        case "phpfunc":
                            $attrData[$fieldId]["overrides"]["computed"] = [
                                "before" => $originValues[$fieldId][$col],
                                "after" => $attrData[$fieldId][$col]
                            ];
                            break;
                        case "options":
                        case "ordered":
                        case "accessibility":
                        case "labeltext":
                        case "phpconstraint":
                            $attrData[$fieldId]["overrides"][$col] = [
                                "before" => $originValues[$fieldId][$col],
                                "after" => $attrData[$fieldId][$col]
                            ];
                            break;
                    }
                }
            }
        }

        return array_values($attrData);
    }

    protected function checkAttribute(SmartStructure\BasicAttribute $oa)
    {
        return $oa->usefor !== "Q" && $oa->type !== "menu";
    }
}

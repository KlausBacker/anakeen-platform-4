<?php

namespace Anakeen\Routes\Devel\Smart;

use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\SmartElement;
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
    protected $sqlFilter = "usefor != 'Q'";

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
        $data["uri"] = URLUtils::generateURL(Settings::ApiV2 . sprintf("devel/smart/structures/%s/fields/", $this->structureName));
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
        $sql = sprintf("select * from docattr where docid in (%s) and {$this->sqlFilter} and type != 'menu' and id !~ '^:' order by ordered", implode(',', $fromids));
        $dbAttrs = [];
        DbManager::query($sql, $dbAttrs);
        $sql = sprintf("select * from docattr where docid in (%s) and {$this->sqlFilter} and id ~ '^:' order by ordered", implode(',', $fromids));

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
            if ($this->checkAttribute($oa) === false) {
                continue;
            }
            if ($oa->getOption("relativeOrder")) {
                $oa->ordered = $oa->getOption("relativeOrder");
                $oa->options = preg_replace("/(relativeOrder=[a-zA-Z0-9_:]+)/", "", $oa->options);
            }

            if (empty($dbAttrs[$oa->id])) {
                if ($oa->id === SmartStructure\Attributes::HIDDENFIELD) {
                    continue;
                }
                $oDocAttr->id = $oa->id;
                $oDocAttr->type = $oa->type;
                $oDocAttr->docid = $oa->structureId;
                $oDocAttr->usefor = $oa->usefor;
                $oDocAttr->ordered = $oa->ordered;
                $oDocAttr->accessibility = SmartStructure\FieldAccessManager::getTextAccess($oa->access);
                $oDocAttr->labeltext = $oa->labelText;
                $oDocAttr->abstract = ($oa->isInAbstract) ? "Y" : "N";
                $oDocAttr->title = ($oa->isInTitle) ? "Y" : "N";
                $oDocAttr->needed = ($oa->needed) ? "Y" : "N";
                $oDocAttr->frameid = ($oa->fieldSet->id != SmartStructure\Attributes::HIDDENFIELD) ? $oa->fieldSet->id : '';
                $oDocAttr->link = $oa->link;
                $oDocAttr->elink = $oa->elink;
                $oDocAttr->options = $oa->options;
                $oDocAttr->phpfile = $oa->phpfile;
                $oDocAttr->phpfunc = $oa->phpfunc;
                $oDocAttr->phpconstraint = $oa->phpconstraint;

                $dbAttrs[$oa->id] = $oDocAttr->getValues();
                $dbAttrs[$oa->id]["declaration"] = "bycode";
            } else {
                $dbAttrs[$oa->id]["declaration"] = "static";
                $currentType = $oa->type;
                if (!empty($oa->format)) {
                    $currentType .= '("' . $oa->format . '")';
                }
                if ($currentType != $dbAttrs[$oa->id]["type"]) {
                    $dbAttrs[$oa->id]["overrides"][] = "type";
                }

                if (!empty($oa->ordered) && $oa->ordered != $dbAttrs[$oa->id]["ordered"]) {
                    if (preg_match("/relativeOrder=([a-zA-Z0-9_:]+)/", $dbAttrs[$oa->id]["options"], $reg)) {
                        $dbAttrs[$oa->id]["ordered"] = $reg[1];
                        if ($oa->ordered !== $reg[1]) {
                            $dbAttrs[$oa->id]["overrides"][] = "ordered";
                        }
                        $dbAttrs[$oa->id]["options"] = preg_replace("/(relativeOrder=[a-zA-Z0-9_:]+)/", "", $dbAttrs[$oa->id]["options"]);
                    }
                } else {
                    if (!empty($oa->ordered) && is_numeric($oa->ordered) && $ancestrors) {
                        /**
                         * @var SmartElement $ancestror
                         */
                        foreach ($ancestrors as $ancestror) {
                            $parentOa = $ancestror->getAttribute($oa->id);
                            if ($parentOa && $parentOa->getOption("relativeOrder")) {
                                $dbAttrs[$oa->id]["ordered"] = $parentOa->getOption("relativeOrder");
                                break;
                            }
                        }
                    }
                }
                if ($oa->access != SmartStructure\FieldAccessManager::getRawAccess($dbAttrs[$oa->id]["accessibility"])) {
                    $dbAttrs[$oa->id]["overrides"]["accessibility"] = [
                        "before" => SmartStructure\FieldAccessManager::getTextAccess($parentStructure->getAttribute($oa->id)->access),
                        "after" => SmartStructure\FieldAccessManager::getTextAccess($oa->access)
                    ];
                    $dbAttrs[$oa->id]["accessibility"] = SmartStructure\FieldAccessManager::getTextAccess($oa->access);
                }
            }


            if (isset($dbAttrs[$oa->id]["type"])) {
                $dbAttrs[$oa->id]["simpletype"] = strtok($dbAttrs[$oa->id]["type"], "(");
                $dbAttrs[$oa->id]["displayOrder"] = $relativeOrder++;
            }

            foreach ($dbModAttr as $modAttr) {
                if ($modAttr["id"] === ":" . $oa->id && $modAttr["docid"] == $oa->structureId) {
                    $dbAttrs[$oa->id]["declaration"] = "overrided";
                    $types = [
                        "labeltext" => "labelText",
                        "ordered" => "ordered",
                        "options" => "options",
                        "link" => "link",
                        "needed" => "needed",
                        "title" => "title",
                        "abstract" => "abstract",
                        "elink" => "elink",
                        "phpfunc" => "phpfunc",
                        "phpconstraint" => "phpconstraint",
                        "phpfile" => "phpfile"];
                    foreach ($types as $type => $oType) {
                        if ($modAttr[$type]) {
                            $before = $parentStructure->getAttribute($oa->id)->$oType;
                            switch ($type) {
                                case "needed":
                                    $after = $oa->needed ? "Y" : "N";
                                    break;
                                case "title":
                                    $after = $oa->isInTitle ? "Y" : "N";
                                    break;
                                case "abstract":
                                    $after = $oa->isInAbstract ? "Y" : "N";
                                    break;
                                default:
                                    $after = $oa->$oType;
                            }

                            if (true || $before != $after) {
                                $dbAttrs[$oa->id][$type] = $oa->$oType;
                                $dbAttrs[$oa->id]["overrides"][$type] = [
                                    "before" => $parentStructure->getAttribute($oa->id)->$oType,
                                    "after" => $oa->$oType
                                ];
                            }
                        }
                    }
                }
            }
        }

        unset($dbAttrs[SmartStructure\Attributes::HIDDENFIELD]);
        foreach ($dbAttrs as & $attr) {
            if (($attr["type"] === "tab" || $attr["type"] === "frame")) {
                if (is_numeric($attr["ordered"])) {
                    $attr["ordered"] = "";
                }
            };

            if (!empty($attr["ordered"]) && !is_numeric($attr["ordered"])) {
                $attr["options"] = preg_replace("/(relativeOrder=[a-zA-Z0-9_:]+)/", "", $attr["options"]);
            }
        }

        uasort($dbAttrs, function ($a, $b) {
            if ($a["displayOrder"] == $b["displayOrder"]) {
                return 0;
            }
            if ($a["displayOrder"] > $b["displayOrder"]) {
                return 1;
            }
            return -1;
        });

        $result = [];
        foreach ($dbAttrs as $dbAttr) {
            $dbAttr["isTitle"] = $dbAttr["title"] === 'Y';
            $dbAttr["isAbstract"] = $dbAttr["abstract"] === 'Y';
            $dbAttr["isNeeded"] = $dbAttr["needed"] === 'Y';

            $dbAttr["optionValues"] = SmartStructure\BasicAttribute::optionsToArray($dbAttr["options"] ?: '');
            $dbAttr["structure"] = SEManager::getNameFromId($dbAttr["docid"]);
            $dbAttr["parentId"] = $dbAttr["frameid"];

            if ($dbAttr["properties"]) {
                $dbAttr["properties"] = json_decode($dbAttr["properties"], true);
            }

            unset($dbAttr["usefor"]);
            unset($dbAttr["options"]);
            unset($dbAttr["frameid"]);
            unset($dbAttr["title"]);
            unset($dbAttr["needed"]);
            unset($dbAttr["abstract"]);
            unset($dbAttr["elink"]);
            unset($dbAttr["docid"]);
            $result[] = $dbAttr;
        }

        return $result;
    }

    protected function checkAttribute(SmartStructure\BasicAttribute $oa)
    {
        return $oa->usefor !== "Q" && $oa->type !== "menu";
    }
}

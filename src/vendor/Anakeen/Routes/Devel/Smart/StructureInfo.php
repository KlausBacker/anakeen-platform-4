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
use Dcp\Ui\RenderConfigManager;

/**
 * Get Structure info
 *
 * @note Used by route : GET /api/v2/devel/smart/structures/{structure}/info/
 */
class StructureInfo
{
    protected $structureName = "";

    /**
     * @var SmartStructure $structure
     */
    protected $structure = null;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
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
        $df->addProperty("security");
        $df->getFormatCollection()->setPropertyRenderHook(function ($propValue, $propId) {
            if ($propId === "security" && isset($propValue["profil"]["id"])) {
                $propValue["profil"]["name"] = SEManager::getNameFromId($propValue["profil"]["id"]);
            }
            return $propValue;
        });
        $data = $df->getData();
        $data["uri"] = URLUtils::generateURL(Settings::ApiV2 . sprintf("devel/smart/structures/%s/info/", $this->structureName));


        $data["workflow"] = $this->getElementInfo($this->structure->wid);
        $data["security"]["cprofid"] = $this->getElementInfo($this->structure->cprofid);
        $data["security"]["cfallid"] = $this->getElementInfo($this->structure->cfallid);
        $data["ui"]["ccvid"] = $this->getElementInfo($this->structure->ccvid);
        $data["tags"] = json_decode($this->structure->atags, true) ?: [];

        $data["ui"]["render"] = RenderConfigManager::getRenderParameter($this->structure->name);
        $cFields = $cNFields = $cParameters = $cNParameters = 0;
        foreach ($this->structure->getAttributes() as $field) {
            if ($field->usefor === "Q") {
                $cParameters++;
                if ($field->isNormal && $field->type !== "array") {
                    $cNParameters++;
                }
            } else {
                $cFields++;
                if ($field->isNormal && $field->type !== "array") {
                    $cNFields++;
                }
            }
        }

        $tmpElet = SEManager::initializeDocument($this->structure->name);

        $sql=sprintf("select count(id) from only doc%s where doctype != 'T' and locked != -1", $this->structure->initid);
        DbManager::query($sql, $eltOnlyCount, true, true);

        $sql=sprintf("select count(id) from doc%s where doctype != 'T' and locked != -1", $this->structure->initid);
        DbManager::query($sql, $eltCount, true, true);


        $data["info"]["elements"]["count"] = intval($eltOnlyCount);
        $data["info"]["elements"]["countWithChilds"] = intval($eltCount);
        $data["info"]["revision"]["maxRevision"] = $this->structure->maxrev;
        $data["info"]["revision"]["isRevisable"] = $tmpElet->isRevisable();
        $data["info"]["fields"]["count"] = $cFields;
        $data["info"]["fields"]["leafCount"] = $cNFields;
        $data["info"]["parameters"]["count"] = $cParameters;
        $data["info"]["parameters"]["leafCount"] = $cNParameters;
        $data["className"] = $this->structure->classname;
        $data["properties"]["parents"] = StructureInfo::getParents($this->structure);
        $data["properties"]["childs"] = StructureInfo::getChilds($this->structure);
        return $data;
    }

    protected static function getElementInfo($eltId)
    {
        $e = SEManager::getDocument($eltId);
        $data = null;
        if ($e) {
            $data = [
                "name" => $e->name,
                "id" => intval($e->initid),
                "title" => $e->title
            ];
        }

        return $data;
    }

    public static function getParents(SmartStructure $structure)
    {
        if ($structure->fromid) {
            $parentsName = array_values(array_map(function ($fromid) {
                return SEManager::getNameFromId($fromid);
            }, $structure->attributes->fromids));
            array_pop($parentsName);
            $parents = array_reverse($parentsName);
        } else {
            $parents = [];
        }
        return $parents;
    }
    public static function getChilds(SmartStructure $structure)
    {
        $childsInfo=$structure->getChildFam();

        $childs=[];
        foreach ($childsInfo as $childInfo) {
            $child = SEManager::getFamily($childInfo["id"]);
            $parentName = "";
            if (!empty($child) && $child->fromid) {
                $parentName = SEManager::getNameFromId($child->fromid);
            }

            $childs[]=[
                "name"=> $childInfo["name"],
                "parent" => $parentName
            ];
        }

        return $childs;
    }
}

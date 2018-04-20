<?php

namespace Anakeen\Routes\DocumentGrid;

use Anakeen\Core\DocManager;
use Anakeen\Router\Exception;
use Anakeen\Routes\Core\DocumentList;

class ColumnsDefinitionAdd
{
    protected $properties = [];
    protected $defaultFamilyId = false;
    /**
     * Create new ressource
     *
     * @return mixed
     * @throws Exception
     */
    public function create()
    {
        $famId = $this->contentParameters["famId"];
        $attrId = $this->contentParameters["attrid"];
        
        $userCol = json_decode(\Anakeen\Core\Internal\ApplicationParameterManager::getUserParameterValue("DOCUMENT_GRID_HTML5", "DG_USERFAMILYCOLS") , true);
        if (!$userCol) {
            $userCol = [];
        }
        
        $family = DocManager::getFamily($famId);
        $attribute = $family->getAttribute($attrId);
        $userCol[$family->name][] = $attribute->id;
        $userCol[$family->name] = array_unique($userCol[$family->name]);
        
        \Anakeen\Core\Internal\ApplicationParameterManager::setUserParameterValue("DOCUMENT_GRID_HTML5", "DG_USERFAMILYCOLS", json_encode($userCol));
        
        return ["family" => $famId, "attrid" => $attrId];
    }


    /**
     * Delete ressource
     *
     * @param string|int $resourceId Resource identifier
     *
     * @return mixed
     * @throws Exception
     */
    public function delete($resourceId)
    {
        $this->contentParameters = \Dcp\HttpApi\V1\Api\Router::extractContentParameters("UPDATE", $this);

        $famId = $this->contentParameters["famId"];
        $attrId = $this->contentParameters["attrid"];
        $userCol = json_decode(\Anakeen\Core\Internal\ApplicationParameterManager::getUserParameterValue("DOCUMENT_GRID_HTML5", "DG_USERFAMILYCOLS") , true);
        if (!$userCol) {
            $userCol = [];
        }

        $family = DocManager::getFamily($famId);
        $attribute = $family->getAttribute($attrId);

        $famPref = $userCol[$family->name];

        unset($famPref[array_search($attribute->id, $famPref) ]);
        $userCol[$family->name] = array_unique(array_values($famPref));

        \Anakeen\Core\Internal\ApplicationParameterManager::setUserParameterValue("DOCUMENT_GRID_HTML5", "DG_USERFAMILYCOLS", json_encode($userCol));

        return ["family" => $famId, "attrid" => $attrId];
    }
}

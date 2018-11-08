<?php

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Search\SearchElements;
use SmartStructure\Fields\Cvdoc as CvFields;

/**
 * Get all views properties availables in view controller elements
 *
 * @note Used by route : GET /api/v2/devel/ui/smart/structures/{structure}/views/
 */
class StructureViews
{

    /**
     * @var SmartStructure
     */
    protected $structure;


    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $structureName = $args["structure"];
        $this->structure = SEManager::getFamily($structureName);
        if (!$this->structure) {
            throw new Exception(sprintf('Structure "%s" not found', $structureName));
        }
    }

    public function doRequest()
    {
        $data["views"] = $this->getViews();

        return $data;
    }

    protected function getViews()
    {

        $s = new SearchElements(\SmartStructure\Cvdoc::familyName);
        // search in all parent structure
        $s->addFilter(DbManager::getSqlOrCond($this->structure->attributes->fromids, CvFields::cv_famid));
        $cvdocList = $s->search()->getResults();
        $data = [];
        foreach ($cvdocList as $cvdoc) {
            /** @var \Anakeen\SmartStructures\Cvdoc\CVDocHooks $cvdoc */
            $views = $cvdoc->getAttributeValue(CvFields::cv_t_views);

            foreach ($views as $view) {
                $cvData = [
                    "cvId" => $cvdoc->name ?: $cvdoc->id,
                    "cvStructure" => SEManager::getNameFromId($cvdoc->getRawValue(CvFields::cv_famid)),
                    "viewId" => $view[CvFields::cv_idview],
                    "viewLabel" => $view[CvFields::cv_lview],
                    "viewMode" => ($view[CvFields::cv_kview] === "VEDIT") ? "edit" : "view",
                    "maskId" => SEManager::getNameFromId($view[CvFields::cv_mskid]) ?: $view[CvFields::cv_mskid],
                    "renderConfigClass" => $view[CvFields::cv_renderconfigclass],
                    "order" => $view[CvFields::cv_order],
                    "displayed" => $view[CvFields::cv_displayed] === "no",
                    "menuList" => $view[CvFields::cv_menu],
                ];
                if ($view[CvFields::cv_idview] === $cvdoc->getRawValue(CvFields::cv_idcview)) {
                    $cvData["mode"] = "creation";
                }

                $data[] = $cvData;
            }
        }
        return $data;
    }
}

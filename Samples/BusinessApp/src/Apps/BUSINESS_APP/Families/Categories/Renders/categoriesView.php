<?php
namespace Sample\BusinessApp\Renders;
//use Dcp\AttributeIdentifiers\BA_CLIENT_CONTRACT as MyAttr;
use Dcp\HttpApi\V1\DocManager\DocManager;


class CategoriesView extends CommonView
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        return $options;
    }

    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences($document);
        $ws=\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $css[__CLASS__] = "BUSINESS_APP/Families/Categories/Renders/categories.css"."?ws=$ws";
        return $css;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences($document);
        $ws=\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js[__CLASS__] = "BUSINESS_APP/Families/ClientContract/Renders/categories.js"."?ws=$ws";
        return $js;
    }
}

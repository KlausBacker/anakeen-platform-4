<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

class HelppageViewRender extends \Dcp\Ui\DefaultView
{
    /**
     * @param \Doc $document Document instance
     *
     * @return \Dcp\Ui\RenderOptions
     */
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        
        return $options;
    }
    public function getTemplates(\Doc $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"] = array(
            "file" => __DIR__.'/HelppageView.mustache'
        );
        /*$templates["sections"]["header"] = array(
            "content" => ' '
        );*/
        return $templates;
    }
    
    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences();
        $ws = \Dcp\Ui\UIGetAssetPath::getWs();

        $js["dduiHelppage"] = 'uiAssets/Families/helppage/prod/helppage.js?ws='.$ws;
        if (\Dcp\Ui\UIGetAssetPath::isInDebug()) {
            $js["dduiHelppage"] = 'uiAssets/Families/helppage/debug/helppage.js?ws='.$ws;
        }

        return $js;
    }
    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences();
        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        //$css["dduiHelppage"] = "uiAssets/Families/helppage/helppage.css?ws=" . $version;
        return $css;
    }
    /**
     * @param \Doc $document Document object instance
     *
     * @return \Dcp\Ui\BarMenu Menu configuration
     */
    public function getMenu(\Doc $document)
    {
        $menu = parent::getMenu($document);
        $langMenuList = new \Dcp\Ui\ListMenu("helppage-langMenu", ___("Lang", "ddui helppage"));
        
        $all_lang_keys = $document->rawValueToArray($document->getFamilyParameterValue('help_p_lang_key'));
        $all_lang_texts = $document->rawValueToArray($document->getFamilyParameterValue('help_p_lang_name'));
        
        $currentLocale = getLocaleConfig();
        foreach ($all_lang_keys as $i => $key) {
            $menuItem = new \Dcp\Ui\ItemMenu("helppage-lang-" . $key, mb_ucfirst($all_lang_texts[$i]));
            $lang = strtolower(substr($key, 3, 2));
            $menuItem->setIcon(sprintf("FDL/Images/flags/%s.png", $lang));
            $menuItem->setUrl("#action/helppage.lang:" . $key);
            
            if (substr($key, 0, 2) === $currentLocale["locale"]) {
                $langMenuList->setIcon(sprintf("FDL/Images/flags/%s.png", $lang));
                $langMenuList->setHtmlLabel(sprintf("(%s) ", substr($key, 0, 2)));
            }
            
            $langMenuList->appendElement($menuItem);
        }
        
        $menu->appendElement($langMenuList);
        
        return $menu;
    }
}

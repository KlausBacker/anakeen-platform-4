<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Helppage\Render;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Utils\Strings;
use Dcp\Ui\BarMenu;
use Dcp\Ui\RenderOptions;
use Dcp\Ui\UIGetAssetPath;

class HelppageViewRender extends \Dcp\Ui\DefaultView
{
    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     *
     * @return \Dcp\Ui\RenderOptions
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document) : RenderOptions
    {
        $options = parent::getOptions($document);
        
        return $options;
    }
    public function getTemplates(\Anakeen\Core\Internal\SmartElement $document = null)
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
    
    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences();
        $ws = \Dcp\Ui\UIGetAssetPath::getWs();

        $js["dduiHelppage"] = 'uiAssets/Families/helppage/prod/helppage.js?ws='.$ws;
        if (\Dcp\Ui\UIGetAssetPath::isInDebug()) {
            $js["dduiHelppage"] = 'uiAssets/Families/helppage/debug/helppage.js?ws='.$ws;
        }

        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "legacy");
        $js["dduiHelppage"] = $path["Helppage"]["js"];

        return $js;
    }
    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $css = parent::getCssReferences();
        //$version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        //$css["dduiHelppage"] = "uiAssets/Families/helppage/helppage.css?ws=" . $version;
        return $css;
    }
    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     *
     * @return \Dcp\Ui\BarMenu Menu configuration
     */
    public function getMenu(\Anakeen\Core\Internal\SmartElement $document) : BarMenu
    {
        $menu = parent::getMenu($document);
        $langMenuList = new \Dcp\Ui\ListMenu("helppage-langMenu", ___("Lang", "ddui helppage"));
        
        $all_lang_keys = $document->getFamilyParameterValue('help_p_lang_key');
        $all_lang_texts = $document->getFamilyParameterValue('help_p_lang_name');
        
        $currentLocale = ContextManager::getLocaleConfig();
        foreach ($all_lang_keys as $i => $key) {
            $menuItem = new \Dcp\Ui\ItemMenu("helppage-lang-" . $key, Strings::mb_ucfirst($all_lang_texts[$i]));
            $lang = strtolower(substr($key, 3, 2));
            $menuItem->setIcon(sprintf("flags/%s.png", $lang));
            $menuItem->setUrl("#action/helppage.lang:" . $key);
            
            if (substr($key, 0, 2) === $currentLocale["locale"]) {
                $langMenuList->setIcon(sprintf("flags/%s.png", $lang));
                $langMenuList->setHtmlLabel(sprintf("(%s) ", substr($key, 0, 2)));
            }
            
            $langMenuList->appendElement($menuItem);
        }
        
        $menu->appendElement($langMenuList);
        
        return $menu;
    }
}

<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DDUI
*/

namespace Dcp\Ui;

class HelpViewRender extends \Dcp\Ui\DefaultView
{
    
    public function getLabel(\Doc $document = null)
    {
        return "Help View";
    }
    /**
     * @param \Doc $document Document instance
     *
     * @return RenderOptions
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
            "file" => 'DOCUMENT/Families/helpView.mustache'
        );
        /*$templates["sections"]["header"] = array(
            "content" => ' '
        );*/
        return $templates;
    }
    
    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences();
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js["dduiHelppage"] = "DOCUMENT/Layout/helppage.js?ws=" . $version;
        return $js;
    }
    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences();
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $css["dduiHelppage"] = "DOCUMENT/Layout/helppage.css?ws=" . $version;
        return $css;
    }
    /**
     * @param \Doc $document Document object instance
     *
     * @return BarMenu Menu configuration
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
            $menuItem->setIcon(sprintf("Images/flags/%s.png", $lang));
            $menuItem->setUrl("#action/helppage.lang:" . $key);
            
            if (substr($key, 0, 2) === $currentLocale["locale"]) {
                $langMenuList->setIcon(sprintf("Images/flags/%s.png", $lang));
                $langMenuList->setHtmlLabel(sprintf("(%s) ", substr($key, 0, 2)));
            }
            
            $langMenuList->appendElement($menuItem);
        }
        
        $menu->appendElement($langMenuList);
        
        return $menu;
    }
}

<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Helppage\Render;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Utils\Strings;
use Anakeen\Ui\BarMenu;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\UIGetAssetPath;

class HelppageViewRender extends \Anakeen\Ui\DefaultView
{
    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     *
     * @return \Anakeen\Ui\RenderOptions
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        return $options;
    }

    public function getTemplates(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"] = array(
            "file" => __DIR__ . '/HelppageView.mustache'
        );
        /*$templates["sections"]["header"] = array(
            "content" => ' '
        );*/
        return $templates;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences();

        $kendoDll = UIGetAssetPath::getJSKendoComponentPath();
        $js["kendoDll"] = $kendoDll;

        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "legacy");
        $js["helpPage"] = $path["Helppage"]["js"];

        return $js;
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     *
     * @return \Anakeen\Ui\BarMenu Menu configuration
     */
    public function getMenu(\Anakeen\Core\Internal\SmartElement $document): BarMenu
    {
        $menu = parent::getMenu($document);
        $langMenuList = new \Anakeen\Ui\ListMenu("helppage-langMenu", ___("Lang", "ddui helppage"));

        $all_lang_keys = $document->getFamilyParameterValue('help_p_lang_key');
        $all_lang_texts = $document->getFamilyParameterValue('help_p_lang_name');

        $currentLocale = ContextManager::getLocaleConfig();
        foreach ($all_lang_keys as $i => $key) {
            $menuItem = new \Anakeen\Ui\ItemMenu("helppage-lang-" . $key, Strings::mbUcfirst($all_lang_texts[$i]));
            $lang = strtolower(substr($key, 3, 2));
            $menuItem->setIcon(sprintf("Helppage/flags/%s.png", $lang));
            $menuItem->setUrl("#action/helppage.lang:" . $key);

            if (substr($key, 0, 2) === $currentLocale["locale"]) {
                $langMenuList->setIcon(sprintf("Helppage/flags/%s.png", $lang));
                $langMenuList->setHtmlLabel(sprintf("(%s) ", substr($key, 0, 2)));
            }

            $langMenuList->appendElement($menuItem);
        }

        $menu->appendElement($langMenuList);

        return $menu;
    }
}

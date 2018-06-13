<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Settings;

class FamilyView extends RenderDefault
{

    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return ___("Family View", "ddui");
    }

    public function getType()
    {
        return IRenderConfig::viewType;
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     * @return BarMenu Menu configuration
     */
    public function getMenu(\Anakeen\Core\Internal\SmartElement $document)
    {
        $menu = new BarMenu();


        if (ContextManager::getCurrentUser()->id == \Anakeen\Core\Account::ADMIN_ID) {
            DefaultView::appendSystemMenu($document, $menu);

            $item = new ItemMenu(
                "Create",
                ___("Create", "UiMenu"),
                sprintf("%sdocuments/%s/views/!defaultCreation.html", Settings::ApiV2, $document->name)
            );
            $item->setTarget("_blank");
            $menu->appendElement($item);
        }


        return $menu;
    }

    public function getTemplates(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $templates = parent::getTemplates($document);

        $templates["sections"]["content"]["file"] = DEFAULT_PUBDIR . "/Apps/DOCUMENT/IHM/views/document/family__content.mustache";
        return $templates;
    }
}

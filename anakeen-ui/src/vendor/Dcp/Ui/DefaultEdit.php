<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

use Anakeen\Core\ContextManager;

class DefaultEdit extends RenderDefault
{

    public function getType()
    {
        return IRenderConfig::editType;
    }

    /**
     * @param \Doc $document Document object instance
     * @return BarMenu Menu configuration
     */
    public function getMenu(\Doc $document)
    {
        $menu = new BarMenu();
        $user = ContextManager::getCurrentUser();


        $this->setEmblemMenu($document, $menu);

        if ($document->wid > 0) {
            $workflowMenu = new SeparatorMenu("workflow", _($document->getStateActivity($document->getState())));
            $workflowMenu->setHtmlAttribute("class", "menu--workflow menu--left");
            $workflowMenu->setBeforeContent(sprintf('<div style="color:%s" class="fa fa-square" />', $document->getStateColor("transparent")));

            $menu->appendElement($workflowMenu);
        }

        $item = new ItemMenu("save", ___("Save", "UiMenu"), "#action/document.save");
        $item->setBeforeContent('<div class="fa fa-save" />');
        if ($this->displayDefaultMenuTooltip) {
            $item->setTooltipLabel(___("Record document to server", "UiMenu"));
        }
        if (empty($document->id)) {
            $item->setVisibility($item::VisibilityHidden);
        }
        $menu->appendElement($item);
        if ($user->id === "1") {
            $item = new ItemMenu("save!", ___("Save !", "UiMenu"), "#action/document.save:force");
            $item->setVisibility($item::VisibilityHidden);
            if ($this->displayDefaultMenuTooltip) {
                $item->setTooltipLabel(___("Record document without constraint check", "UiMenu"));
            }
            if (empty($document->id)) {
                $item->setVisibility($item::VisibilityHidden);
            }
            $menu->appendElement($item);
        }

        if (empty($document->id)) {
            $item = new ItemMenu("create", ___("Create", "UiMenu"), "#action/document.create");
            $item->setBeforeContent('<div class="fa fa-save" />');
            if ($this->displayDefaultMenuTooltip) {
                $item->setTooltipLabel(___("Create new document to server", "UiMenu"));
            }
            $menu->appendElement($item);

            if ($user->id === "1") {
                $item = new ItemMenu("create!", ___("Create !", "UiMenu"), "#create!/{{document.properties.id}}");
                $item->setVisibility($item::VisibilityHidden);
                if ($this->displayDefaultMenuTooltip) {
                    $item->setTooltipLabel(___("Record document without constraint check", "UiMenu"));
                }
                $menu->appendElement($item);
            }
        }

        if ($document->id > 0) {
            $item = new ItemMenu("close", ___("Close", "UiMenu"), "#action/document.close:!defaultConsultation:unlock");
            $item->setBeforeContent('<div class="fa fa-times" />');
            if ($this->displayDefaultMenuTooltip) {
                $item->setTooltipLabel(___("Close form", "UiMenu"));
            }
            $menu->appendElement($item);
        }

        $this->addHelpMenu($document, $menu);
        return $menu;
    }

    /**
     * @param \Doc             $document
     * @param Barmenu|ListMenu $menu
     */
    public function appendSaveAndCloseMenu($document, $menu)
    {

        $item = new ItemMenu("saveAndClose", ___("Save and close", "UiMenu"), "#action/document.saveAndClose");
        $item->setBeforeContent('<div class="fa fa-save" />');
        if ($this->displayDefaultMenuTooltip) {
            $item->setTooltipLabel(___("Record to server and view document", "UiMenu"));
        }
        if (empty($document->id)) {
            $item->setVisibility($item::VisibilityHidden);
        }
        $menu->appendElement($item);
    }


    /**
     * @param \Doc             $document
     * @param Barmenu|ListMenu $menu
     */
    public function appendCreateAndCloseMenu($document, $menu)
    {

        $item = new ItemMenu("createAndClose", ___("Create and close", "UiMenu"), "#action/document.createAndClose");
        $item->setBeforeContent('<div class="fa fa-save" />');
        if ($this->displayDefaultMenuTooltip) {
            $item->setTooltipLabel(___("Create to server and view document", "UiMenu"));
        }
        $menu->appendElement($item);
    }

    /**
     * @param \Doc $document Document instance
     *
     * @return RenderOptions
     */
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $this->addDocumentHelpLinks($options, $document);

        return $options;
    }
}

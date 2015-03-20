<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class DefaultEdit extends RenderDefault
{
    
    public function getLabel(\Doc $document = null)
    {
        return ___("Default Edit", "ddui");
    }
    
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
        $user = getCurrentUser();
        
        $item = new ItemMenu("save", ___("Save", "UiMenu") , "#event/document:save");
        $item->setBeforeContent('<div class="fa fa-save" />');
        $item->setTooltipLabel(___("Record document to server", "UiMenu"));
        if (empty($document->id)) {
            $item->setVisibility($item::VisibilityHidden);
        }
        $menu->appendElement($item);
        
        $item = new ItemMenu("saveAndClose", ___("Save and close", "UiMenu") , "#event/document:saveAndClose");
        $item->setBeforeContent('<div class="fa fa-save" />');
        $item->setTooltipLabel(___("Record to server and view document", "UiMenu"));
        if (empty($document->id)) {
            $item->setVisibility($item::VisibilityHidden);
        }
        $menu->appendElement($item);
        if ($user->id === "1") {
            $item = new ItemMenu("save!", ___("Save !", "UiMenu") , "#event/document:save:force");
            $item->setVisibility($item::VisibilityHidden);
            $item->setTooltipLabel(___("Record document without constraint check", "UiMenu"));
            if (empty($document->id)) {
                $item->setVisibility($item::VisibilityHidden);
            }
            $menu->appendElement($item);
        }
        
        if (empty($document->id)) {
            $item = new ItemMenu("create", ___("Create", "UiMenu") , "#event/document:create");
            $item->setBeforeContent('<div class="fa fa-save" />');
            $item->setTooltipLabel(___("Create new document to server", "UiMenu"));
            $menu->appendElement($item);
            
            $item = new ItemMenu("createAndClose", ___("Create and close", "UiMenu") , "#event/document:createAndClose");
            $item->setBeforeContent('<div class="fa fa-save" />');
            $item->setTooltipLabel(___("Create to server and view document", "UiMenu"));
            $menu->appendElement($item);
            
            if ($user->id === "1") {
                $item = new ItemMenu("create!", ___("Create !", "UiMenu") , "#create!/{{document.properties.id}}");
                $item->setVisibility($item::VisibilityHidden);
                $item->setTooltipLabel(___("Record document without constraint check", "UiMenu"));
                $menu->appendElement($item);
            }
        }
        /*
        $item = new ItemMenu("cancel", ___("Cancel", "UiMenu") , "#cancel/{{document.properties.id}}");
        $item->setBeforeContent('<div class="fa fa-undo" />');
        $item->setTooltipLabel(___("Abord modifications", "UiMenu"));
        $menu->appendElement($item);
        */
        
        if ($document->id > 0) {
            $item = new ItemMenu("close", ___("Close", "UiMenu") , "#event/document:close:!defaultConsultation:unlock");
            $item->setBeforeContent('<div class="fa fa-times" />');
            $item->setTooltipLabel(___("Close form", "UiMenu"));
            $menu->appendElement($item);
        }
        if ($document->wid > 0) {
            $workflowMenu = new SeparatorMenu("workflow", _($document->getStateActivity($document->getState())));
            //$workflowMenu->setHtmlAttribute("style", "float:right;background-color:inherit");
            $workflowMenu->setHtmlAttribute("class", "menu--workflow menu--right");
            $workflowMenu->setBeforeContent(sprintf('<div style="color:%s" class="fa fa-square" />', $document->getStateColor("transparent")));
            
            $menu->appendElement($workflowMenu);
        }
        
        return $menu;
    }
}

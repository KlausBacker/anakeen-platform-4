<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class DefaultEdit extends RenderDefault
{
    
    public function getLabel()
    {
        return _("Default Edit");
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
        if ($document->id > 0) {
            $item = new ItemMenu("save", ___("Save", "UiMenu") , "#save/{{document.properties.id}}");
            $item->setBeforeContent('<div class="fa fa-save" />');
            $item->setTooltipLabel(___("Record document to server", "UiMenu"));
            $menu->appendElement($item);
            if ($user->id === "1") {
                $item = new ItemMenu("save!", ___("Save !", "UiMenu") , "#save!/{{document.properties.id}}");
                $item->setVisibility($item::VisibilityHidden);
                $item->setTooltipLabel(___("Record document without constraint check", "UiMenu"));
                $menu->appendElement($item);
            }
        } else {
            $item = new ItemMenu("save", ___("Create", "UiMenu") , "#create/{{document.properties.id}}");
            $item->setBeforeContent('<div class="fa fa-save" />');
            $item->setTooltipLabel(___("Record document to server", "UiMenu"));
            $menu->appendElement($item);
            if ($user->id === "1") {
                $item = new ItemMenu("save!", ___("Save !", "UiMenu") , "#save!/{{document.properties.id}}");
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
        $item = new ItemMenu("close", ___("Close", "UiMenu") , "#close/{{document.properties.id}}");
        $item->setBeforeContent('<div class="fa fa-times" />');
        $item->setTooltipLabel(___("See document in read mode", "UiMenu"));
        $menu->appendElement($item);
        
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

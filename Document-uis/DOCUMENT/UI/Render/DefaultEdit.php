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
        
        $item = new ItemMenu("saveAndClose", ___("Save and close", "UiMenu") , "#action/document.saveAndClose");
        $item->setBeforeContent('<div class="fa fa-save" />');
        if ($this->displayDefaultMenuTooltip) {
            $item->setTooltipLabel(___("Record to server and view document", "UiMenu"));
        }
        if (empty($document->id)) {
            $item->setVisibility($item::VisibilityHidden);
        }
        $menu->appendElement($item);
        
        $item = new ItemMenu("save", ___("Save", "UiMenu") , "#action/document.save");
        $item->setBeforeContent('<div class="fa fa-save" />');
        if ($this->displayDefaultMenuTooltip) {
            $item->setTooltipLabel(___("Record document to server", "UiMenu"));
        }
        if (empty($document->id)) {
            $item->setVisibility($item::VisibilityHidden);
        }
        $menu->appendElement($item);
        if ($user->id === "1") {
            $item = new ItemMenu("save!", ___("Save !", "UiMenu") , "#action/document.save:force");
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
            
            $item = new ItemMenu("createAndClose", ___("Create and close", "UiMenu") , "#action/document.createAndClose");
            $item->setBeforeContent('<div class="fa fa-save" />');
            if ($this->displayDefaultMenuTooltip) {
                $item->setTooltipLabel(___("Create to server and view document", "UiMenu"));
            }
            $menu->appendElement($item);
            
            $item = new ItemMenu("create", ___("Create", "UiMenu") , "#action/document.create");
            $item->setBeforeContent('<div class="fa fa-save" />');
            if ($this->displayDefaultMenuTooltip) {
                $item->setTooltipLabel(___("Create new document to server", "UiMenu"));
            }
            $menu->appendElement($item);
            
            if ($user->id === "1") {
                $item = new ItemMenu("create!", ___("Create !", "UiMenu") , "#create!/{{document.properties.id}}");
                $item->setVisibility($item::VisibilityHidden);
                if ($this->displayDefaultMenuTooltip) {
                    $item->setTooltipLabel(___("Record document without constraint check", "UiMenu"));
                }
                $menu->appendElement($item);
            }
        }
        
        if ($document->id > 0) {
            $item = new ItemMenu("close", ___("Close", "UiMenu") , "#action/document.close:!defaultConsultation:unlock");
            $item->setBeforeContent('<div class="fa fa-times" />');
            if ($this->displayDefaultMenuTooltip) {
                $item->setTooltipLabel(___("Close form", "UiMenu"));
            }
            $menu->appendElement($item);
        }
        if ($document->wid > 0) {
            $workflowMenu = new SeparatorMenu("workflow", _($document->getStateActivity($document->getState())));
            $workflowMenu->setHtmlAttribute("class", "menu--workflow menu--right");
            $workflowMenu->setBeforeContent(sprintf('<div style="color:%s" class="fa fa-square" />', $document->getStateColor("transparent")));
            
            $menu->appendElement($workflowMenu);
        }
        
        $this->setEmblemMenu($document, $menu);
        $this->addHelpMenu($document, $menu);
        return $menu;
    }
    /**
     * @param \Doc $document Document instance
     *
     * @return RenderOptions
     */
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $helpDoc = $document->getHelpPage();
        if ($helpDoc && $helpDoc->isAlive()) {
            $attrids = $helpDoc->getMultipleRawValues(\Dcp\AttributeIdentifiers\Helppage::help_sec_key);
            
            foreach ($attrids as $k => $aid) {
                if ($aid) {
                    $options->commonOption($aid)->setLinkHelp($helpDoc->initid);
                }
            }
        }
        
        return $options;
    }
}

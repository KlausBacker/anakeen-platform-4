<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class DefaultView extends RenderDefault
{
    
    public function getLabel()
    {
        return _("Default View");
    }
    
    public function getType()
    {
        return RenderConfig::viewType;
    }
    /**
     * @param BarMenu $menu
     * @param \Doc $document
     * @return BarMenu
     */
    protected function setMemuVisibility(BarMenu & $menu, \Doc $document)
    {
        
        if ($editErr = $document->CanEdit()) {
            $menu->getElement("modify")->setVisibility(ElementMenu::VisibilityDisabled)->setTooltipLabel($editErr);
        }
        $deleteErr = $document->control("delete");
        if ($document->locked == - 1) {
            $menu->getElement("delete'")->setVisibility(ElementMenu::VisibilityHidden);
            if ($deleteErr) {
                $menu->getElement("restore")->setVisibility(ElementMenu::VisibilityDisabled)->setTooltipLabel($deleteErr);
            }
        } else {
            
            $menu->getElement("restore")->setVisibility(ElementMenu::VisibilityHidden);
        }
        
        $menu->getElement("lock")->setVisibility(ElementMenu::VisibilityDisabled);
        return $menu;
    }
    /**
     * @param \Doc $document Document object instance
     * @return BarMenu Menu configuration
     */
    public function getMenu(\Doc $document)
    {
        $menu = new BarMenu();
        
        $menu->appendElement(new ItemMenu("modify", ___("Modify", "UiMenu") , "?app=DOCUMENT&action=VIEW&render=defaultEdit&id={{document.properties.id}}"));
        
        $menu->appendElement(new ItemMenu("delete", ___("Delete", "UiMenu") , "#delete/{{document.properties.id}}"));
        $menu->appendElement(new ItemMenu("restore", ___("Restore", "UiMenu") , "#restore/{{document.properties.id}}"));
        
        if ($document->wid > 0) {
            $workflowMenu = new DynamicMenu("workflow", ___("Transition", "UiMenu"));
            $workflowMenu->setUrl("?app=...&id={{document.property.id}}&menu=workflow");
            $menu->appendElement($workflowMenu);
        }
        
        $menu->appendElement(new ItemMenu("histo", ___("Historic", "UiMenu") , "#historic/{{document.properties.id}}"));
        $menu->getElement("histo")->setTarget('_blank')->setHtmlAttribute("date-test", "testing");
        
        $menu->appendElement(new ListMenu("advanced", ___("Advanced", "UiMenu")));
        $menu->getElement("advanced")->appendElement(new ItemMenu("properties", ___("properties", "UiMenu") , "?app=DOCUMENT&action=VIEW&render=defaultEdit&id={{document.properties.id}}"));
        
        $securitySubMenu = new ListMenu("security", ___("Security", "UiMenu"));
        $securitySubMenu->appendElement(new ItemMenu("profil", ___("Profil access", "UiMenu") , "?app=...={{document.properties.id}}"));
        $securitySubMenu->appendElement(new ItemMenu("lock", ___("Lock", "UiMenu") , "?app=...={{document.properties.id}}"));
        $securitySubMenu->appendElement(new ItemMenu("unlock", ___("Unlock", "UiMenu") , "?app=...={{document.properties.id}}"));
        
        $menu->getElement("advanced")->appendElement($securitySubMenu);
        
        return $this->setMemuVisibility($menu, $document);
    }
}

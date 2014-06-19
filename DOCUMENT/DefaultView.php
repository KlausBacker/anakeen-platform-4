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
    protected function setMenuVisibility(BarMenu & $menu, \Doc $document)
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
        
        $itemModify = new ItemMenu("modify", ___("Modify", "UiMenu") , "?app=DOCUMENT&action=VIEW&render=defaultEdit&id={{document.properties.id}}");
        $itemModify->setTooltipLabel(___("Display document form", "UiMenu"));
        $menu->appendElement($itemModify);
        
        $menu->appendElement(new ItemMenu("delete", ___("Delete", "UiMenu") , "#delete/{{document.properties.id}}"));
        $menu->appendElement(new ItemMenu("restore", ___("Restore", "UiMenu") , "#restore/{{document.properties.id}}"));
        
        if ($document->wid > 0) {
            $workflowMenu = new DynamicMenu("workflow", _($document->getStateActivity($document->getState())));
            $workflowMenu->setContent(function (ListMenu & $menu) use ($document)
            {
                $this->getWorkflowMenu($document, $menu);
            });
            $color = $document->getStateColor("transparent");
            $workflowMenu->setHtmlAttribute("style", "float:right; background-color:$color");
            $menu->appendElement($workflowMenu);
        }
        
        $menu->appendElement(new ItemMenu("histo", ___("Historic", "UiMenu") , "#historic/{{document.properties.id}}"));
        $menu->getElement("histo")->setTarget('_blank')->setHtmlAttribute("date-test", "testing");
        
        $menu->appendElement(new ListMenu("advanced", ___("Advanced", "UiMenu")));
        $menu->getElement("advanced")->appendElement(new ItemMenu("properties", ___("Properties", "UiMenu") , "?app=DOCUMENT&action=VIEW&render=defaultEdit&id={{document.properties.id}}"));
        
        $securitySubMenu = new ListMenu("security", ___("Security", "UiMenu"));
        $securitySubMenu->appendElement(new ItemMenu("profil", ___("Profil access", "UiMenu") , "?app=...={{document.properties.id}}"));
        $securitySubMenu->appendElement(new ItemMenu("lock", ___("Lock", "UiMenu") , "?app=...={{document.properties.id}}"));
        $securitySubMenu->appendElement(new ItemMenu("unlock", ___("Unlock", "UiMenu") , "?app=...={{document.properties.id}}"));
        
        $menu->getElement("advanced")->appendElement($securitySubMenu);
        
        return $this->setMenuVisibility($menu, $document);
    }
    /**
     * Get workflow submenu contents
     * @param \Doc $doc
     * @param ListMenu $menu
     */
    public function getWorkflowMenu(\Doc $doc, ListMenu & $menu)
    {
        
        if ($doc->wid > 0) {
            /**
             * @var \WDoc $wdoc
             */
            $wdoc = \Dcp\DocManager::getDocument($doc->wid, false);
            if (!$doc) {
                return;
            }
            $wdoc->Set($doc, true);
            $fstate = $wdoc->GetFollowingStates();
            
            foreach ($fstate as $v) {
                
                $tr = $wdoc->getTransition($doc->state, $v);
                
                $label = $tr['id'] ? _($tr['id']) : $wdoc->getActivity($v, mb_ucfirst(_($v)));
                $itemMenu = new ItemMenu($v, $label);
                
                if ((empty($tr["nr"])) || ((!empty($tr["ask"])) && is_array($tr["ask"]) && (count($tr["ask"]) > 0))) {
                    $itemMenu->setUrl(sprintf("?app=FDL&action=EDITCHANGESTATE&newstate=%s&id={{document.properties.id}}", urlencode($v)));
                    $itemMenu->setTarget("_dialog"); // alternative to data-popup
                    
                } else {
                    $itemMenu->setUrl(sprintf("?app=FREEDOM&action=MODSTATE&newstate=%s&id={{document.properties.id}}", urlencode($v)));
                }
                $visibility = $itemMenu::VisibilityVisible;
                $tooltip = $wdoc->getActivity($v, mb_ucfirst(_($v)));
                //$icon = (!$tr) ? "Images/noaccess.png" : ((is_array($tr["ask"])) ? "Images/miniask.png" : "");
                $icon = (!$tr) ? "Images/noaccess.png" : "";
                if ($tr && (!empty($tr["m0"]))) {
                    // verify m0
                    $err = call_user_func(array(
                        $wdoc,
                        $tr["m0"],
                    ) , $v, $wdoc->doc->state);
                    if ($err) {
                        $visibility = $itemMenu::VisibilityDisabled;
                        $tooltip = $err;
                        $icon = ""; // no image "Images/nowaccess.png";
                        
                    }
                }
                
                if ($icon) {
                    $itemMenu->setIcon($icon);
                }
                if ($tooltip) {
                    $itemMenu->setTooltipLabel($tooltip);
                }
                
                $color = $wdoc->getColor($v);
                if ($color) {
                    $itemMenu->setHtmlAttribute("style", "background-color:$color");
                }
                $itemMenu->setVisibility($visibility);
                
                $menu->appendElement($itemMenu);
            }
            
            $sep = new SeparatorMenu('workflowSep');
            $menu->appendElement($sep);
            
            $itemMenu = new ItemMenu('workflowDraw', ___("View workflow graph"));
            $itemMenu->setUrl(sprintf("?app=FDL&action=VIEW_WORKFLOW_GRAPH&format=png&orient=LR&tool=dot&id=%d", $wdoc->id));
            $menu->appendElement($itemMenu);
        }
    }
}

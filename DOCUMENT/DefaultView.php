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
        
        if ($document->isLocked()) {
            $menu->getElement("lock")->setVisibility(ElementMenu::VisibilityHidden);
            $cuf = ($document->CanUnLockFile() == "");
            if (!$cuf) {
                if ($document->locked == - 1) {
                    
                    $menu->getElement("unlock")->setVisibility(ElementMenu::VisibilityHidden);
                } else {
                    $menu->getElement("unlock")->setVisibility(ElementMenu::VisibilityDisabled);
                }
            }
        } else {
            $menu->getElement("unlock")->setVisibility(ElementMenu::VisibilityHidden);
        }
        
        return $menu;
    }
    /**
     * @param \Doc $document Document object instance
     * @return BarMenu Menu configuration
     */
    public function getMenu(\Doc $document)
    {
        $menu = new BarMenu();
        
        $item = new ItemMenu("modify", ___("Modify", "UiMenu") , "?app=DOCUMENT&action=VIEW&render=defaultEdit&id={{document.properties.id}}");
        $item->setTooltipLabel(___("Display document form", "UiMenu"));
        $item->setBeforeContent('<div class="fa fa-pencil" />');
        
        $menu->appendElement($item);
        
        $item = new ItemMenu("delete", ___("Delete", "UiMenu") , "#delete/{{document.properties.id}}");
        $item->setTooltipLabel(___("Put document to the trash", "UiMenu"));
        $confirmOption = new MenuConfirmOptions();
        $confirmOption->title = ___("Confirm deletion of {{document.properties.title}}");
        $confirmOption->confirmButton = ___("Confirm deletion", "UiMenu");
        $confirmOption->windowWidth = "350px";
        $confirmOption->windowHeight = "150px";
        $item->useConfirm(sprintf(___("Sure delete %s ?", "UiMenu") , $document->getTitle()) , $confirmOption);
        $item->setBeforeContent('<div class="fa fa-trash-o" />');
        $menu->appendElement($item);
        
        $item = new ItemMenu("restore", ___("Restore", "UiMenu") , "#restore/{{document.properties.id}}");
        $item->setTooltipLabel(___("Restore document from the trash", "UiMenu"));
        $menu->appendElement($item);
        
        if ($document->wid > 0) {
            $workflowMenu = new DynamicMenu("workflow", _($document->getStateActivity($document->getState())));
            $workflowMenu->setContent(function (ListMenu & $menu) use ($document)
            {
                $this->getWorkflowMenu($document, $menu);
            });
            $workflowMenu->setBeforeContent(sprintf('<div style="color:%s" class="fa fa-square" />', $document->getStateColor("transparent")));
            $workflowMenu->setHtmlAttribute("class", "menu--workflow");
            $menu->appendElement($workflowMenu);
        }
        
        $item = new ItemMenu("histo", ___("Historic", "UiMenu") , "?app=FREEDOM&action=HISTO&id={{document.properties.id}}");
        $targetOption = new MenuTargetOptions();
        $targetOption->windowHeight = "400px";
        $targetOption->windowWidth = "600px";
        $item->setTarget("_dialog", $targetOption);
        $menu->appendElement($item);
        
        $menu->appendElement(new ListMenu("advanced", ___("Advanced", "UiMenu")));
        $item = new ItemMenu("properties", ___("Properties", "UiMenu") , "?app=FDL&action=IMPCARD&zone=FDL:VIEWPROPERTIES:T&id={{document.properties.id}}");
        $targetOption = new MenuTargetOptions();
        $targetOption->windowHeight = "400px";
        $targetOption->windowWidth = "400px";
        $item->setTarget("_dialog", $targetOption);
        $menu->getElement("advanced")->appendElement($item);
        
        $securitySubMenu = new ListMenu("security", ___("Security", "UiMenu"));
        $item = new ItemMenu("profil", ___("Profil access", "UiMenu") , "?app=FREEDOM&action=FREEDOM_GACCESS&id={{document.properties.id}}");
        $targetOption = new MenuTargetOptions();
        $targetOption->windowHeight = "400px";
        $targetOption->windowWidth = "600px";
        $item->setTarget("_dialog", $targetOption);
        $securitySubMenu->appendElement($item);
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
                    $itemMenu->setUrl(sprintf("?app=FDL&action=EDITCHANGESTATE&nstate=%s&id={{document.properties.id}}", urlencode($v)));
                    $itemMenu->setTarget("_dialog"); // alternative to data-popup
                    
                } else {
                    $itemMenu->setUrl(sprintf("?app=FREEDOM&action=MODSTATE&newstate=%s&id={{document.properties.id}}", urlencode($v)));
                }
                $visibility = $itemMenu::VisibilityVisible;
                $tooltip = $wdoc->getActivity($v, mb_ucfirst(_($v)));
                //$icon = (!$tr) ? "Images/noaccess.png" : ((is_array($tr["ask"])) ? "Images/miniask.png" : "");
                $icon = '';
                if (!$tr) {
                    // $itemMenu->setHtmlLabel(sprintf('%s <div class="fa fa-warning menu--transition-unknow" /> ', htmlspecialchars($label)));
                    $itemMenu->setTextLabel('');
                    $itemMenu->setHtmlLabel(sprintf('<div class="menu--transition-unknow" >%s <div class="fa fa-warning" /> </div>', htmlspecialchars($label)));
                    
                    $itemMenu->setBeforeContent(sprintf('<div style="color:%s" class="fa fa-square menu--transition" />', $wdoc->getColor($v)));
                } else {
                    $itemMenu->setBeforeContent(sprintf('<div style="color:%s" class="fa fa-square menu--transition" />', $wdoc->getColor($v)));
                }
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
                    //$itemMenu->setHtmlAttribute("style", "border-right:solid 10px $color");
                    
                }
                $itemMenu->setVisibility($visibility);
                
                $menu->appendElement($itemMenu);
            }
            
            $sep = new SeparatorMenu('workflowSep');
            $menu->appendElement($sep);
            
            $itemMenu = new ItemMenu('workflowDraw', ___("View workflow graph", "UiMenu"));
            $itemMenu->setTarget("_dialog");
            $itemMenu->setUrl(sprintf("?app=FDL&action=VIEW_WORKFLOW_GRAPH&format=png&orient=LR&tool=dot&id=%d", $wdoc->id));
            $menu->appendElement($itemMenu);
        }
    }
}

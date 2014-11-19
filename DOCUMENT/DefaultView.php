<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

use Dcp\HttpApi\V1\DocManager\DocManager;

class DefaultView extends RenderDefault
{
    
    public function getLabel()
    {
        return ___("Default View", "ddui");
    }
    
    public function getType()
    {
        return IRenderConfig::viewType;
    }
    /**
     * @param BarMenu $menu
     * @param \Doc $document
     * @return BarMenu
     */
    protected function setMenuVisibility(BarMenu & $menu, \Doc $document)
    {
        // Trash document
        if ($document->doctype === "Z") {
            $deleteErr = $document->control("delete");
            if (!$deleteErr) {
                $menu->getElement("restore")->setVisibility(ElementMenu::VisibilityVisible);
            } else {
                $menu->getElement("restore")->setVisibility(ElementMenu::VisibilityDisabled);
            }
            $menu->getElement("delete")->setVisibility(ElementMenu::VisibilityHidden);
            $menu->getElement("modify")->setVisibility(ElementMenu::VisibilityHidden);
            $menu->getElement("lock")->setVisibility(ElementMenu::VisibilityHidden);
            $menu->getElement("unlock")->setVisibility(ElementMenu::VisibilityVisible);
        } else {
            if ($document->locked == - 1) {
                // Fixed document
                $menu->getElement("delete")->setVisibility(ElementMenu::VisibilityHidden);
                $menu->getElement("lock")->setVisibility(ElementMenu::VisibilityHidden);
                $menu->getElement("unlock")->setVisibility(ElementMenu::VisibilityHidden);
                
                $menu->getElement("restore")->setVisibility(ElementMenu::VisibilityHidden);
                $menu->getElement("modify")->setVisibility(ElementMenu::VisibilityHidden);
                $menu->getElement("security")->setVisibility(ElementMenu::VisibilityHidden);
                
                $item = new ItemMenu("gotolatest", ___("View current revision", "UiMenu") , "?app=DOCUMENT&id={{document.properties.id}}");
                $item->setTooltipLabel(___("Display latest document revision", "UiMenu"));
                $item->setBeforeContent('<div class="fa fa-share" />');
                
                $menu->insertBefore("modify", $item);
            } else {
                
                if ($editErr = $document->CanEdit()) {
                    $menu->getElement("modify")->setVisibility(ElementMenu::VisibilityDisabled)->setTooltipLabel($editErr);
                }
                $deleteErr = $document->control("delete");
                if ($deleteErr) {
                    $menu->getElement("delete")->setVisibility(ElementMenu::VisibilityDisabled)->setTooltipLabel($deleteErr);
                }
                $menu->getElement("restore")->setVisibility(ElementMenu::VisibilityHidden);
                // Alive document
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
            }
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
        
        $item = new ItemMenu("modify", ___("Modify", "UiMenu") , "?app=DOCUMENT&mode=edit&id={{document.properties.id}}");
        $item->setTooltipLabel(___("Display document form", "UiMenu"));
        $item->setBeforeContent('<div class="fa fa-pencil" />');
        
        $menu->appendElement($item);
        
        $item = new ItemMenu("delete", ___("Delete", "UiMenu") , "#delete/{{document.properties.id}}");
        $item->setTooltipLabel(___("Put document to the trash", "UiMenu"));
        $confirmOption = new MenuConfirmOptions();
        $confirmOption->title = ___("Confirm deletion of {{{document.properties.title}}}");
        $confirmOption->confirmButton = ___("Confirm deletion", "UiMenu");
        $confirmOption->windowWidth = "350px";
        $confirmOption->windowHeight = "150px";
        $item->useConfirm(sprintf(___("Sure delete %s ?", "UiMenu") , $document->getTitle()) , $confirmOption);
        $item->setBeforeContent('<div class="fa fa-trash-o" />');
        $menu->appendElement($item);
        
        $item = new ItemMenu("restore", ___("Restore", "UiMenu") , "#restore/{{document.properties.id}}");
        $item->setTooltipLabel(___("Restore document from the trash", "UiMenu"));
        $menu->appendElement($item);
        
        $item = new ItemMenu("histo", ___("Historic", "UiMenu") , "#event/document:history");
        $item->setBeforeContent('<div class="fa fa-history" />');
        /*$targetOption = new MenuTargetOptions();
        $targetOption->windowHeight = "400px";
        $targetOption->windowWidth = "600px";
        $item->setTarget("_dialog", $targetOption);*/
        $menu->appendElement($item);
        
        $menu->appendElement(new ListMenu("advanced", ___("Advanced", "UiMenu")));
        
        $item = new ItemMenu("properties", ___("Properties", "UiMenu") , "#event/document:properties");
        $menu->getElement("advanced")->appendElement($item);
        
        $item = new ItemMenu("propertiesOld", ___("Old Properties", "UiMenu") , "?app=FDL&action=IMPCARD&zone=FDL:VIEWPROPERTIES:T&id={{document.properties.id}}");
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
        if ($document->wid > 0) {
            if ($document->locked != - 1) {
                $workflowMenu = new DynamicMenu("workflow", _($document->getStateActivity($document->getState())));
                $workflowMenu->setContent(function (ListMenu & $menu) use ($document)
                {
                    $this->getWorkflowMenu($document, $menu);
                });
                $workflowMenu->setBeforeContent(sprintf('<div style="color:%s" class="fa fa-square" />', $document->getStateColor("transparent")));
                $workflowMenu->setHtmlAttribute("class", "menu--workflow menu--right");
                $menu->appendElement($workflowMenu);
            } else {
                $workflowMenu = new SeparatorMenu("workflow", _($document->getState()));
                
                $workflowMenu->setBeforeContent(sprintf('<div style="color:%s" class="fa fa-square" />', $document->getStateColor("transparent")));
                $workflowMenu->setHtmlAttribute("class", "menu--workflow menu--right");
                $menu->appendElement($workflowMenu);
            }
        }
        
        $this->addCvMenu($document, $menu);
        $this->addFamilyMenu($document, $menu);
        return $this->setMenuVisibility($menu, $document);
    }
    
    public function getTemplates(\Doc $document = null)
    {
        $templates = parent::getTemplates($document);
        if ($document->locked == - 1) {
            $templates["sections"]["header"]["file"] = "DOCUMENT/IHM/views/document/document__header-fixed.mustache";
        }
        return $templates;
    }
    /**
     * Get workflow submenu contents
     * @param \Doc $doc
     * @param ListMenu $menu
     */
    protected function getWorkflowMenu(\Doc $doc, ListMenu & $menu)
    {
        
        if ($doc->wid > 0 && $doc->locked != - 1) {
            /**
             * @var \WDoc $wdoc
             */
            $wdoc = DocManager::getDocument($doc->wid, false);
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
    /**
     * Add Menu item defined by attribute family
     * @param \Doc $doc
     * @param BarMenu $menu target menu
     */
    public function addFamilyMenu(\Doc $doc, BarMenu & $menu)
    {
        include_once ("FDL/popupdocdetail.php");
        $links = array();
        addFamilyPopup($links, $doc);
        $this->addOldMenu($links, $menu);
    }
    /**
     * Add Menu item defined by attribute family
     * @param \Doc $doc
     * @param BarMenu $menu target menu
     * @throws \Dcp\Ui\Exception
     */
    protected function addCvMenu(\Doc $doc, BarMenu & $menu)
    {
        if ($doc->cvid > 0) {
            $cv = DocManager::getDocument($doc->cvid);
            if (!$cv) {
                throw new Exception("UI0202", $doc->cvid);
            }
            /**
             * @var \CVDoc $cv
             */
            $cv->set($doc);
            $views = $cv->getDisplayableViews();
            foreach ($views as $view) {
                $vid = $view["cv_idview"];
                $label = $cv->getLocaleViewLabel($vid);
                $idMenu = "vid-" . $vid;
                $cvMenu = $view["cv_menu"];
                $menuItem = new ItemMenu($idMenu, $label);
                $menuItem->setUrl(sprintf("?app=DOCUMENT&vid=%s&id=%s", $vid, $doc->id));
                if ($cvMenu) {
                    $idListMenu = $cvMenu;
                    $lmenu = $menu->getElement($idListMenu);
                    if (!$lmenu) {
                        // Create new list menu
                        $lmenu = new listMenu($idListMenu, $cv->getLocaleViewMenu($vid));
                        $menu->insertBefore("advanced", $lmenu);
                    }
                    $lmenu->appendElement($menuItem);
                } else {
                    $menu->insertBefore("advanced", $menuItem);
                }
            }
            $defaultview = $doc->getDefaultView(true);
            if ($defaultview !== 0) {
                $modifyItem = $menu->getElement("modify");
                $modifyItem->setTextLabel($cv->getLocaleViewLabel($defaultview['cv_idview']));
            }
        }
    }
    /**
     * Add Menu item defined by attribute family
     * @param array $links old configuration format for links
     * @param BarMenu $menu target menu
     */
    protected function addOldMenu(array $links, BarMenu & $menu)
    {
        $advMenu = $menu->getElement("advanced");
        foreach ($links as $idLink => $link) {
            if (isset($link["visibility"])) {
                $menuItem = new ItemMenu($idLink, $link["descr"]);
                if (!empty($link["target"])) {
                    if (preg_match("/[0-9]+$/", $link["target"])) {
                        
                        $menuItem->setTarget("_dialog");
                    } else {
                        $menuItem->setTarget($link["target"]);
                    }
                } else {
                    $menuItem->setTarget("_dialog");
                }
                if (!empty($link["url"])) {
                    $menuItem->setUrl($link["url"]);
                }
                if (!empty($link["title"])) {
                    $menuItem->setTooltipLabel($link["title"]);
                }
                if (!empty($link["confirm"]) && $link["confirm"] === "true") {
                    
                    $menuItem->useConfirm($link["tconfirm"]);
                }
                
                if (!empty($link["submenu"])) {
                    if ($link["visibility"] === POPUP_ACTIVE || $link["visibility"] === POPUP_CTRLACTIVE) {
                        $lmenu = $menu->getElement($link["submenu"]);
                        if (!$lmenu) {
                            // Create new list menu
                            $lmenu = new listMenu($link["submenu"], $link["submenu"]);
                            $menu->insertBefore("advanced", $lmenu);
                        }
                        $lmenu->appendElement($menuItem);
                    }
                } elseif ($link["visibility"] === POPUP_ACTIVE) {
                    $menu->insertBefore("advanced", $menuItem);
                } elseif ($link["visibility"] === POPUP_CTRLACTIVE) {
                    $advMenu->appendElement($menuItem);
                }
            }
        }
    }
}

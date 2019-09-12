<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\Utils\Strings;
use \SmartStructure\Fields\Cvdoc as CvAttributes;

class DefaultView extends RenderDefault
{

    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return ___("Default View", "ddui");
    }

    public function getType()
    {
        return IRenderConfig::viewType;
    }

    /**
     * @param BarMenu                             $menu
     * @param \Anakeen\Core\Internal\SmartElement $document
     *
     * @return BarMenu
     */
    protected function setMenuVisibility(BarMenu & $menu, \Anakeen\Core\Internal\SmartElement $document)
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
        } else {
            if ($document->locked == -1) {
                // Fixed document
                $menu->getElement("delete")->setVisibility(ElementMenu::VisibilityHidden);

                $menu->getElement("restore")->setVisibility(ElementMenu::VisibilityHidden);
                $menu->getElement("modify")->setVisibility(ElementMenu::VisibilityHidden);

                $item = new ItemMenu("gotolatest", ___("View current revision", "UiMenu"));
                $item->setUrl(sprintf("#action/document.load:%d:%s", $document->initid, \Anakeen\Routes\Ui\DocumentView::defaultViewConsultationId));
                if ($this->displayDefaultMenuTooltip) {
                    $item->setTooltipLabel(___("Display latest document revision", "UiMenu"));
                }
                $item->setBeforeContent('<div class="fa fa-share" />');

                $menu->insertBefore("modify", $item);
            } else {
                if ($editErr = $document->canEdit()) {
                    $menu->getElement("modify")->setVisibility(ElementMenu::VisibilityDisabled);
                    if ($this->displayDefaultMenuTooltip) {
                        $menu->getElement("modify")->setTooltipLabel($editErr);
                    }
                }
                $deleteErr = $document->control("delete");
                if ($deleteErr) {
                    $menu->getElement("delete")->setVisibility(ElementMenu::VisibilityDisabled);
                    if ($this->displayDefaultMenuTooltip) {
                        $menu->getElement("delete")->setTooltipLabel($deleteErr);
                    }
                }
                $menu->getElement("restore")->setVisibility(ElementMenu::VisibilityHidden);
            }
        }
        return $menu;
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     *
     * @return BarMenu Menu configuration
     */
    public function getMenu(\Anakeen\Core\Internal\SmartElement $document): BarMenu
    {
        $menu = new BarMenu();

        $this->appendWorkflowMenu($document, $menu);

        $this->setEmblemMenu($document, $menu);

        $item = new ItemMenu("modify", ___("Modify", "UiMenu"), "#action/document.edit");
        if ($this->displayDefaultMenuTooltip) {
            $item->setTooltipLabel(___("Display document form", "UiMenu"));
        }
        $item->setBeforeContent('<div class="fa fa-pencil" />');
        $item->setImportant(true);
        $menu->appendElement($item);

        $item = new ItemMenu("delete", ___("Delete", "UiMenu"), "#action/document.delete");
        if ($this->displayDefaultMenuTooltip) {
            $item->setTooltipLabel(___("Put document to the trash", "UiMenu"));
        }
        $confirmOption = new MenuConfirmOptions();
        $confirmOption->title = ___("Confirm deletion of {{{document.properties.title}}}", "render");
        $confirmOption->confirmButton = ___("Confirm deletion", "UiMenu");
        $confirmOption->windowWidth = "35rem";
        $confirmOption->windowHeight = "13rem";
        $item->useConfirm(sprintf(___("Sure delete %s ?", "UiMenu"), $document->getHTMLTitle()), $confirmOption);
        $item->setBeforeContent('<div class="fa fa-trash-o" />');
        $menu->appendElement($item);

        $item = new ItemMenu("restore", ___("Restore", "UiMenu"), "#action/document.restore");
        if ($this->displayDefaultMenuTooltip) {
            $item->setTooltipLabel(___("Restore document from the trash", "UiMenu"));
        }
        $menu->appendElement($item);

        if (ContextManager::getCurrentUser()->id == \Anakeen\Core\Account::ADMIN_ID) {
            self::appendSystemMenu($document, $menu);
        }

        $this->addCvMenu($document, $menu);
        $this->addHelpMenu($document, $menu);
        return $this->setMenuVisibility($menu, $document);
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @param BarMenu                             $menu
     */
    public function appendWorkflowMenu($document, $menu)
    {
        if ($document->wid > 0) {
            if ($document->locked != -1) {
                $workflowMenu = new DynamicMenu("workflow");
                $workflowMenu->setHtmlLabel(sprintf(
                    '<i style="color:%s" class="menu--workflow-color fa fa-square" /> %s',
                    $document->getStateColor("transparent"),
                    htmlspecialchars($document->getStepLabel())
                ));
                $workflowMenu->setContent(function (ListMenu & $menu) use ($document) {
                    $this->getWorkflowMenu($document, $menu);
                });
                $workflowMenu->setBeforeContent('<div class="fa fa-sitemap" />');
                $workflowMenu->setHtmlAttribute("class", "menu--workflow menu--left");
                if ($this->displayDefaultMenuTooltip) {
                    $workflowMenu->setTooltipLabel(___("Goto next activity", "UiMenu"), "left");
                }
                $workflowMenu->setImportant(true);
                $menu->appendElement($workflowMenu);
            } else {
                $workflowMenu = new SeparatorMenu("workflow", $document->getStepLabel());

                $workflowMenu->setBeforeContent(sprintf('<div style="color:%s" class="fa fa-square" />', $document->getStateColor("transparent")));
                $workflowMenu->setHtmlAttribute("class", "menu--workflow menu--right");
                $menu->appendElement($workflowMenu);
            }
        }
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @param Barmenu                             $menu
     */
    public static function appendSystemMenu($document, $menu)
    {
        $systemList = new ListMenu('system', ___("System", "UiMenu"));
        $systemList->setBeforeContent('<div class="fa fa-cogs" />');

        $item = new ItemMenu("historic", ___("Historic", "UiMenu"), "#action/document.history");
        $item->setBeforeContent('<div class="fa fa-history" />');
        $systemList->appendElement($item);

        $item = new ItemMenu("properties", ___("Properties", "UiMenu"), "#action/document.properties");
        $item->setBeforeContent('<div class="fa fa-info" />');
        $systemList->appendElement($item);

        $item = new ItemMenu("lock", ___("Lock", "UiMenu"), "#action/document.lock");

        $item->setTooltipLabel(___("Lock document", "ddui"));
        $item->setBeforeContent('<div class="fa fa-lock" />');
        $systemList->appendElement($item);

        $item = new ItemMenu("unlock", ___("Unlock", "UiMenu"), "#action/document.unlock");

        $item->setTooltipLabel(___("Unlock document", "ddui"));
        $item->setBeforeContent('<div class="fa fa-unlock" />');
        $systemList->appendElement($item);
        $menu->appendElement($systemList);

        if ($document->isLocked()) {
            $menu->getElement("lock")->setVisibility(ElementMenu::VisibilityHidden);
            $errcuf = $document->CanUnLockFile();
            if ($errcuf) {
                if ($document->locked == -1) {
                    $menu->getElement("unlock")->setVisibility(ElementMenu::VisibilityHidden);
                } else {
                    $menu->getElement("unlock")->setVisibility(ElementMenu::VisibilityDisabled)->setTooltipLabel($errcuf);
                }
            }
        } else {
            $menu->getElement("unlock")->setVisibility(ElementMenu::VisibilityHidden);
        }
    }

    public function getTemplates(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $templates = parent::getTemplates($document);
        if ($document->locked == -1) {
            $templates["sections"]["header"]["file"] = DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/views/document/document__header-fixed.mustache";
        }
        return $templates;
    }

    /**
     * Get workflow submenu contents
     *
     * @param \Anakeen\Core\Internal\SmartElement $doc
     * @param ListMenu                            $menu
     */
    protected function getWorkflowMenu(\Anakeen\Core\Internal\SmartElement $doc, ListMenu & $menu)
    {

        if ($doc->wid > 0 && $doc->locked != -1) {
            /**
             * @var \Anakeen\SmartStructures\Wdoc\WDocHooks $wdoc
             */
            $wdoc = SEManager::getDocument($doc->wid, false);
            if (!$doc) {
                return;
            }
            $wdoc->set($doc, true);
            $fstate = $wdoc->getFollowingStates();

            foreach ($fstate as $v) {
                $tr = $wdoc->searchTransition($doc->state, $v);

                $label = $tr['id'] ? $wdoc->getTransitionLabel($tr['id']) : $wdoc->getActivity($v, Strings::mbUcfirst($wdoc->getStateLabel($v)));

                $itemMenu = new ItemMenu($v, $label);

                $itemMenu->setUrl(sprintf("#action/document.transition:%s:%s", urlencode($tr['id']), urlencode($v)));
                $itemMenu->setTarget("_dialog"); // alternative to data-popup
                $visibility = $itemMenu::VisibilityVisible;
                $tooltip = $wdoc->getActivity($v, Strings::mbUcfirst($wdoc->getStateLabel($v)));
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
                $m0error = '';

                if ($tr) {
                    // verify m0
                    $m0error = $wdoc->executeTransitionM($tr['id'], "m0", $v, $wdoc->doc->state, '');

                    if ($m0error) {
                        $visibility = $itemMenu::VisibilityDisabled;
                        $tooltip = $m0error;
                        $icon = ""; // no image "Images/nowaccess.png";
                    }
                }

                if ($icon) {
                    $itemMenu->setIcon($icon);
                }
                if ($tooltip) {
                    if ($m0error || $this->displayDefaultMenuTooltip) {
                        $itemMenu->setTooltipLabel($tooltip, "left");
                    }
                }

                $itemMenu->setVisibility($visibility);

                $menu->appendElement($itemMenu);
            }

            $sep = new SeparatorMenu('workflowSep');
            $menu->appendElement($sep);

            $itemMenu = new ItemMenu('workflowDraw', ___("View transition graph", "UiMenu"));
            $itemMenu->setUrl(sprintf("#action/document.transitionGraph"));
            if (count($fstate) === 0) {
                $itemMenu->setVisibility($itemMenu::VisibilityDisabled);
            }
            $menu->appendElement($itemMenu);
        }
    }

    /**
     * Add Menu item defined by attribute family
     *
     * @param \Anakeen\Core\Internal\SmartElement $doc
     * @param BarMenu                             $menu target menu
     *
     * @throws \Anakeen\Ui\Exception
     */
    protected function addCvMenu(\Anakeen\Core\Internal\SmartElement $doc, BarMenu & $menu)
    {
        if ($doc->cvid > 0) {
            $cv = SEManager::getDocument($doc->cvid);
            if (!$cv) {
                throw new Exception("UI0202", $doc->cvid);
            }
            /**
             * @var \SmartStructure\Cvdoc $cv
             */
            $cv->Set($doc);
            $views = $cv->getDisplayableViews();
            foreach ($views as $view) {
                $vid = $view[CvAttributes::cv_idview];

                $label = $cv->getLocaleViewLabel($vid);
                $idMenu = "vid-" . $vid;
                $cvMenu = $view["cv_menu"];
                $menuItem = new ItemMenu($idMenu, $label);
                $menuItem->setUrl(sprintf("#action/document.load:%d:%s", $doc->initid, $vid));
                if ($cvMenu) {
                    $idListMenu = $cv->getLocaleViewMenu($vid);
                    $lmenu = $menu->getElement($idListMenu);
                    if (!$lmenu) {
                        // Create new list menu
                        $lmenu = new ListMenu($idListMenu, $idListMenu);
                        $menu->insertBefore("delete", $lmenu);
                    }
                    $lmenu->appendElement($menuItem);
                } else {
                    $menu->insertBefore("delete", $menuItem);
                }
            }
            $defaultview = MaskManager::getDefaultView($doc, true);
            if ($defaultview !== 0) {
                $modifyItem = $menu->getElement("modify");
                $modifyItem->setTextLabel($cv->getLocaleViewLabel($defaultview['cv_idview']));

                $menu->removeElement("vid-" . $defaultview['cv_idview']);
            }
        }
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     *
     * @return DocumentTemplateContext get template controller
     */
    public function getContextController(\Anakeen\Core\Internal\SmartElement $document): DocumentTemplateContext
    {
        $controller = parent::getContextController($document);
        if ($document->locked == -1) {
            $controller->offsetSet("isdocumentdeleted", ($document->doctype == "Z"));
            $controller->offsetSet("formattedRevdate", substr($document->mdate, 0, 16));
        }
        return $controller;
    }
}

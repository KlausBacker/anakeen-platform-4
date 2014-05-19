<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class DefaultView implements RenderConfig
{
    
    public function getLabel()
    {
        return _("Default View");
    }
    
    public function getDocumentTemplate()
    {
        $templateFile = "DOCUMENT/Render/View/defaultView.html";
        if (!file_exists($templateFile)) {
            throw new Exception("UI0005", $templateFile);
        }
        return file_get_contents($templateFile);
    }
    
    public function getCssReferences()
    {
        return array(
            "css/dcp/system.css"
        );
    }
    
    public function getJsReferences()
    {
        return array(
            "lib/jquery/jquery.js"
        );
    }
    
    public function getTemplates()
    {
        return array(
            "body" => array(
                "file" => "DOCUMENT/Render/View/defaultView.mustache"
            ) ,
            "sections" => array(
                "header" => array(
                    "file" => "DOCUMENT/Render/View/defaultHeader.mustache"
                ) ,
                "menu" => array(
                    "file" => "DOCUMENT/Render/View/defaultMenu.mustache"
                ) ,
                "content" => array(
                    "file" => "DOCUMENT/Render/View/defaultContent.mustache"
                ) ,
                "footer" => array(
                    "file" => "DOCUMENT/Render/View/defaultFooter.mustache"
                ) ,
                "message" => array(
                    "file" => "DOCUMENT/Render/View/defaultMessage.mustache"
                )
            ) ,
            "attribute" => array(
                "text" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "longtext" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultLongtextValue.mustache"
                    )
                ) ,
                
                "image" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "file" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "frame" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "enum" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "date" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "int" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "double" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "money" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "password" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "xml" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "thesaurus" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "tab" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "time" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "timestamp" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "array" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "color" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "docid" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "htmltext" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "account" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/View/Attribute/defaultTextValue.mustache"
                    )
                )
            )
        );
    }
    
    public function getOptions()
    {
        return array(
            "common" => array(
                "showEmptyContent" => "",
                "labelPosition" => "left",
                "linkTitle" => "",
                "linkTarget" => "_self",
                "linkConfirm" => false,
                "linkTextConfirm" => ""
            ) ,
            "account" => array(
                "noAccessText" => _("Account access deny")
            ) ,
            "date" => array(
                "format" => _("Y-m-d")
            ) ,
            "docid" => array(
                "noAccessText" => _("Information access deny")
            ) ,
            "enum" => array(
                "boolColor" => ""
            ) ,
            "file" => array(
                "downloadInline" => false
            ) ,
            "image" => array(
                "downloadInline" => false,
                "width" => "80px"
            ) ,
            "money" => array(
                "format" => "%!.2n"
            ) ,
            "text" => array(
                "format" => "%s"
            ) ,
            "time" => array(
                "format" => "%H=>%M"
            ) ,
            "timestamp" => array(
                "format" => _("Y-m-d") . " %H=>%M"
            )
        );
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
        
        $menu->appendElement(new ItemMenu("modify", ___("Modify", "UiMenu") , "?app=DOCUMENT&action=VIEW&render=defaultEdit&id={{document.property.id}}"));
        
        $menu->appendElement(new ItemMenu("delete", ___("Delete", "UiMenu") , "?app=...&id={{document.property.id}}"));
        $menu->appendElement(new ItemMenu("restore", ___("Restore", "UiMenu") , "?app=...&id={{document.property.id}}"));
        
        if ($document->wid > 0) {
            $workflowMenu = new DynamicMenu("workflow", ___("Transition", "UiMenu"));
            $workflowMenu->setUrl("?app=...&id={{document.property.id}}&menu=workflow");
            $menu->appendElement($workflowMenu);
        }
        
        $menu->appendElement(new ItemMenu("histo", ___("Historic", "UiMenu") , "?app=...&id={{document.property.id}}"));
        $menu->getElement("histo")->setTarget('_blank')->setHtmlAttribute("date-test", "testing");
        
        $menu->appendElement(new ListMenu("advanced", ___("Advanced", "UiMenu")));
        $menu->getElement("advanced")->appendElement(new ItemMenu("properties", ___("properties", "UiMenu") , "?app=DOCUMENT&action=VIEW&render=defaultEdit&id={{document.property.id}}"));
        
        $securitySubMenu = new ListMenu("security", ___("Security", "UiMenu"));
        $securitySubMenu->appendElement(new ItemMenu("profil", ___("Profil access", "UiMenu") , "?app=...={{document.property.id}}"));
        $securitySubMenu->appendElement(new ItemMenu("lock", ___("Lock", "UiMenu") , "?app=...={{document.property.id}}"));
        $securitySubMenu->appendElement(new ItemMenu("unlock", ___("Unlock", "UiMenu") , "?app=...={{document.property.id}}"));
        
        $menu->getElement("advanced")->appendElement($securitySubMenu);
        
        return $this->setMemuVisibility($menu, $document);
    }
}

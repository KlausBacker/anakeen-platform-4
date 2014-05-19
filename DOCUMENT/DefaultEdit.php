<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class DefaultEdit implements RenderConfig
{
    
    public function getLabel()
    {
        return _("Default Edit");
    }
    
    public function getDocumentTemplate()
    {
        $templateFile = "DOCUMENT/Render/Edit/defaultView.html";
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
                "file" => "DOCUMENT/Render/Edit/defaultView.mustache"
            ) ,
            "sections" => array(
                "header" => array(
                    "file" => "DOCUMENT/Render/Edit/defaultHeader.mustache"
                ) ,
                "menu" => array(
                    "file" => "DOCUMENT/Render/Edit/defaultMenu.mustache"
                ) ,
                "content" => array(
                    "file" => "DOCUMENT/Render/Edit/defaultContent.mustache"
                ) ,
                "footer" => array(
                    "file" => "DOCUMENT/Render/Edit/defaultFooter.mustache"
                ) ,
                "message" => array(
                    "file" => "DOCUMENT/Render/Edit/defaultMessage.mustache"
                )
            ) ,
            "attribute" => array(
                "text" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "longtext" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultLongtextValue.mustache"
                    )
                ) ,
                
                "image" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "file" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "frame" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "enum" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "date" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "int" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "double" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "money" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "password" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "xml" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "thesaurus" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "tab" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "time" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "timestamp" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "array" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "color" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "docid" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "htmltext" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
                    )
                ) ,
                
                "account" => array(
                    "label" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultViewLabel.mustache"
                    ) ,
                    "value" => array(
                        "file" => "DOCUMENT/Render/Edit/Attribute/defaultTextValue.mustache"
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
        return RenderConfig::editType;
    }
    /**
     * @param \Doc $document Document object instance
     * @return BarMenu Menu configuration
     */
    public function getMenu(\Doc $document)
    {
        $menu = new BarMenu();
        if ($document->id > 0) {
            $menu->appendElement(new ItemMenu("save", ___("Save", "UiMenu") , "..js.."));
        } else {
            $menu->appendElement(new ItemMenu("save", ___("Create", "UiMenu") , "..js.."));
        }
        $menu->appendElement(new ItemMenu("cancel", ___("Cancel", "UiMenu") , "?app=DOCUMENT&action=&id={{document.property.id}}"));
        
        return $menu;
    }
}

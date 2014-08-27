<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class RenderDefault implements RenderConfig
{
    public function getLabel()
    {
        return _("Abstract view");
    }
    
    public function getDocumentTemplate()
    {
        $templateFile = "DOCUMENT/Render/defaultView.html";
        if (!file_exists($templateFile)) {
            throw new Exception("UI0005", $templateFile);
        }
        return file_get_contents($templateFile);
    }
    
    public function getCssReferences()
    {
        $version = \ApplicationParameterManager::getParameterValue("CORE", "WVERSION");
        return array(
            "css/dcp/document/bootstrap.css?ws=" . $version,
            "css/dcp/document/kendo.css?ws=" . $version,
            "css/dcp/document/document.css?ws=" . $version
        );
    }
    
    public function getJsReferences()
    {
        return array( //"lib/jquery/jquery.js"
            
        );
    }
    
    public function getRequireReference()
    {
        $version = \ApplicationParameterManager::getParameterValue("CORE", "WVERSION");
        return array(
            "ws" => $version,
            "src" => "lib/RequireJS/require.js?ws=" . $version,
            "config" => "DOCUMENT/IHM/require_config.js?ws=" . $version,
            "main" => "DOCUMENT/IHM/main.js?ws=" . $version,
            "prod" => "DOCUMENT/IHM/main-built.js?ws=" . $version
        );
    }
    
    public function getTemplates()
    {
        return array(
            "body" => array(
                "file" => "DOCUMENT/IHM/views/document/document.mustache"
            ) ,
            "sections" => array(
                "header" => array(
                    "file" => "DOCUMENT/IHM/views/document/document__header.mustache"
                ) ,
                "menu" => array(
                    "file" => "DOCUMENT/IHM/views/document/document__menu.mustache"
                ) ,
                "content" => array(
                    "file" => "DOCUMENT/IHM/views/document/document__content.mustache"
                ) ,
                "footer" => array(
                    "file" => "DOCUMENT/IHM/views/document/document__footer.mustache"
                )
            ) ,
            "menu" => array(
                "menu" => array(
                    "file" => "DOCUMENT/IHM/widgets/menu/menu.mustache",
                ) ,
                "itemMenu" => array(
                    "file" => "DOCUMENT/IHM/widgets/menu/itemMenu.mustache"
                ) ,
                "listMenu" => array(
                    "file" => "DOCUMENT/IHM/widgets/menu/listMenu.mustache"
                ) ,
                "dynamicMenu" => array(
                    "file" => "DOCUMENT/IHM/widgets/menu/dynamicMenu.mustache"
                ) ,
                "separatorMenu" => array(
                    "file" => "DOCUMENT/IHM/widgets/menu/separatorMenu.mustache"
                )
            ) ,
            "attribute" => array(
                "simpleWrapper" => array(
                    "file" => "DOCUMENT/IHM/views/attributes/singleWrapper.mustache"
                ) ,
                
                "default" => array( // use it when no type is defined
                    "write" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/default/write.mustache"
                    ) ,
                    "read" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/default/read.mustache"
                    )
                ) ,
                "label" => array(
                    "file" => "DOCUMENT/IHM/widgets/attributes/label/label.mustache"
                ) ,
                "longtext" => array(
                    "write" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/longtext/longtextWrite.mustache"
                    ) ,
                    "read" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/default/read.mustache"
                    )
                ) ,
                "file" => array(
                    "write" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/file/fileWrite.mustache"
                    ) ,
                    "read" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/file/fileRead.mustache"
                    )
                ) ,
                "enum" => array(
                    "write" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/enum/enumWrite.mustache"
                    ) ,
                    "read" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/enum/enumRead.mustache"
                    )
                ) ,
                "htmltext" => array(
                    "write" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/longtext/longtextWrite.mustache"
                    ) ,
                    "read" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/htmltext/htmltextRead.mustache"
                    )
                ) ,
                "docid" => array(
                    "write" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/docid/docidWrite.mustache"
                    ) ,
                    "read" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/docid/docidRead.mustache"
                    )
                ) ,
                "account" => array(
                    "write" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/docid/docidWrite.mustache"
                    ) ,
                    "read" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/docid/docidRead.mustache"
                    )
                ) ,
                "image" => array(
                    "write" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/image/imageWrite.mustache"
                    ) ,
                    "read" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/image/imageRead.mustache"
                    )
                ) ,
                "frame" => array(
                    "label" => array(
                        "file" => "DOCUMENT/IHM/views/attributes/frame/label.mustache"
                    ) ,
                    "content" => array(
                        "file" => "DOCUMENT/IHM/views/attributes/frame/content.mustache"
                    )
                ) ,
                "array" => array(
                    "label" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/array/label.mustache"
                    ) ,
                    "content" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/array/content.mustache"
                    ) ,
                    "line" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/array/line.mustache"
                    )
                ) ,
                "tab" => array(
                    "label" => array(
                        "file" => "DOCUMENT/IHM/views/attributes/tab/label.mustache"
                    )
                )
            ) ,
            "window" => array(
                "confirm" => array(
                    "file" => "DOCUMENT/IHM/views/window/confirm.mustache"
                )
            )
        );
    }
    /**
     * @param \Doc $document Document instance
     * @return RenderOptions
     */
    public function getOptions(\Doc $document)
    {
        $opt = new RenderOptions();
        
        $opt->setCustomOption("mode", $this->getType());
        $this->setLinkOption($document, $opt);
        
        $opt->image()->setLabels(array(
            "dropFileHere" => ___("Drop image here", "ddui-image") ,
            "placeHolder" => ___("Click to upload image", "ddui-image") ,
            "tooltipLabel" => ___("Choose image", "ddui-image") ,
            "downloadLabel" => ___("Download image", "ddui-image") ,
            "kiloByte" => ___("kB", "ddui-file") ,
            "recording" => ___("Recording", "ddui-file") ,
            "transferring" => ___("Transferring", "ddui-file") ,
        ));
        $opt->file()->setLabels(array(
            "dropFileHere" => ___("Drop file here", "ddui-file") ,
            "placeHolder" => ___("Click to upload file", "ddui-file") ,
            "tooltipLabel" => ___("Choose file", "ddui-file") ,
            "downloadLabel" => ___("Download file", "ddui-file") ,
            "kiloByte" => ___("kB", "ddui-file") ,
            "recording" => ___("Recording", "ddui-file") ,
            "transferring" => ___("Transferring", "ddui-file") ,
        ));
        return $opt;
    }
    
    protected function setLinkOption(\Doc $document, RenderOptions & $opt)
    {
        
        $linkOption = new htmlLinkOptions();
        $linkOption->title = ___("View {{displayValue}}", "ddui");
        $linkOption->url = "?app=DOCUMENT&action=VIEW&id={{value}}";
        $opt->docid()->setLink($linkOption);
        $opt->account()->setLink(clone $linkOption);
        $opt->thesaurus()->setLink(clone $linkOption);
        
        $oas = $document->getNormalAttributes();
        
        foreach ($oas as $oa) {
            if ($oa->link) {
                $linkOption = new htmlLinkOptions($document->urlWhatEncode($oa->link));
                
                $opt->text($oa->id)->setLink($linkOption);
            }
        }
    }
    /**
     * @param \Doc $document
     * @return RenderAttributeVisibilities new attribute visibilities
     */
    public function getVisibilities(\Doc $document)
    {
        return new RenderAttributeVisibilities($document);
    }
    
    public function getType()
    {
        return "abstract";
    }
    /**
     * @param \Doc $document Document object instance
     * @return BarMenu Menu configuration
     */
    public function getMenu(\Doc $document)
    {
        $menu = new BarMenu();
        
        return $menu;
    }
    /**
     * @param \Doc $document Document instance
     * @return DocumentTemplateContext get template controller
     */
    public function getContextController(\Doc $document)
    {
        return new DocumentTemplateContext($document);
    }
}

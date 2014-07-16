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
        return array(
            "css/dcp/document/bootstrap.css",
            "css/dcp/document/kendo.css",
            "css/dcp/document/document.css"
        );
    }
    
    public function getJsReferences()
    {
        return array( //"lib/jquery/jquery.js"
            
        );
    }
    
    public function getRequireReference()
    {
        return array(
            "src" => "lib/RequireJS/require.js",
            "main" => "DOCUMENT/IHM/main.js",
            "prod" => "DOCUMENT/IHM/main-prod.js"
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
                "label" => array(
                    "file" => "DOCUMENT/IHM/widgets/attributes/label/label.mustache"
                ) ,
                "text" => array(
                    "write" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/default/write.mustache"
                    ) ,
                    "read" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/default/read.mustache"
                    )
                ) ,
                "int" => array(
                    "write" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/default/write.mustache"
                    ) ,
                    "read" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/default/read.mustache"
                    )
                ) ,
                "double" => array(
                    "write" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/default/write.mustache"
                    ) ,
                    "read" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/default/read.mustache"
                    )
                ) ,
                "docid" => array(
                    "write" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/docid/write.mustache"
                    ) ,
                    "read" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/docid/read.mustache"
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
        return $opt;
    }

    protected function setLinkOption(\Doc $document, RenderOptions &$opt) {
        $oas=$document->getNormalAttributes();

        foreach ($oas as $oa) {
            if ($oa->link) {
                $opt->text($oa->id)->setLink($document->urlWhatEncode($oa->link));

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

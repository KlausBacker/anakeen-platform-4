<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 03/06/14
 * Time: 12:16
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
            "css/dcp/document2/document2.css"
        );
    }

    public function getJsReferences()
    {
        return array(//"lib/jquery/jquery.js"
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
                "file" => "DOCUMENT/Render/defaultView.mustache"
            ),
            "sections" => array(
                "header" => array(
                    "file" => "DOCUMENT/Render/defaultHeader.mustache"
                ),
                "menu" => array(
                    "file" => "DOCUMENT/Render/defaultMenu.mustache"
                ),
                "content" => array(
                    "file" => "DOCUMENT/Render/defaultContent.mustache"
                ),
                "footer" => array(
                    "file" => "DOCUMENT/Render/defaultFooter.mustache"
                )
            ),
            "menu" => array(
                "menu" => array(
                    "file" => "DOCUMENT/IHM/widgets/menu/menu.mustache",
                ),
                "menu__element" => array(
                    "file" => "DOCUMENT/IHM/widgets/menu/menu__element.mustache"
                )
            ),
            "attribute" => array(
                "simpleWrapper" => array(
                    "file" => "DOCUMENT/IHM/views/attributes/singleWrapper.mustache"
                ),
                "label" => array(
                    "file" => "DOCUMENT/IHM/widgets/attributes/label/label.mustache"
                ),
                "text" => array(
                    "write" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/text/write.mustache"
                    ),
                    "read" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/text/read.mustache"
                    )
                ),
                "frame" => array(
                    "label" => array(
                        "file" => "DOCUMENT/IHM/views/attributes/frame/label.mustache"
                    ),
                    "content" => array(
                        "file" => "DOCUMENT/IHM/views/attributes/frame/content.mustache"
                    )
                ),
                "array" => array(
                    "label" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/array/label.mustache"
                    ),
                    "content" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/array/content.mustache"
                    ),
                    "line" => array(
                        "file" => "DOCUMENT/IHM/widgets/attributes/array/line.mustache"
                    )
                ),
                "tab" => array(
                    "label" => array(
                        "file" => "DOCUMENT/IHM/views/attributes/tab/label.mustache"
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
            ),
            "account" => array(
                "noAccessText" => _("Account access deny")
            ),
            "date" => array(
                "format" => _("Y-m-d")
            ),
            "docid" => array(
                "noAccessText" => _("Information access deny")
            ),
            "enum" => array(
                "boolColor" => ""
            ),
            "file" => array(
                "downloadInline" => false
            ),
            "image" => array(
                "downloadInline" => false,
                "width" => "80px"
            ),
            "money" => array(
                "format" => "%!.2n"
            ),
            "text" => array(
                "format" => "%s"
            ),
            "time" => array(
                "format" => "%H=>%M"
            ),
            "timestamp" => array(
                "format" => _("Y-m-d") . " %H=>%M"
            ),
            "mode" => $this->getType()
        );
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
} 
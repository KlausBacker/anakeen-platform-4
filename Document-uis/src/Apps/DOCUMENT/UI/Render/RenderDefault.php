<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class RenderDefault implements IRenderConfig
{
    /**
     * @var bool display or not default system menu
     */
    protected $displayDefaultMenuTooltip = false;
    
    protected $customClientData = null;
    
    public function getLabel(\Doc $document = null)
    {
        return __CLASS__;
    }
    
    public function getCssReferences(\Doc $document = null)
    {
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        return array(
            "bootstrap" => "css/dcp/document/bootstrap.css?ws=" . $version,
            "kendo" => "css/dcp/document/kendo.css?ws=" . $version,
            "document" => "css/dcp/document/document.css?ws=" . $version,
            "datatable" => "uiAssets/externals/jquery-dataTables/css/dataTables.bootstrap.css?ws=" . $version
        );
    }
    
    public function getJsReferences(\Doc $document = null)
    {
        return array();
    }
    
    public function getRequireReference()
    {
        $pubExternalPath = "uiAssets/externals";
        $pubInternalPath = "uiAssets/anakeen/IHM";
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $modeDebug = \ApplicationParameterManager::getParameterValue("DOCUMENT", "MODE_DEBUG");
        if (\ApplicationParameterManager::getParameterValue("DOCUMENT", "ACTIVATE_LOGGING") === "TRUE") {
            $jsRef = array(
                "traceKit" => "$pubExternalPath/TraceKit/tracekit.js?ws=" . $version,
                "traceError" => "$pubInternalPath/dynacaseReport.js?ws=" . $version
            );
        } else {
            $jsRef = array(
                "traceError" => "$pubInternalPath/dynacaseReportLight.js?ws=" . $version
            );
        }
        if ($modeDebug !== "FALSE") {
            return $jsRef = array_merge($jsRef, array(
                "require" => "$pubExternalPath/RequireJS/require.js?ws=" . $version,
                "config" => "$pubInternalPath/require_config.js?ws=" . $version,
                "document" => "$pubInternalPath/main.js?ws=" . $version
            ));
        } else {
            return $jsRef = array_merge($jsRef, array(
                "require" => "$pubExternalPath/RequireJS/require.js?ws=" . $version,
                "config" => "$pubInternalPath/require_config.min.js?ws=" . $version,
                "document" => "$pubInternalPath/main-built.js?ws=" . $version
            ));
        }
        /*return $jsRef = array_merge($jsRef, array(
            "require" => "$pubExternalPath/RequireJS/require.js?ws=" . $version,
            "config" => $modeDebug !== "FALSE" ? "$pubInternalPath/require_config.js?ws=" . $version : "$pubInternalPath/require_config.min.js?ws=" . $version,
            "kendo-ddui" => $modeDebug !== "FALSE" ? "$pubExternalPath/KendoUI/js/kendo-ddui-builded.js?ws=" . $version : "$pubExternalPath/KendoUI/js/kendo-ddui-builded.min.js?ws=" . $version,
            "document" => $modeDebug !== "FALSE" ? PUBLIC_DIR."/uiAssets/anakeen/IHM/main.js?ws=" . $version : "$pubInternalPath/main-built.js?ws=" . $version
        ));*/
    }
    
    public function getTemplates(\Doc $document = null)
    {
        return array(
            "body" => array(
                "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/views/document/document.mustache"
            ) ,
            "sections" => array(
                "header" => array(
                    "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/views/document/document__header.mustache"
                ) ,
                "menu" => array(
                    "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/views/document/document__menu.mustache"
                ) ,
                "content" => array(
                    "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/views/document/document__content.mustache"
                ) ,
                "footer" => array(
                    "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/views/document/document__footer.mustache"
                )
            ) ,
            "menu" => array(
                "menu" => array(
                    "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/menu/menu.mustache",
                ) ,
                "itemMenu" => array(
                    "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/menu/itemMenu.mustache"
                ) ,
                "listMenu" => array(
                    "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/menu/listMenu.mustache"
                ) ,
                "dynamicMenu" => array(
                    "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/menu/dynamicMenu.mustache"
                ) ,
                "separatorMenu" => array(
                    "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/menu/separatorMenu.mustache"
                )
            ) ,
            "attribute" => array(
                "simpleWrapper" => array(
                    "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/views/attributes/singleWrapper.mustache"
                ) ,
                "description" => array(
                    "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/views/attributes/attributeDescription.mustache"
                ) ,
                
                "default" => array( // use it when no type is defined
                    "write" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/default/write.mustache"
                    ) ,
                    "read" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/default/read.mustache"
                    )
                ) ,
                "label" => array(
                    "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/label/label.mustache"
                ) ,
                "longtext" => array(
                    "write" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/longtext/longtextWrite.mustache"
                    ) ,
                    "read" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/default/read.mustache"
                    )
                ) ,
                "file" => array(
                    "write" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/file/fileWrite.mustache"
                    ) ,
                    "read" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/file/fileRead.mustache"
                    )
                ) ,
                "enum" => array(
                    "write" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/enum/enumWrite.mustache"
                    ) ,
                    "writeRadio" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/enum/enumWriteRadio.mustache"
                    ) ,
                    "writeToggle" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/enum/enumWriteToggle.mustache"
                    ) ,
                    "read" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/enum/enumRead.mustache"
                    )
                ) ,
                "htmltext" => array(
                    "write" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/longtext/longtextWrite.mustache"
                    ) ,
                    "read" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/htmltext/htmltextRead.mustache"
                    )
                ) ,
                "docid" => array(
                    "write" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/docid/docidWrite.mustache"
                    ) ,
                    "read" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/docid/docidRead.mustache"
                    )
                ) ,
                "account" => array(
                    "write" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/docid/docidWrite.mustache"
                    ) ,
                    "read" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/docid/docidRead.mustache"
                    )
                ) ,
                "thesaurus" => array(
                    "write" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/docid/docidWrite.mustache"
                    ) ,
                    "read" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/docid/docidRead.mustache"
                    )
                ) ,
                "image" => array(
                    "write" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/image/imageWrite.mustache"
                    ) ,
                    "read" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/image/imageRead.mustache"
                    )
                ) ,
                "frame" => array(
                    "label" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/views/attributes/frame/label.mustache"
                    ) ,
                    "content" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/views/attributes/frame/content.mustache"
                    )
                ) ,
                "array" => array(
                    "label" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/array/label.mustache"
                    ) ,
                    "content" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/array/content.mustache"
                    ) ,
                    "line" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/array/line.mustache"
                    ) ,
                    "responsive" => array(
                        "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/widgets/attributes/array/responsive.mustache"
                    )
                )
            ) ,
            "window" => array(
                "confirm" => array(
                    "file" => PUBLIC_DIR."/uiAssets/anakeen/IHM/views/window/confirm.mustache"
                )
            )
        );
    }
    /**
     * @param \Doc $document Document instance
     *
     * @return RenderOptions
     */
    public function getOptions(\Doc $document)
    {
        $opt = new RenderOptions();
        
        $opt->setCustomOption("mode", $this->getType());
        $this->setLinkOption($document, $opt);
        $opt->commonOption()->setTranslations(array(
            "closeErrorMessage" => ___("Close message", "ddui")
        ));
        $opt->arrayAttribute()->setTranslations(array(
            "limitMaxMessage" => ___("Row count limit to {{limit}}", "ddui") ,
            "limitMinMessage" => ___("Min row limit is {{limit}}", "ddui")
        ));
        $opt->image()->setTranslations(array(
            "dropFileHere" => ___("Drop image here", "ddui-image") ,
            "tooltipLabel" => ___("Choose image", "ddui-image") ,
            "downloadLabel" => ___("Download image", "ddui-image") ,
            "kiloByte" => ___("kB", "ddui-file") ,
            "recording" => ___("Recording", "ddui-file") ,
            "transferring" => ___("Transferring", "ddui-file") ,
        ));
        $opt->image()->setPlaceHolder(___("Click to upload image", "ddui-image"));
        $opt->file()->setTranslations(array(
            "dropFileHere" => ___("Drop file here", "ddui-file") ,
            "tooltipLabel" => ___("Choose file", "ddui-file") ,
            "downloadLabel" => ___("Download file", "ddui-file") ,
            "kiloByte" => ___("kB", "ddui-file") ,
            "byte" => ___("B", "ddui-file") ,
            "recording" => ___("Recording", "ddui-file") ,
            "transferring" => ___("Transferring", "ddui-file") ,
        ));
        $opt->file()->setPlaceHolder(___("Click to upload file", "ddui-file"));
        
        $opt->enum()->setTranslations(array(
            "chooseMessage" => ___("Choose", "ddui-enum") ,
            "invalidEntry" => ___("Invalid entry", "ddui-enum") ,
            "invertSelection" => "", //___("Click to answer \"{{displayValue}}\"", "ddui-enum") ,
            "selectMessage" => "", //___("Select {{displayValue}}", "ddui-enum") ,
            "unselectMessage" => "", //___("Unselect {{displayValue}}", "ddui-enum") ,
            "chooseAnotherChoice" => ___("Choose another choice", "ddui-enum") ,
            "selectAnotherChoice" => ___("Select alternative choice", "ddui-enum") ,
            "displayOtherChoice" => ___("{{value}} **", "ddui-enum")
        ));
        $opt->enum()->setPlaceHolder(___("Choose", "ddui-enum"));
        $opt->date()->setTranslations(array(
            "invalidDate" => ___("Invalid date", "ddui-date")
        ));
        $opt->docid()->setTranslations(array(
            "allSelectedDocument" => ___("No more matching", "ddui-docid")
        ));
        $opt->account()->setTranslations(array(
            "allSelectedDocument" => ___("No more matching", "ddui-docid")
        ));
        $opt->arrayAttribute()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        
        $selectedTab = $document->getUTag("lasttab");
        if ($selectedTab) {
            $opt->document()->setOpenFirstTab($selectedTab->comment);
        }
        
        return $opt;
    }
    
    protected function setLinkOption(\Doc $document, RenderOptions & $opt)
    {
        
        $linkOption = new htmlLinkOptions();
        //$linkOption->title = ___("View {{{displayValue}}}", "ddui");
        $linkOption->target = "_render";
        $linkOption->url = "api/v1/documents/{{value}}{{#isRevision}}/revisions/{{revisionTarget}}{{/isRevision}}.html";
        $opt->docid()->setLink($linkOption);
        $opt->account()->setLink(clone $linkOption);
        $opt->thesaurus()->setLink(clone $linkOption);
        
        $oas = $document->getNormalAttributes();
        
        foreach ($oas as $oa) {
            if ($oa->link) {
                $link = preg_replace("/%" . ($oa->id) . "%/i", '@@value@@', $oa->link);
                $encLink = $document->urlWhatEncode($link);
                $encLink = str_replace('@@value@@', '{{value}}', $encLink);
                $linkOption = new htmlLinkOptions($encLink);
                if ($oa->isMultiple()) {
                    $values = $document->getMultipleRawValues($oa->id);
                    foreach ($values as $k => $v) {
                        $encLink = $document->urlWhatEncode($link, $k);
                        $encLink = str_replace('@@value@@', '{{value}}', $encLink);
                        $linkOption->urls[] = $encLink;
                    }
                }
                $opt->text($oa->id)->setLink($linkOption);
            }
        }
    }
    /**
     * @param \Doc $document
     *
     * @return RenderAttributeVisibilities new attribute visibilities
     */
    public function getVisibilities(\Doc $document)
    {
        return new RenderAttributeVisibilities($document);
    }
    /**
     * @param \Doc $document
     *
     * @return RenderAttributeNeeded new mandatory attributes
     */
    public function getNeeded(\Doc $document)
    {
        return new RenderAttributeNeeded($document);
    }
    
    public function getType()
    {
        return "abstract";
    }
    /**
     * @param \Doc $document Document object instance
     *
     * @return BarMenu Menu configuration
     */
    public function getMenu(\Doc $document)
    {
        $menu = new BarMenu();
        
        return $menu;
    }
    /**
     * @param \Doc $document Document instance
     *
     * @return DocumentTemplateContext get template controller
     */
    public function getContextController(\Doc $document)
    {
        return new DocumentTemplateContext($document);
    }
    
    protected function setEmblemMenu(\Doc $document, BarMenu $menu)
    {
        
        $item = new SeparatorMenu("EmblemLock", "");
        $item->setHtmlAttribute("class", "menu--right emblem emblem--lock" . ((abs($document->locked) == getCurrentUser()->id) ? " emblem-lock--my" : ""));
        $item->setHtmlLabel('{{#document.properties.security.lock.lockedBy.id}} <span class="dcpDocument__emblem__lock {{#document.properties.security.lock.temporary}} dcpDocument__emblem__lock--temporary {{/document.properties.security.lock.temporary}}fa fa-lock"></span>{{/document.properties.security.lock.lockedBy.id}}');
        
        if ($document->locked == - 1) {
            $item->setTooltipLabel(___("Revision", "ddui") , "", false);
            $item->setHtmlLabel('<span class="dcpDocument__emblem__revised fa fa-archive"></span>');
        } elseif ($document->locked < - 1) {
            $item->setTooltipLabel(sprintf('%s "<b>{{document.properties.security.lock.lockedBy.title}}</b>" ', htmlspecialchars(___("Modifying by", "ddui") , ENT_QUOTES)) , "", true);
        } else {
            $item->setTooltipLabel(sprintf('%s "<b>{{document.properties.security.lock.lockedBy.title}}</b>" ', htmlspecialchars(___("Locked by", "ddui") , ENT_QUOTES)) , "", true);
        }
        
        $item->setImportant(true);
        $menu->appendElement($item);
        
        $item = new SeparatorMenu("EmblemReadOnly", "");
        $item->setHtmlAttribute("class", "menu--right emblem emblem--readonly");
        $item->setHtmlLabel('{{#document.properties.security.readOnly}}<span class="fa-stack fa-lg">
        <i class="fa fa-ban fa-stack-1x fa-rotate-90 text-danger"></i>
        <i class="fa fa-pencil fa-stack-1x"></i>
        </span>{{/document.properties.security.readOnly}}');
        
        $item->setTooltipLabel(___("Read only document", "ddui"));
        $item->setImportant(true);
        $menu->appendElement($item);
        if ($document->confidential > 0) {
            $item = new SeparatorMenu("EmblemConfidential", "");
            $item->setHtmlAttribute("class", "menu--right emblem emblem--confidential");
            $item->setHtmlLabel('<i  class="fa fa-eye-slash"></i>');
            
            $item->setTooltipLabel(___("Confidential document", "ddui"));
            $item->setImportant(true);
            $menu->appendElement($item);
        }
    }
    /**
     * Add Help if Help document is associated to family
     * @param \Doc $doc
     * @param BarMenu $menu target menu
     * @throws \Dcp\Ui\Exception
     */
    protected function addHelpMenu(\Doc $doc, BarMenu & $menu)
    {
        $helpDoc = $helpDoc = $this->getDefaultHelpPageDocument($doc);
        if ($helpDoc) {
            $menuItem = new ItemMenu("help", ___("Help", "UiMenu"));
            $menuItem->setBeforeContent('<div class="fa fa-question-circle" />');
            $menuItem->setUrl(sprintf("#action/document.help:%d", $helpDoc->initid));
            $menu->appendElement($menuItem);
        }
    }
    /**
     * Get custom data to transmit to client document controller
     *
     * @param \Doc $document Document object instance
     *
     * @return mixed
     */
    public function getCustomServerData(\Doc $document)
    {
        return null;
    }
    /**
     * Retrieve some custom data
     *
     * @param \Doc $document Document object instance
     * @param mixed $data data provided by client
     *
     * @return mixed
     */
    public function setCustomClientData(\Doc $document, $data)
    {
        $this->customClientData = $data;
    }
    
    public function getEtag(\Doc $document)
    {
        return '';
    }
    /**
     * Add setLinkHelp option to attributes referenced in HELP family related document
     * @param RenderOptions $options
     * @param \Doc          $document Document instance
     *
     * @return $this
     */
    protected function addDocumentHelpLinks(RenderOptions $options, \Doc $document)
    {
        $helpDoc = $this->getDefaultHelpPageDocument($document);
        if ($helpDoc) {
            \Dcp\HttpApi\V1\DocManager\DocManager::cache()->addDocument($helpDoc);
            $attrids = $helpDoc->getMultipleRawValues(\Dcp\AttributeIdentifiers\Helppage::help_sec_key);
            
            foreach ($attrids as $k => $aid) {
                if ($aid) {
                    $options->commonOption($aid)->setLinkHelp($helpDoc->initid);
                }
            }
        }
        return $this;
    }
    /**
     * Return default help document associated with family
     * @param \Doc $document
     *
     * @return \Dcp\Family\HELPPAGE|null
     */
    protected function getDefaultHelpPageDocument(\Doc $document)
    {
        $helpDoc = $document->getHelpPage();
        if ($helpDoc && $helpDoc->isAlive()) {
            return $helpDoc;
        }
        return null;
    }
}

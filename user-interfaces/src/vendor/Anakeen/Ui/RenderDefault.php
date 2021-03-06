<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

use Anakeen\Core\ContextManager;
use Anakeen\Routes\Ui\DocumentView;
use Anakeen\Search\SearchElements;
use SmartStructure\Fields\Renderdescription as DescriptionFields;
use SmartStructure\Renderdescription;

class RenderDefault implements IRenderConfig
{
    /**
     * @var bool display or not default system menu
     */
    protected $displayDefaultMenuTooltip = false;

    protected $customClientData = null;

    protected $defaultDescription = null;

    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return __CLASS__;
    }

    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return [];
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return [];
    }

    public function getMessages(\Anakeen\Core\Internal\SmartElement $document)
    {
        return [];
    }

    public function getJsDeps()
    {
        $pubExternalPath = "/uiAssets/externals";
        $pubInternalPath = "/uiAssets/anakeen";
        $version = ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $jsRef = [
            "kendoui" => UIGetAssetPath::getJSKendoPath()
        ];

        if (ContextManager::getParameterValue("Ui", "ACTIVATE_LOGGING") === "TRUE") {
            $jsRef = array_merge($jsRef, [
                "traceKit" => "$pubExternalPath/traceKit/traceKit.js?ws=" . $version,
                "traceError" => "$pubInternalPath/dynacaseReport.js?ws=" . $version
            ]);
        }
        return $jsRef;
    }

    public function getSmartElementJs()
    {
        return UIGetAssetPath::getJSSmartElementPath();
    }

    public function getTemplates(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return array(
            "body" => array(
                "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/views/document/document.mustache"
            ),
            "sections" => array(
                "header" => array(
                    "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/views/document/document__header.mustache"
                ),
                "menu" => array(
                    "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/views/document/document__menu.mustache"
                ),
                "content" => array(
                    "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/views/document/document__content.mustache"
                ),
                "footer" => array(
                    "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/views/document/document__footer.mustache"
                )
            ),
            "menu" => array(
                "menu" => array(
                    "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/menu/menu.mustache",
                ),
                "itemMenu" => array(
                    "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/menu/itemMenu.mustache"
                ),
                "listMenu" => array(
                    "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/menu/listMenu.mustache"
                ),
                "dynamicMenu" => array(
                    "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/menu/dynamicMenu.mustache"
                ),
                "callableMenu" => array(
                    "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/menu/callableMenu.mustache"
                ),
                "separatorMenu" => array(
                    "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/menu/separatorMenu.mustache"
                )
            ),
            "attribute" => array(
                "simpleWrapper" => array(
                    "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/views/attributes/singleWrapper.mustache"
                ),
                "description" => array(
                    "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/views/attributes/attributeDescription.mustache"
                ),

                "default" => array( // use it when no type is defined
                    "write" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/default/write.mustache"
                    ),
                    "read" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/default/read.mustache"
                    )
                ),
                "label" => array(
                    "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/label/label.mustache"
                ),
                "longtext" => array(
                    "write" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/longtext/longtextWrite.mustache"
                    ),
                    "read" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/default/read.mustache"
                    )
                ),
                "file" => array(
                    "write" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/file/fileWrite.mustache"
                    ),
                    "read" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/file/fileRead.mustache"
                    )
                ),
                "enum" => array(
                    "write" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/enum/enumWrite.mustache"
                    ),
                    "writeRadio" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/enum/enumWriteRadio.mustache"
                    ),
                    "writeToggle" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/enum/enumWriteToggle.mustache"
                    ),
                    "read" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/enum/enumRead.mustache"
                    )
                ),
                "htmltext" => array(
                    "write" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/htmltext/htmltextWrite.mustache"
                    ),
                    "read" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/htmltext/htmltextRead.mustache"
                    )
                ),
                "json" => array(
                    "write" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/longtext/longtextWrite.mustache"
                    ),
                    "read" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/default/read.mustache"
                    )
                ),
                "xml" => array(
                    "write" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/longtext/longtextWrite.mustache"
                    ),
                    "read" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/default/read.mustache"
                    )
                ),
                "docid" => array(
                    "write" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/docid/docidWrite.mustache"
                    ),
                    "read" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/docid/docidRead.mustache"
                    )
                ),
                "account" => array(
                    "write" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/docid/docidWrite.mustache"
                    ),
                    "read" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/docid/docidRead.mustache"
                    )
                ),
                "thesaurus" => array(
                    "write" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/docid/docidWrite.mustache"
                    ),
                    "read" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/docid/docidRead.mustache"
                    )
                ),
                "image" => array(
                    "write" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/image/imageWrite.mustache"
                    ),
                    "read" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/image/imageRead.mustache"
                    )
                ),
                "frame" => array(
                    "label" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/views/attributes/frame/label.mustache"
                    ),
                    "content" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/views/attributes/frame/content.mustache"
                    )
                ),
                "array" => array(
                    "label" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/array/label.mustache"
                    ),
                    "content" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/array/content.mustache"
                    ),
                    "line" => array(
                        "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/widgets/attributes/array/line.mustache"
                    )
                )
            ),
            "window" => array(
                "confirm" => array(
                    "file" => DEFAULT_PUBDIR . "/vendor/Anakeen/DOCUMENT/IHM/views/window/confirm.mustache"
                )
            )
        );
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     *
     * @return RenderOptions
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $opt = new RenderOptions();

        $opt->setCustomOption("mode", $this->getType());
        $this->setLinkOption($document, $opt);
        $opt->commonOption()->setTranslations(array(
            "closeErrorMessage" => ___("Close message", "ddui")
        ));
        $opt->arrayAttribute()->setTranslations(array(
            "limitMaxMessage" => ___("Row count limit to {{limit}}", "ddui"),
            "limitMinMessage" => ___("Min row limit is {{limit}}", "ddui"),
            "dragLine" => ___("Drag to move line", "ddui"),
            "selectLine" => ___("Select line", "ddui"),
            "deleteLine" => ___("Delete line", "ddui")
        ));
        $opt->image()->setTranslations(array(
            "dropFileHere" => ___("Drop image here", "ddui-image"),
            "tooltipLabel" => ___("Choose image", "ddui-image"),
            "downloadLabel" => ___("Download image", "ddui-image"),
            "kiloByte" => ___("kB", "ddui-file"),
            "recording" => ___("Recording", "ddui-file"),
            "transferring" => ___("Transferring", "ddui-file"),
        ));
        $opt->image()->setPlaceHolder(___("Click to upload image", "ddui-image"));
        $opt->file()->setTranslations(array(
            "dropFileHere" => ___("Drop file here", "ddui-file"),
            "tooltipLabel" => ___("Choose file", "ddui-file"),
            "downloadLabel" => ___("Download file", "ddui-file"),
            "kiloByte" => ___("kB", "ddui-file"),
            "byte" => ___("B", "ddui-file"),
            "recording" => ___("Recording", "ddui-file"),
            "transferring" => ___("Transferring", "ddui-file"),
        ));
        $opt->file()->setPlaceHolder(___("Click to upload file", "ddui-file"));

        $opt->enum()->setTranslations(array(
            "chooseMessage" => ___("Choose", "ddui-enum"),
            "invalidEntry" => ___("Invalid entry", "ddui-enum"),
            "invertSelection" => "", //___("Click to answer \"{{displayValue}}\"", "ddui-enum") ,
            "selectMessage" => "", //___("Select {{displayValue}}", "ddui-enum") ,
            "unselectMessage" => "", //___("Unselect {{displayValue}}", "ddui-enum") ,
            "chooseAnotherChoice" => ___("Choose another choice", "ddui-enum"),
            "selectAnotherChoice" => ___("Select alternative choice", "ddui-enum"),
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
        $opt->commonOption()->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::upPosition);
        $opt->int()->setTranslations(array(
            "increaseLabel" => ___("Increase value", "ddui-numerical"),
            "decreaseLabel" => ___("Decrease value", "ddui-numerical")
        ));
        $opt->double()->setTranslations(array(
            "increaseLabel" => ___("Increase value", "ddui-numerical"),
            "decreaseLabel" => ___("Decrease value", "ddui-numerical")
        ));
        $opt->money()->setTranslations(array(
            "increaseLabel" => ___("Increase value", "ddui-numerical"),
            "decreaseLabel" => ___("Decrease value", "ddui-numerical")
        ));
        $opt->htmltext()->setTranslations(HtmltextRenderOptions::getTranslations());


        $selectedTab = $document->getUTag("lasttab");
        if ($selectedTab) {
            $opt->document()->setOpenFirstTab($selectedTab->comment);
        }
        $this->setDefaultDescriptions($document, $opt);

        return $opt;
    }

    protected function setDefaultDescriptions(\Anakeen\Core\Internal\SmartElement $smartElement, RenderOptions $options)
    {
        $descriptions = $this->getDefaultDescriptions($smartElement);
        if ($descriptions) {
            /** @var Renderdescription $descriptions */
            self::applyRenderDescription($descriptions, $options);
        }
    }

    protected function getDefaultDescriptions(\Anakeen\Core\Internal\SmartElement $smartElement)
    {
        if ($this->defaultDescription === null) {
            $s = new SearchElements(\SmartStructure\Renderdescription::familyName);

            $s->overrideAccessControl();
            $s->addFilter("%s = '%d'", DescriptionFields::rd_famid, $smartElement->fromid);


            $lang = ContextManager::getLanguage();
            // print_r($lang);
            // $s->addFilter(new \Anakeen\Search\Filters\IsEmpty(DescriptionFields::rd_lang));
            // $s->addFilter(new \Anakeen\Search\Filters\OneEquals(DescriptionFields::rd_lang, substr($lang, 0, 2)));
            $s->addFilter(new \Anakeen\Search\Filters\OrOperator(
                new \Anakeen\Search\Filters\IsEqual(DescriptionFields::rd_lang, substr($lang, 0, 2)),
                new \Anakeen\Search\Filters\IsEmpty(DescriptionFields::rd_lang)
            ));
            $s->addFilter(new \Anakeen\Search\Filters\OneEquals(DescriptionFields::rd_mode, $this->getType()));
            $s->search();

            if ($s->count() > 0) {
                /** @var Renderdescription $descriptions */
                $descriptions = $s->getNextElement();
                $this->defaultDescription = $descriptions;
            } else {
                $this->defaultDescription = false;
            }
        }
        return $this->defaultDescription;
    }

    public static function applyRenderDescription(Renderdescription $renderDescription, RenderOptions $options)
    {
        $info = $renderDescription->getAttributeValue(DescriptionFields::rd_t_fields);
        foreach ($info as $fieldDescription) {
            if (!empty($fieldDescription[DescriptionFields::rd_field]) && !empty($fieldDescription[DescriptionFields::rd_description])) {
                $options->commonOption($fieldDescription[DescriptionFields::rd_field])->setDescription(
                    $fieldDescription[DescriptionFields::rd_description],
                    $fieldDescription[DescriptionFields::rd_placement],
                    $fieldDescription[DescriptionFields::rd_subdescription] ?: "",
                    $fieldDescription[DescriptionFields::rd_collapsable] !== "false"
                );
            }
        }
    }

    protected function setLinkOption(\Anakeen\Core\Internal\SmartElement $document, RenderOptions $opt)
    {
        $linkOption = new htmlLinkOptions();
        $linkOption->target = "_render";
        $linkOption->url = "/api/v2/smart-elements/{{value}}{{#isRevision}}/revisions/{{revisionTarget}}{{/isRevision}}.html";
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
     * @param \Anakeen\Core\Internal\SmartElement $document
     *
     * @param \SmartStructure\Mask|null $mask The mask referenced in view control when use a specific view
     * @return RenderAttributeVisibilities new attribute visibilities
     */
    public function getVisibilities(
        \Anakeen\Core\Internal\SmartElement $document,
        \SmartStructure\Mask $mask = null
    ): RenderAttributeVisibilities {
        $renderVisibilities = new RenderAttributeVisibilities($document, $mask);
        $fields = $document->getNormalAttributes();
        foreach ($fields as $field) {
            if ($field->getOption("autotitle") === "yes") {
                $renderVisibilities->setVisibility($field->id, RenderAttributeVisibilities::HiddenVisibility);
            }
        }

        return $renderVisibilities;
    }

    /**
     * Return needed fields.
     * Computed from current mask
     *
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @param \SmartStructure\Mask|null $mask The mask used in current view
     * @return RenderAttributeNeeded new mandatory attributes
     */
    public function getNeeded(
        \Anakeen\Core\Internal\SmartElement $document,
        \SmartStructure\Mask $mask = null
    ): RenderAttributeNeeded {
        // No apply mask here because it is already set used by getVisibilities
        return new RenderAttributeNeeded($document);
    }

    public function getType()
    {
        return "abstract";
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     *
     * @return BarMenu Menu configuration
     */
    public function getMenu(\Anakeen\Core\Internal\SmartElement $document): BarMenu
    {
        return new BarMenu();
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     *
     * @return DocumentTemplateContext get template controller
     */
    public function getContextController(\Anakeen\Core\Internal\SmartElement $document): DocumentTemplateContext
    {
        return new DocumentTemplateContext($document);
    }

    protected function setEmblemMenu(\Anakeen\Core\Internal\SmartElement $document, BarMenu $menu)
    {
        $item = new SeparatorMenu("EmblemLock", "");
        $item->setHtmlAttribute(
            "class",
            "menu--left emblem emblem--lock" . ((abs(intval($document->locked)) == ContextManager::getCurrentUser()->id) ? " emblem-lock--my" : "")
        );
        $labelClass = "dcpDocument__emblem__lock fa fa-lock";
        $labelClass .= " {{#document.properties.security.lock.temporary}} dcpDocument__emblem__lock--temporary {{/document.properties.security.lock.temporary}}";
        $item->setHtmlLabel(
            '{{#document.properties.security.lock.lockedBy.id}} 
<span class="' . $labelClass . '"></span>
{{/document.properties.security.lock.lockedBy.id}}'
        );

        if ($document->locked == -1) {
            $item->setTooltipLabel(___("Revision", "ddui"), "", false);
            $item->setHtmlLabel('<span class="dcpDocument__emblem__revised fa fa-archive"></span>');
        } elseif ($document->locked < -1) {
            $item->setTooltipLabel(
                sprintf(
                    '%s "<b>{{document.properties.security.lock.lockedBy.title}}</b>" ',
                    htmlspecialchars(___("Modifying by", "ddui"), ENT_QUOTES)
                ),
                "",
                true
            );
        } else {
            $item->setTooltipLabel(sprintf(
                '%s "<b>{{document.properties.security.lock.lockedBy.title}}</b>" ',
                htmlspecialchars(___("Locked by", "ddui"), ENT_QUOTES)
            ), "", true);
        }

        $item->setImportant(true);
        $menu->appendElement($item);

        $item = new SeparatorMenu("EmblemReadOnly", "");
        $item->setHtmlAttribute("class", "menu--left emblem emblem--readonly");
        $item->setHtmlLabel('{{#document.properties.security.readOnly}}<span class="fa-stack fa-lg">
        <i class="fa fa-ban fa-stack-1x fa-rotate-90 text-danger"></i>
        <i class="fa fa-pencil fa-stack-1x"></i>
        </span>{{/document.properties.security.readOnly}}');

        $item->setTooltipLabel(___("Read only document", "ddui"));
        $item->setImportant(true);
        $menu->appendElement($item);
        if ($document->confidential > 0) {
            $item = new SeparatorMenu("EmblemConfidential", "");
            $item->setHtmlAttribute("class", "menu--left emblem emblem--confidential");
            $item->setHtmlLabel('<i  class="fa fa-eye-slash"></i>');

            $item->setTooltipLabel(___("Confidential document", "ddui"));
            $item->setImportant(true);
            $menu->appendElement($item);
        }
    }


    /**
     * Get custom data to transmit to client document controller
     *
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     *
     * @return mixed
     */
    public function getCustomServerData(\Anakeen\Core\Internal\SmartElement $document)
    {
        return null;
    }

    /**
     * Retrieve some custom data
     *
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     * @param mixed $data data provided by client
     *
     * @return void
     */
    public function setCustomClientData(\Anakeen\Core\Internal\SmartElement $document, $data)
    {
        $this->customClientData = $data;
    }

    public function getEtag(\Anakeen\Core\Internal\SmartElement $document)
    {
        $etags = DocumentView::getDefaultETag($document);
        $descriptions = $this->getDefaultDescriptions($document);
        if ($descriptions) {
            $etags .= " " . $descriptions->mdate . " " . $descriptions->id;
        }
        return $etags;
    }
}

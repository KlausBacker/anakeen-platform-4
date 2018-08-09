<?php


namespace Anakeen\SmartStructures\Dsearch\Render;

use Anakeen\Core\SEManager;
use Dcp\Ui\BarMenu;
use Dcp\Ui\DocumentTemplateContext;
use Dcp\Ui\ItemMenu as ItemMenu;

class SearchViewRender extends \Dcp\Ui\DefaultView
{

    public function getTemplates(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"]["file"]
            = __DIR__ . "/searchHTML5_view.mustache";
        return $templates;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences($document);

        $js["smartElementGrid"] = \Dcp\Ui\UIGetAssetPath::getJSSmartElementGridPath();

        $js["dSearch"] = \Dcp\Ui\UIGetAssetPath::getCustomAssetPath('uiAssets/Families/dsearch/prod/dsearch.js');
        if (\Dcp\Ui\UIGetAssetPath::isInDebug()) {
            $js["dSearch"] = \Dcp\Ui\UIGetAssetPath::getCustomAssetPath('uiAssets/Families/dsearch/debug/dsearch.js?');
        }

        return $js;
    }

    public function getMenu(\Anakeen\Core\Internal\SmartElement $document): BarMenu
    {
        $myMenu = parent::getMenu($document);
        $myItem = new ItemMenu("searchview");
        $myItem->setTextLabel(___("consult", "searchUi"));

        $myMenu->removeElement("se_open");
        $myItem->setUrl("#action/previewConsult");
        $myMenu->appendElement($myItem);

        return $myMenu;
    }

    public function getContextController(\Anakeen\Core\Internal\SmartElement $document): DocumentTemplateContext
    {
        $controller = parent::getContextController($document);

        $tabConditions = array();

        $attributes = $document->getAttributeValue("se_attrids");
        $condition = $document->getAttributeValue("se_ol");

        $family = SEManager::getFamily($document->getRawValue("se_famid"));

        /**
         * @var \Anakeen\SmartStructures\Wdoc\WDocHooks $workflow
         */
        $workflow = SEManager::getDocument($document->wid);

        foreach ($attributes as $index => $attribute) {
            $operand = $document->getAttributeValue("se_ols")[$index];
            $leftp = $document->getAttributeValue("se_leftp")[$index];
            $rightp = $document->getAttributeValue("se_rightp")[$index];
            $func = $document->getAttributeValue("se_funcs")[$index];
            $key = $document->getAttributeValue("se_keys")[$index];

            /**
             * get operands
             */
            if ($condition != "perso") {
                $operand = ___($condition);
            } else {
                $operand = ___($operand);
            }


            /**
             * get parenthesis
             */
            if ($leftp == "yes") {
                $leftp = "(";
            } else {
                $leftp = "";
            }

            if ($rightp == "yes") {
                $rightp = ")";
            } else {
                $rightp = "";
            }

            /**
             * get attribute label and type
             */
            $attr = $family->getAttribute($attribute);
            if (!$attr) {
                $type = "text";
                if ($attribute == "title") {
                    $attr = ___("doctitle", "searchui");
                } else {
                    if ($attribute == "mdate") {
                        $type = "date";
                        $attr = ___("mdate", "searchui");
                    } else {
                        if ($attribute == "cdate") {
                            $type = "date";
                            $attr = ___("cdate", "searchui");
                        } else {
                            if ($attribute == "revision") {
                                $attr = ___("revision", "searchui");
                            } else {
                                if ($attribute == "owner") {
                                    $attr = ___("id owner", "searchui");
                                } else {
                                    if ($attribute == "locked") {
                                        $attr = ___("id locked", "searchui");
                                    } else {
                                        if ($attribute == "allocated") {
                                            $attr = ___("id allocated", "searchui");
                                        } else {
                                            if ($attribute == "svalues") {
                                                $attr = ___("any values", "searchui");
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                if ($attr->isMultiple()) {
                    $type = $attr->type . "[]";
                } else {
                    $type = $attr->type;
                }
                $attr = $attr->getLabel();
            }

            /**
             * get function label
             */
            $boolTyped = false;
            $doccollection = new \DocCollection();
            foreach ($doccollection->top as $k => &$tmptop) {
                if ($k == $func) {
                    foreach ($tmptop["sdynlabel"] as $i => $label) {
                        if ($i == $type) {
                            $func = ___($label);
                            $boolTyped = true;
                        } else {
                            if ($i == "array" && strripos($type, "[]") != false) {
                                $func = ___($label);
                                $boolTyped = true;
                            }
                        }
                    }

                    if (!$boolTyped) {
                        $func = ___($tmptop["dynlabel"]);
                    }
                }
            }
            $leftfunc = explode("{left}", $func)[0];
            $rightfunc = explode("{left}", $func)[1];
            $rightfunc = explode("{right}", $rightfunc)[0];

            if ($index == 0) {
                $operand = "";
                $leftfunc = ucfirst($leftfunc);
            }


            /**
             * get key label
             */
            if (($type == "docid") || ($type == "docid[]")) {
                $key = $document->getTitle($key);
            } else {
                if (($type == "enum") || ($type == "enum[]")) {
                    $oa = $family->getAttribute($attribute);
                    /**
                     * @var \Anakeen\Core\SmartStructure\NormalAttribute $oa
                     */
                    $key = $oa->getEnumLabel($key);
                } else {
                    if ($type == "date") {
                        if (strripos($key, "(") === false) {
                            $key = explode("-", $key)[2] . "/" . explode("-", $key)[1] . "/" . explode("-", $key)[0];
                        }
                    } else {
                        if ($attribute == "state") {
                            $key = ___($key);
                            $attr = ___("state");
                        } else {
                            if ($attribute == "activity") {
                                $key = $workflow->getStateActivity($key);
                                $attr = ___("activity");
                            } else {
                                if ($attribute == "step") {
                                    if ($workflow->getStateActivity($key) != "") {
                                        $key = ___($key) . "/" . $workflow->getStateActivity($key);
                                    } else {
                                        $key = ___($key);
                                    }
                                    $attr = ___("step");
                                }
                            }
                        }
                    }
                }
            }

            if ($key == "") {
                $key = $document->getAttributeValue("se_keys")[$index];
            }

            if (strripos($func, "{right}") === false) {
                $tabConditions[] = array("myList" => $operand . " " . $leftp . $leftfunc . " [" . $attr . "] " . $rightfunc . $rightp);
            } else {
                $tabConditions[] = array("myList" => $operand . " " . $leftp . $leftfunc . " [" . $attr . "] " . $rightfunc . " \"" . $key . "\" " . $rightp);
            }
        }

        $controller->offsetSet("myValues", $tabConditions);
        return $controller;
    }
}

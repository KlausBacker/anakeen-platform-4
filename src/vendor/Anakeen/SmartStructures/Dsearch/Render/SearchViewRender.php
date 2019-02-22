<?php


namespace Anakeen\SmartStructures\Dsearch\Render;

use Anakeen\Core\SEManager;
use Anakeen\Ui\BarMenu;
use Anakeen\Ui\DocumentTemplateContext;
use Anakeen\Ui\ItemMenu as ItemMenu;
use Anakeen\Ui\UIGetAssetPath;

class SearchViewRender extends \Anakeen\Ui\DefaultView
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

        $js["smartElementGrid"] = \Anakeen\Ui\UIGetAssetPath::getSmartWebComponentsPath();

        $js["dSearch"] = \Anakeen\Ui\UIGetAssetPath::getElementAssets("smartStructures", "prod")["Dsearch"]["js"];
        if (\Anakeen\Ui\UIGetAssetPath::isInDebug()) {
            $js["dSearch"] =  \Anakeen\Ui\UIGetAssetPath::getElementAssets("smartStructures", "dev")["Dsearch"]["js"];
        }

        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "prod");
        $js["dSearch"] = $path["Dsearch"]["js"];

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
        if ($family->wid) {
            $workflow = SEManager::getDocument($family->wid);
        }

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
                $operand = _($condition);
            } else {
                $operand = _($operand);
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
                            $func = _($label);
                            $boolTyped = true;
                        } else {
                            if ($i == "array" && strripos($type, "[]") != false) {
                                $func = _($label);
                                $boolTyped = true;
                            }
                        }
                    }

                    if (!$boolTyped) {
                        $func = _($tmptop["dynlabel"]);
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
                            $key = $workflow?$workflow->getStateLabel($key):$key;
                            $attr = ___("state", "dsearch");
                        } else {
                            if ($attribute == "activity") {
                                $key = $workflow?$workflow->getStateActivity($key):$key;
                                $attr = ___("activity", "dsearch");
                            } else {
                                if ($attribute == "step") {
                                    if ($workflow && $workflow->getStateActivity($key) != "") {
                                        $key =  $workflow->getStateLabel($key) . "/" . $workflow->getStateActivity($key);
                                    } else {
                                        $key =  $workflow->getStateLabel($key);
                                    }
                                    $attr = ___("step", "dsearch");
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

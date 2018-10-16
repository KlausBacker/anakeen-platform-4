<?php

namespace Anakeen\Core\SmartStructure;

use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\QueryDb;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use Dcp\Exception;

/**
 * Class ExportConfiguration
 * @package Anakeen\Core\SmartStructure
 *
 * Export Smart Structure in Xml
 */
class ExportConfiguration
{
    protected $data;
    const NS = "smart";
    const NSBASEURL = "https://platform.anakeen.com/4/schemas/";
    const NSURL = self::NSBASEURL."smart/1.0";
    /**
     * @var SmartStructure|null
     */
    protected $sst = null;
    protected $dom;
    protected $domConfig;
    /**
     * @var \DOMElement[]
     */
    protected $fieldSets = [];

    /**
     * ExportConfiguration constructor.
     * @param SmartStructure $sst Smart Structure to export
     */
    public function __construct(SmartStructure $sst)
    {

        $this->sst = $sst;
        $this->dom = new \DOMDocument("1.0", "UTF-8");
        $this->dom->formatOutput = true;
        $this->domConfig = $this->cel("config");
        $this->dom->appendChild($this->domConfig);

        $structConfig = $this->cel("structure-configuration");
        $structConfig->setAttribute("name", $this->sst->name);
        if ($this->sst->id < 1000) {
            $structConfig->setAttribute("id", $this->sst->id);
        }

        $this->extract($structConfig);
    }

    protected function extract($structConfig)
    {
        $this->extractProps($structConfig);
        $this->extractAttr($structConfig);
        $this->extractModAttr($structConfig);
        $this->extractHooks($structConfig);
        $this->extractAutoComplete($structConfig);
        $this->extractDefaults($structConfig);
        $this->extractEnums();

        $this->domConfig->appendChild($structConfig);
    }

    /**
     * Return Xml string for smart structre configuration
     * @return string
     */
    public function toXml()
    {
        return $this->dom->saveXML();
    }



    protected static function getLogicalName($id) {
        $name= SEManager::getNameFromId($id);
        if ($name === null) {
            $name= "NAME#$id";
        }
        return $name;
    }




    protected function extractProps(\DOMElement $structConfig)
    {
        $structConfig->setAttribute("label", $this->sst->title);
        if ($this->sst->fromid) {
            $extendTag = $this->cel("extends");
            $extendTag->setAttribute("ref", static::getLogicalName($this->sst->fromid));
            $structConfig->appendChild($extendTag);
        }
        if ($this->sst->dfldid) {
            $tag = $this->cel("default-folder");
            $tag->nodeValue = static::getLogicalName($this->sst->dfldid);
            $structConfig->appendChild($tag);
        }
        if ($this->sst->icon) {
            $tag = $this->cel("icon");
            $tag->setAttribute("file", $this->sst->icon);
            $structConfig->appendChild($tag);
        }
        if ($this->sst->classname) {
            $tag = $this->cel("class");
            $tag->nodeValue = $this->sst->classname;
            $structConfig->appendChild($tag);
        }
        if ($this->sst->methods) {
            $tag = $this->cel("methods");
            $tag->nodeValue = $this->sst->methods;
            $structConfig->appendChild($tag);
        }
        if ($this->sst->schar === "S") {
            $tag = $this->cel("revisable");
            $tag->nodeValue = "false";
            $structConfig->appendChild($tag);
        } elseif ($this->sst->schar === "R") {
            $tag = $this->cel("revisable");
            if ($this->sst->maxrev) {
                $tag->setAttribute("max", $this->sst->maxrev);
            }
            $tag->nodeValue = "auto";
            $structConfig->appendChild($tag);
        } elseif ($this->sst->maxrev) {
            $tag = $this->cel("revisable");
            $tag->setAttribute("max", $this->sst->maxrev);
            $tag->nodeValue = "default";
            $structConfig->appendChild($tag);
        }
        if ($this->sst->usefor) {
            $tag = $this->cel("usefor");
            $tag->nodeValue = $this->sst->usefor;
            $structConfig->appendChild($tag);
        }
        if ($this->sst->atags) {
            $atags = json_decode($this->sst->atags, true);
            foreach ($atags as $kTag=>$vTag) {
                $tag = $this->cel("tag");
                $tag->setAttribute("name",$kTag);
                $tag->nodeValue = $vTag;
                $structConfig->appendChild($tag);
            }
        }
    }

    protected function extractModAttr(\DOMElement $structConfig)
    {
        /**
         * @var \DOMElement[]
         */
        $q = new QueryDb("", DocAttr::class);
        $q->addQuery("id ~ '^:'");
        $q->addQuery(sprintf(" docid=%d", $this->sst->id));
        $l = $q->Query();

        if ($q->nb === 0) {
            return;
        }
        /**
         * @var DocAttr $docattr
         */
        foreach ($l as $docattr) {
            $smartOver = $this->cel("field-override");
            $attrid = substr($docattr->id, 1);
            $smartOver->setAttribute("field", $attrid);
            $attr = $this->sst->getAttribute($attrid);
            if (!$attr) {
                throw new Exception("Attr $attrid");
            }

            if ($docattr->accessibility) {
                $smartOver->setAttribute("access", $docattr->accessibility);
            }

            if ($docattr->needed) {
                $smartOver->setAttribute("needed", ($docattr->needed === "Y") ? "true" : "false");
            }
            if ($docattr->title) {
                $smartOver->setAttribute("is-title", ($docattr->title === "Y") ? "true" : "false");
            }
            if ($docattr->abstract) {
                $smartOver->setAttribute("is-abstract", ($docattr->abstract === "Y") ? "true" : "false");
            }
            if ($docattr->ordered) {
                $smartOver->setAttribute("insert-after", $docattr->ordered);
            }
            if ($docattr->labeltext) {
                $smartOver->setAttribute("label", $docattr->labeltext);
            }

            if ($docattr->title) {
                $smartOver->setAttribute("label", $docattr->labeltext);
            }
            if ($docattr->type) {
                $smartOver->setAttribute("type", $docattr->type);
            }
            if ($docattr->link) {
                $smartOver->setAttribute("link", $docattr->link);
            }
            if ($docattr->options) {
                $this->setOptions($smartOver, $attr->getOptions());
            }
            if ($docattr->phpconstraint) {
                $smartOver->appendChild($this->getConstraint($attr));
            }


            if ($docattr->phpfunc && (!$attr->phpfile) && $attr->type !== "enum") {
                $smartOver->appendChild($this->getComputeFunc($attr));
            }
            if ($docattr->phpfunc && ($attr->phpfile) && $attr->type !== "enum") {
                $smartOver->appendChild($this->getAutocompleteFunc($attr));
            }

            if ($docattr->frameid) {
                if (isset($this->fieldSets[$docattr->frameid])) {
                    $this->fieldSets[$docattr->frameid]->appendChild($smartOver);
                } else {
                    $smartOver->setAttribute("unknow-fieldset", $docattr->frameid);
                    $structConfig->appendChild($smartOver);
                }
            } else {
                $structConfig->appendChild($smartOver);
            }
        }
    }

    protected function extractDefaults(\DOMElement $structConfig)
    {
        $smartDefaults = $this->cel("defaults");

        $defaults = $this->sst->getOwnDefValues();
        foreach ($defaults as $attrid => $default) {
            $attr = $this->sst->getAttribute($attrid);
            if (!$attr) {
                continue;
            }
            $def = $this->cel("default");
            $def->setAttribute("field", $attrid);
            if (SmartElement::seemsMethod($default)) {
                $this->insertCallable($def, $default);
            } else {
                $def->nodeValue = $default;
            }
            $smartDefaults->appendChild($def);
        }
        $structConfig->appendChild($smartDefaults);
    }

    protected function extractEnums()
    {

        $smartEnums = $this->cel("enumerates");
        $attrs = $this->sst->getNormalAttributes();
        $enumNames = [];
        foreach ($attrs as $attr) {
            if ($attr->type === "enum" && $attr->format && $attr->structureId == $this->sst->id) {
                $enumNames[] = $attr->format;
            }
        }


        $sql = sprintf("select * from docenum where %s order by eorder", DbManager::getSqlOrCond($enumNames, "name"));
        DbManager::query($sql, $enums);
        /**
         * @var \DOMElement[] $enumConfs
         */
        $enumConfs = [];
        /**
         * @var \DOMElement[][] $parents
         */
        $parents = [];
        if ($enums) {
            foreach ($enums as $enum) {
                $enumName = $enum["name"];
                if (!isset($enumConfs[$enumName])) {
                    $enumConfs[$enumName] = $this->cel("enum-configuration");
                    $enumConfs[$enumName]->setAttribute("name", $enumName);
                }
                $enumTag = $this->cel("enum");
                $enumTag->setAttribute("name", $enum["key"]);
                $enumTag->setAttribute("label", $enum["label"]);
                if ($enum["parentkey"]) {
                    $parentId = $enum["parentkey"];
                    if (!isset($parents[$enumName][$parentId])) {
                        $parents[$enumName][$parentId] = $this->cel("enum");
                        $parents[$enumName][$parentId]->setAttribute("name", $parentId);
                        $enumConfs[$enumName]->appendChild($parents[$enumName][$parentId]);
                    }
                    $parents[$enumName][$parentId]->appendChild($enumTag);
                } else {
                    $enumConfs[$enumName]->appendChild($enumTag);
                    $parents[$enumName][$enum["key"]] = $enumTag;
                }
            }

            foreach ($enumConfs as $enumConf) {
                $smartEnums->appendChild($enumConf);
            }
            $this->domConfig->appendChild($smartEnums);
        }
    }

    protected function extractAttr(\DOMElement $structConfig)
    {
        $smartAttributes = $this->cel("fields");
        $smartParameters = $this->cel("parameters");

        /**
         * @var \DOMElement[]
         */

        $attrs = $this->sst->getAttributes();
        $this->fieldSets = [];
        foreach ($attrs as $attr) {
            if ($attr->structureId !== $this->sst->id) {
                continue;
            }

            if ($this->isModAttr($attr)) {
                continue;
            }

            if ($attr->usefor === "Q") {
                $rootAttr = $smartParameters;
            } else {
                $rootAttr = $smartAttributes;
            }

            $smartAttr = null;
            $attrName = $attr->id;
            $type = $attr->type;
            switch ($type) {
                case "menu":
                case "action":
                    break;
                case "tab":
                case "frame":
                case "array":
                    $smartAttr = $this->cel("field-set");
                    $smartAttr->setAttribute("name", $attrName);
                    $smartAttr->setAttribute("type", $type);
                    $this->fieldSets[$attrName] = $smartAttr;
                    break;
                default:
                    $smartAttr = $this->cel("field-" . $type);
                    $smartAttr->setAttribute("name", $attrName);
            }

            if ($smartAttr) {
                if (!empty($attr->labelText)) {
                    $smartAttr->setAttribute("label", $attr->labelText);
                }
                if (!empty($attr->access)) {
                    $smartAttr->setAttribute("access", FieldAccessManager::getTextAccess($attr->access));
                }
                if (!empty($attr->link)) {
                    $smartAttr->setAttribute("link", $attr->link);
                }


                if ($attr->isNormal) {
                    /**
                     * @var NormalAttribute $attr ;
                     */
                    if ($attr->needed) {
                        $smartAttr->setAttribute("needed", "true");
                    }
                    if ($attr->isInTitle) {
                        $smartAttr->setAttribute("is-title", "true");
                    }
                    if ($attr->isInAbstract) {
                        $smartAttr->setAttribute("is-abstract", "true");
                    }
                    if ($type === "docid" || $type === "account") {
                        if ($attr->format) {
                            $smartAttr->setAttribute("relation", $attr->format);
                        }
                    }
                    if (in_array($type, ["int", "double", "money", "date", "time", "timestamp"])) {
                        if ($attr->format) {
                            $smartAttr->setAttribute("format", $attr->format);
                        }
                    }
                    if ($type === "enum") {
                        if ($attr->format) {
                            $smartAttr->setAttribute("relation", $attr->format);
                        } else {
                            $smartAttr->setAttribute("relation", sprintf("%s-%s", strtolower($this->sst->name), $attr->id));
                        }
                    }
                }


                $opts = $attr->getOptions();
                $this->setOptions($smartAttr, $opts);


                if ($attr->fieldSet && $attr->fieldSet->id !== Attributes::HIDDENFIELD) {
                    $parentName = $attr->fieldSet->id;
                    if (!isset($this->fieldSets[$parentName])) {
                        $smartAttrShadow = $this->cel("field-set");
                        $smartAttrShadow->setAttribute("name", $parentName);
                        $smartAttrShadow->setAttribute("extended", "true");
                        $this->fieldSets[$parentName] = $smartAttrShadow;
                        $rootAttr->appendChild($smartAttrShadow);
                    }
                } else {
                    $parentName = "";
                }

                if (isset($this->fieldSets[$parentName])) {
                    $this->fieldSets[$parentName]->appendChild($smartAttr);
                } else {
                    $rootAttr->appendChild($smartAttr);
                }
            }
        }
        $structConfig->appendChild($smartAttributes);
        $structConfig->appendChild($smartParameters);
    }

    protected function extractHooks(\DOMElement $structConfig)
    {
        $smartHooks = $this->cel("hooks");

        /**
         * @var \DOMElement[]
         */
        $attrs = $this->sst->getNormalAttributes();
        $this->fieldSets = [];
        foreach ($attrs as $attr) {
            if ($attr->structureId !== $this->sst->id) {
                continue;
            }

            /**
             * @var NormalAttribute $attr ;
             */


            if ($attr->isNormal && $attr->phpconstraint) {
                // No export computed constraint
                if (!preg_match('/Anakeen\\\\Core\\\\Utils\\\\Numbers\\:\\:is/', $attr->phpconstraint)) {
                    $smartHooks->appendChild($this->getConstraint($attr));
                }
            }


            if ($attr->isNormal && $attr->phpfunc && (!$attr->phpfile) && $attr->type !== "enum") {
                $smartHooks->appendChild($this->getComputeFunc($attr));
            }
        }
        $structConfig->appendChild($smartHooks);
    }


    protected function extractAutoComplete(\DOMElement $structConfig)
    {
        $smartAuto = $this->cel("autocompletion");
        /**
         * @var \DOMElement[]
         */
        $attrs = $this->sst->getNormalAttributes();
        $this->fieldSets = [];
        foreach ($attrs as $attr) {
            if ($attr->structureId !== $this->sst->id) {
                continue;
            }

            if ($attr->isNormal && $attr->phpfunc && ($attr->phpfile) && $attr->type !== "enum") {
                $smartAuto->appendChild($this->getAutocompleteFunc($attr));
            } elseif ($attr->isNormal && $attr->properties && $attr->properties->autocomplete) {
                $smartAuto->appendChild($this->getAutocompleteFunc($attr));
            }
        }

        $structConfig->appendChild($smartAuto);
    }

    protected function setOptions(\DOMElement $smartAttr, array $opts)
    {
        foreach ($opts as $key => $value) {
            if ($key === "multiple") {
                $smartAttr->setAttribute("multiple", ($value === "yes") ? "true" : "false");
            } elseif ($key === "relativeOrder") {
                if ($value && $value !== "::auto") {
                    $smartAttr->setAttribute("insert-after", $value);
                }
            } else {
                $smartAttrOpt = $this->cel("field-option");
                $smartAttrOpt->nodeValue = $value;
                $smartAttrOpt->setAttribute("name", $key);
                $smartAttr->appendChild($smartAttrOpt);
            }
        }
    }

    protected function getAutocompleteFunc(NormalAttribute $attr)
    {
        $smartAttrHook = $this->cel("field-autocomplete");
        $smartAttrHook->setAttribute("field", $attr->id);
        $smartAttrCallable = $this->cel("field-callable");

        if ($attr->properties && $attr->properties->autocomplete) {
            $parseMethod = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod();
            $parseMethod->parse($attr->properties->autocomplete);
            $smartAttrCallable->setAttribute("function", sprintf("%s::%s", $parseMethod->className, $parseMethod->methodName));
        } else {
            $parseMethod = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyFunction();
            $parseMethod->parse($attr->phpfunc);
            $smartAttrCallable->setAttribute("function", $parseMethod->functionName);
            if ($attr->phpfile) {
                $smartAttrCallable->setAttribute("external-file", $attr->phpfile);
            }
        }

        $smartAttrHook->appendChild($smartAttrCallable);
        foreach ($parseMethod->inputs as $input) {
            $smartAttrArg = $this->cel("field-argument");
            $smartAttrArg->setAttribute("type", $input->type === "any" ? "field" : "string");

            $smartAttrArg->nodeValue = $input->name;
            $smartAttrHook->appendChild($smartAttrArg);
        }

        $smartAttrreturns = $this->cel("field-returns");
        foreach ($parseMethod->outputs as $output) {
            $smartAttrreturn = $this->cel("field-return");
            $smartAttrreturn->setAttribute("field", $output);
            $smartAttrreturns->appendChild($smartAttrreturn);
        }
        $smartAttrHook->appendChild($smartAttrreturns);
        return $smartAttrHook;
    }

    protected function getComputeFunc(NormalAttribute $attr)
    {

        $smartAttrHook = $this->cel("field-hook");
        $smartAttrHook->setAttribute("event", "onPreRefresh");
        $smartAttrHook->setAttribute("field", $attr->id);
        $this->insertCallable($smartAttrHook, $attr->phpfunc);
        return $smartAttrHook;
    }

    protected function insertCallable(\DOMElement $smartAttrHook, $phpfunc)
    {
        $smartAttrCallable = $this->cel("field-callable");

        $parseMethod = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod();
        $parseMethod->parse($phpfunc);

        $smartAttrCallable->setAttribute("function", $parseMethod->className . "::" . $parseMethod->methodName);

        $smartAttrHook->appendChild($smartAttrCallable);
        foreach ($parseMethod->inputs as $input) {
            $smartAttrArg = $this->cel("field-argument");
            $smartAttrArg->setAttribute("type", $input->type === "any" ? "field" : "string");
            if ($input->type === "any") {
                $input->name = strtolower($input->name);
            }
            $smartAttrArg->nodeValue = $input->name;
            $smartAttrHook->appendChild($smartAttrArg);
        }

        if ($parseMethod->outputs) {
            $smartAttrreturn = $this->cel("field-return");
            foreach ($parseMethod->outputs as $output) {
                $smartAttrreturn->setAttribute("field", $output);
            }

            $smartAttrHook->appendChild($smartAttrreturn);
        }
    }

    protected function getComputeMethod($attrid, $phpfunc, $eventName)
    {
        $smartAttrHook = $this->cel("field-hook");
        $smartAttrHook->setAttribute("event", $eventName);
        $smartAttrHook->setAttribute("field", $attrid);
        $smartAttrCallable = $this->cel("field-callable");

        $parseMethod = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod();
        $parseMethod->parse($phpfunc);

        $smartAttrCallable->setAttribute("function", $parseMethod->className . "::" . $parseMethod->methodName);

        $smartAttrHook->appendChild($smartAttrCallable);
        foreach ($parseMethod->inputs as $input) {
            $smartAttrArg = $this->cel("field-argument");
            $smartAttrArg->setAttribute("type", $input->type === "any" ? "field" : "string");
            if ($input->type === "any") {
                $input->name = strtolower($input->name);
            }
            $smartAttrArg->nodeValue = $input->name;
            $smartAttrHook->appendChild($smartAttrArg);
        }

        $smartAttrreturn = $this->cel("field-return");
        $smartAttrreturn->setAttribute("field", $attrid);
        $smartAttrHook->appendChild($smartAttrreturn);
        return $smartAttrHook;
    }

    protected function getConstraint(NormalAttribute $attr)
    {
        $smartAttrHook = $this->cel("field-hook");
        $smartAttrHook->setAttribute("type", "constraint");
        $smartAttrHook->setAttribute("event", "onPreStore");
        $smartAttrHook->setAttribute("field", $attr->id);
        $smartAttrCallable = $this->cel("field-callable");

        $parseMethod = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod();
        $parseMethod->parse($attr->phpconstraint);

        $smartAttrCallable->setAttribute("function", $parseMethod->className . "::" . $parseMethod->methodName);

        $smartAttrHook->appendChild($smartAttrCallable);
        foreach ($parseMethod->inputs as $input) {
            $smartAttrArg = $this->cel("field-argument");
            $smartAttrArg->setAttribute("type", $input->type === "any" ? "field" : "string");
            if ($input->type === "any") {
                $input->name = strtolower($input->name);
            }
            $smartAttrArg->nodeValue = $input->name;
            $smartAttrHook->appendChild($smartAttrArg);
        }

        $smartAttrreturn = $this->cel("field-return");
        $smartAttrHook->appendChild($smartAttrreturn);
        return $smartAttrHook;
    }

    protected function isModAttr(BasicAttribute $attr)
    {
        $sql = sprintf("select id from docattr where docid=%d and id =':%s'", $this->sst->id, pg_escape_string($attr->id));
        DbManager::query($sql, $id, true, true);
        return $id !== false;
    }

    protected function cel($name)
    {
        return $this->dom->createElementNS(self::NSURL, self::NS . ":" . $name);
    }
}

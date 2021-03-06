<?php

namespace Anakeen\Routes\Core\Lib;

use Anakeen\Core\SmartStructure\Attributes;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\Settings;
use Anakeen\Router\Exception;

/**
 * Class DocumentData
 *
 * @package Anakeen\Routes\Core
 */
class DocumentApiData
{
    const GET_PROPERTIES = "document.properties";
    const GET_PROPERTY = "document.properties.";
    const GET_ATTRIBUTES = "document.attributes";
    const GET_ATTRIBUTE = "document.attributes.";
    const GET_STRUCTURE = "family.structure";
    /**
     * @var \Anakeen\Core\Internal\SmartElement document instance
     */
    protected $_document = null;

    protected $defaultFields = null;
    protected $returnFields = null;
    protected $valueRender = array();
    protected $propRender = array();
    /**
     * @var \Slim\Http\request
     */
    protected $request;
    /**
     * @var DocumentDataFormatter
     */
    protected $documentFormater = null;
    /**
     * @var int document icon width in px
     */
    public $iconSize = 32;
    protected $documentId;
    /**
     * @var \Anakeen\Core\Internal\SmartElement
     */
    protected $localSe;

    /**
     * DocumentData constructor.
     *
     * @param \Anakeen\Core\Internal\SmartElement|null $document
     */
    public function __construct(\Anakeen\Core\Internal\SmartElement $document)
    {
        $this->_document = $document;
        $this->defaultFields = self::GET_PROPERTIES . "," . self::GET_ATTRIBUTES;
    }


    /**
     * Initialize the default fields
     *
     * @param array $fields
     *
     * @return $this
     */
    public function setFields(array $fields)
    {
        $this->returnFields = $fields;
        return $this;
    }

    /**
     * Get data from document object
     * No access control are done
     *
     * @param \Anakeen\Core\Internal\SmartElement $document Document
     *
     * @return mixed
     * @throws Exception
     */
    public function getInternal(\Anakeen\Core\Internal\SmartElement $document)
    {
        $this->_document = $document;
        return $this->getDocumentData();
    }

    /**
     * Get the list of the properties required
     *
     * @return array
     */
    protected function _getPropertiesId()
    {
        $properties = array();
        $returnFields = $this->getFields();
        $subField = self::GET_PROPERTY;
        foreach ($returnFields as $currentField) {
            if (strpos($currentField, $subField) === 0) {
                $properties[] = substr($currentField, mb_strlen(self::GET_PROPERTY));
            }
        }
        return $properties;
    }

    /**
     * Get the attributes values
     *
     * @return mixed
     */
    protected function _getAttributes()
    {
        if ($this->_document->doctype === "C") {
            return array();
        }

        return \Anakeen\Routes\Core\Lib\DocumentUtils::getAttributesFields(
            $this->_document,
            self::GET_ATTRIBUTE,
            $this->getFields()
        );
    }

    /**
     * Get the restrict fields value
     *
     * The restrict fields is used for restrict the return of the get request
     *
     * @return array|null
     */
    protected function getFields()
    {
        if ($this->returnFields === null) {
            if ($this->request) {
                $fields = $this->request->getQueryParam("fields");
                if (empty($fields)) {
                    $fields = $this->defaultFields;
                }
                if ($fields) {
                    $this->returnFields = array_map("trim", explode(",", $fields));
                } else {
                    $this->returnFields = array();
                }
            } else {
                return array_map("trim", explode(",", $this->defaultFields));
            }
        }
        return $this->returnFields;
    }

    /**
     * Check if the current restrict field exist
     *
     * @param string $fieldId field
     * @param boolean $strict strict test
     *
     * @return bool
     */
    protected function hasFields($fieldId, $strict = false)
    {
        $returnFields = $this->getFields();

        if (!$strict) {
            foreach ($returnFields as $aField) {
                if (strpos($aField, $fieldId) === 0) {
                    return true;
                }
            }
        } else {
            if (in_array($fieldId, $returnFields)) {
                return true;
            }
        }

        return false;
    }

    protected function getDocumentDataFormatter()
    {
        return new DocumentDataFormatter($this->_document);
    }

    /**
     * Get document data
     *
     * @return array
     * @throws Exception
     */
    public function getDocumentData()
    {
        $return = array();
        $this->documentFormater = $this->getDocumentDataFormatter();
        $correctField = false;
        $hasProperties = false;

        if ($this->hasFields(self::GET_PROPERTIES, true) && !$this->hasFields(self::GET_PROPERTY)) {
            $correctField = true;
            $hasProperties = true;
            $this->documentFormater->useDefaultProperties();
        } elseif ($this->hasFields(self::GET_PROPERTY)) {
            $correctField = true;
            $hasProperties = true;
            $this->documentFormater->setProperties(
                $this->_getPropertiesId(),
                $this->hasFields(self::GET_PROPERTIES, true)
            );
        }

        if ($this->hasFields(self::GET_ATTRIBUTES)) {
            $correctField = true;
            $this->documentFormater->setAttributes($this->_getAttributes());
        }

        $return["document"] = $this->documentFormater->getData();

        if (!$hasProperties) {
            unset($return["document"]["properties"]);
        }

        if ($this->hasFields(self::GET_STRUCTURE)) {
            $correctField = true;
            $return["family"]["structure"] = $this->_getDocumentStructure();
        }

        if (!$correctField) {
            $fields = $this->getFields();
            if ($fields) {
                throw new Exception("ROUTES0103", implode(",", $fields));
            }
        }
        return $return;
    }

    /**
     * Generate the structure of the document
     *
     * @return array
     */
    protected function _getDocumentStructure()
    {
        $this->_document->attributes->orderAttributes();
        $attributes = $this->_document->getAttributes();

        $pos = 1;
        $attrsOrder = [];
        foreach ($this->_document->attributes->attr as $attrs) {
            $attrsOrder[$attrs->id] = $pos++;
        }

        $return = array();
        foreach ($attributes as $attribute) {
            if ($attribute->usefor === "Q") {
                continue;
            }
            $parentAttribute = $attribute->fieldSet;
            $parentIds = array();
            while ($parentAttribute && $parentAttribute->id != Attributes::HIDDENFIELD) {
                $parentId = $parentAttribute->id;
                $parentIds[] = $parentId;
                $parentAttribute = $parentAttribute->fieldSet;
            }
            $parentIds = array_reverse($parentIds);
            $previousId = null;
            unset($target);
            if (!$parentIds && $attribute->id !== Attributes::HIDDENFIELD) {
                if (!isset($return[$attribute->id])) {
                    $return[$attribute->id] = $this->getAttributeInfo(
                        $this->_document->getAttribute($attribute->id),
                        $attrsOrder[$attribute->id]
                    );
                    $return[$attribute->id]["content"] = array();
                }
            }

            foreach ($parentIds as $aid) {
                if ($previousId === null) {
                    if (!isset($return[$aid])) {
                        $return[$aid] = $this->getAttributeInfo(
                            $this->_document->getAttribute($aid),
                            $attrsOrder[$aid]
                        );
                        $return[$aid]["content"] = array();
                    }
                    $target = &$return[$aid]["content"];
                } else {
                    if (!isset($target[$aid])) {
                        $target[$aid] = $this->getAttributeInfo(
                            $this->_document->getAttribute($aid),
                            $attrsOrder[$aid]
                        );
                        $target[$aid]["content"] = array();
                    }
                    $target = &$target[$aid]["content"];
                }
                $previousId = $aid;
            }

            $target[$attribute->id] = $this->getAttributeInfo($attribute, $attrsOrder[$attribute->id]);
        }
        return $return;
    }

    /**
     * Get the attribute info
     *
     * @param \Anakeen\Core\SmartStructure\BasicAttribute $attribute
     * @param int $order
     *
     * @return array
     */
    public function getAttributeInfo(\Anakeen\Core\SmartStructure\BasicAttribute $attribute, $order = 0)
    {
        $info = array(
            "id" => $attribute->id,
            "label" => $attribute->getLabel(),
            "type" => $attribute->type,
            "logicalOrder" => $order,
            "multiple" => $attribute->isMultiple(),
            "options" => $attribute->getOptions()
        );

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        if (isset($attribute->needed)) {
            /**
             * @var \Anakeen\Core\SmartStructure\NormalAttribute $attribute ;
             */
            $info["needed"] = $attribute->needed;
        }
        if (!empty($attribute->phpfile) && $attribute->type !== "enum") {
            /**
             * @var \Anakeen\Core\SmartStructure\NormalAttribute $attribute ;
             */
            if ((strlen($attribute->phpfile) > 1) && ($attribute->phpfunc)) {
                $familyParser = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyFunction();
                $structureFunction = $familyParser->parse($attribute->phpfunc);
                foreach ($structureFunction->outputs as $k => $output) {
                    if (substr($output, 0, 2) === "CT") {
                        unset($structureFunction->outputs[$k]);
                    } else {
                        $structureFunction->outputs[$k] = strtolower($output);
                    }
                }
                $info["helpOutputs"] = $structureFunction->outputs;
            }
        }

        if (!empty($attribute->properties->autocomplete)) {
            $familyParser = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod();
            $structureFunction = $familyParser->parse($attribute->properties->autocomplete);
            foreach ($structureFunction->outputs as $k => $output) {
                if (substr($output, 0, 2) === "CT") {
                    unset($structureFunction->outputs[$k]);
                } else {
                    $structureFunction->outputs[$k] = strtolower($output);
                }
            }
            $info["helpOutputs"] = $structureFunction->outputs;
        }

        if ($attribute->inArray()) {
            if ($this->_document->doctype === "C") {
                /**
                 * @var \Anakeen\Core\SmartStructure $family
                 */
                $family = $this->_document;
                $defaultValue = $family->getDefValue($attribute->id);
            } else {
                $defaultValue = $this->_document->getFamilyDocument()->getDefValue($attribute->id);
            }
            if ($defaultValue) {
                if ($this->_document->doctype === "C") {
                    if (!$this->localSe || $this->localSe->fromid !== $this->_document->id) {
                        $this->localSe = SEManager::initializeDocument($this->_document->id);
                    }
                    $defaultValue = $this->localSe->applyMethod($defaultValue, $defaultValue);
                } else {
                    $defaultValue = $this->_document->applyMethod($defaultValue, $defaultValue);
                }
            }

            $formatDefaultValue = $this->documentFormater->getFormatCollection()->getInfo(
                $attribute,
                $defaultValue,
                $this->_document
            );

            if ($formatDefaultValue) {
                if ($attribute->isMultipleInArray()) {
                    foreach ($formatDefaultValue as $aDefvalue) {
                        $info["defaultValue"][] = $aDefvalue[0];
                    }
                } else {
                    $info["defaultValue"] = $formatDefaultValue[0];
                }
            }
        }

        if ($attribute->type === "enum") {
            if ($attribute->getOption("eformat") !== "auto") {
                $enums = $attribute->getEnumLabel();
                $enumItems = array();
                foreach ($enums as $key => $label) {
                    $enumItems[] = array(
                        "key" => (string) $key,
                        "label" => $label
                    );
                }
                $info["enumItems"] = $enumItems;
            }
            $url = sprintf(
                "smart-structures/%s/enumerates/%s",
                ($this->_document->doctype === "C" ? $this->_document->name : $this->_document->fromname),
                $attribute->id
            );

            $info["enumUrl"] = sprintf("%s%s", Settings::ApiV2, $url);
        }

        return $info;
    }

    /**
     * Compute etag from a document id
     *
     * @param int $id
     *
     * @return string
     * @throws \Anakeen\Database\Exception
     */
    protected static function getDocumentEtag($id)
    {
        $result = array();
        $sql = sprintf("select id, mdate, views from docread where id = %d", $id);

        DbManager::query($sql, $result, false, true);
        $user = \Anakeen\Core\ContextManager::getCurrentUser();
        $result[] = $user->id;
        $result[] = $user->memberof;
        // Necessary only when use family.structure
        $result[] = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_LANG");
        $result[] = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        return join(" ", $result);
    }

    /**
     * Check is the ID is canonical and redirect if not
     *
     * @param $identifier
     * @param $initid
     *
     * @return bool
     */
    protected function checkId($identifier, &$initid)
    {
        if (is_numeric($identifier)) {
            $identifier = (int) $identifier;
            $initid = SEManager::getInitIdFromIdOrName($identifier);

            if ($initid !== 0 && $initid != $identifier) {
                return false;
            }
        }
        return true;
    }
}

<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\URLUtils;
use Dcp\Core\DbManager;
use Dcp\Core\DocManager;
use Dcp\Core\Settings;
use Dcp\HttpApi\V1\Crud\DocumentUtils;
use Dcp\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Dcp\Routes\Document;

/**
 * Class DocumentData
 *
 * @note Used by route : GET /api/v2/documents/{docid}
 * @package Anakeen\Routes\Core
 */
class DocumentData
{
    const GET_PROPERTIES = "document.properties";
    const GET_PROPERTY = "document.properties.";
    const GET_ATTRIBUTES = "document.attributes";
    const GET_ATTRIBUTE = "document.attributes.";
    const GET_STRUCTURE = "family.structure";
    /**
     * @var \Doc document instance
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
     * DocumentData constructor.
     *
     * @param \Doc|null $document
     */
    public function __construct($document = null)
    {
        if ($document !== null) {
            $this->_document = $document;
        }
        $this->defaultFields = self::GET_PROPERTIES . "," . self::GET_ATTRIBUTES;
    }

    /**
     * Get ressource
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\Response
     * @throws Exception
     * @throws \Dcp\Core\Exception
     * @throws \Dcp\Db\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $mb=microtime(true);
        $this->request=$request;
        $this->documentId=$args["docid"];

        if (! $this->checkId($this->documentId, $initid)) {
            // Redirect to other url
            $document=DocManager::getDocument($initid, false);
            $location=Document::getURI($document);
            return $response->withStatus(307)
                ->withHeader("location", $location);
        }

        $this->setDocument($this->documentId);
        $etag=$this->getDocumentEtag($this->_document->id);
        $response=ApiV2Response::withEtag($request, $response, $etag);
        if (ApiV2Response::matchEtag($request, $etag)) {
            return $response;
        }

        $err = $this->_document->control("view");
        if (!$err) {
            if ($this->_document->isConfidential()) {
                $err = "Confidential document";
            }
        } else {
            $err="Access not granted";
        }
        if ($err) {
            $exception = new Exception("ROUTES0101", $this->documentId, $err);
            $exception->setHttpStatus("403", "Forbidden");
            $exception->setUserMessage(___("Document access not granted", "ank"));
            throw $exception;
        }
        if ($this->_document->mid == 0) {
            $this->_document->applyMask(\Doc::USEMASKCVVIEW);
        }
        $data=$this->getDocumentData();
        $data["duration"]=sprintf("%.04f", microtime(true) - $mb);
        return ApiV2Response::withData($response, $data);
    }

    /**
     * Find the current document and set it in the internal options
     *
     * @param $ressourceId string|int identifier of the document
     * @throws Exception
     */
    protected function setDocument($ressourceId)
    {
        $this->_document = DocManager::getDocument($ressourceId);
        if (!$this->_document) {
            $exception = new Exception("ROUTES0100", $ressourceId);
            $exception->setHttpStatus("404", "Document not found");
            $exception->setUserMessage(sprintf(___("Document \"%s\" not found", "ank"), $ressourceId));
            throw $exception;
        }
        if ($this->_document->doctype === "Z") {
            $exception = new Exception("ROUTES0102", $ressourceId);
            $exception->setHttpStatus("404", "Document deleted");
            $location=URLUtils::generateUrl(sprintf("%s/trash/%d", Settings::ApiV2, $this->_document->initid));
            $exception->setURI($location);
            throw $exception;
        }

        DocManager::cache()->addDocument($this->_document);
    }
    /**
     * Initialize the default fields
     *
     * @param $fields
     * @return $this
     */
    public function setDefaultFields($fields)
    {
        $this->returnFields = null;
        $this->defaultFields = $fields;
        return $this;
    }
    /**
     * Get data from document object
     * No access control are done
     * @param \Doc $document Document
     * @throws Exception
     * @return mixed
     */
    public function getInternal(\Doc $document)
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

        return DocumentUtils::getAttributesFields($this->_document, self::GET_ATTRIBUTE, $this->getFields());
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
            $fields=$this->request->getQueryParam("fields");
            if (empty($fields)) {
                $fields = $this->defaultFields;
            }
            if ($fields) {
                $this->returnFields = array_map("trim", explode(",", $fields));
            } else {
                $this->returnFields = array();
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
    /**
     * Get document data
     *
     * @throws Exception
     * @return array
     */
    protected function getDocumentData()
    {
        $return = array();
        $this->documentFormater = new DocumentDataFormatter($this->_document);
        $correctField = false;
        $hasProperties = false;

        if ($this->hasFields(self::GET_PROPERTIES, true) && !$this->hasFields(self::GET_PROPERTY)) {
            $correctField = true;
            $hasProperties = true;
            $this->documentFormater->useDefaultProperties();
        } elseif ($this->hasFields(self::GET_PROPERTY)) {
            $correctField = true;
            $hasProperties = true;
            $this->documentFormater->setProperties($this->_getPropertiesId(), $this->hasFields(self::GET_PROPERTIES, true));
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
        $normalAttributes = $this->_document->getNormalAttributes();

        $return = array();
        $order = 0;
        foreach ($normalAttributes as $attribute) {
            if ($attribute->type === "array") {
                continue;
            }
            $parentAttribute = $attribute->fieldSet;
            $parentIds = array();
            while ($parentAttribute && $parentAttribute->id != 'FIELD_HIDDENS') {
                $parentId = $parentAttribute->id;
                $parentIds[] = $parentId;
                $parentAttribute = $parentAttribute->fieldSet;
            }
            $parentIds = array_reverse($parentIds);
            $previousId = null;
            unset($target);

            foreach ($parentIds as $aid) {
                if ($previousId === null) {
                    if (!isset($return[$aid])) {
                        $return[$aid] = $this->getAttributeInfo($this->_document->getAttribute($aid), $order++);
                        $return[$aid]["content"] = array();
                    }
                    $target = & $return[$aid]["content"];
                } else {
                    if (!isset($target[$aid])) {
                        $target[$aid] = $this->getAttributeInfo($this->_document->getAttribute($aid), $order++);
                        $target[$aid]["content"] = array();
                    }
                    $target = & $target[$aid]["content"];
                }
                $previousId = $aid;
            }
            $target[$attribute->id] = $this->getAttributeInfo($attribute, $order++);
        }
        return $return;
    }
    /**
     * Get the attribute info
     *
     * @param \BasicAttribute $attribute
     * @param int $order
     * @return array
     */
    public function getAttributeInfo(\BasicAttribute $attribute, $order = 0)
    {
        $info = array(
            "id" => $attribute->id,
            "visibility" => ($attribute->mvisibility) ? $attribute->mvisibility : $attribute->visibility,
            "label" => $attribute->getLabel() ,
            "type" => $attribute->type,
            "logicalOrder" => $order,
            "multiple" => $attribute->isMultiple() ,
            "options" => $attribute->getOptions()
        );

        if (isset($attribute->needed)) {
            /**
             * @var \NormalAttribute $attribute ;
             */
            $info["needed"] = $attribute->needed;
        }
        if (!empty($attribute->phpfile) && $attribute->type !== "enum") {
            /**
             * @var \NormalAttribute $attribute ;
             */
            if ((strlen($attribute->phpfile) > 1) && ($attribute->phpfunc)) {
                $familyParser = new \ParseFamilyFunction();
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

        if ($attribute->inArray()) {
            if ($this->_document->doctype === "C") {
                /**
                 * @var \DocFam $family
                 */
                $family = $this->_document;
                $defaultValue = $family->getDefValue($attribute->id);
            } else {
                $defaultValue = $this->_document->getFamilyDocument()->getDefValue($attribute->id);
            }
            if ($defaultValue) {
                $defaultValue = $this->_document->applyMethod($defaultValue, $defaultValue);
            }

            $formatDefaultValue = $this->documentFormater->getFormatCollection()->getInfo($attribute, $defaultValue, $this->_document);

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
                        "key" => (string)$key,
                        "label" => $label
                    );
                }
                $info["enumItems"] = $enumItems;
            }
            $url=sprintf("families/%s/enumerates/%s", ($this->_document->doctype === "C" ? $this->_document->name : $this->_document->fromname), $attribute->id);

            $info["enumUri"] = sprintf("%s%s/%s", URLUtils::getBaseURL(), Settings::ApiV2, $url);
        }

        return $info;
    }

    /**
     * Compute etag from a document id
     *
     * @param int $id
     *
     * @return string
     * @throws \Dcp\Db\Exception
     */
    protected static function getDocumentEtag($id)
    {
        $result = array();
        $sql = sprintf("select id, revdate, views from docread where id = %d", $id);
        
        DbManager::query($sql, $result, false, true);
        $user = \Dcp\Core\ContextManager::getCurrentUser();
        $result[] = $user->id;
        $result[] = $user->memberof;
        // Necessary only when use family.structure
        $result[] = \ApplicationParameterManager::getScopedParameterValue("CORE_LANG");
        $result[] = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        return join(" ", $result);
    }

    /**
     * Check is the ID is canonical and redirect if not
     *
     * @param $identifier
     * @param $initid
     *
     * @return bool
     * @throws \Dcp\Core\Exception
     */
    protected function checkId($identifier, &$initid)
    {
        if (is_numeric($identifier)) {
            $identifier=(int)$identifier;
            $initid = DocManager::getInitIdFromIdOrName($identifier);

            if ($initid !== 0 && $initid != $identifier) {
                return false;
            }
        }
        return true;
    }
}

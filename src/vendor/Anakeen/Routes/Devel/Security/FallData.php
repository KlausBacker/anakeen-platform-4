<?php

namespace Anakeen\Routes\Devel\Security;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\SmartElementManager;
use SmartStructure\Fields\Fieldaccesslayerlist as FallFields;
use SmartStructure\Fields\Fieldaccesslayer as FalFields;

/**
 * Get Right Accesses
 *
 * @note Used by route : GET api/v2/devel/security/fall/{id}
 */
class FallData
{
    protected $documentId;
    /**
     * @var SmartElement
     */
    protected $_document;
    /** @var SmartStructure */
    protected $structure;
    protected $applyedLayer = [];

    /**
     * Return right accesses for a profil element
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     *
     * @return \Slim\Http\response $response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->documentId = $args["id"];
        $this->setDocument($this->documentId);
        $famid = $this->_document->getRawValue(FallFields::fall_famid);
        if ($famid) {
            $this->structure = SEManager::getDocument($famid);
        } else {
            throw new Exception("Field \"%s\" (structure) is not set", FallFields::fall_famid);
        }

        $this->applyedLayer = $request->getParam("layers", []);
        foreach ($this->applyedLayer as &$layerName) {
            if (!is_numeric($layerName)) {
                $layerName = SEManager::getIdFromName($layerName);
            }
        }
    }


    public function doRequest()
    {
        $data["properties"] = ProfileUtils::getProperties($this->_document, false);

        $data["properties"]["structure"] = $this->structure->name;

        foreach ($this->applyedLayer as $requestLayer) {
            $data["request"]["layers"][] = ProfileUtils::getProperties(SEManager::getDocument($requestLayer), false);
        }

        $fields = $this->getFields();

        $data["layers"] = $this->getLayers($fields);

        $this->computeAccess($fields);
        $data["fields"] = $fields;

        return $data;
    }

    protected function getLayers(&$fields)
    {
        $layers = $this->_document->getMultipleRawValues(FallFields::fall_layer);
        $acl = $this->_document->getMultipleRawValues(FallFields::fall_aclname);

        $data = [];
        foreach ($layers as $k => $layerId) {
            $layer = SEManager::getDocument($layerId);
            if ($layer) {
                $prop = ProfileUtils::getProperties($layer, false);
                $prop["aclName"] = $acl[$k];
                $data[] = $prop;
                $fieldLayers = $layer->getArrayRawValues(FalFields::fal_t_fields);

                foreach ($fieldLayers as $fieldLayer) {
                    $id = $fieldLayer[FalFields::fal_fieldid];
                    $fields[$id]["access"][$layer->name] = $fieldLayer[FalFields::fal_fieldaccess];

                    $oa = $this->structure->getAttribute($id);

                    if ($oa && in_array($layer->id, $this->applyedLayer)) {
                        $oa->access |= SmartStructure\FieldAccessManager::getRawAccess($fieldLayer[FalFields::fal_fieldaccess]);
                    }
                }
            }
        }
        return $data;
    }

    protected function getFields()
    {
        $data = [];

        $fields = $this->structure->getAttributes();
        foreach ($fields as $field) {
            if ($field->usefor === "Q" || $field->id === SmartStructure\Attributes::HIDDENFIELD) {
                continue;
            }
            $data[$field->id] =
                [
                    "id" => $field->id,
                    "type" => $field->type,
                    "parent" => ($field->fieldSet->id !== SmartStructure\Attributes::HIDDENFIELD) ? $field->fieldSet->id : null,
                    "access" => [
                        "_original_" => SmartStructure\FieldAccessManager::getTextAccess($field->access),
                        "_originalComputed_" => SmartStructure\FieldAccessManager::getTextAccess($field->getAccess())
                    ]
                ];
        }

        return $data;
    }


    protected function computeAccess(&$data)
    {
        $fields = $this->structure->getAttributes();
        foreach ($fields as $field) {
            if ($field->usefor === "Q" || $field->id === SmartStructure\Attributes::HIDDENFIELD) {
                continue;
            }
            $data[$field->id]["access"]["_final_"] = SmartStructure\FieldAccessManager::getTextAccess($field->getAccess());
        }
    }


    /**
     * Find the current document and set it in the internal options
     *
     * @param $resourceId
     *
     * @throws Exception
     * @throws \Anakeen\Core\DocManager\Exception
     */
    protected function setDocument($resourceId)
    {
        $this->_document = SmartElementManager::getDocument($resourceId);
        if (!$this->_document) {
            $exception = new Exception("ROUTES0100", $resourceId);
            $exception->setHttpStatus("404", "Element not found");
            throw $exception;
        }
        if ($this->_document->fromname !== "FIELDACCESSLAYERLIST") {
            throw new Exception(sprintf("Element \"%s\" is not a %s (it is a %s)", $resourceId, SEManager::getTitle("FIELDACCESSLAYERLIST"), $this->_document->fromname));
        }
    }
}

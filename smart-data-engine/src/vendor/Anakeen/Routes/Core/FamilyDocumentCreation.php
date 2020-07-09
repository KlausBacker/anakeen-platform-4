<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\SmartElementManager;

/**
 * Class FamilyDocumentCreation
 *
 * @note    Used by route : POST /api/v2/smart-structures/{family}/
 * @package Anakeen\Routes\Core
 */
class FamilyDocumentCreation extends DocumentUpdateData
{
    /**
     * @var \Anakeen\Core\SmartStructure
     */
    protected $_family = null;
    /**
     * @var \Anakeen\Core\Internal\SmartElement document instance
     */
    protected $_document = null;


    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $familyId = $args["family"];

        $this->_family = SEManager::getFamily($familyId);
        if (!$this->_family) {
            $exception = new Exception("CRUD0200", $familyId);
            $exception->setHttpStatus("404", "Family not found");
            throw $exception;
        }

        $this->create($request, $this->_family, $messages);

        $data = $this->getDocumentData();
        $response = $response->withStatus(201);
        return ApiV2Response::withData($response, $data, $messages);
    }

    /**
     * @param \Slim\Http\request $request
     * @param \Anakeen\Core\SmartStructure $family
     * @param array $messages
     * @return \Anakeen\Core\Internal\SmartElement
     * @throws Exception
     */
    public function create(\Slim\Http\request $request, SmartStructure $family, &$messages)
    {
        try {
            $this->_document = SmartElementManager::createDocument($family->id);
        } catch (Exception $exception) {
            if ($exception->getDcpCode() === "APIDM0003") {
                $exception = new Exception("API0204", $family->name);
                $exception->setHttpStatus(403, "Forbidden");
                throw $exception;
            } else {
                throw $exception;
            }
        }

        $this->updateDocument($request, $messages);
        $this->updateHtmlImageLinks();

        return $this->_document;
    }

    /**
     * Recompute image url in htmltext after id has been set
     */
    protected function updateHtmlImageLinks()
    {
        $fields = $this->_document->getNormalAttributes();
        $htmlFields = [];
        foreach ($fields as $field) {
            if ($field->type === "htmltext") {
                $value = $this->_document->getRawValue($field->id);
                if ($value) {
                    $err = $this->_document->setValue($field->id, $value . "\n");
                    if (!$err && $this->_document->getRawValue($field->id) !== $value) {
                        $htmlFields[] = $field->id;
                    }
                }
            }
        }
        if ($htmlFields) {
            $this->_document->modify(false, $htmlFields, false);
        }
    }
}

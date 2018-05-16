<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\SEManager;
use Anakeen\Core\Utils\Gettext;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use \Anakeen\Core\Internal\StoreInfo;

/**
 * Class DocumentUpdateData
 *
 * Modify attribute values of a document
 *
 * @note    Used by route : PUT /api/v2/documents/{docid}
 * @package Anakeen\Routes\Core
 */
class DocumentUpdateData extends DocumentData
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $mb = microtime(true);
        $this->request = $request;
        $this->documentId = $args["docid"];


        $messages = [];
        $this->updateData($request, $this->documentId, $messages);


        $data = $this->getDocumentData();
        $data["duration"] = sprintf("%.04f", microtime(true) - $mb);

        return ApiV2Response::withData($response, $data, $messages);
    }


    public function updateData(\Slim\Http\request $request, $docid, &$messages = [])
    {
        $this->setDocument($docid);

        $err = $this->_document->canEdit();
        if ($err) {
            $exception = new Exception("CRUD0201", $docid, $err);
            $exception->setUserMessage(___("Update forbidden", "HTTPAPI_V1"));
            $exception->setHttpStatus("403", "Forbidden");
            throw $exception;
        }

        if ($this->_document->doctype === 'C') {
            $exception = new Exception("CRUD0213", $this->_document->name);
            $exception->setHttpStatus("403", "Forbidden");
            throw $exception;
        }
        $this->updateDocument($request, $messages);
    }

    protected function updateDocument(\Slim\Http\request $request, &$messages = [])
    {
        $messages=[];
        $dataDocument = $request->getParsedBody();
        $newValues = $this->getAttributeValues($dataDocument);
        foreach ($newValues as $aid => $value) {
            try {
                if ($value === null or $value === '') {
                    $this->_document->setAttributeValue($aid, null);
                } else {
                    $this->_document->setAttributeValue($aid, $value);
                }
            } catch (\Dcp\AttributeValue\Exception $e) {
                $exception = new Exception("ROUTES0107", $this->_document->id, $aid, $e->getDcpMessage());
                $exception->setHttpStatus("500", "Unable to modify the document");
                $exception->setUserMEssage(___("Document update failed", "ank"));
                $info = array(
                    "id" => $aid,
                    "index" => $e->index,
                    "err" => $e->originalError ? $e->originalError : $e->getDcpMessage()
                );

                $exception->setData($info);
                throw $exception;
            }
        }

        $this->renameFileNames();
        /**
         * @var StoreInfo $info
         */
        $err = $this->_document->store($info);
        if ($err) {
            $exception = new Exception("ROUTES0109", $this->_document->id, $err);
            $exception->setHttpStatus("500", "Unable to modify the document");
            $exception->setUserMEssage(___("Document update failed", "ank"));
            $exception->setData($info);
            throw $exception;
        }
        if ($info->refresh) {
            $message = new \Anakeen\Routes\Core\Lib\ApiMessage();
            $message->contentText = ___("Document information", "ank");
            $message->contentHtml = $info->refresh;
            $message->type = $message::MESSAGE;
            $message->code = "refresh";
            $messages[] = $message;
        }
        if ($info->postStore) {
            $message = new \Anakeen\Routes\Core\Lib\ApiMessage();
            $message->contentText = $info->postStore;
            $message->type = $message::MESSAGE;
            $message->code = "store";
            $messages[] = $message;
        }
        $this->_document->addHistoryEntry(Gettext::___("Updated by API", "ank"), \DocHisto::NOTICE, "UPDATE");
        SEManager::cache()->addDocument($this->_document);
    }


    /**
     * Honor "rn" file option
     * Rename file names if a new file is loaded.
     */
    protected function renameFileNames()
    {
        $fa = $this->_document->GetFileAttributes();
        foreach ($fa as $aid => $oa) {
            $rn = $oa->getOption("rn");
            $ov = $this->_document->getOldRawValue($aid);
            if ($rn && $ov !== false && $ov !== $this->_document->getRawValue($aid)) {
                $this->_document->refreshRn();
                return;
            }
        }
    }

    /**
     * Extract raw value from body content
     *
     * @param array $dataDocument
     *
     * @return array
     * @throws Exception
     */
    protected static function getAttributeValues(array $dataDocument)
    {

        if (!isset($dataDocument["document"]["attributes"]) || !is_array($dataDocument["document"]["attributes"])) {
            throw new Exception("ROUTES0106", print_r($dataDocument, true));
        }
        $values = $dataDocument["document"]["attributes"];

        $newValues = array();
        // Only keep the value element of each attribute passed
        foreach ($values as $attributeId => $value) {
            if (is_array($value) && !array_key_exists("value", $value)) {
                $multipleValues = array();
                foreach ($value as $singleValue) {
                    if (is_array($singleValue) && !array_key_exists("value", $singleValue)) {
                        $multipleSecondLevelValues = array();
                        foreach ($singleValue as $secondVValue) {
                            $multipleSecondLevelValues[] = $secondVValue["value"];
                        }
                        $multipleValues[] = $multipleSecondLevelValues;
                    } else {
                        if (!array_key_exists("value", $singleValue)) {
                            throw new Exception("ROUTES0108", $attributeId);
                        }
                        $multipleValues[] = $singleValue["value"];
                    }
                }
                $newValues[$attributeId] = $multipleValues;
            } else {
                if (!is_array($value) || !array_key_exists("value", $value)) {
                    throw new Exception("ROUTES0108", $attributeId);
                }
                $newValues[$attributeId] = $value["value"];
            }
        }
        return $newValues;
    }
}

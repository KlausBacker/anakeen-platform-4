<?php

namespace Anakeen\Routes\Ui;

use Anakeen\Routes\Core\DocumentUpdateData;
use Anakeen\Router\Exception;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;

/**
 * Class DocumentViewUpdate
 *
 * @note    Used by route : PUT /api/v2/documents/{docid}/revisions/{revision}/views/{view}
 * @package Anakeen\Routes\Ui
 */
class DocumentViewUpdate extends DocumentView
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {

        $resourceId = $args["docid"];
        $this->viewIdentifier = $args["view"];
        $this->revision = $args["revision"];

        $messages = [];

        $document = $this->getDocument($resourceId);


        if ($this->viewIdentifier != self::coreViewEditionId) {
            // apply specified mask
            if (($this->viewIdentifier != self::defaultViewEditionId) && ($document->cvid > 0)) {
                // special controlled view

                /**
                 * @var \SmartStructure\Cvdoc $cvdoc
                 */
                $cvdoc = SEManager::getDocument($document->cvid);
                $cvdoc->Set($document);
                $err = $cvdoc->control($this->viewIdentifier); // control special view
                if ($err != "") {
                    $exception = new Exception("CRUDUI0008", $this->viewIdentifier, $cvdoc->getTitle(), $document->getTitle());
                    $exception->setUserMessage($err);
                    $exception->setHttpStatus("403", "Access deny");
                    throw $exception;
                }
            }
        }


        $dataDocument = $request->getParsedBody();
        $properties = $dataDocument["document"]["properties"];

        if ($document && !$document->isAlive() && isset($properties["status"]) && $properties["status"] === "alive") {
            //Handle Restore Document from trash
            $err = $document->undelete();
            if ($err) {
                throw new \Dcp\Ui\Exception("Unable to restore $err");
            }
        } else {
            if ($err = $document->canEdit()) {
                throw new Exception("CRUD0201", $resourceId, $err);
            }
            $documentData = new DocumentUpdateData();
            $documentData->updateData($request, $document->initid, $messages);
        }


        if ($document->canEdit() != "") {
            // Redirect to a consultation view if edit access has changed
            $args["view"] = self::defaultViewConsultationId;
        }

        $response = parent::__invoke($request, $response, $args);
        if ($messages) {
            $response = ApiV2Response::withMessages($response, $messages);
        }
        return $response;
    }

    protected function getEtagInfo($docid)
    {
        return null;
    }
}

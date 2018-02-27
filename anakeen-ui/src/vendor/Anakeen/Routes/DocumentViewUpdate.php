<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Routes\Ui;

use Anakeen\Routes\Core\DocumentUpdateData;
use Dcp\AttributeIdentifiers\Cvdoc as CvdocAttribute;
use Anakeen\Router\Exception;
use Dcp\Core\DocManager as DocManager;

/**
 * Class DocumentViewUpdate
 *
 * @note Used by route : PUT /api/v2/documents/{docid}/revisions/{revision}/views/{view}
 * @package Anakeen\Routes\Ui
 */
class DocumentViewUpdate extends DocumentView
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {

        $resourceId = $args["docid"];
        $this->request = $request;
        $this->viewIdentifier = $args["view"];

        $this->revision = $args["revision"];
        $document = $this->getDocument($resourceId);
        if ($err = $document->canEdit()) {
            throw new Exception("CRUD0201", $resourceId, $err);
        }

        if ($this->viewIdentifier != self::coreViewEditionId) {
            // apply specified mask
            if (($this->viewIdentifier != self::defaultViewEditionId) && ($document->cvid > 0)) {
                // special controlled view

                /**
                 * @var \Dcp\Family\Cvdoc $cvdoc
                 */
                $cvdoc = DocManager::getDocument($document->cvid);
                $cvdoc->Set($document);
                $err = $cvdoc->control($this->viewIdentifier); // control special view
                if ($err != "") {
                    $exception = new Exception("CRUDUI0008", $this->viewIdentifier, $cvdoc->getTitle(), $document->getTitle());
                    $exception->setUserMessage($err);
                    $exception->setHttpStatus("403", "Access deny");
                    throw $exception;
                }
                $tview = $cvdoc->getView($this->viewIdentifier);
                $mask = $tview[CvdocAttribute::cv_mskid];
                if ($mask) {
                    $document->setMask($mask); // apply mask to avoid modification of invisible attribute
                }
            } elseif ($document->cvid > 0) {
                $document->setMask($document::USEMASKCVEDIT);
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
            $documentData = new DocumentUpdateData();
            $response = $documentData->__invoke($request, $response, $args);
        }


        if ($document->canEdit() != "") {
            // Redirect to a consultation view if edit access has changed
            $args["view"] = self::defaultViewConsultationId;
        }

        return parent::__invoke($request, $response, $args);
    }

    protected function getEtagInfo($docid)
    {
        return null;
    }
}

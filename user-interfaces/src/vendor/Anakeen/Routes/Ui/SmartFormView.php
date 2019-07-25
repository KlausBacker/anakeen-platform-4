<?php

namespace Anakeen\Routes\Ui;

use Anakeen\Router\Exception;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;

/**
 * Class DocumentView
 *
 * @note    Used by route : GET /api/v2/smart-elements/{docid}/revisions/{revision}/views/{view}
 * @note    Used by route : GET /api/v2/smart-elements/{docid}/views/{view}
 * @package Anakeen\Routes\Ui
 */
class SmartFormView extends DocumentView
{


    /**
     * Read a resource
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return mixed
     * @throws Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);



        if (!in_array($this->viewIdentifier, array(
                self::coreViewCreationId,
                self::defaultViewConsultationId,
                self::defaultViewEditionId,
                self::defaultViewCreationId,
                self::coreViewConsultationId,
                self::coreViewEditionId
            ))
           ) {
            $exception = new Exception("CRUDUI0001", $this->viewIdentifier, $this->documentId);
            $exception->setHttpStatus("404", "View not found");
            throw $exception;
        }

        $info = $this->doRequest($messages);
        return ApiV2Response::withData($response, $info, $messages);
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->document=SEManager::createDocument(1);
        $this->viewIdentifier = $args["view"];
            $this->requestFields =  array(
                self::fieldRenderOptions,
                self::fieldRenderLabel,
                self::fieldCustomServerData,
                self::fieldMenu,
                self::fieldTemplate,
                self::fieldDocumentData,
                self::fieldLocale,
                self::fieldStyle,
                self::fieldScript,
            );


        if (!empty($request->getQueryParam("noStructureFamily"))) {
            $this->needSendFamilyStructure = false;
        }

        //Add custom client data
        if ($request->getMethod() === "GET" && !empty($request->getQueryParam(self::fieldCustomClientData))) {
            $this->customClientData = json_decode($request->getQueryParam(self::fieldCustomClientData), true);
        }

        if (in_array($request->getMethod(), ["POST", "PUT"])) {
            $body = $request->getParsedBody();
            if (isset($body[self::fieldCustomClientData])) {
                $this->customClientData = $body[self::fieldCustomClientData];
            }
        }
    }

    protected function doRequest(&$messages = [])
    {
        $refreshMsg = '';
        $creationMode = false;
        $family = null;
        if ($this->viewIdentifier === self::coreViewCreationId || $this->viewIdentifier === self::defaultViewCreationId) {
            /**
             * @var \Anakeen\Core\SmartStructure $family
             */
            $family = SEManager::getFamily($this->documentId);
            $creationMode = true;
        } else {
        }



        $vid = $this->viewIdentifier;
        $messages = [];
        $info["view"] = $this->getViewInformation($vid, $messages);

        if ($vid === "") {
            $coreViews = $this->getCoreViews($this->document);
            if ($this->viewIdentifier === self::defaultViewConsultationId) {
                $info["properties"] = $coreViews[self::coreViewConsultationId];
            } elseif ($this->viewIdentifier === self::defaultViewEditionId) {
                $info["properties"] = $coreViews[self::coreViewEditionId];
            } elseif ($this->viewIdentifier === self::coreViewConsultationId) {
                $info["properties"] = $coreViews[self::coreViewConsultationId];
            } elseif ($this->viewIdentifier === self::coreViewEditionId) {
                $info["properties"] = $coreViews[self::coreViewEditionId];
            } elseif ($this->viewIdentifier === self::coreViewCreationId) {
                $info["properties"] = $coreViews[self::coreViewCreationId];
            } elseif ($this->viewIdentifier === self::defaultViewCreationId) {
                $info["properties"] = $coreViews[self::coreViewCreationId];
            }
        }

        return $info;
    }
}

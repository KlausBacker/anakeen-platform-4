<?php


namespace Anakeen\Routes\Ui;

use Anakeen\Router\ApiV2Response;
use Anakeen\Routes\Core\Lib\ApiMessage;
use Anakeen\SmartElementManager;

/**
 * Class CallMenu
 * @note    Used by route : POST /api/v2/documents/{docid}/views/{view}/menus/{menu}/call
 * @package Anakeen\Routes\Ui
 */
class CallMenu
{
    /**
     * @var \Anakeen\Core\SmartStructure
     */
    protected $_family = null;


    /**
     * Get Submenu content
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     * @return \Slim\Http\response
     * @throws \Dcp\Ui\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $resp = $this->getMenuContent($request, $response, $args["docid"], $args["view"], $args["menu"]);
        $data =$resp->getData();
        if ($resp->needReload()) {
            $data["needReload"]=true;
        }
        return ApiV2Response::withData($response, $data, [$resp->getMessage()]);
    }

    protected function getMenuContent(\Slim\Http\request $request, \Slim\Http\response $response, $documentId, $vId, $menuId): CallMenuResponse
    {
        $renderMode = "view";

        $doc = SmartElementManager::getDocument($documentId);

        if (!$doc) {
            throw new \Dcp\Ui\Exception(sprintf(___("Document \"%s\" not found ", "ddui"), $documentId));
        }
        if ($request->getMethod() === "POST" || $request->getMethod() === "PUT") {
            $err = $doc->control("edit");
            if ($err) {
                throw new \Dcp\Ui\Exception($err);
            }
        } elseif ($request->getMethod() === "DELETE") {
            $err = $doc->control("delete");
            if ($err) {
                throw new \Dcp\Ui\Exception($err);
            }
        }

        if ($vId && $vId[0] === "!") {
            $vId = '';
        }

        $config = \Dcp\Ui\RenderConfigManager::getRenderConfig($renderMode, $doc, $vId);
        $menu = $config->getMenu($doc);
        /**
         * @var \Dcp\Ui\CallableMenu $element
         */
        $element = $menu->getElement($menuId);
        if (!$element) {
            throw new \Dcp\Ui\Exception(sprintf(___("Menu id \"%s\" not found ", "ui"), $menuId));
        }

        if ($element->getMethod() !== $request->getMethod()) {
            throw new \Dcp\Ui\Exception(sprintf(___("Menu \"%s\" : Method mismatch need \"%s\" and has \"%s\"", "ui"), $menuId, $request->getMethod(), $element->getMethod()));
        }

        return $element->callMenuRequest($request, $response);
    }
}

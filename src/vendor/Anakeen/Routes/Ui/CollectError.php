<?php

namespace Anakeen\Routes\Ui;

use Anakeen\Core\ContextManager;

use Anakeen\LogManager;

/**
 * Class Autocomplete
 *
 * @note    Used by route : POST /api/v2/smart-elements/{docid}/autocomplete/{attrid}
 * @package Anakeen\Routes\Ui
 */
class CollectError
{

    /**
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return mixed
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $requestData = $request->getParsedBody();
        $message = isset($requestData["message"]) ? $requestData["message"] : "";
        $context = isset($requestData["url"]) ? "## URL : " . $requestData["url"] : "";
        $context.= " " . (isset($requestData["useragent"]) ? "## BrowserUserAgent : " . $requestData["useragent"] : "");
        $context.= " ##User : " . ContextManager::getCurrentUser()->getAccountName() . ")";
        $stack = isset($requestData["stack"]) ? print_r($requestData["stack"], true) : "";
        LogManager::error($message, ["context" => $context, "stack" => $stack]);
        return $response->withStatus(200);
    }
}

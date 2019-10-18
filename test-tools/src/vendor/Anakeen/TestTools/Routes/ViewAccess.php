<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Core\SEManager;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;

class ViewAccess
{
    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     */
    public function __invoke(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        $args
    ) {
        if (!empty($args['seId'])) {
            $smartElement = SEManager::getDocument($args['seId']);
            if (empty($smartElement)) {
                $exception = new Exception("ANKTEST001", $args['seId']);
                $exception->setHttpStatus("500", "Cannot get smart element");
                throw $exception;
            }
            $cvid = $smartElement->cvid;
            $viewController = SEManager::getDocument($cvid);
            if (empty($viewController)) {
                $exception = new Exception("ANKTEST001", $cvid);
                $exception->setHttpStatus("500", "Cannot get view controller");
                throw $exception;
            }
            $err = $viewController->control($args["viewId"]);
            error_log(">>>>>>>>>>>>>>>".print_r($viewController, true));
            if (!empty($err)) {
                $exception = new Exception("ANKTEST003", $smartElement->id, $err);
                $exception->setHttpStatus("403", "Access forbidden");
                throw $exception;
            }
        } else {
            $exception = new Exception("ANKTEST004", 'seId');
            $exception->setHttpStatus("400", "smart element identifier is required");
            throw $exception;
        }
        $smartElementData = new \Anakeen\Routes\Core\Lib\DocumentApiData($smartElement);
        $smartElementData->setFields(["document.properties.all", "document.attributes.all"]);
        return ApiV2Response::withData($response, $smartElementData->getDocumentData());
    }
}

<?php

namespace Anakeen\Components\Grid\Routes;

use Anakeen\Components\Grid\SmartGridControllerParameter;
use Anakeen\Components\Grid\Exceptions\Exception;
use Anakeen\Components\Grid\SmartGridController;
use Anakeen\Router\ApiV2Response;

/**
 * Get Smart Element Grid controller
 *
 * Class GridController
 *
 * @note    Used by route : GET /api/v2/grid/controller/<controller>/<operationId>[/<collectionId>]
 * @package Anakeen\Routes\Authent
 */
class GridController
{

    const OPERATION_CONFIG = "config";
    const OPERATION_CONTENT = "content";
    const OPERATION_EXPORT = "export";

    /**
     * Recursively JSON decodes a non homogen PHP structure.
     * Example: [ "columns" => [ 0 => "{field: 'title', label: 'Title'}" ] ]
     * should give : [ "columns" => [ 0 => [ "field" => "title", "label" => "Title" ] ] ]
     *
     * @param mixed $params - The params to decode
     *
     * @return mixed
     */
    protected static function parseParams($params)
    {
        if (is_string($params)) {
            return json_decode($params, true);
        } elseif (is_array($params)) {
            return array_map("static::parseParams", $params);
        } else {
            return $params;
        }
    }

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        if (!isset($args["controllerName"])) {
            throw new Exception("GRID0008");
        }

        if (!isset($args["operationId"])) {
            throw new Exception("GRID0009");
        }

        $controllerName = $args["controllerName"];
        $operationId = $args["operationId"];
        $collectionId = $args["collectionId"] ?? null;

        $controller = SmartGridControllerParameter::getControllerByName($controllerName);
        if (!empty($controller)) {

            /**
             * @var SmartGridController
             */
            $controllerClass = $controller["class"];
            if (!is_subclass_of($controllerClass, SmartGridController::class)) {
                throw new Exception("GRID0014", $controllerClass, SmartGridController::class);
            }

            $clientConfig = static::parseParams($request->getParams());
            $data = [];
            switch ($operationId) {
                case self::OPERATION_CONFIG:
                    $data = $controllerClass::getGridConfig($collectionId, $clientConfig);
                    break;
                case self::OPERATION_CONTENT:
                    $data = $controllerClass::getGridContent($collectionId, $clientConfig);
                    break;
                case self::OPERATION_EXPORT:
                    $data = $controllerClass::exportGridContent($response, $collectionId, $clientConfig);
                    return $data;
                    break;
                default:
                    throw new Exception("GRID0010", $operationId);
                    break;
            }
            return ApiV2Response::withData($response, $data);
        } else {
            $error = new Exception("GRID0011", $controllerName);
            $error->setHttpStatus(404, "Cannot find Smart Grid Controller " . $controllerName);
            throw $error;
        }
    }
}

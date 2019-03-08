<?php

namespace Anakeen\Routes\TransformationEngine\Admin;

use Anakeen\Core\DbManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Exception;

/**
 *
 * @use     by route GET /api/admin/transformationengine/config/
 */
class GetTeConfiguration
{

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
    }

    /**
     *
     * @return array
     * @throws Exception
     */
    protected function doRequest()
    {
        return self::getTeConfig();
    }

    public static function getTeConfig()
    {
        $data = [];

        DbManager::query("select * from paramv where name ~ '^TE::'", $values);

        foreach ($values as $value) {
            list(, $name) = explode("::", $value["name"]);

            switch ($name) {
                case "TE_TIMEOUT":
                case "TE_PORT":
                    $data[$name]  = intval($value["val"]);
                    break;
                case "TE_ACTIVATE":
                    $data[$name]  = ($value["val"] === "yes");
                    break;
                default:
                    $data[$name] = $value["val"];
            }
        }

        return $data;
    }
}

<?php


namespace Anakeen\Routes\Devel\Security;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\RouterManager;

class Routes
{
    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @return \Slim\Http\response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response)
    {
        $allRoutes = new \Anakeen\Router\RouterManager();
        $tabRoutes = $allRoutes->getRoutes();
        $result = [];
        foreach ($tabRoutes as $route) {
            $formatedRoute = $this->formatRoute($route);
            if ($formatedRoute !== null) {
                $result[] = $formatedRoute;
            }
        }
        return ApiV2Response::withData($response, $result);
    }
    /**
     * @param $route
     * @return array
     * @throws \Dcp\Core\Exception
     * Retrieve dataSource from RoutesConfig
     */
    private function formatRoute(\Anakeen\Router\Config\RouterInfo $route)
    {
        $formatedRoute = [];
        $nsName = explode('::', $route->name, 2);

        if (!empty($nsName[1])) {
            $formatedRoute['nameSpace'] = $nsName[0];
            $formatedRoute['name'] = $nsName[1];
        } else {
            $formatedRoute['name'] = $nsName[0];
        }
        $formatedRoute['description'] = $route->description;

        $formatedRoute['method'] = $route->methods[0];
        $formatedRoute['pattern'] = is_array($route->pattern) ? implode("\n", $route->pattern) : $route->pattern;
        $formatedRoute['priority'] = $route->priority;
        $formatedRoute['override'] = $route->override;
        $formatedRoute['active'] = $route->isActive();
        $formatedRoute['requiredAccess'] = $route->requiredAccess;

        return $formatedRoute;
    }
}
<?php

namespace Anakeen\Routes\Admin\Routes;

use Anakeen\Core\DbManager;
use Dcp\Db\Exception;

class AllRoutes
{
    private function formatRoute($route)
    {
            $formatedRoute = [];

            $nsName = explode('::', $route->name, 2);

            $formatedRoute['nameSpace'] = $nsName[0];
            $formatedRoute['name'] = $nsName[1];

            $formatedRoute['description'] = $route->description;

            $formatedRoute['method'] = $route->methods[0];
            $formatedRoute['pattern'] = $route->pattern;

            $formatedRoute['priority'] = $route->priority;
            $formatedRoute['overrided'] = $route->override;


            return $formatedRoute;
    }
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response)
    {
        $allRoutes = new \Anakeen\Router\RoutesConfig();
        $tabRoutes = $allRoutes->getRoutes();
        $result = [];
        foreach($tabRoutes as $route){
            $formatedRoute = $this->formatRoute($route);
            if($formatedRoute !== null){
                $result[]=$formatedRoute;
            }
        }
        return $response->withJson($this->formatTreeDataSource($result));
    }
}
<?php

namespace Anakeen\Routes\Admin\Routes;

use Anakeen\Core\DbManager;
use Dcp\Db\Exception;

class AllRoutes
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
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

            if(!empty($nsName[1])) {
                $formatedRoute['nameSpace'] = $nsName[0];
                $formatedRoute['name'] = $nsName[1];
            } else {
                $formatedRoute['name'] = $nsName[0];
            }
            $formatedRoute['description'] = $route->description;

            $formatedRoute['method'] = $route->methods[0];
            $formatedRoute['pattern'] = $route->pattern;

            $formatedRoute['priority'] = $route->priority;
            $formatedRoute['overrided'] = $route->override;


            return $formatedRoute;
    }

    private function formatTreeDataSource($routes) {
        $route = $routes;
        uasort($route, function ($a, $b)
        {
            if ($a['name'] && !$b['name']) {
                return -1;
            } elseif (!$a['name'] && $b['name']) {
                return 1;
            } else {
                return ($a['nameSpace'] < $b['nameSpace']) ? -1 : 1;
            }
        });
        $currentId = 1;
        $tree = [];
        $nameSpaceTab = [];
        $nameTab = [];

        foreach($route as $item){
            $item['id'] = $currentId++;
            $currentNameSpace = $nameSpaceTab[$item['nameSpace']];
            if($currentNameSpace === null && $item['nameSpace'] !== null) {
                $newId = $currentId++;
                array_push($tree, ['id' => $newId, 'parentId' => null, 'name' => $item['nameSpace'], 'rowLevel' => 1]);
                $nameSpaceTab[$item['nameSpace']] = $newId;
                $currentNameSpace = $newId;
            }
            if($item['name']) {
                $currentName = $nameTab[$item['nameSpace']][$item['name']];
                if($currentName === null){
                    $newId = $currentId++;
                    array_push($tree,['id' => $newId, 'parentId' => $currentNameSpace, 'name' => $item['name'],'description' => $item['description'], 'priority' => $item['priority'], 'method' => $item['method'], 'pattern' => $item['pattern'], 'overrided' => $item['override'], 'rowLevel' => 2]);
                    $nameTab[$item['nameSpace']][$item['name']] = $newId;
                }
            } else {
                $item['parentId'] = $currentNameSpace;
                $tree[] = $item;
            }
        }
        return $tree;
    }
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response)
    {
        if (strToupper($request->getMethod()) === 'GET') {
            $allRoutes = new \Anakeen\Router\RoutesConfig();
            $tabRoutes = $allRoutes->getRoutes();
            $result = [];
            foreach ($tabRoutes as $route) {
                $formatedRoute = $this->formatRoute($route);
                if ($formatedRoute !== null) {
                    $result[] = $formatedRoute;
                }
            }
            return $response->withJson($this->formatTreeDataSource($result));
        } else if (strToupper($request->getMethod()) === 'POST') {
            return $response->withJson($request->getParam('toggleValue'));
        } else {
            return $response->withStatus(405, 'method unauthorized');
        }
    }
}
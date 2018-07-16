<?php

namespace Anakeen\Routes\Admin\Routes;

use Anakeen\Router\ApiV2Response;

class AllRoutes
{
    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @throws \Dcp\Core\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response)
    {
            $allRoutes = new \Anakeen\Router\RoutesConfig();
            $tabRoutes = $allRoutes->getRoutes();
            $result = [];
            foreach ($tabRoutes as $route) {
                $formatedRoute = $this->formatRoute($route);
                if ($formatedRoute !== null) {
                    $result[] = $formatedRoute;
                }
            }
            return ApiV2Response::withData($response, $this->formatTreeDataSource($result));
    }
    /**
     * @param $route
     * @return array
     * @throws \Dcp\Core\Exception
     * Retrieve dataSource from RoutesConfig
     */
    private function formatRoute($route)
    {
            $active =\Anakeen\Router\RouterLib::getRouteInfo($route->name);
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
            $formatedRoute['pattern'] = is_array($route->pattern) ? implode("\n",$route->pattern) : $route->pattern;
            $formatedRoute['priority'] = $route->priority;
            $formatedRoute['overrided'] = $route->override;
            $formatedRoute['active'] = $active->isActive();

            return $formatedRoute;
    }

    /**
     * @param $routes
     * @return array
     * reformat treeDataSource to correspond treeList content
     */
    private function formatTreeDataSource($routes) {
        $route = $routes;
        uasort($route, function ($a, $b)
        {
            if ($a['name'] && !$b['name']) {
                return -1;
            } elseif (!$a['name'] && $b['name']) {
                return 1;
            } else {
                return (strcmp($a['nameSpace'], $b['nameSpace'])) ? -1 : 1;
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
                array_push($tree, ['id' => $newId, 'parentId' => null, 'name' => $item['nameSpace'], 'rowLevel' => 1, 'active' => $item['active']]);
                $nameSpaceTab[$item['nameSpace']] = $newId;
                $currentNameSpace = $newId;
            }
            if($item['name']) {
                $currentName = $nameTab[$item['nameSpace']][$item['name']];
                if($currentName === null){
                    $newId = $currentId++;
                    array_push($tree,['id' => $newId, 'parentId' => $currentNameSpace, 'name' => $item['name'],'description' => $item['description'], 'priority' => $item['priority'], 'method' => $item['method'], 'pattern' => $item['pattern'], 'overrided' => $item['override'], 'rowLevel' => 2, 'active' => $item['active']]);
                    $nameTab[$item['nameSpace']][$item['name']] = $newId;
                }
            } else {
                $item['parentId'] = $currentNameSpace;
                $tree[] = $item;
            }
        }
        return $tree;
    }
}
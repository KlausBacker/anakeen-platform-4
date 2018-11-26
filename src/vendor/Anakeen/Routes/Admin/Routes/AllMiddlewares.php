<?php

namespace Anakeen\Routes\Admin\Routes;

use Anakeen\Router\ApiV2Response;

class AllMiddlewares
{
    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @throws \Dcp\Core\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response)
    {
        $allMiddlewares = new \Anakeen\Router\RoutesConfig();
        $tabMiddlewares = $allMiddlewares->getMiddlewares();
        $result = [];
        foreach ($tabMiddlewares as $middleware) {
            $formatedMiddleware = $this->formatMiddleware($middleware);
            if ($formatedMiddleware !== null) {
                $result[] = $formatedMiddleware;
            }
        }
        return ApiV2Response::withData($response,$this->formatTreeDataSource($result));
    }

    /**
     * @param $route
     * @return array
     * @throws \Dcp\Core\Exception
     * Retrieve middlewares dataSource from RoutesConfig
     */
    private function formatMiddleware($middleware)
    {
        $formatedMiddleware = [];
        $nsName = explode('::', $middleware->name, 2);

        if(!empty($nsName[1])) {
            $formatedMiddleware['nameSpace'] = $nsName[0];
            $formatedMiddleware['name'] = $nsName[1];
        } else {
            $formatedMiddleware['name'] = $nsName[0];
        }
        $formatedMiddleware['description'] = $middleware->description;
        $formatedMiddleware['method'] = $middleware->methods[0];
        $formatedMiddleware['pattern'] = $middleware->pattern;
        $formatedMiddleware['priority'] = $middleware->priority;

        return $formatedMiddleware;
    }

    /**
     * @param $routes
     * @return array
     * reformat dataSource to correspond treeList content
     */
    private function formatTreeDataSource($middlewares) {
        $middleware = $middlewares;
        uasort($middleware, function ($a, $b)
        {
            if ($a['name'] && !$b['name']) {
                return -1;
            } elseif (!$a['name'] && $b['name']) {
                return 1;
            } else {
                return (strcmp($a['nameSpace'] ,$b['nameSpace'])) ? -1 : 1;
            }
        });
        $currentId = 1;
        $tree = [];
        $nameSpaceTab = [];
        $nameTab = [];

        foreach($middleware as $item){
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
                    array_push($tree,['id' => $newId, 'parentId' => $currentNameSpace, 'name' => $item['name'],'description' => $item['description'], 'priority' => $item['priority'], 'method' => $item['method'], 'pattern' => $item['pattern'], 'rowLevel' => 2]);
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
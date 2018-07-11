<?php

namespace Anakeen\Routes\Admin\Routes;

use Anakeen\Core\DbManager;
use Dcp\Db\Exception;

class AllMiddlewares
{
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
            return $response->withJson($this->formatTreeDataSource($result));
    }
}
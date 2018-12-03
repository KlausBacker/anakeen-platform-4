<?php


namespace Anakeen\Routes\Devel\Routes;

use Anakeen\Router\ApiV2Response;

class Middlewares
{
    protected $currentNameSpace = null;
    protected $currentName = null;
    protected $currentRoutePattern = null;
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $allMiddlewares = new \Anakeen\Router\RoutesConfig();
        $tabMiddlewares = $allMiddlewares->getMiddlewares();
        $result = [];
        if (isset($args['name'])) {
            $routeParser = new \FastRoute\RouteParser\Std();
            $allRoutes = new \Anakeen\Router\RouterManager();
            $tabRoutes = $allRoutes->getRoutes();
            foreach ($tabRoutes as $route) {
                if ($route->name === $args['name']) {
                    $this->currentRoutePattern = $route->pattern;
                }
            }
            foreach ($tabMiddlewares as $middleware) {
                $middlewareInfos = $routeParser->parse($middleware->pattern);
                $regExpMiddle = \Anakeen\Router\RouterLib::parseInfoToRegExp($middlewareInfos);
                foreach ($regExpMiddle as $regExp) {
                    if (preg_match_all($regExp, $this->currentRoutePattern) === 1) {
                        array_push($result, $this->formatMiddleware($middleware));
                    }
                }
            }
        } else {
            foreach ($tabMiddlewares as $middleware) {
                $formatedMiddleware = $this->formatMiddleware($middleware);
                if ($formatedMiddleware !== null) {
                    $result[] = $formatedMiddleware;
                }
            }
        }
        return ApiV2Response::withData($response, $this->formatTreeDataSource($result));
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

        if (!empty($nsName[1])) {
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

    private function formatTreeDataSource($routes)
    {
        $route = $routes;
        uasort($route, function ($a, $b) {
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

        foreach ($route as $item) {
            $item['id'] = $currentId++;
            if (isset($nameSpaceTab[$item['nameSpace']])) {
                $this->currentNameSpace = $nameSpaceTab[$item['nameSpace']];
            } else {
                $this->currentNameSpace = null;
            }
            if ($this->currentNameSpace === null && $item['nameSpace'] !== null) {
                $newId = $currentId++;
                array_push($tree, ['id' => $newId, 'parentId' => null, 'name' => $item['nameSpace'], 'rowLevel' => 1]);
                $nameSpaceTab[$item['nameSpace']] = $newId;
                $this->currentNameSpace = $newId;
            }
            if ($item['name']) {
                if (isset($nameTab[$item['name']])) {
                    $this->currentName = $nameTab[$item['nameSpace']][$item['name']];
                } else {
                    $this->currentName = null;
                }
                if ($this->currentName === null) {
                    $newId = $currentId++;
                    array_push($tree, ['id' => $newId, 'parentId' => $this->currentNameSpace, 'name' => $item['name'],
                        'description' => $item['description'], 'priority' => $item['priority'],
                        'method' => $item['method'], 'pattern' => $item['pattern'], 'rowLevel' => 2, 'parentName' => $item["nameSpace"]]);
                    $nameTab[$item['nameSpace']][$item['name']] = $newId;
                }
            } else {
                $item['parentId'] = $this->currentNameSpace;
                $tree[] = $item;
            }
        }
        return $tree;
    }
}

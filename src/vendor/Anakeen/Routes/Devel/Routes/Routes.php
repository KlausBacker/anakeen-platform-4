<?php

namespace Anakeen\Routes\Devel\Routes;

use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;

class Routes
{
    protected $currentNameSpace = null;
    protected $currentName = null;
    protected $regExpMiddleware = null;
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $allRoutes = new \Anakeen\Router\RouterManager();
        $tabRoutes = $allRoutes->getRoutes();
        $result = [];
        if (isset($args['name'])) {
            $routeParser = new \FastRoute\RouteParser\Std();
            $allMiddlewares = new \Anakeen\Router\RoutesConfig();
            $tabMiddlewares= $allMiddlewares->getMiddlewares();
            foreach ($tabMiddlewares as $middleware) {
                if ($middleware->name === $args['name']) {
                    $middlewareInfos = $routeParser->parse($middleware->pattern);
                    $this->regExpMiddleware = \Anakeen\Router\RouterLib::parseInfoToRegExp($middlewareInfos);
                }
            }
            foreach ($tabRoutes as $route) {
                if (is_array($route->pattern)) {
                    foreach ($route->pattern as $item) {
                        foreach ($this->regExpMiddleware as $regExp) {
                            if (preg_match_all($regExp, $item) === 1) {
                                array_push($result, $this->formatRoute($route));
                            }
                        }
                    }
                } else {
                    foreach ($this->regExpMiddleware as $regExp) {
                        if (preg_match_all($regExp, $route->pattern) === 1) {
                            array_push($result, $this->formatRoute($route));
                        }
                    }
                }
            }
        } else {
            foreach ($tabRoutes as $route) {
                $formatedRoute = $this->formatRoute($route);
                if ($formatedRoute !== null) {
                    $result[] = $formatedRoute;
                }
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
        $formatedRoute['pattern'] = is_array($route->pattern) ? implode(",", $route->pattern) : $route->pattern;
        $formatedRoute['priority'] = $route->priority;
        $formatedRoute['override'] = $route->override;
        $formatedRoute['active'] = $route->isActive();

        return $formatedRoute;
    }

    /**
     * @param $routes
     * @return array
     * reformat treeDataSource to correspond treeList content
     */
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
                        'method' => $item['method'], 'pattern' => $item['pattern'],
                        'override' => $item['override'], 'rowLevel' => 2, 'active' => $item['active'], 'parentName' => $item["nameSpace"]]);
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

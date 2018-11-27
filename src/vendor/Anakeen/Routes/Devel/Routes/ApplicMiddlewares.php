<?php


namespace Anakeen\Routes\Devel\Routes;

use Anakeen\Router\ApiV2Response;

class ApplicMiddlewares
{
    protected $currentNameSpace = null;
    protected $currentName = null;
    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     * @throws \Dcp\Core\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $routeParser = new \FastRoute\RouteParser\Std();
        $allRoutes = new \Anakeen\Router\RouterManager();
        $tabRoutes = $allRoutes->getRoutes();
        $allMiddlewares = new \Anakeen\Router\RouterManager();
        $tabMiddlewares = $allMiddlewares->getMiddlewares();
        $result = [];
        foreach ($tabRoutes as $route) {
            if (strcmp($route->name, $args['name']) === 0) {
                foreach ($tabMiddlewares as $middleware) {
                    $path = $this->getMiddlewarePath($routeParser->parse($middleware->pattern));
                    if (is_array($route->pattern)) {
                        foreach ($route->pattern as $item) {
                            if (preg_match_all('/\\' . $path . '/', $item) === 1) {
                                array_push($result, $this->formatRoute($middleware, $item));
                            }
                        }
                    } else {
                        if (preg_match_all('/\\' . $path . '/', $route->pattern) === 1) {
                            array_push($result, $this->formatRoute($middleware, $route->pattern));
                        }
                    }
                }
            }
        }
        return ApiV2Response::withData($response, $this->formatTreeDataSource($result));
    }

    /**
     * @param $middleware
     * @return string
     * Retrieve regex from middleware pattern
     */
    private function getMiddlewarePath($middleware)
    {
        $path = '';
        $index = 0;
        foreach ($middleware[1] as $item) {
            if (is_string($item)) {
                $path .= $item[$index++];
                continue;
            }
            $path .= $item[$index++];
        }
        return $path;
    }

    /**
     * @param $route
     * @return array
     * @throws \Dcp\Core\Exception
     * Retrieve dataSource from RoutesConfig
     */
    private function formatRoute(\Anakeen\Router\Config\RouterInfo $route, $pattern)
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

        return $formatedRoute;
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
                        'method' => $item['method'], 'pattern' => $item['pattern'], 'rowLevel' => 2]);
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
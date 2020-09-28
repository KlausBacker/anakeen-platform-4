<?php


namespace Anakeen\Routes\Devel\Security;

use Anakeen\Router\ApiV2Response;
use Anakeen\Routes\Devel\GridFiltering;
use Anakeen\Ui\DataSource;

class Routes extends GridFiltering
{
    protected $sWhere;
    const PAGESIZE = 50;
    protected $filters = [];
    protected $slice = self::PAGESIZE;
    protected $offset = 0;
    protected $filtered = [];

    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @return \Slim\Http\response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        $allRoutes = new \Anakeen\Router\RouterManager();
        $tabRoutes = $allRoutes->getRoutes();
        $result = [];
        foreach ($tabRoutes as $route) {
            $formatedRoute = $this->formatRoute($route);
            if ($formatedRoute !== null) {
                $result[] = $formatedRoute;
            }
        }
        if ($this->filters) {
            $this->filtered = [];
            $this->filtered = $this->recursiveFilter($result, $this->filters[0]);
            foreach ($this->filters as $filter) {
                if ($filter !== $this->filters[0]) {
                    $this->filtered = $this->recursiveFilter($this->filtered, $filter);
                }
            }
            $data["requestParameters"] = $this->getRoutesRequestParameters($this->filtered);
            $data["routes"] = $this->filtered;
        } else {
            $data["requestParameters"] = $this->getRoutesRequestParameters($result);
            $data["routes"] = $result;
        }
        $data["routes"] = array_slice($data["routes"], $this->offset, $this->slice);
        return ApiV2Response::withData($response, $data);
    }

    private function recursiveFilter($result, $filter)
    {
        $filtered = [];
        foreach ($result as $r) {
            if (is_string($r[$filter["field"]])) {
                if (strcmp($r[$filter["field"]], "") === 0) {
                    $value = " ";
                } else {
                    $value = $r[$filter["field"]];
                }
            } else {
                $value = $r[$filter["field"]];
            }
            if (is_array(json_decode(json_encode($value), true))) {
                foreach ($value as $item) {
                    if (!is_array(json_decode(json_encode($item), true))) {
                        $item = explode("::", $item[0])[1];
                        if (stripos($item, $filter["value"]) !== false) {
                            $filtered[] = $r;
                        }
                    } else {
                        foreach ($item as $leaf) {
                            $leaf = explode("::", $leaf)[1];
                            if (stripos($leaf, $filter["value"]) !== false) {
                                $filtered[] = $r;
                            }
                        }
                    }
                }
            } else {
                if (stripos($value, $filter["value"]) !== false) {
                    $filtered[] = $r;
                }
            }
        }
        return $filtered;
    }
    /**
     * @param $route
     * @return array
     * @throws \Anakeen\Core\Exception
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

    protected function getRoutesRequestParameters($tab)
    {
        $requestData["take"] = $this->slice;
        $requestData["skip"] = $this->offset;
        $requestData["total"] = count($tab);
        return $requestData;
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $filters = $request->getQueryParam("filter");
        if ($filters) {
            $this->filters = DataSource::getFlatLevelFilters($filters);
        }
        if ($request->getQueryParam("take") === 'all') {
            $this->slice = $request->getQueryParam("take");
        } else {
            $this->slice = intval($request->getQueryParam("take", self::PAGESIZE));
        }
        $this->offset = intval($request->getQueryParam("skip", 0));
    }
}

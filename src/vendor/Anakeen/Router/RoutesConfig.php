<?php

namespace Anakeen\Router;

use \Anakeen\Core\ContextManager;
use \Anakeen\Router\Config\RouterInfo;

/**
 * Class RoutesConfig
 * Get configured routes from File Cache
 *
 * @package Anakeen\Router
 */
class RoutesConfig
{
    const CACHEFILE = "routesConfig.cache";
    /**
     * @var RouterInfo[]
     */
    protected $routes;
    /**
     * @var RouterInfo[]
     */
    protected $middlewares;
    protected $data;
    protected $fileCache;


    public function __construct()
    {
        $this->fileCache = sprintf(
            "%s/%s/%s",
            ContextManager::getRootDirectory(),
            \Anakeen\Core\Settings::RouterConfigDir,
            self::CACHEFILE
        );
    }

    protected function loadData()
    {
        if (!$this->data) {
            if (is_file($this->fileCache)) {
                $this->data = unserialize(file_get_contents($this->fileCache));
            }
            if ( ! $this->data) {
                throw new Exception("ROUTES0003");
            }
        }
    }

    public function resetCache()
    {
        $routeConfig = \Anakeen\Router\RouterLib::getRouterConfig();
        $data["routes"] = $routeConfig->getRoutes();
        $data["middlewares"] = $routeConfig->getMiddlewares();

        file_put_contents($this->fileCache, serialize($data));
    }

    /**
     * @return RouterInfo[]
     */
    public function getRoutes()
    {
        $this->loadData();
        return $this->data["routes"];
    }

    /**
     * @return RouterInfo[]
     */
    public function getMiddlewares()
    {
        $this->loadData();
        return $this->data["middlewares"];
    }
}

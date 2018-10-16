<?php

namespace Anakeen\Routes\Devel\Config;

use Anakeen\Core\SEManager;
use Anakeen\Router\Exception;

/**
 * Class Structure
 * Get configuration of smart structure object
 * use by route GET /api/v2/devel/config/structures/{structure}.xml
 * use by route GET /api/v2/devel/config/uis/{structure}.xml
 * use by route GET /api/v2/devel/config/accesses/{structure}.xml
 */
class Structure
{
    protected $structure;
    protected $structureId = 0;
    protected $type = "structures";

    /**
     * Return right accesses for a profil element
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     *
     * @return \Slim\Http\response $response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        $response = $response->withAddedHeader("Content-type", "text/xml");
        $response = $response->write($this->doRequest());
        return $response;
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->structureId = $args["structure"];
        $this->structure = SEManager::getFamily($this->structureId);
        if (!$this->structure) {
            throw new Exception(sprintf("Structure \"%s\" not found", $this->structureId));
        }
        $this->type = $args["type"];
    }

    public function doRequest()
    {
        switch ($this->type) {
            case "uis":
                $e = new \Anakeen\Ui\ExportRenderConfiguration($this->structure);
                break;
            case "accesses":
                $e = new \Anakeen\Core\SmartStructure\ExportConfigurationAccesses($this->structure);
                break;
            case "structures":
            default:
                $e = new \Anakeen\Core\SmartStructure\ExportConfiguration($this->structure);
                break;
        }

        return $e->toXml();
    }
}

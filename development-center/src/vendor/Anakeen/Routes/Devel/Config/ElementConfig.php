<?php

namespace Anakeen\Routes\Devel\Config;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Router\Exception;
use Anakeen\Workflow\ExportElementConfiguration;

class ElementConfig
{
    /** @var  SmartElement */
    protected $element;
    protected $elementId;

    /**
     * Return xml config for system elements
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
        $this->elementId = $args["id"];
        $this->element = SEManager::getDocument($this->elementId);
        if (!$this->element) {
            throw new Exception(sprintf("Element \"%s\" not found", $this->elementId));
        }
    }


    public function doRequest()
    {
        switch ($this->element->fromname) {
            case "TIMER":
                return ExportElementConfiguration::getTimerConfig($this->element->id);
                break;
            case "MAILTEMPLATE":
                return ExportElementConfiguration::getMailTemplateConfig($this->element->id);
                break;
            case "CVDOC":
                return ExportElementConfiguration::getCvdocConfig($this->element->id);
                break;
            case "MASK":
                return ExportElementConfiguration::getMaskConfig($this->element->id);
                break;
            case "FIELDACCESSLAYERLIST":
                return ExportElementConfiguration::getFieldAccessConfig($this->element->id);
                break;
            case "PDIR":
            case "PSEARCH":
            case "PFAM":
            case "PDOC":
                return ExportElementConfiguration::getProfileConfig($this->element->id);
                break;
        }

        throw new Exception(sprintf("Configuration of structure \"%s\" not supported", $this->element->fromname));
    }
}

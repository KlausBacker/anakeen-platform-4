<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Core\ContextManager;
use Anakeen\Core\ExportCollection;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\Search\Internal\SearchSmartData;
use Anakeen\SmartElementManager;
use SmartStructure\Fields\Hubconfiguration as Fields;

/**
 *
 * @note used by route /hub/config/{hubId}.xml
 */
class ExportConfiguration
{
    const PREIMPORT = "preImport";
    protected $structureName = "";
    /**
     * @var $hubInstance \SmartStructure\Hubinstanciation
     */
    protected $hubInstance;
    protected $hasSuperRole;
    protected $tmpDir;
    /**
     * @var array
     */
    protected $extraIds = [];

    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->structureName = $args["hubId"];

        $this->hubInstance = SmartElementManager::getDocument($this->structureName);

        $hubFile = $this->exportHubElement();
        $outputFile = $this->exportMain();
        if ($this->extraIds) {
            $this->appendXmlFile($outputFile, $this->exportExtraElement());
        }

        $this->appendXmlFile($outputFile, $hubFile);


        $this->prettyXml($outputFile, $this->hubInstance->getTitle());
        $response = ApiV2Response::withFile($response, $outputFile, sprintf("%s.xml", $this->hubInstance->getTitle()), false);
        unlink($outputFile);
        return $response;
    }

    protected function appendXmlFile($file, $fileToAppend)
    {
        $dom1 = new \DOMDocument();
        $dom1->formatOutput = true;
        $dom1->preserveWhiteSpace = false;
        $dom1->load($file);

        $root = $dom1->documentElement;

        $dom2 = new \DOMDocument();
        $dom2->load($fileToAppend);


        $root2 = $dom2->documentElement;

        foreach ($root2->childNodes as $childs) {
            if (get_class($childs) === \DOMElement::class) {
                $node = $dom1->importNode($childs, true);
                $root->appendChild($node);
            }
        }

        $dom1->save($file);
    }

    protected function exportMain()
    {
        $outFile = $this->getTmpDir() . "hubConfig";

        $dl = new \DocumentList();
        $dl->addDocumentIdentifiers([$this->hubInstance->initid]);

        $ec = new ExportCollection();

        $ec->setOutputFormat(ExportCollection::xmlFileOutputFormat);
        $ec->setDocumentlist($dl);
        $ec->setExportFiles(true);

        $ec->setOutputFilePath($outFile);
        $ec->export();

        return $outFile;
    }


    protected function exportExtraElement()
    {
        $outFile = $this->getTmpDir() . "hubExtra";

        $search = new SearchSmartData("", "HUBCONFIGURATION");
        $search->overrideViewControl();
        $search->addFilter("%s = '%s'", Fields::hub_station_id, $this->hubInstance->initid);
        $search->setOrder(Fields::hub_docker_position . ',' . Fields::hub_order);
        $search->setObjectReturn(true);
        $search->search();

        $this->extraIds = array_unique($this->extraIds);

        $dl = new \DocumentList();
        $dl->addDocumentIdentifiers($this->extraIds);

        foreach ($dl as $element) {
            if (!$element->name) {
                $err = $element->setLogicalName(sprintf("HUBEXT_%04d", $element->initid));
                if ($err) {
                    throw new Exception($err);
                }
            }
        }


        // Need reset in case of logical name update
        $dl = new \DocumentList();
        $dl->addDocumentIdentifiers($this->extraIds);

        $ec = new ExportCollection();
        $ec->setOutputFormat(ExportCollection::xmlFileOutputFormat);
        $ec->setDocumentlist($dl);
        $ec->setExportFiles(true);
        $ec->setOutputFilePath($outFile);
        $ec->export();


        return $outFile;
    }

    protected function exportHubElement()
    {
        $outFile = $this->getTmpDir() . "hubElements";

        $search = new SearchSmartData("", "HUBCONFIGURATION");
        $search->overrideViewControl();
        $search->addFilter("%s = '%s'", Fields::hub_station_id, $this->hubInstance->initid);
        $search->setOrder(Fields::hub_docker_position . ',' . Fields::hub_order);
        $search->setObjectReturn(true);
        $search->search();

        $dl = $search->getDocumentList();
        foreach ($dl as $element) {
            if (!$element->name) {
                $err = $element->setLogicalName(sprintf("HUBELT_%04d", $element->initid));
                if ($err) {
                    throw new Exception($err);
                }
            }

            $this->recordExtraElements($element);
        }

        // Need reset in case of logical name update
        $search->reset();

        $ec = new ExportCollection();
        $ec->setOutputFormat(ExportCollection::xmlFileOutputFormat);
        $ec->setDocumentlist($search->search()->getDocumentList());
        $ec->setExportFiles(true);
        $ec->setOutputFilePath($outFile);
        $ec->export();


        return $outFile;
    }


    protected function recordExtraElements(SmartElement $element)
    {
        $dataIds = [];
        $element->getHooks()->trigger(self::PREIMPORT, $dataIds);
        $this->extraIds = array_merge($this->extraIds, $dataIds);
    }

    protected function prettyXml($file, $title)
    {
        $dom = new \DOMDocument("1.0", "UTF-8");
        $dom->formatOutput = true;
        $dom->preserveWhiteSpace = false;
        $dom->load($file);

        $dom->documentElement->setAttribute("name", $title);

        $dom->save($file);
    }

    protected function getTmpDir()
    {
        if (!$this->tmpDir) {
            $this->tmpDir = tempnam(ContextManager::getTmpDir(), "hub");
            if (file_exists($this->tmpDir)) {
                unlink($this->tmpDir);
            }
            mkdir($this->tmpDir);
            $this->tmpDir .= "/";
        }
        return $this->tmpDir;
    }
}

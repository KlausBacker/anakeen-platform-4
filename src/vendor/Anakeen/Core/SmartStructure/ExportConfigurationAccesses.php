<?php

namespace Anakeen\Core\SmartStructure;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use Anakeen\Workflow\ExportElementConfiguration;

/**
 * Class ExportConfiguration
 * @package Anakeen\Core\SmartStructure
 *
 * Export Smart Structure in Xml
 */
class ExportConfigurationAccesses extends ExportConfiguration
{
    protected $extractedAccessProfile = [];

    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * ExportConfiguration constructor.
     * @param SmartStructure $sst Smart Structure to export
     */
    public function __construct(SmartStructure $sst)
    {
        $this->sst = $sst;
        $this->dom = new \DOMDocument("1.0", "UTF-8");
        $this->dom->formatOutput = true;
        $this->domConfig = $this->cel("config");
        $this->dom->appendChild($this->domConfig);

        $this->initStructureConfig();
    }

    public function extract()
    {
        $this->extractProfil();

        $this->domConfig->appendChild($this->structConfig);
    }


    public function extractProfil($part = "all")
    {
        $structConfig=$this->structConfig;
        $access = $this->cel("accesses");

        if ($this->sst->profid) {
            $tag = $this->cel("structure-access-configuration");
            $access->appendChild($tag);
            $accessControl = $this->setAccess($this->sst->profid);

            if ($part === "all" || $part === "ref") {
                $tag->setAttribute("ref", static::getLogicalName($this->sst->profid));
            }
            if ($this->sst->profid !== $this->sst->id) {
                if ($part === "all" || $part === "access") {
                    $this->domConfig->appendChild($accessControl);
                }
            } else {
                if ($part === "all" || $part === "access") {
                    $tag->appendChild($accessControl);
                }
            }
        }
        if ($this->sst->cprofid) {
            if ($part === "all"|| $part === "ref") {
                $tag = $this->cel("element-access-configuration");
                $tag->setAttribute("ref", static::getLogicalName($this->sst->cprofid));
                $access->appendChild($tag);
            }
            if ($part === "all" || $part === "access") {
                $accessControl = $this->setAccess($this->sst->cprofid);
                $this->domConfig->appendChild($accessControl);
            }
        }

        if ($this->sst->cfallid) {
            if ($part === "all" || $part === "ref") {
                $tag = $this->cel("field-access-configuration");
                $tag->setAttribute("ref", static::getLogicalName($this->sst->cfallid) ?: $this->sst->cfallid);
                $access->appendChild($tag);
            }
            if ($part === "all" || $part === "access") {
                $this->setFieldAccessProfile($this->sst->cfallid);
                $this->setFieldAccess($this->sst->cfallid);
                $accessControl = $this->setAccess($this->sst->cfallid);
                $this->domConfig->appendChild($accessControl);
            }
        }

        $structConfig->appendChild($access);
    }


    protected function setFieldAccess($fallid)
    {
        $tag = $this->cel("field-access-layer-list");
        $fall = SEManager::getDocument($fallid);
        if (! $fall) {
            $tag->setAttribute("name", "UNKNOW#".$fall->name);
            return;
        }
        SEManager::cache()->addDocument($fall);

        $tag->setAttribute("name", $fall->name);
        $tag->setAttribute("label", $fall->title);
        $tag->setAttribute("structure", static::getLogicalName($fall->getRawValue(\SmartStructure\Fields\Fieldaccesslayerlist::fall_famid)));


        $layers = $fall->getMultipleRawValues(\SmartStructure\Fields\Fieldaccesslayerlist::fall_layer);
        $aclNames = $fall->getMultipleRawValues(\SmartStructure\Fields\Fieldaccesslayerlist::fall_aclname);
        foreach ($layers as $kl => $layer) {
            $fal = $this->cel("field-access-layer");
            $fal->setAttribute("ref", static::getLogicalName($layer));
            $fal->setAttribute("access-name", $aclNames[$kl]);
            $tag->appendChild($fal);
        }

        $this->domConfig->appendChild($tag);
    }

    protected function setFieldAccessProfile($fallid)
    {
        $fall = SEManager::getDocument($fallid);
        if (! $fall) {
            $this->setComment(sprintf("Field Access %s not found", $fallid));
            return;
        }
        $layers = $fall->getMultipleRawValues(\SmartStructure\Fields\Fieldaccesslayerlist::fall_layer);
        $aclNames = $fall->getMultipleRawValues(\SmartStructure\Fields\Fieldaccesslayerlist::fall_aclname);
        foreach ($layers as $kl => $layer) {
            $tag = $this->cel("field-access-layer");
            $eLayer = SEManager::getDocument($layer);
            SEManager::cache()->addDocument($eLayer);
            $tag->setAttribute("name", $eLayer->name);
            $tag->setAttribute("label", $eLayer->getTitle());
            $tag->setAttribute("access-name", $aclNames[$kl]);
            $tag->setAttribute("structure", static::getLogicalName($eLayer->getRawValue(\SmartStructure\Fields\Fieldaccesslayer::fal_famid)));

            $fieldIds = $eLayer->getMultipleRawValues(\SmartStructure\Fields\Fieldaccesslayer::fal_fieldid);
            $fieldAccesses = $eLayer->getMultipleRawValues(\SmartStructure\Fields\Fieldaccesslayer::fal_fieldaccess);
            foreach ($fieldIds as $k => $accessField) {
                $atag = $this->cel("field-access");
                $atag->setAttribute("field", $accessField);
                $atag->setAttribute("access", $fieldAccesses[$k]);
                $tag->appendChild($atag);
            }
            $this->domConfig->appendChild($tag);
        }
        foreach ($layers as $kl => $layer) {
            $eLayer = SEManager::getDocument($layer);
            $this->setAccessProfile($eLayer);
        }
    }

    protected function setAccessProfile(SmartElement $e)
    {
        if (isset($this->extractedAccessProfile[$e->id])) {
            return null;
        }

        $this->extractedAccessProfile[$e->id] = true;

        $accessNode=ExportElementConfiguration::getAccessProfile($e->id, $this->dom);
        $this->domConfig->appendChild($accessNode);
    }


    protected function setAccess(string $profid, $returns = "all")
    {
        return ExportElementConfiguration::getAccess($profid, $returns, $this->dom);
    }
}

<?php

namespace Anakeen\Core\SmartStructure;

use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\DocumentAccess;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;

/**
 * Class ExportConfiguration
 * @package Anakeen\Core\SmartStructure
 *
 * Export Smart Structure in Xml
 */
class ExportConfigurationAccesses extends ExportConfiguration
{
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

        $structConfig = $this->cel("structure-configuration");
        $structConfig->setAttribute("name", $this->sst->name);
        if ($this->sst->id < 1000) {
            $structConfig->setAttribute("id", $this->sst->id);
        }

        $this->extract($structConfig);
    }

    protected function extract($structConfig)
    {

        $this->extractProfil($structConfig);

        $this->domConfig->appendChild($structConfig);
    }


    protected function extractProfil(\DOMElement $structConfig)
    {
        $access = $this->cel("accesses");
        if ($this->sst->cprofid) {
            $tag = $this->cel("element-access-configuration");
            $tag->setAttribute("ref", SEManager::getNameFromId($this->sst->cprofid));
            $access->appendChild($tag);
            $accessControl = $this->setAccess($this->sst->cprofid);
            $this->domConfig->appendChild($accessControl);
        }

        if ($this->sst->profid) {
            $tag = $this->cel("structure-access-configuration");
            $access->appendChild($tag);
            $accessControl = $this->setAccess($this->sst->profid);
            if ($this->sst->profid !== $this->sst->id) {
                $tag->setAttribute("ref", SEManager::getNameFromId($this->sst->profid));
                $this->domConfig->appendChild($accessControl);
            } else {
                $tag->appendChild($accessControl);
            }
        }

        if ($this->sst->cfallid) {
            $tag = $this->cel("field-access-configuration");
            $tag->setAttribute("ref", static::getLogicalName($this->sst->cfallid) ?: $this->sst->cfallid);
            $access->appendChild($tag);
            $this->setFieldAccessProfile($tag, $this->sst->cfallid);
            $this->setFieldAccess($this->sst->cfallid);
            $accessControl = $this->setAccess($this->sst->cfallid);
            $this->domConfig->appendChild($accessControl);

        }

        $structConfig->appendChild($access);
    }


    protected function setFieldAccess($fallid)
    {
        $tag = $this->cel("field-access-layer-list");
        $fall = SEManager::getDocument($fallid);
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

    protected function setFieldAccessProfile(\DOMElement $domNode, $fallid)
    {
        $fall = SEManager::getDocument($fallid);
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
        if ($e->accessControl()->isRealProfile()) {
            $accessControl = $this->setAccess($e->id);
            $this->domConfig->appendChild($accessControl);
        } else {
            $accessControl = $this->setAccessRef($e);
            $this->domConfig->appendChild($accessControl);
        }
    }

    protected function setAccessRef(SmartElement $e)
    {
        $accessControl = $this->cel("access-configuration");
        $accessControl->setAttribute("name", $e->name ?: $e->id);
        $accessControl->setAttribute("ref", static::getLogicalName($e->dprofid ?: $e->profid));

        return $accessControl;
    }

    protected function setAccess($profid)
    {
        $accessControl = $this->cel("access-configuration");
        $profil = SEManager::getDocument($profid);

        $accessControl->setAttribute("name", $profil->name ?: $profil->id);
        $accessControl->setAttribute("label", $profil->title);
        $accessControl->setAttribute("profil-type", $profil->fromname);
        if ($profil->getRawValue("dpdoc_famid")) {
            $accessControl->setAttribute("access-structure", static::getLogicalName($profil->getRawValue("dpdoc_famid")));
        }
        if ($profil->getRawValue("ba_desc")) {
            if ($profil->accessControl()->isRealProfile()) {
                $desc = $this->cel("description");
                $desc->appendChild($this->dom->createCDATASection($profil->getRawValue("ba_desc")));
                $accessControl->appendChild($desc);
            }
        }
        $sql = sprintf(
            "select users.login, docperm.upacl from docperm,users where docperm.docid=%d and users.id=docperm.userid and docperm.upacl != 0 order by users.login",
            $profil->id
        );
        DbManager::query($sql, $resultsAccount);
        $sql = sprintf(
            "select vgroup.id as attrid, docperm.upacl from docperm,vgroup where docperm.docid=%d and vgroup.num=docperm.userid and docperm.upacl != 0 order by vgroup.id",
            $profil->id
        );
        DbManager::query($sql, $resultsRelation);

        $sql = sprintf(
            "select users.login, docpermext.acl from docpermext,users where docpermext.docid=%d and users.id=docpermext.userid order by users.login",
            $profil->id
        );
        DbManager::query($sql, $resultsExtAccount);

        $sql = sprintf(
            "select vgroup.id as attrid, docpermext.acl from docpermext,vgroup where docpermext.docid=%d and vgroup.num=docpermext.userid order by vgroup.id",
            $profil->id
        );
        DbManager::query($sql, $resultsExtRelation);

        $results = array_merge($resultsAccount, $resultsRelation);

        /**
         * @var \DOMElement[] $elementAccesses
         */
        $elementAccesses = [];
        $accessResults = [];
        // Add special acls - Always defined in each profil
        $profil->acls[] = "modifyacl";
        $profil->acls[] = "viewacl";

        foreach ($profil->acls as $acl) {
            if (isset(DocumentAccess::$dacls[$acl])) {
                $pos = DocumentAccess::$dacls[$acl]["pos"];
                foreach ($results as $result) {
                    if (\DocPerm::controlMask($result["upacl"], $pos)) {
                        $accessResult = [
                            "acl" => $acl
                        ];

                        $elementAccount = null;
                        if (isset($result["login"])) {
                            $accessResult["login"] = $result["login"];
                        }
                        if (isset($result["attrid"])) {
                            $accessResult["attrid"] = $result["attrid"];
                        }

                        $accessResults[] = $accessResult;
                    }
                }
            }
        }

        $extended = array_merge($resultsExtAccount, $resultsExtRelation);
        foreach ($extended as $result) {
            $accessResult = [
                "acl" => $result["acl"]
            ];
            if (isset($result["login"])) {
                $accessResult["login"] = $result["login"];
            }
            if (isset($result["attrid"])) {
                $accessResult["attrid"] = $result["attrid"];
            }
            $accessResults[] = $accessResult;
        }
        foreach ($accessResults as $result) {
            $acl = $result["acl"];
            if (!isset($elementAccesses[$acl])) {
                $elementAccesses[$acl] = $this->cel("element-access");
                $elementAccesses[$acl]->setAttribute("access", $acl);
            }
            if (isset($result["login"])) {
                $elementAccesses[$acl]->setAttribute("account", $result["login"]);
            }
            if (isset($result["attrid"])) {
                $elementAccesses[$acl]->setAttribute("field", $result["attrid"]);
            }
        }


        foreach ($elementAccesses as $elementAccess) {
            $accessControl->appendChild($elementAccess);
        }
        return $accessControl;
    }


}

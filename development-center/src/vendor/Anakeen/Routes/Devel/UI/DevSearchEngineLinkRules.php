<?php


namespace Anakeen\Routes\Devel\UI;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;

class DevSearchEngineLinkRules
{

    private $ruleMap;

    public function __construct()
    {
        $this->ruleMap = self::buildRuleMap();
    }

    private function buildRuleMap()
    {
        $map = array();
        $map["SmartElement"] = self::buildSmartElementRules();
        $map["SmartStructure"] = self::buildSmartStructureRules();
        $map["Profile"] = self::buildProfileRules();
        $map["W"] = self::buildWorkflowRules();
        $map["FIELDACCESSLAYERLIST"] = self::buildFieldAccesLayerListRules();
        $map["HUBINSTANCIATION"] = self::buildHubInstanciationRules();
        return $map;
    }

    private function buildSmartElementRules()
    {
        $seRules = array();
        $seRules["Display"] = ["/devel/smartElements/%s/view?initid=%s", "id", "initid"];
        $seRules["Security"] = ["/devel/smartElements/%s/security?profilid=%s", "name", "profid"];
        $seRules["Properties"] = ["/devel/smartElements/%s/properties", "id"];
        return $seRules;
    }

    private function buildSmartStructureRules()
    {
        $ssRules = array();
        $ssRules["Display"] = ["/devel/smartStructures/%s/infos", "name"];
        $ssRules["Security"] = ["/devel/security/smartStructures/%s/infos", "name"];
        $ssRules["Smart Elements"] = ["/devel/smartElements/?fromid=%s", "name"];
        $ssRules["User Interfaces"] = ["/devel/ui/%s/infos", "name"];
        $ssRules["Enumerates"] = ["/devel/enums/?name=%s", "name"];

        return $ssRules;
    }
    private function buildProfileRules()
    {
        $profilerules = array();
        $profilerules["Profiles"] = ["/devel/security/profiles/%s?name=%s", "id", "name"];
        return $profilerules;
    }

    private function buildWorkflowRules()
    {
        $workflowRules = array();
        $workflowRules["Workflow"] = ["/devel/wfl/%s/infos", "name"];
        return $workflowRules;
    }

    private function buildFieldAccesLayerListRules()
    {
        $cvdocRules = array();
        $cvdocRules["Rights"] = ["/devel/security/fieldAccess/%s/rights?name=%s", "id", "name"];
        $cvdocRules["Config"] = ["/devel/security/fieldAccess/%s/config?name=%s", "id", "name"];
        return $cvdocRules;
    }

    private function buildHubInstanciationRules()
    {
        $hubInstanciationRules = array();
        $hubInstanciationRules["Hub"] = ["/devel/hub/"];
        return $hubInstanciationRules;
    }

    public function getLinks(SmartElement $se)
    {
        $docType = $se->getPropertyValue("doctype");
        if ($docType != "C") { //If not a smart structure
            $fromName = $se->fromname;
            $profileLinks = ($this->isInProfileSection($se)) ? $this->getLinkResults("Profile", $se) : array();
            $cvdocLinks = $this->buildCvdocLinks($se);
            $links = array_merge(
                $this->getLinkResults("SmartElement", $se),
                $this->getLinkResults($docType, $se),
                $profileLinks,
                $cvdocLinks,
                $this->getLinkResults($fromName, $se)
            );
        } else {
            $links = $this->getLinkResults("SmartStructure", $se);
        }

        return $links;
    }

    private function getLinkResults($key, SmartElement $se)
    {
        $linkResults = array();
        if (array_key_exists($key, $this->ruleMap)) {
            $ruleArray = $this->ruleMap[$key];
            if (!empty($ruleArray)) {
                foreach ($ruleArray as $label => $linkParam) {
                    $baseLink = $linkParam[0];
                    $propertyNames = array_slice($linkParam, 1);
                    $propertyValues = array();
                    foreach ($propertyNames as $propertyName) {
                        array_push($propertyValues, $se->getPropertyValue($propertyName));
                    }
                    $entry["label"] = $label;
                    $entry["link"] = vsprintf($baseLink, $propertyValues);
                    array_push($linkResults, $entry);
                }
            }
        }
        return $linkResults;
    }

    private function isInProfileSection(SmartElement $se)
    {
        $isProfile = false;
        $pDocId = SEManager::getFamilyIdFromName("PDOC");
        $structure = $se->getFamilyDocument();
        if (isset($structure)) {
            if ($structure->initid == $pDocId || $structure->fromid == $pDocId) {
                $isProfile = true;
            }
        }
        return $isProfile;
    }

    private function buildCvdocLinks(SmartElement $se)
    {
        $cvdocLinks = array();
        if ($se->fromname == "CVDOC") {
            $cvfamid = $se->getAttributeValue("cv_famid");
            if (isset($cvfamid)) {
                $ss = SEManager::getFamily($cvfamid);
                if (isset($ss)) {
                    $ssname = $ss->name;
                    $control["label"] = "Control";
                    $control["link"] = sprintf("/devel/ui/%s/control/element/%s?name=%s", $ssname, $se->id, $se->name);
                    $permissions["label"] = "Permissions";
                    $permissions["link"] = sprintf("devel/ui/%s/control/permissions/%s?name=%s", $ssname, $se->id, $se->name);
                    array_push($cvdocLinks, $control, $permissions);
                }
            }
        }
        return $cvdocLinks;
    }
}

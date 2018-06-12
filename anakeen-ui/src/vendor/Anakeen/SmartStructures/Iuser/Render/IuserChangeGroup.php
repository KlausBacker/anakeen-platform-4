<?php

namespace Anakeen\SmartStructures\Iuser\Render;

use Anakeen\Core\ContextManager;
use Anakeen\SmartElementManager;
use Anakeen\Ui\DefaultConfigEditRender;
use \SmartStructure\Attributes\Iuser as myAttributes;
use SmartStructure\Iuser;

class IuserChangeGroup extends DefaultConfigEditRender
{
    public function getTemplates(\Anakeen\Core\Internal\SmartElement $smartElement = null)
    {
        $templates = parent::getTemplates($smartElement);
        $templates["sections"]["content"] = array(
            "file" => __DIR__.'/groupModify.mustache'
        );
        return $templates;
    }

    public function getCustomServerData(\Anakeen\Core\Internal\SmartElement $smartElement)
    {
        $data = parent::getCustomServerData($smartElement);
        $data["FAMILY"] = "IUSER";
        $data["groups"] = $this->getAllMyGroups($smartElement);
        return $data;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $smartElement = null)
    {
        $js = parent::getJsReferences();
        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");

        $js["dduiGroup"] = '/uiAssets/Families/iuser/prod/changeGroup.js?ws='.$version;
        if (\Dcp\Ui\UIGetAssetPath::isInDebug()) {
            $js["dduiGroup"] = '/uiAssets/Families/iuser/debug/changeGroup.js?ws='.$version;
        }

        return $js;
    }

    protected function getAllMyGroups(\Anakeen\Core\Internal\SmartElement $smartElement) {
        /* @var $smartElement Iuser */
        $account = $smartElement->getAccount();
        return array_reduce($account->getGroupsId(), function($accumulator, $currentValue) {
            $accumulator[$currentValue] = [
                "accountId" =>$currentValue
            ];
            return $accumulator;
        }, []);
    }

    public function setCustomClientData(\Anakeen\Core\Internal\SmartElement $smartElement, $data)
    {
        $data = \Dcp\Ui\Utils::getCustomClientData();
        parent::setCustomClientData($smartElement, $data);
        if (isset($data["parentGroups"])) {
            $newGroups = array_keys($data["parentGroups"]);
            /* @var $smartElement Iuser */
            $account = $smartElement->getAccount();
            $oldGroups = $account->getGroupsId();
            $groupsToAdd = array_diff($newGroups, $oldGroups);
            $groupsToDelete = array_diff($oldGroups, $newGroups);
            $currentUserSEId = $smartElement->getPropertyValue("initid");
            array_walk($groupsToDelete, function($currentGroupId) use ($currentUserSEId) {
                $group = new \Anakeen\Core\Account("", $currentGroupId);
                $groupSE = SmartElementManager::getDocument($group->fid);
                /* @var $groupSE \SmartStructure\Igroup */
                $groupSE->removeDocument($currentUserSEId);
            });
            array_walk($groupsToAdd, function($currentGroupId) use ($currentUserSEId) {
                $group = new \Anakeen\Core\Account("", $currentGroupId);
                $groupSE = SmartElementManager::getDocument($group->fid);
                /* @var $groupSE \SmartStructure\Igroup */
                $groupSE->insertDocument($currentUserSEId);
            });
        }
    }

}
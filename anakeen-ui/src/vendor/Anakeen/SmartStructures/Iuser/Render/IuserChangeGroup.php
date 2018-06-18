<?php

namespace Anakeen\SmartStructures\Iuser\Render;

use Anakeen\Routes\Core\Lib\ApiMessage;
use Anakeen\SmartElementManager;
use Anakeen\Ui\DefaultConfigEditRender;
use Dcp\Ui\UIGetAssetPath;
//use \SmartStructure\Attributes\Iuser as myAttributes;
use SmartStructure\Iuser;

class IuserChangeGroup extends DefaultConfigEditRender
{
    public function getTemplates(\Anakeen\Core\Internal\SmartElement $smartElement = null)
    {
        $templates = parent::getTemplates($smartElement);
        $templates["sections"]["content"] = array(
            "file" => __DIR__ . '/groupModify.mustache'
        );
        return $templates;
    }

    public function getCustomServerData(\Anakeen\Core\Internal\SmartElement $smartElement)
    {
        $data = parent::getCustomServerData($smartElement);
        $data["GROUP_ANALYZE"] = true;
        $data["groups"] = $this->getAllMyGroups($smartElement);
        return $data;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $smartElement = null)
    {
        $js = parent::getJsReferences();

        $js["dduiGroup"] = UIGetAssetPath::getCustomAssetPath('/uiAssets/Families/iuser/prod/changeGroup.js');
        if (\Dcp\Ui\UIGetAssetPath::isInDebug()) {
            $js["dduiGroup"] = UIGetAssetPath::getCustomAssetPath('/uiAssets/Families/iuser/debug/changeGroup.js');
        }

        return $js;
    }

    protected function getAllMyGroups(\Anakeen\Core\Internal\SmartElement $smartElement)
    {
        /* @var Iuser $smartElement */
        $account = $smartElement->getAccount();
        return array_reduce($account->getGroupsId(), function ($accumulator, $currentValue) {
            $accumulator[$currentValue] = [
                "accountId" => $currentValue
            ];
            return $accumulator;
        }, []);
    }

    public function getMessages(\Anakeen\Core\Internal\SmartElement $userAccount)
    {
        $data=$this->customClientData;
        $msg=parent::getMessages($userAccount);
        if (isset($data["parentGroups"])) {
            $newGroups = array_keys($data["parentGroups"]);
            /* @var Iuser $userAccount  */
            $account = $userAccount->getAccount();
            $oldGroups = $account->getGroupsId();
            $groupsToAdd = array_diff($newGroups, $oldGroups);
            $groupsToDelete = array_diff($oldGroups, $newGroups);
            $currentUserSEId = $userAccount->getPropertyValue("initid");
            array_walk($groupsToDelete, function ($currentGroupId) use ($currentUserSEId) {
                $group = new \Anakeen\Core\Account("", $currentGroupId);
                $groupSE = SmartElementManager::getDocument($group->fid);
                /* @var \SmartStructure\Igroup $groupSE  */
                $groupSE->removeDocument($currentUserSEId);
            });
            array_walk($groupsToAdd, function ($currentGroupId) use ($currentUserSEId) {
                $group = new \Anakeen\Core\Account("", $currentGroupId);
                $groupSE = SmartElementManager::getDocument($group->fid);
                /* @var  \SmartStructure\Igroup $groupSE */
                $groupSE->insertDocument($currentUserSEId);
            });

          //  $userAccount->updateFromSystem();
            $userAccount->store();
            $msg[]=new ApiMessage(___("Group are be updated", "smart iuser"), ApiMessage::SUCCESS);
        }
        return $msg;
    }
}

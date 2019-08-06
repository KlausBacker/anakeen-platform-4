<?php

namespace Anakeen\SmartStructures\Iuser\Render;

use Anakeen\Routes\Core\Lib\ApiMessage;
use Anakeen\SmartElementManager;
use Anakeen\Ui\DefaultConfigEditRender;
use Anakeen\Ui\UIGetAssetPath;
//use \SmartStructure\Fields\Iuser as myAttributes;
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

        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "legacy");
        $js["iuserGroup"] = $path["IuserGroup"]["js"];

        return $js;
    }

    public function getJsDeps()
    {
        $js =  parent::getJsDeps();
        $kendoDll = UIGetAssetPath::getJSKendoComponentPath();
        $js["kendoDll"] = $kendoDll;
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
        $data = $this->customClientData;
        $msg = parent::getMessages($userAccount);
        if (isset($data["parentGroups"])) {
            $newGroups = array_keys($data["parentGroups"]);
            /* @var Iuser $userAccount */
            $account = $userAccount->getAccount();
            $oldGroups = $account->getGroupsId();
            $groupsToAdd = array_diff($newGroups, $oldGroups);
            $groupsToDelete = array_diff($oldGroups, $newGroups);
            $currentUserSEId = $userAccount->getPropertyValue("initid");
            array_walk($groupsToDelete, function ($currentGroupId) use ($currentUserSEId) {
                $group = new \Anakeen\Core\Account("", $currentGroupId);
                $groupSE = SmartElementManager::getDocument($group->fid);
                /* @var \SmartStructure\Igroup $groupSE */
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
            $msg[] = new ApiMessage(sprintf(___("Groups of \"%s\" has been updated", "smart iuser"), $userAccount->getTitle()), ApiMessage::SUCCESS);
        }
        return $msg;
    }
}

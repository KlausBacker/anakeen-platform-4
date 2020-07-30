<?php

namespace Anakeen\SmartStructures\Iuser\Render;

use Anakeen\Core\DbManager;
use Anakeen\Database\Exception;
use Anakeen\Routes\Core\Lib\ApiMessage;
use Anakeen\SmartElementManager;
use Anakeen\Ui\BarMenu;
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

    public function getMenu(\Anakeen\Core\Internal\SmartElement $document): BarMenu
    {
        $menu= parent::getMenu($document);

        $saveItem=$menu->getElement("save");
        $saveItem->setTextLabel(___("Record parent groups", "iuser"));
        // $saveItem->setBeforeContent('<div class="fa fa-users" />');
        return $menu;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $smartElement = null)
    {
        $js = parent::getJsReferences();

        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "prod");
        $js["iuserGroup"] = $path["IuserGroup"]["js"];

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
            $newGroups = $data["parentGroups"];
            /* @var Iuser $userAccount */
            $account = $userAccount->getAccount();
            $oldGroups = $account->getGroupsId();
            $groupsToAdd = array_diff($newGroups, $oldGroups);
            $groupsToDelete = array_diff($oldGroups, $newGroups);
            $currentUserSEId = $userAccount->getPropertyValue("initid");
            DbManager::savePoint("CHGGROUP");
            array_walk($groupsToDelete, function ($currentGroupId) use ($currentUserSEId) {
                $group = new \Anakeen\Core\Account("", $currentGroupId);
                $groupSE = SmartElementManager::getDocument($group->fid);

                /* @var \SmartStructure\Igroup $groupSE */
                $groupSE->insertGroups();
                $groupSE->removeDocument($currentUserSEId);
            });

            array_walk($groupsToAdd, function ($currentGroupId) use ($currentUserSEId) {
                $group = new \Anakeen\Core\Account("", $currentGroupId);
                try {
                    $groupSE = SmartElementManager::getDocument($group->fid);
                    /* @var  \SmartStructure\Igroup $groupSE */
                    $groupSE->insertGroups();
                    $groupSE->insertDocument($currentUserSEId);
                } catch (Exception $e) {
                    DbManager::rollbackPoint("CHGGROUP");
                    if (strpos($e->getMessage(), "GROUPLOOP0001") !== false) {
                        $e->setUserMessage(sprintf(
                            "Cannot insert group \"%s\": it is a subgroup",
                            $groupSE->getTitle()
                        ));
                    }
                    throw $e;
                }
            });
            //  $userAccount->updateFromSystem();
            $userAccount->store();

            DbManager::commitPoint("CHGGROUP");
            $msg[] = new ApiMessage(
                sprintf(
                    ___("Groups of \"%s\" has been updated", "smart iuser"),
                    $userAccount->getTitle()
                ),
                ApiMessage::SUCCESS
            );
        }
        return $msg;
    }
}

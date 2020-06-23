<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 04/06/18
 * Time: 15:08
 */

namespace Anakeen\Routes\Admin\Account;

use Anakeen\Core\DocManager;
use Anakeen\SmartElementManager;

class Config
{
    private static $defaultFamUser = "IUSER";
    private static $defaultFamGroup = "IGROUP";

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @return \Slim\Http\Response
     * @throws DocManager\Exception
     * @throws \Anakeen\Exception
     * @throws \Anakeen\Search\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response)
    {

        $result = [
            "user"=> [],
            "group"=> []
        ];
        $user = SmartElementManager::getFamily(self::$defaultFamUser);
        if (!$user) {
            $result["user"][] = [
                "text" => sprintf(___("Structure '%s' do not exist", "AdminCenterAccounts"), self::$defaultFamUser),
                "accessDenied" => true
            ];
        } else {
            $result["user"][] = [
                "id"=> $user->name,
                "text" => $user->getTitle(),
                "imageUrl" => $user->getIcon("", 15),
                "canCreate" => !$user->control("icreate")
            ];
            $this->getChildrenFamilies($user, $result["user"]);
        }

        $group = SmartElementManager::getFamily(self::$defaultFamGroup);
        if (!$group) {
            $result["group"][] = [
                "text" => sprintf(___("Structure '%s' do not exist", "AdminCenterAccounts"), self::$defaultFamGroup),
                "accessDenied" => true
            ];
        } else {
            $result["group"][] = [
                "id"=> $group->name,
                "text" => $group->getTitle(),
                "imageUrl" => $group->getIcon("", 5),
                "canCreate" => !$group->control("icreate")
            ];

            $this->getChildrenFamilies($group, $result["group"]);
        }

        return $response->withJson($result);
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $smartElement
     * @param array $result
     * @return array
     * @throws \Anakeen\Search\Exception
     */
    public function getChildrenFamilies(\Anakeen\Core\Internal\SmartElement $smartElement, &$result = [])
    {
        $search =  new \Anakeen\Search\Internal\SearchSmartData("", -1);
        $search->setObjectReturn();
        $search->overrideViewControl();
        $search->addFilter("fromid = %d", $smartElement->id);
        foreach ($search->getDocumentList() as $currentDoc) {
            $result[] = [
                "id"=> $currentDoc->name,
                "text" => $currentDoc->getTitle(),
                "imageUrl" => '/'.$currentDoc->getIcon("", 5),
                "canCreate" => !$currentDoc->control("icreate")
            ];
            $this->getChildrenFamilies($currentDoc, $result);
        }

        return $result;
    }
}

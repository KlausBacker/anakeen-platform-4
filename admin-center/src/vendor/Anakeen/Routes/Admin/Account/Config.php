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
     * @throws DocManager\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response) {

        $result = [
            "user"=> [],
            "group"=> []
        ];
        $user = SmartElementManager::getDocument(self::$defaultFamUser);

        $result["user"][] = [
            "id"=> $user->name,
            "text" => $user->getTitle(),
            "imageUrl" => '/'.$user->getIcon("", 5),
            "canCreate" => !$user->control("icreate")
        ];

        $this->getChildrenFamilies($user, $result["user"]);

        $group = SmartElementManager::getDocument(self::$defaultFamGroup);

        $result["group"][] = [
            "id"=> $group->name,
            "text" => $group->getTitle(),
            "imageUrl" => '/'.$group->getIcon("", 5),
            "canCreate" => !$group->control("icreate")
        ];

        $this->getChildrenFamilies($group, $result["group"]);

        return $response->withJson($result);
    }

    public function getChildrenFamilies(\Anakeen\Core\Internal\SmartElement $smartElement, &$result = []) {
        $search = new \SearchDoc("", -1);
        $search->setObjectReturn();
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
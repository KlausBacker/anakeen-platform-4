<?php

namespace Anakeen\App\Core;

use Anakeen\Core\AssetManager;
use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;

/**
 * Class WelcomePage
 *
 * Welcome Page
 * @note Used by route : GET /
 * @package Anakeen\Routes\Core
 */
class WelcomePage
{
    protected $compatibleAdminRole = array(
        "AdminCenter::FunctionalAdmin",
        "AdminCenter::Admin"
    );
    protected $compatibleDevelRole = array(
        "DevCenter::DevelAccess"
    );
    /**
     * Return Welcome page
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     *
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $templateFile=__DIR__."/welcomePage.mustache.html";

        $mustache=new \Mustache_Engine();
        $user = ContextManager::getCurrentUser();
        $hasAdminRole = $this->checkRoles($user, $this->compatibleAdminRole);
        $hasDevelRole = $this->checkRoles($user, $this->compatibleDevelRole);
        $data["cssRef"]= AssetManager::getAssetLink(__DIR__."/WelcomePage.css");
        $data["thisyear"]= strftime("%Y", time());
        $data["version"]= \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "VERSION");
        $data["userRealName"]= $user->getAccountName();
        $data["userDomain"]= \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_CLIENT");
        $data["hasAdmin"]= class_exists("Anakeen\\Routes\\Admin\\MainPage") && $hasAdminRole; //Test to detect admin center
        $data["hasDevel"]= class_exists("Anakeen\\Routes\\Devel\\UI\\Main") && $hasDevelRole; //Test to detect admin center

        $out=$mustache->render(file_get_contents($templateFile), $data);
        return $response->write($out);
    }

    public function checkRoles($user, $acls)
    {
        if ($user->login === "admin") {
            return true;
        }
        $sql = "select * from permission,acl where permission.id_acl = acl.id and ";
        $aclNames = implode(" or ", array_map(function ($item) {
            return "acl.name='".$item."'";
        }, $acls));
        $sql .= $aclNames;
        DbManager::query($sql, $groupsid, true, false);
        return in_array($user->id, $groupsid);
    }
}

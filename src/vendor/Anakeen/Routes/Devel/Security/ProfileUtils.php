<?php

namespace Anakeen\Routes\Devel\Security;

use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\DocumentAccess;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;

/**
 * Get Right Accesses
 *
 * @note Used by route : GET api/v2/devel/security/profile/{id}/accesses/
 */
class ProfileUtils
{

    /**
     * Get inherited accesses (from group management)
     *
     * @param array $accesses
     * @param SmartElement $profil
     */
    public static function getGreyAccesses(array &$accesses, SmartElement $profil)
    {
        $parentGroups = self::getGroupParents();
        foreach ($accesses as &$access) {
            if ($access["account"]["type"] === "group" || $access["account"]["type"] === "user") {
                if (isset($parentGroups[$access["id"]])) {
                    $access["parents"] = $parentGroups[$access["id"]];
                }
                foreach ($profil->acls as $aclName) {
                    if (!isset($access["acls"][$aclName])) {
                        if ($profil->accessControl()->isExtendedAcl($aclName)) {
                            if (\DocPermExt::isGranted($access["id"], $aclName, $profil->id)) {
                                $access["acls"][$aclName] = "inherit";
                            }
                        } elseif (DocumentAccess::controlUserId($profil->id, $access["id"], $aclName) === "") {
                            $access["acls"][$aclName] = "inherit";
                        }
                    }
                }
            }
        }
    }

    public static function getProperties(SmartElement $profile)
    {
        $props = [
            "id" => $profile->id,
            "title" => $profile->getTitle(),
            "icon" => $profile->getIcon(),
            "type" => $profile->fromname,
            "name" => $profile->name
        ];

        // Add reference profil information
        $dprofid = $profile->getPropertyValue("dprofid");
        if (!empty($dprofid)) {
            $refProfile = SEManager::getDocument($dprofid);
            if (!empty($refProfile)) {
                $props["reference"] = [
                    "id" => $refProfile->id,
                    "title" => $refProfile->getTitle(),
                    "icon" => $refProfile->getIcon("", "24"),
                    "type" => $refProfile->fromname,
                    "name" => $refProfile->name
                ];
            }
        }

        if ($profile->accessControl()->isRealProfile()) {
            $props["structure"] = SEManager::getNameFromId($profile->getRawValue("dpdoc_famid"));
        }

        $acls = array_values($profile->acls);

        $extended = $profile->extendedAcls;

        foreach ($acls as $acl) {
            if (isset($extended[$acl])) {
                $isExtendedAcl = true;
                $label = $extended[$acl]["description"];
            } else {
                $isExtendedAcl = false;
                $label = DocumentAccess::$dacls[$acl]["description"];
            }
            $props["acls"][] = [
                "name" => $acl,
                "label" => $label,
                "extended" => $isExtendedAcl

            ];
        }

        return $props;
    }

    protected static function getGroupParents()
    {
        $sql = "select users.id as id, groups.idgroup as parent from users, groups where groups.iduser = users.id and accounttype='G';";
        DbManager::query($sql, $results);

        $parents = [];
        foreach ($results as $result) {
            $parents[intval($result["id"])][] = intval($result["parent"]);
        }
        return $parents;
    }

    public static function completeGroupAccess(array &$accesses)
    {

        $sql = "select id, login from users where accounttype='G'";
        DbManager::query($sql, $groups);
        foreach ($groups as $group) {
            if (!self::existsAccess($group["login"], $accesses)) {
                $accesses[] = ["id" => intval($group["id"]), "account" => ["reference" => $group["login"], "type" => "group"]];
            }
        }
    }

    public static function completeRoleAccess(array &$accesses)
    {
        $sql = "select id, login from users where accounttype='R'";
        DbManager::query($sql, $roles);
        foreach ($roles as $role) {
            if (!self::existsAccess($role["login"], $accesses)) {
                $accesses[] = ["id" => intval($role["id"]), "account" => ["reference" => $role["login"], "type" => "role"]];
            }
        }
    }

    protected static function existsAccess($accountRef, $accesses)
    {
        foreach ($accesses as $access) {
            if ($access["account"]["reference"] === $accountRef) {
                return true;
            }
        }
        return false;
    }


    public static function getGreenAccesses(SmartElement $profil)
    {
        $sql = sprintf(
            "select docperm.upacl, users.login, users.id, users.accounttype from docperm, users where userid=users.id and docid = %d order by users.accounttype, users.login;",
            $profil->id
        );
        DbManager::query($sql, $results);

        $greenAccess = [];
        foreach ($results as $result) {
            $greenAccess[$result["login"]] = [
                "id" => $result["id"],
                "uperm" => $result["upacl"],
                "type" => self::getAccountType($result["accounttype"]),
                "aclNames" => []
            ];
        }

        $sql = sprintf(
            "select docpermext.acl, users.login, users.id, users.accounttype from docpermext, users where userid=users.id and docid = %d order by users.accounttype, users.login;",
            $profil->id
        );
        DbManager::query($sql, $extResults);
        foreach ($extResults as $result) {
            if (!isset($greenAccess[$result["login"]])) {
                $greenAccess[$result["login"]] = [
                    "id" => $result["id"],
                    "type" => self::getAccountType($result["accounttype"]),
                    "uperm" => 0,
                    "aclNames" => []
                ];
            }
            $greenAccess[$result["login"]]["aclNames"][] = $result["acl"];
        }

        $sql = sprintf(
            "select docperm.upacl, vgroup.id as field, vgroup.num as id from docperm, vgroup where userid=vgroup.num and docid =  %d order by vgroup.id;",
            $profil->id
        );
        DbManager::query($sql, $results);
        foreach ($results as $result) {
            $greenAccess[$result["field"]] = [
                "id" => $result["id"],
                "uperm" => $result["upacl"],
                "type" => "field",
                "aclNames" => []
            ];
        }

        $sql = sprintf(
            "select docpermext.acl, vgroup.id as field, vgroup.num as id from docpermext, vgroup where userid=vgroup.num and docid =  %d order by vgroup.id;",
            $profil->id
        );
        DbManager::query($sql, $results);
        foreach ($results as $result) {
            if (!isset($greenAccess[$result["field"]])) {
                $greenAccess[$result["field"]] = [
                    "id" => $result["id"],
                    "uperm" => 0,
                    "type" => "field",
                    "aclNames" => []
                ];
            }
            $greenAccess[$result["field"]]["aclNames"][] = $result["acl"];
        }


        $accesses = [];
        foreach ($greenAccess as $login => $accountAccess) {
            $uperm = $accountAccess["uperm"];
            $aclNames = $accountAccess["aclNames"];
            $access = ["id" => intval($accountAccess["id"]), "account" => ["reference" => $login, "type" => $accountAccess["type"]]];

            foreach ($profil->acls as $aclName) {
                if ($uperm && DocumentAccess::hasControl($uperm, $aclName)) {
                    $access["acls"][$aclName] = "set";
                } elseif (array_search($aclName, $aclNames) !== false) {
                    $access["acls"][$aclName] = "set";
                }
            }

            $accesses[] = $access;
        }

        return $accesses;
    }

    public static function getAccountType($systemType)
    {
        switch ($systemType) {
            case 'U':
                return "user";
            case "G":
                return "group";
            case "R":
                return "role";
        }
        return "?";
    }
}

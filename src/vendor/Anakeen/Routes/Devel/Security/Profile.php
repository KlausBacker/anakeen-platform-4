<?php

namespace Anakeen\Routes\Devel\Security;

use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\DocumentAccess;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\SmartElementManager;

/**
 * Get Right Accesses
 *
 * @note Used by route : GET api/v2/devel/security/profile/{id}/accesses/
 */
class Profile
{
    protected $documentId;
    /**
     * @var SmartElement
     */
    protected $_document;
    protected $completeGroup = false;
    protected $completeRole = false;

    /**
     * Return right accesses for a profil element
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param $args
     *
     * @return \Slim\Http\response $response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(
        \Slim\Http\request $request,
        $args
    ) {
        $this->documentId = $args["id"];
        $this->setDocument($this->documentId);
        $this->completeGroup = ($request->getQueryParam("group") === "all");
        $this->completeRole = ($request->getQueryParam("role") === "all");
    }


    public function doRequest()
    {
        $data["properties"] = $this->getProperties();
        $data["accesses"] = $this->getGreenAccesses();

        if ($this->completeGroup) {
            // add all groups in response even they has no accesses
            $this->completeGroupAccess($data["accesses"]);
        }
        if ($this->completeRole) {
            // add all roles in response even they has no accesses
            $this->completeRoleAccess($data["accesses"]);
        }

        $this->getGreyAccesses($data["accesses"]);
        return $data;
    }


    protected function getGreyAccesses(array &$accesses)
    {
        $parentGroups = self::getGroupParents();
        foreach ($accesses as &$access) {
            if ($access["account"]["type"] === "group" || $access["account"]["type"] === "user") {
                if (isset($parentGroups[$access["id"]])) {
                    $access["parents"] = $parentGroups[$access["id"]];
                }
                foreach ($this->_document->acls as $aclName) {
                    if (!isset($access[$aclName])) {
                        if (DocumentAccess::controlUserId($this->_document->id, $access["id"], $aclName) === "") {
                            $access[$aclName] = "inherit";
                        }
                    }
                }
            }
        }
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

    protected function completeGroupAccess(array &$accesses)
    {

        $sql = "select id, login from users where accounttype='G'";
        DbManager::query($sql, $groups);
        foreach ($groups as $group) {
            if (!self::existsAccess($group["login"], $accesses)) {
                $accesses[] = ["id" => intval($group["id"]), "account" => ["reference" => $group["login"], "type" => "group"]];
            }
        }
    }

    protected function completeRoleAccess(array &$accesses)
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
            if ($access["account"] === $accountRef) {
                return true;
            }
        }
        return false;
    }

    protected function getProperties()
    {
        $props = [
            "id" => $this->_document->id,
            "title" => $this->_document->getTitle(),
            "icon" => $this->_document->getIcon(),
            "type" => $this->_document->fromname,
            "name" => $this->_document->name
        ];

        if ($this->_document->accessControl()->isRealProfile()) {
            $props["structure"] = SEManager::getNameFromId($this->_document->getRawValue("dpdoc_famid"));
        }

        $props["acl"] = array_values($this->_document->acls);

        return $props;
    }

    protected function getGreenAccesses()
    {
        $sql = sprintf(
            "select docperm.upacl, users.login, users.id, users.accounttype from docperm, users where userid=users.id and docid = %d order by users.accounttype, users.login;",
            $this->_document->id
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
            $this->_document->id
        );
        DbManager::query($sql, $extResults);
        foreach ($extResults as $result) {
            if (!isset($greenAccess[$result["login"]])) {
                $greenAccess[$result["login"]] = [
                    "id" => $result["id"],
                    "type" => self::getAccountType($result["accounttype"]),
                    "aclNames" => []
                ];
            }
            $greenAccess[$result["login"]]["aclNames"][] = $result["acl"];
        }

        $sql = sprintf(
            "select docperm.upacl, vgroup.id as field, vgroup.num as id from docperm, vgroup where userid=vgroup.num and docid =  %d order by vgroup.id;",
            $this->_document->id
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
            $this->_document->id
        );
        DbManager::query($sql, $results);
        foreach ($results as $result) {
            if (!isset($greenAccess[$result["field"]])) {
                $greenAccess[$result["field"]] = [
                    "id" => $result["id"],
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

            foreach ($this->_document->acls as $aclName) {
                if ($uperm && DocumentAccess::hasControl($uperm, $aclName)) {
                    $access[$aclName] = "set";
                } elseif (array_search($aclName, $aclNames) !== false) {
                    $access[$aclName] = "set";
                }
            }


            $accesses[] = $access;
        }

        return $accesses;
    }

    protected static function getAccountType($systemType)
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

    /**
     * Find the current document and set it in the internal options
     *
     * @param $resourceId
     *
     * @throws Exception
     */
    protected function setDocument($resourceId)
    {
        $this->_document = SmartElementManager::getDocument($resourceId);
        if (!$this->_document) {
            $exception = new Exception("ROUTES0100", $resourceId);
            $exception->setHttpStatus("404", "Element not found");
            throw $exception;
        }
        if ($this->_document->defDoctype !== "P") {
            if ($this->_document->id !== $this->_document->profid) {
                throw new Exception("DEV0100", $resourceId);
            }
        }
    }
}

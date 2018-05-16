<?php

/**
 * Intranet User & Group  manipulation
 * Trait TAccountFamily
 */
namespace Anakeen\SmartStructures\Iuser;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;

/**
 * Trait TAccount
 * @mixin \Anakeen\Core\Internal\SmartElement
 * @package Anakeen\SmartStructures\Iuser
 */
trait TAccount
{
    /**
     * @var \Anakeen\Core\Account
     */
    public $wuser;

    /**
     * verify if the login syntax is correct and if the login not already exist
     *
     * @param string $login login to test
     *
     * @return array 2 items $err & $sug for view result of the constraint
     * @throws \Dcp\Db\Exception
     */
    public function constraintLogin($login)
    {
        $sug = array(
            "-"
        );
        $err = '';
        if ($login == "") {
            $err = _("the login must not be empty");
        } elseif ($login == "-") {
        } elseif ($login == "-") {
        } else {
            if ($err == "") {
                return $this->ExistsLogin($login);
            }
        }
        return array(
            "err" => $err,
            "sug" => $sug
        );
    }

    /**
     * verify if the login not already exist
     *
     * @param string $login login to test
     *
     * @return array 2 items $err & $sug for view result of the constraint
     * @throws \Dcp\Db\Exception
     */
    public function existsLogin($login)
    {
        $sug = array();
        
        $id = $this->getRawValue("US_WHATID");
        
        $q = new \Anakeen\Core\Internal\QueryDb("", \Anakeen\Core\Account::class);
        $q->AddQuery(sprintf("login='%s'", pg_escape_string(mb_strtolower($login))));
        if ($id) {
            $q->AddQuery(sprintf("id != %d", $id));
        }
        $q->Query(0, 0, "TABLE");
        $err = $q->basic_elem->msg_err;
        if (($err == "") && ($q->nb > 0)) {
            $err = _("login yet use");
        }
        
        return array(
            "err" => $err,
            "sug" => $sug
        );
    }
    
    public function preCreated()
    {
        if ($this->getRawValue("US_WHATID") != "") {
            $filter = array(
                "us_whatid = '" . intval($this->getRawValue("US_WHATID")) . "'"
            );
            $tdoc = \Anakeen\SmartStructures\Dir\DirLib::internalGetDocCollection($this->dbaccess, 0, 0, "ALL", $filter, 1, "TABLE", $this->fromid);
            if (count($tdoc) > 0) {
                return _("system id already set in database\nThis kind of document can not be duplicated");
            }
        }
        return '';
    }
    /**
     * avoid deletion of system document
     */
    public function preDocDelete()
    {
        $err = parent::preDocDelete();
        if ($err == "") {
            $uid = $this->getRawValue("us_whatid");
            if (($uid > 0) && ($uid < 10)) {
                $err = _("this system user cannot be deleted");
            }
        }
        return $err;
    }
    /**
     * get system id account from document id account
     * @param array $accountIds
     * @return array
     */
    public function getSystemIds(array $accountIds)
    {
        $accountIds = array_unique($accountIds);
        $kr = array_search('', $accountIds);
        if ($kr !== false) {
            unset($accountIds[$kr]);
        }
        $sysIds = array();
        if (count($accountIds) > 0) {
            $sql = sprintf("select id from users where fid in (%s)", implode(',', $accountIds));
            DbManager::query($sql, $sysIds, true, false);
            $sysIds = array_unique($sysIds);
        }
        return $sysIds;
    }

    /**
     * internal function use for choosegroup
     * use to compute displayed group tree
     */
    public function _getChildsGroup($id, $groups)
    {
        $tlay = array();
        foreach ($groups as $k => $v) {
            if ($v["idgroup"] == $id) {
                $tlay[$k] = $v;
                $tlay[$k]["SUBUL"] = $this->_getChildsGroup($v["id"], $groups);
                $fid = $v["fid"];
                if ($fid) {
                    $tdoc = SEManager::getRawDocument($fid);
                    $icon = $this->getIcon($tdoc["icon"]);
                    $tlay[$k]["icon"] = $icon;
                } else {
                    $tlay[$k]["icon"] = "Images/igroup.gif";
                }
            }
        }
        
        if (count($tlay) == 0) {
            return "";
        }
        global $action;
        $lay = new \Layout("USERCARD/Layout/ligroup.xml", $action);
        uasort($tlay, array(
            get_class($this) ,
            "_cmpgroup"
        ));
        $lay->setBlockData("LI", $tlay);
        return $lay->gen();
    }
    /**
     * to sort group by name
     */
    public static function _cmpgroup($a, $b)
    {
        return strcasecmp($a['lastname'], $b['lastname']);
    }


    /**
     * return document objet from what id (user or group)
     *
     * @param int $wid what identifier
     *
     * @return \SmartStructure\Iuser|\SmartStructure\IGROUP|false the object document (false if not found)
     */
    public function getDocUser($wid)
    {
        $u = new \Anakeen\Core\Account("", $wid);
        if ($u->isAffected()) {
            if ($u->fid > 0) {
                $du = SEManager::getDocument($u->fid);
                /**
                 * @var \SmartStructure\Iuser|\SmartStructure\IGROUP $du
                 */
                if ($du && $du->isAlive()) {
                    return $du;
                }
            }
        }
        return false;
    }
    /**
     * return system account object conform to whatid
     *
     * @param bool $nocache set to true if need to reload user object from database
     *
     * @return \Anakeen\Core\Account|false return false if not found
     */
    public function getAccount($nocache = false)
    {
        if ($nocache) {
            $this->wuser=null; // needed for reaffect new values
        } elseif ($this->wuser) {
            if ($this->wuser->fid != $this->getRawValue("us_whatid")) {
                $this->wuser=null; // clear cache when reaffect
            }
        }
        
        if (!isset($this->wuser)) {
            $wid = $this->getRawValue("us_whatid");
            if ($wid > 0) {
                $this->wuser = new \Anakeen\Core\Account("", $wid);
            }
        }
        if (!isset($this->wuser)) {
            return false;
        }
        return $this->wuser;
    }

    /**
     * reset wuser
     */
    protected function postAffect(array $data, $more, $reset)
    {
        if (isset($this->wuser)) {
            $this->wuser=null;
        }
    }
}

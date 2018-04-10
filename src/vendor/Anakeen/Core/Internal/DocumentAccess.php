<?php

namespace Anakeen\Core\Internal;

use Anakeen\Core\ContextManager;
use \Anakeen\Core\DbManager;
use \Anakeen\Core\DocManager;

/**
 * Control Access Document Class
 *
 */
class DocumentAccess
{
    private static $globalDocPermLock = false;

    const POS_INIT = 0;
    const POS_VIEW = 1;
    const POS_EDIT = 2;
    const POS_DEL = 3;
    const POS_SEND = 4;
    const POS_OPEN = 5;
    const POS_EXEC = 5;// idem OPEN : alias
    const POS_CONT = 6; // view containt
    const POS_VACL = 7;
    const POS_MACL = 8;
    const POS_ULCK = 9;
    const POS_CONF = 10;// confidential
    const POS_CREATE = 5;
    const POS_ICREATE = 6;
    // end of privilege is 31 : (coded on 32bits)

    // access privilege definition
    public static $dacls = array(
        "init" => array(
            "pos" => self::POS_INIT,
            "description" => "control initialized"
        ),
        "view" => array(
            "pos" => self::POS_VIEW, # N_("view document")
            "description" => "view document"
        ), #  N_("view")
        "send" => array(
            "pos" => self::POS_SEND, # N_("send document")
            "description" => "send document"
        ), # N_("send")
        "edit" => array(
            "pos" => self::POS_EDIT, # N_("edit document")
            "description" => "edit document"
        ), #  N_("edit")
        "delete" => array(
            "pos" => self::POS_DEL, # N_("delete document")
            "description" => "delete document"
        ), #  N_("delete")
        "open" => array(
            "pos" => self::POS_OPEN, # N_("open folder")
            "description" => "open folder"
        ), #  N_("open")
        "execute" => array(
            "pos" => self::POS_EXEC, # N_("execute search")
            "description" => "execute search"
        ), #  N_("execute")
        "modify" => array(
            "pos" => self::POS_CONT, # N_("modify folder")
            "description" => "modify folder"
        ), #  N_("modify")
        "viewacl" => array(
            "pos" => self::POS_VACL, # N_("view acl")
            "description" => "view acl"
        ), #  N_("viewacl")
        "modifyacl" => array(
            "pos" => self::POS_MACL, # N_("modify acl")
            "description" => "modify acl"
        ), #  N_("modifyacl")
        "create" => array(
            "pos" => self::POS_CREATE, # N_("modify acl")
            "description" => "create doc"
        ), #  N_("create doc")
        "unlock" => array(
            "pos" => self::POS_ULCK, # N_("unlock")
            "description" => "unlock unowner locked doc"
        ), #  N_("unlock unowner locked doc")
        "icreate" => array(
            "pos" => self::POS_ICREATE, # N_("icreate")
            "description" => "create doc manually"
        ), #  N_("create doc manually")
        "confidential" => array(
            "pos" => self::POS_CONF, # N_("confidential")
            "description" => "view confidential"
        ) #  N_("view confidential")
    );


    /**
     * @var \Doc
     */
    protected $document;

    /**
     * @return mixed
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param \Doc $document
     * @return DocumentAccess
     */
    public function setDocument(\Doc $document)
    {
        $this->document = $document;
        return $this;
    }

    public function isControlled()
    {
        return ($this->document->profid != 0);
    }

    /**
     * Unset all Acl for document (for everybody)
     * @param int $userid user system identifier
     */
    public function removeControl($userid = -1)
    {
        if ($this->document->id == $this->document->profid) {
            if ($userid == -1) {
                // inhibated all doc references this profil

                DbManager::query(sprintf("delete from docperm where docid=%d", $this->document->id));
                DbManager::query(sprintf("delete from docpermext where docid=%d", $this->document->id));
            } else {
                DbManager::query(sprintf("delete from docperm where docid=%d and userid=%d", $this->document->id, $userid));
                DbManager::query(sprintf("delete from docpermext where docid=%d and userid=%d", $this->document->id, $userid));
            }
        }
    }

    /**
     * activate access specific control
     * @param bool $userctrl if true add all acls for current user
     * @return string
     */
    public function setControl($userctrl = true)
    {
        if ($userctrl) {
            $perm = new \DocPerm($this->document->dbaccess, array(
                $this->document->id,
                ContextManager::getCurrentUser()->id
            ));
            $perm->docid = $this->document->id;
            $perm->userid = ContextManager::getCurrentUser()->id;
            $perm->upacl = -2; // all privileges
            if (!$perm->IsAffected()) {
                // add all privileges to current user
                $perm->Add();
            } else {
                $perm->Modify();
            }
        }
        // reactivation of doc with its profil
        if ($this->document->doctype == 'P') {
            DbManager::query("update doc set profid=-profid where profid=-" . $this->document->id . " and locked != -1;");
        }

        $this->document->profid = $this->document->id;
        $err = $this->document->modify(true, array(
            "profid"
        ), true);
        return $err;
    }


    /**
     * set profil for document
     *
     * @param int  $profid identifier for profil document
     * @param \Doc $fromdocidvalues
     *
     * @return string
     * @throws \Dcp\Core\Exception
     * @throws \Dcp\Db\Exception
     */
    public function setProfil($profid, $fromdocidvalues = null)
    {
        $err = '';
        if ($profid && !is_numeric($profid)) {
            $profid = DocManager::getIdFromName($profid);
        }
        if (empty($profid)) {
            $profid = 0;
            $this->document->dprofid = 0;
            $this->document->views = '{0}';
        }
        $this->document->profid = $profid;
        if (($profid > 0) && ($profid != $this->document->id)) {
            // make sure that the profil is activated
            $pdoc = \Anakeen\Core\DocManager::getDocument($profid);
            if ($pdoc && $pdoc->getRawValue("DPDOC_FAMID") > 0) {
                // dynamic profil
                $this->document->dprofid = $profid;
                $this->computeDProfil($this->document->dprofid, $fromdocidvalues);
                unset($this->document->uperm); // force recompute privileges
            } else {
                $this->document->dprofid = 0;
                $this->setViewProfil();
            }
            if ($pdoc->profid == 0) {
                $this->document->profid = -$profid;
            } // inhibition
        } elseif (($profid > 0) && ($profid == $this->document->id)) {
            $this->document->dprofid = 0;
            $this->setViewProfil();
        }
        if ($this->document->id > 0) {
            $err = $this->document->modify(true, array(
                "profid",
                "dprofid",
                "views"
            ), true);
        }
        return $err;
    }

    /**
     * return true if document is profile document PDOC, PDIR, ...
     * @return bool
     */
    public function isRealProfile()
    {
        return ($this->document->getAttribute("dpdoc_famid") != null);
    }

    /**
     * recompute view vector privilege
     */
    protected function setViewProfil()
    {
        if ($this->document->dprofid) {
            $this->computeDProfil();
        } else {
            if ($this->document->profid == $this->document->id) {
                DbManager::query(sprintf("select userid from docperm where docid=%d and upacl & 2 != 0", $this->document->id), $uids, true, false);
                $this->document->views = '{' . implode(',', $uids) . '}';
                $this->document->modify(true, array(
                    'views'
                ), true);
                if ($this->isRealProfile()) {
                    //propagate static profil views on linked documents
                    DbManager::query(sprintf("update doc set views='%s' where profid=%d and (dprofid is null or dprofid = 0)", $this->document->views, $this->document->id));
                }
            } else {
                // static profil
                if ($this->document->profid > 0) {
                    DbManager::query(sprintf("select views from docread where id=%d", $this->document->profid), $view, true, true);
                } else {
                    $view = '{0}';
                }
                $this->document->views = $view;
                if ($this->document->id) {
                    $this->document->modify(true, array(
                        'views'
                    ), true);
                }
            }
        }
    }

    /**
     * apply computeDProfil in all documents with this profile
     *
     * @return void
     * @throws \Dcp\Core\Exception
     * @throws \Dcp\Db\Exception
     * @throws \Dcp\SearchDoc\Exception
     */
    public function recomputeProfiledDocument()
    {
        if ($this->isRealProfile()) {
            if ($this->document->getRawValue("dpdoc_famid") > 0) {
                // dynamic profil
                // recompute associated documents
                \Anakeen\Core\Utils\System::setMaxExecutionTimeTo(0);

                if (DbManager::inTransition()) {
                    // when are in transaction must lock complete table to avoid too many locks on each rows
                    DbManager::query("lock table docperm in exclusive mode");
                    self::$globalDocPermLock = true;
                }
                $s = new \SearchDoc($this->document->dbaccess);
                $s->addFilter("dprofid = %d", $this->document->id);
                $s->setObjectReturn();
                $s->overrideViewControl();
                $s->search();
                while ($doc = $s->getNextDoc()) {
                    $doc->accessControl()->computeDProfil();
                }
                // in case of change profil status (static -> dynamic)
                $s = new \SearchDoc($this->document->dbaccess);
                $s->addFilter("profid = %d", $this->document->id);
                $s->setObjectReturn();
                $s->overrideViewControl();
                $s->search();
                while ($doc = $s->getNextDoc()) {
                    $doc->accessControl()->setProfil($this->document->id);
                }
            } else {
                // static profil
                // revert to static profiling
                $s = new \SearchDoc($this->document->dbaccess);
                $s->addFilter("dprofid = %d", $this->document->id);
                $s->setObjectReturn();
                $s->overrideViewControl();
                $s->search();
                while ($doc = $s->getNextDoc()) {
                    $doc->accessControl()->setProfil($this->document->id);
                }
            }
        }
    }

    /**
     * reset right for dynamic profil
     *
     * @param int  $dprofid         identifier for dynamic profil document
     * @param \Doc $fromdocidvalues other document to reference dynamic profiling (default itself)
     *
     * @return string error message
     * @throws \Dcp\Core\Exception
     * @throws \Dcp\Db\Exception
     */
    public function computeDProfil($dprofid = 0, $fromdocidvalues = null)
    {
        $err = '';
        $pfamid = 0;
        if ($this->document->id == 0) {
            return '';
        }
        if ($dprofid == 0) {
            $dprofid = $this->document->dprofid;
        }
        if ($dprofid <= 0) {
            return '';
        }
        $perm = null;
        $vupacl = array();

        $tVgroup2attrid = array();
        $pdoc = \Anakeen\Core\DocManager::getDocument($dprofid);
        if ($pdoc) {
            $pfamid = $pdoc->getRawValue("DPDOC_FAMID");
        }
        if ($pfamid > 0) {
            if ($this->document->profid != $this->document->id) {
                $this->document->profid = $this->document->id; //private profil
                $this->document->modify(true, array(
                    "profid"
                ));
            }

            $query = new \Anakeen\Core\Internal\QueryDb($this->document->dbaccess, \DocPerm::class);
            $query->AddQuery(sprintf("docid=%d", $pdoc->id));
            $tacl = $query->Query(0, 0, "TABLE");
            if (!is_array($tacl)) {
                //	print "err $tacl";
                $tacl = array();
            }
            $tgnum = array(); // list of virtual user/group
            foreach ($tacl as $v) {
                if ($v["userid"] >= \VGroup::STARTIDVGROUP) {
                    $tgnum[] = $v["userid"];
                }
            }
            if (count($tgnum) > 0) {
                $query = new \Anakeen\Core\Internal\QueryDb($this->document->dbaccess, \VGroup::class);
                $query->AddQuery(DbManager::getSqlOrCond($tgnum, "num", true));
                $tg = $query->Query(0, 0, "TABLE");
                if ($query->nb > 0) {
                    foreach ($tg as $vg) {
                        $tVgroup2attrid[$vg["num"]] = $vg["id"];
                    }
                }
            }
            $point = uniqid("dcp:docperm");
            DbManager::savePoint($point);
            if (!self::$globalDocPermLock) {
                DbManager::lockPoint($this->document->initid, "PERM");
            }
            // Need to lock to avoid constraint errors when concurrent docperm update
            $this->document->exec_query(sprintf("delete from docperm where docid=%d", $this->document->id));
            if ($fromdocidvalues == null) {
                $fromdocidvalues = &$this->document;
            }
            $greenUid = array();
            foreach ($tacl as $v) {
                if ($v["userid"] < \VGroup::STARTIDVGROUP) {
                    $tuid = array(
                        $v["userid"]
                    );
                } else {
                    $tuid = array();
                    $aid = $tVgroup2attrid[$v["userid"]];
                    /**
                     * @var \Doc $fromdocidvalues
                     */
                    $duid = $fromdocidvalues->getRawValue($aid);
                    if ($duid == "") {
                        $duid = $fromdocidvalues->getFamilyParameterValue($aid);
                    }
                    if ($duid != "") {
                        $duid = str_replace("<BR>", "\n", $duid); // docid multiple
                        $tduid = \Doc::rawValueToArray($duid);
                        foreach ($tduid as $duid) {
                            if ($duid > 0) {
                                $docu = DocManager::getRawDocument(intval($duid));
                                if (!is_array($docu)) {
                                    // No use exception because document may has been deleted
                                    $errorMessage = \ErrorCode::getError('DOC0127', var_export($duid, true), var_export($aid, true));
                                    $this->document->log->error($errorMessage);
                                    $this->document->addHistoryEntry($errorMessage, \DocHisto::ERROR);
                                } elseif (!array_key_exists('us_whatid', $docu)) {
                                    $errorMessage = \ErrorCode::getError('DOC0128', var_export($duid, true), var_export($aid, true));
                                    $this->document->log->error($errorMessage);
                                    $this->document->addHistoryEntry($errorMessage, \DocHisto::ERROR);
                                } elseif (empty($docu['us_whatid'])) {
                                    // No use exception because account may has been deleted
                                    $errorMessage = \ErrorCode::getError('DOC0129', var_export($duid, true), var_export($aid, true));
                                    $this->document->log->error($errorMessage);
                                    $this->document->addHistoryEntry($errorMessage, \DocHisto::ERROR);
                                } else {
                                    $tuid[] = $docu["us_whatid"];
                                }
                            }
                        }
                    }
                }
                foreach ($tuid as $ku => $uid) {
                    // add right in case of multiple use of the same user : possible in dynamic profile
                    if (($v["upacl"] & 2) && $uid) {
                        $greenUid[$uid] = $uid;
                    }
                    if (!isset($vupacl[$uid])) {
                        $vupacl[$uid] = 0;
                    }
                    $vupacl[$uid] = (intval($vupacl[$uid]) | intval($v["upacl"]));
                    if ($uid > 0) {
                        $perm = new \DocPerm($this->document->dbaccess, array(
                            $this->document->id,
                            $uid
                        ));
                        $perm->upacl = $vupacl[$uid];
                        // print "<BR>\nset perm $uid : ".$this->document->id."/".$perm->upacl.'/'.$vupacl[$uid]."\n";
                        if ($perm->isAffected()) {
                            $err = $perm->modify();
                        } else {
                            if ($perm->upacl) {
                                // add if necessary
                                $err = $perm->Add();
                            }
                        }
                    }
                }
            }

            DbManager::commitPoint($point);
            $this->document->views = '{' . implode(',', $greenUid) . '}';
            $this->document->Modify(true, array(
                'views'
            ), true);
            $err .= $this->computeDProfilExt($pdoc->id, $fromdocidvalues);
        }
        unset($this->document->uperm); // force recompute privileges
        return $err;
    }

    /**
     * reset right for dynamic profil
     *
     * @param int  $dprofid         identifier for dynamic profil document
     * @param \Doc $fromdocidvalues other document to reference dynamic profiling (default itself)
     *
     * @return string error message
     * @throws \Dcp\Db\Exception
     */
    private function computeDProfilExt($dprofid, $fromdocidvalues = null)
    {
        $err = '';
        if (count($this->document->extendedAcls) == 0) {
            return '';
        }

        $tVgroup2attrid = array();
        $query = new \Anakeen\Core\Internal\QueryDb($this->document->dbaccess, \DocPermExt::class);
        $query->AddQuery(sprintf("docid=%d", $dprofid));
        $tacl = $query->Query(0, 0, "TABLE");
        if (!is_array($tacl)) {
            //	print "err $tacl";
            $tacl = array();
        }
        if (!$tacl) {
            return '';
        } // no ext acl
        $tgnum = array(); // list of virtual user/group
        foreach ($tacl as $v) {
            if ($v["userid"] >= \VGroup::STARTIDVGROUP) {
                $tgnum[] = $v["userid"];
            }
        }
        if (count($tgnum) > 0) {
            $query = new \Anakeen\Core\Internal\QueryDb($this->document->dbaccess, \VGroup::class);

            $query->AddQuery(DbManager::getSqlOrCond($tgnum, "num", true));
            $tg = $query->Query(0, 0, "TABLE");
            if ($query->nb > 0) {
                foreach ($tg as $vg) {
                    $tVgroup2attrid[$vg["num"]] = $vg["id"];
                }
            }
        }
        $this->document->exec_query(sprintf("delete from docpermext where docid=%d", $this->document->id));
        if ($fromdocidvalues == null) {
            $fromdocidvalues = &$this->document;
        }
        $greenUid = array();
        foreach ($tacl as $v) {
            if ($v["userid"] < \VGroup::STARTIDVGROUP) {
            } else {
                $aid = $tVgroup2attrid[$v["userid"]];
                /**
                 * @var \Doc $fromdocidvalues
                 */
                $duid = $fromdocidvalues->getRawValue($aid);
                if ($duid == "") {
                    $duid = $fromdocidvalues->getFamilyParameterValue($aid);
                }
                if ($duid != "") {
                    $duid = str_replace("<BR>", "\n", $duid); // docid multiple
                    $tduid = \Doc::rawValueToArray($duid);
                    foreach ($tduid as $duid) {
                        if ($duid > 0) {
                            $docu = DocManager::getRawDocument(intval($duid)); // not for idoc list for the moment
                            $greenUid[$docu["us_whatid"] . $v["acl"]] = array(
                                "uid" => $docu["us_whatid"],
                                "acl" => $v["acl"]
                            );
                            //print "<br>$aid:$duid:".$docu["us_whatid"];
                        }
                    }
                }
            }
        }

        $pe = new \DocPermExt($this->document->dbaccess);
        $pe->docid = $this->document->id;
        foreach ($greenUid as $ku => $uid) {
            // add right in case of multiple use of the same user : possible in dynamic profile
            $pe->userid = $uid["uid"];
            $pe->acl = $uid["acl"];
            $err .= $pe->add();
        }

        return $err;
    }

    /**
     * modify control for a specific user
     *
     * @param int    $uid           user identifier
     * @param string $aclname       name of the acl (edit, view,...)
     * @param bool   $deletecontrol set true if want delete a control
     *
     * @return string error message (empty if no errors)
     * @throws \Dcp\Core\Exception
     */
    protected function modifyControl($uid, $aclname, $deletecontrol = false)
    {
        $err = '';
        if (!isset(self::$dacls[$aclname])) {
            return sprintf(_("unknow privilege %s"), $aclname);
        }
        $pos = self::$dacls[$aclname]["pos"];
        $uid = $this->getUid($uid);

        if ($uid > 0) {
            $perm = new \DocPerm($this->document->dbaccess, array(
                $this->document->id,
                $uid
            ));
            if ($deletecontrol) {
                $perm->UnsetControlP($pos);
            } else {
                $perm->SetControlP($pos);
            }
            if ($perm->isAffected()) {
                $err = $perm->modify();
            } else {
                $err = $perm->Add();
            }
        }
        $this->setViewProfil();
        return $err;
    }

    /**
     * add control for a specific user
     *
     * @param int    $uid     user identifier
     * @param string $aclname name of the acl (edit, view,...)
     *
     * @return string error message (empty if no errors)
     * @throws \Dcp\Core\Exception
     */
    public function addControl($uid, $aclname)
    {
        if ($this->isExtendedAcl($aclname)) {
            return $this->modifyExtendedControl($uid, $aclname, false);
        } else {
            return $this->modifyControl($uid, $aclname, false);
        }
    }

    public function isExtendedAcl($aclname)
    {
        return (!empty($this->document->extendedAcls[$aclname]));
    }

    /**
     * suppress control for a specific user
     *
     * is not a negative control
     *
     * @param int    $uid     user identifier
     * @param string $aclname name of the acl (edit, view,...)
     *
     * @return string error message (empty if no errors)
     * @throws \Dcp\Core\Exception
     */
    public function delControl($uid, $aclname)
    {
        if ($this->isExtendedAcl($aclname)) {
            return $this->modifyExtendedControl($uid, $aclname, true);
        } else {
            return $this->ModifyControl($uid, $aclname, true);
        }
    }

    /**
     * set control view for document
     *
     * @param int $cvid identifier for control view document
     *
     */
    public function setCvid($cvid)
    {
        if ($cvid && !is_numeric($cvid)) {
            $cvid = DocManager::getIdFromName($cvid);
        }
        $this->document->cvid = $cvid;
    }

    /**
     * use to know if current user has access privilege
     *
     * @param int    $docid   profil identifier
     * @param string $aclname name of the acl (edit, view,...)
     * @param bool   $strict  set to true to not use substitute
     * @return string if empty access granted else error message
     */
    public function controlId($docid, $aclname, $strict = false)
    {
        if ($this->isExtendedAcl($aclname)) {
            return $this->controlExtId($docid, $aclname, $strict);
        } else {
            if ($strict) {
                $uperm = \DocPerm::getUperm($docid, ContextManager::getCurrentUser()->id, $strict);
                return $this->controlUp($uperm, $aclname);
            } else {
                if ($this->document->profid == $docid) {
                    if (!isset($this->document->uperm)) {
                        $this->document->uperm = \DocPerm::getUperm($docid, ContextManager::getCurrentUser()->id);
                    }
                    return $this->controlUp($this->document->uperm, $aclname);
                } else {
                    $uperm = \DocPerm::getUperm($docid, ContextManager::getCurrentUser()->id);
                    return $this->controlUp($uperm, $aclname);
                }
            }
        }
    }

    /**
     * use to know if current user has access privilege
     *
     * @param int    $docid   profil identifier
     * @param string $aclname name of the acl (edit, view,...)
     * @param bool   $strict  set to true to not use substitute
     * @return string if empty access granted else error message
     */
    public function controlExtId($docid, $aclname, $strict = false)
    {
        $err = '';
        $grant = \DocPermExt::isGranted(ContextManager::getCurrentUser()->id, $aclname, $docid, $strict);

        if (!$grant) {
            $err = sprintf(_("no privilege %s for %s [%d]"), $aclname, $this->document->title, $this->document->id);
        }
        return $err;
    }

    /**
     * use to know if current user has access privilege
     *
     * @param int    $docid   profil identifier
     * @param int    $uid     user identifier
     * @param string $aclname name of the acl (edit, view,...)
     * @return string if empty access granted else error message
     */
    public function controlUserId($docid, $uid, $aclname)
    {
        $perm = new \DocPerm($this->document->dbaccess, array(
            $docid,
            $uid
        ));

        if ($perm->isAffected()) {
            $uperm = $perm->uperm;
        } else {
            $uperm = $perm->getUperm($docid, $uid);
        }

        return $this->controlUp($uperm, $aclname);
    }

    /**
     * use to know if permission has access privilege
     *
     * @param int    $uperm   permission mask
     * @param string $aclname name of the acl (edit, view,...)
     * @return string if empty access granted else error message
     */
    public function controlUp($uperm, $aclname)
    {
        $has = self::hasControl($uperm, $aclname);
        if ($has === true) {
            return "";
        } elseif ($has === false) {
            return sprintf(_("no privilege %s for %s [%d]"), $aclname, $this->document->title, $this->document->id);
        } else {
            return sprintf(_("unknow privilege %s"), $aclname);
        }
    }

    public static function hasControl($uperm, $aclname)
    {
        if (isset(self::$dacls[$aclname])) {
            return (($uperm & (1 << (self::$dacls[$aclname]["pos"]))) != 0) ? true : false;
        } else {
            return null;
        }
    }

    /**
     * modify control for a specific user
     *
     * @param string $uName         user identifier
     * @param string $aclname       name of the acl (edit, view,...)
     * @param bool   $deletecontrol set true if want delete a control
     *
     * @return string error message (empty if no errors)
     * @throws \Dcp\Core\Exception
     */
    private function modifyExtendedControl($uName, $aclname, $deletecontrol = false)
    {
        $err = '';
        $uid = $this->getUid($uName);
        $eacl = new \DocPermExt($this->document->dbaccess, array(
            $this->document->id,
            $uid,
            $aclname
        ));
        if ($deletecontrol) {
            if ($eacl->isAffected()) {
                $err = $eacl->Delete();
            }
        } else {
            // add extended acl
            if (!$eacl->isAffected()) {
                $eacl->userid = $uid;
                $eacl->acl = $aclname;
                $eacl->docid = $this->document->id;
                $err = $eacl->add();
            }
        }
        return $err;
    }

    /**
     * If reference is not a number => try to get user id from document logical name
     * if not found try to get user id from attribute
     *
     * @param string $accountReference
     *
     * @return string
     * @throws \Dcp\Core\Exception
     */
    private function getUid($accountReference)
    {
        // Test logical name
        if (!is_numeric($accountReference) && strpos($accountReference, \ImportDocumentDescription::attributePrefix) !== 0) {
            if (strpos($accountReference, \ImportDocumentDescription::documentPrefix) === 0) {
                $accountReference = substr($accountReference, strlen(\ImportDocumentDescription::documentPrefix));
            }
            $uiid = DocManager::getIdFromName($accountReference);
            if ($uiid) {
                $udoc = DocManager::getDocument($uiid);
                if ($udoc && $udoc->isAlive()) {
                    $accountReference = $udoc->getRawValue("us_whatid");
                }
            }
        }
        // Test  account attribute reference
        if (!is_numeric($accountReference) && strpos($accountReference, \ImportDocumentDescription::documentPrefix) !== 0) {
            if (strpos($accountReference, \ImportDocumentDescription::attributePrefix) === 0) {
                $accountReference = substr($accountReference, strlen(\ImportDocumentDescription::attributePrefix));
            }
            // logical name
            $vg = new \VGroup($this->document->dbaccess, strtolower($accountReference));
            if (!$vg->isAffected()) {
                // try to add
                $ddoc = DocManager::getFamily($this->document->getRawValue("dpdoc_famid"));
                $oa = $ddoc->getAttribute($accountReference);
                if (($oa->type == "docid") || ($oa->type == "account")) {
                    $vg->id = $oa->id;
                    $vg->Add();
                    $accountReference = $vg->num;
                }
                //else : $err = sprintf(_("unknow virtual user identificateur %s") , $uid);
            } else {
                $accountReference = $vg->num;
            }
        }
        return $accountReference;
    }
}

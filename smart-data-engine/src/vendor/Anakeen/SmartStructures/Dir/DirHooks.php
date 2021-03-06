<?php /** @noinspection PhpUnusedParameterInspection */

/**
 * Folder  definition
 *
 */

namespace Anakeen\SmartStructures\Dir;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\SmartHooks;

/**
 * Folder document Class
 *
 */
class DirHooks extends \Anakeen\SmartStructures\Profiles\PDirHooks
{
    /** @var string before add new Smart Element into Folder */
    const PREINSERT = "preInsert";
    /** @var string after adding new Smart Element into Folder */
    const POSTINSERT = "postInsert";
    /** @var string before remove Smart Element from Folder */
    const PREREMOVE = "preRemove";
    /** @var string after removing Smart Element from Folder */
    const POSTREMOVE = "postRemove";
    /** @var string before add several Smart Element into Folder */
    const PREINSERTMULTIPLE = "preInsertMultiple";
    /** @var string after adding several Smart Element into Folder */
    const POSTINSERTMULTIPLE = "postInsertMultiple";

    public $defDoctype = 'D';
    private $authfam = false;
    private $norestrict = false;


    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            $allbut = $this->getRawValue("FLD_ALLBUT");
            $tfamid = $this->getMultipleRawValues("FLD_FAMIDS");

            if (($allbut === "0") && ((count($tfamid) === 0) || ((count($tfamid) === 1) && (empty($tfamid[0]))))) {
                $this->clearValue("FLD_ALLBUT");
                $this->modify();
            }
            return "";
        })->addListener(SmartHooks::POSTAFFECT, function () {
            $this->authfam = false;
            $this->norestrict = false;
        });
    }

    /**
     * clear containt of this folder
     *
     * @return string error message, if no error empty string
     */
    public function clear()
    {
        if ($this->isLocked(true)) {
            return sprintf(_("folder is locked. Cannot containt modification"));
        }
        // need this privilege
        $err = $this->controlAccess("modify");
        if ($err != "") {
            return $err;
        }
        $this->addHistoryEntry(_("Folder cleared"));
        $this->addLog('clearcontent');
        $err = $this->query(sprintf("delete from fld where dirid=%d", $this->initid));
        $this->updateFldRelations();
        return $err;
    }

    /**
     * Test if current user can add or delete document in this folder
     *
     * @return string error message, if no error empty string
     */
    public function canModify()
    {
        if ($this->isLocked(true)) {
            return sprintf(_("folder is locked. Cannot containt modification"));
        }
        // need this privilege
        $err = $this->controlAccess("modify");
        return $err;
    }


    /**
     * add a document reference in this folder
     *
     * if mode is latest the user always see latest revision
     * if mode is static the user see the revision which has been inserted
     *
     * @param int $docid document ident for the insertion
     * @param string $mode latest|static
     * @param bool $noprepost if true if the virtuals methods {@link DirHooks::PREINSERT()} and {@link DirHooks::POSTINSERT()} are not called
     * @param bool $forcerestrict if true don't test restriction (if have)
     * @param bool $nocontrol if true no test acl "modify"
     *
     * @return string error message, if no error empty string
     * @api add a document reference in this folder
     *
     */
    public function insertDocument(
        $docid,
        $mode = "latest",
        $noprepost = false,
        $forcerestrict = false,
        $nocontrol = false
    ) {
        $err = '';
        if (!$nocontrol) {
            $err = $this->canModify();
            if ($err != "") {
                return $err;
            }
        }

        $doc = SEManager::getDocument($docid);

        if (!$doc) {
            return sprintf(_("Cannot add in %s folder, doc id (%d) unknown"), $this->title, $docid);
        }
        $qf = new \QueryDir($this->dbaccess);
        switch ($mode) {
            case "static":
                $qf->qtype = 'F'; // fixed document
                $qf->childid = $doc->id; // initial doc
                break;

            case "latest":
            default:
                $qf->qtype = 'S'; // single user query
                $qf->childid = $doc->initid; // initial doc
                break;
        }
        $qf->dirid = $this->initid; // the reference folder is the initial id
        $qf->query = "";
        if (!$qf->exists()) {
            // use pre virtual method
            if (!$noprepost) {
                $err = $this->getHooks()->trigger(DirHooks::PREINSERT, $doc->id);
            }
            if ($err != "") {
                return $err;
            }
            // verify if doc family is autorized
            if ((!$forcerestrict) && (!$this->isAuthorized($doc->fromid))) {
                return sprintf(
                    _("Cannot add %s in %s folder, restriction set to add this kind of document"),
                    $doc->title,
                    $this->title
                );
            }

            $err = $qf->add();
            if ($err == "") {
                $this->addHistoryEntry(sprintf(_("Document %s inserted"), $doc->title));
                $doc->addHistoryEntry(sprintf(
                    _("Document inserted in %s folder"),
                    $this->title,
                    \DocHisto::INFO,
                    "MOVEADD"
                ));

                $this->addLog('addcontent', array(
                    "insert" => array(
                        "id" => $doc->id,
                        "title" => $doc->title
                    )
                ));
                // add default folder privilege to the doc
                if (intval($doc->profid) === 0) { // only if no privilege yet
                    switch ($doc->defProfFamId) {
                        case FAM_ACCESSDOC:
                            $profid = $this->getRawValue("FLD_PDOCID", 0);
                            if ($profid > 0) {
                                $doc->accessControl()->setProfil($profid);
                                $err = $doc->modify(true, array(
                                    "profid",
                                    "dprofid"
                                ), true);
                                if ($err == "") {
                                    $doc->addHistoryEntry(sprintf(
                                        _("Change profil to default document profil : %d"),
                                        $profid
                                    ));
                                }
                            }
                            break;

                        case FAM_ACCESSDIR:
                            $profid = $this->getRawValue("FLD_PDIRID", 0);
                            if ($profid > 0) {
                                $doc->accessControl()->setProfil($profid);
                                // copy default privilege if not set
                                if ($doc->getRawValue("FLD_PDIRID") == "") {
                                    $doc->setValue("FLD_PDIRID", $this->getRawValue("FLD_PDIRID"));
                                    $doc->setValue("FLD_PDIR", $this->getRawValue("FLD_PDIR"));
                                }
                                if ($doc->getRawValue("FLD_PDOCID") == "") {
                                    $doc->setValue("FLD_PDOCID", $this->getRawValue("FLD_PDOCID"));
                                    $doc->setValue("FLD_PDOC", $this->getRawValue("FLD_PDOC"));
                                }
                                $err = $doc->modify();
                                if ($err == "") {
                                    $doc->addHistoryEntry(sprintf(
                                        _("Change profil to default subfolder profil : %d"),
                                        $profid
                                    ));
                                }
                            }
                            break;
                    }
                }
            }
            if ($doc->prelid == "") {
                $doc->prelid = $this->initid;
                $doc->modify(true, array(
                    "prelid"
                ), true);
            }

            if ($err == "") {
                $this->updateFldRelations();
                // use post virtual method
                if (!$noprepost) {
                    $err = $this->getHooks()->trigger(DirHooks::POSTINSERT, $doc->id);
                }
            }
        }
        return $err;
    }
    // --------------------------------------------------------------------


    /**
     * insert multiple document reference in this folder
     *
     * if mode is latest the user always see latest revision
     * if mode is static the user see the revision which has been inserted
     *
     * @param array $tdocs documents  for the insertion
     * @param string $mode latest|static static is not implemented yet
     * @param boolean $noprepost not call preInsert and postInsert method (default if false)
     * @param array $tinserted
     * @param array $twarning
     * @param array $info
     *
     * @return string error message, if no error empty string
     * @api insert multiple document reference in this folder
     *
     */
    public function insertMultipleDocuments(
        array $tdocs,
        $mode = "latest",
        $noprepost = false,
        &$tinserted = array(),
        &$twarning = array(),
        &$info = array()
    ) {
        $insertError = array();
        if (!$noprepost) {
            $tdocids = array();
            $isStatic = ($mode === "static");
            foreach ($tdocs as $v) {
                if (!empty($v["initid"])) {
                    $tdocids[] = ($isStatic) ? $v["id"] : $v["initid"];
                }
            }
            $err = $this->getHooks()->trigger(DirHooks::PREINSERTMULTIPLE, $tdocids);
            $info = array(
                "error" => $err,
                "preInsertMultipleDocuments" => $err,
                "postInsertDocument" => array(),
                "postInsertMultipleDocuments" => '',
                "preInsertDocument" => array(),
                "modifyError" => ""
            );
            if ($err != "") {
                return $err;
            }
        }
        $err = $this->canModify();
        if ($err != "") {
            $info = array(
                "error" => $err,
                "preInsertMultipleDocuments" => "",
                "postInsertDocument" => array(),
                "postInsertMultipleDocuments" => '',
                "preInsertDocument" => array(),
                "modifyError" => $err
            );
            return $err;
        }
        $tAddeddocids = array();
        // verify if doc family is autorized
        $qf = new \QueryDir($this->dbaccess);
        $tmsg = array();
        foreach ($tdocs as $tdoc) {
            if (!$this->isAuthorized($tdoc["fromid"])) {
                $warn = sprintf(
                    _("Cannot add %s in %s folder, restriction set to add this kind of document"),
                    $tdoc["title"],
                    $this->title
                );
                $twarning[$tdoc['id']] = $warn;
            } else {
                switch ($mode) {
                    case "static":
                        $qf->qtype = 'F'; // fixed document
                        $docid = $tdoc["id"];
                        $qf->childid = $tdoc["id"]; // initial doc
                        break;

                    case "latest":
                    default:
                        $qf->qtype = 'S'; // single user query
                        $docid = $tdoc["initid"];
                        $qf->childid = $tdoc["initid"]; // initial doc
                        break;
                }

                $insertOne = "";
                $qf->dirid = $this->initid; // the reference folder is the initial id
                $qf->query = "";
                // use post virtual method
                if (!$noprepost) {
                    $multipleMode = true;
                    $insertOne = $this->getHooks()->trigger(DirHooks::PREINSERT, $tdoc["initid"], $multipleMode);
                }

                if ($insertOne == "") {
                    $insertOne = $qf->add(false, true);
                    if ($insertOne == "") {
                        $this->addHistoryEntry(
                            sprintf(_("Document %s inserted"), $tdoc["title"]),
                            \DocHisto::INFO,
                            "MODCONTAIN"
                        );

                        $this->addLog('addcontent', array(
                            "insert" => array(
                                "id" => $tdoc["id"],
                                "title" => $tdoc["title"]
                            )
                        ));
                        $tAddeddocids[] = $docid;
                        $tinserted[$docid] = sprintf(_("Document %s inserted"), $tdoc["title"]);
                        // use post virtual method
                        if (!$noprepost) {
                            $multipleMode = true;
                            $tmsg[$docid] = $this->getHooks()->trigger(
                                DirHooks::POSTINSERT,
                                $tdoc["initid"],
                                $multipleMode
                            );
                        }
                    }
                } else {
                    $twarning[$docid] = $insertOne;
                }
                $insertError[$docid] = $insertOne;
            }
        }
        // use post virtual method
        $msg = '';
        if (!$noprepost) {
            $this->updateFldRelations();
            $msg = $this->getHooks()->trigger(DirHooks::POSTINSERTMULTIPLE, $tAddeddocids);
            $err .= $msg;
        }
        // integrate pre insert errors
        foreach ($insertError as $oneError) {
            if ($oneError) {
                $err .= ($err) ? ', ' : '';
                $err .= $oneError;
            }
        }
        // integrate postInsert Error
        foreach ($tmsg as $oneError) {
            if ($oneError) {
                $err .= ($err) ? ', ' : '';
                $err .= $oneError;
            }
        }
        $info = array(
            "error" => $err,
            "preInsertMultipleDocuments" => '',
            "postInsertDocument" => $tmsg,
            "postInsertMultipleDocuments" => $msg,
            "preInsertDocument" => $insertError,
            "modifyError" => ""
        );
        return $err;
    }

    /**
     * insert multiple static document reference in this folder
     * be carreful : not verify restriction folders
     * to be use when many include (verification constraint must ne set before by caller)
     *
     * @param array $tdocids identifier documents  for the insertion
     *
     * @return string error message, if no error empty string
     */
    public function quickInsertMSDocId($tdocids)
    {
        $err = $this->canModify();
        if ($err != "") {
            return $err;
        }
        $qf = new \QueryDir($this->dbaccess);
        $qf->qtype = 'S'; // single user query
        $qf->dirid = $this->initid; // the reference folder is the initial id
        $qf->query = "";
        foreach ($tdocids as $docid) {
            $tcopy[$docid]["childid"] = $docid;
        }

        $err = $qf->adds($tcopy, true);
        $this->updateFldRelations();

        return $err;
    }

    /**
     * insert all static document which are included in $docid in this folder
     * be carreful : not verify restriction folders
     * to be use when many include (verification constraint must ne set before by caller)
     *
     * @param int $docid identifier document  for the insertion  (must be initial id)
     *
     * @return string error message, if no error empty string
     */
    public function insertFolder($docid)
    {
        if (!is_numeric($docid)) {
            return sprintf(_("Dir::insertFolder identifier [%s] must be numeric"), $docid);
        }
        if ($this->isLocked(true)) {
            return sprintf(_("folder is locked. Cannot containt modification"));
        }
        // need this privilege
        $err = $this->controlAccess("modify");
        if ($err != "") {
            return $err;
        }

        $err = $this->query(sprintf(
            "insert INTO fld (select %d,query,childid,qtype from fld where dirid=%d);",
            $this->initid,
            $docid
        ));

        $this->updateFldRelations();
        return $err;
    }

    // --------------------------------------------------------------------
    public function getQids($docid)
    {
        // return array of queries id includes in a directory
        // --------------------------------------------------------------------
        $tableid = array();

        $doc = SEManager::getDocument($docid);
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \QueryDir::class);
        $query->AddQuery("dirid=" . $this->id);
        $query->AddQuery("((childid=$docid) and (qtype='F')) OR ((childid={$doc->initid}) and (qtype='S'))");
        $tableq = $query->Query();

        if ($query->nb > 0) {
            foreach ($tableq as $k => $v) {
                $tableid[$k] = $v->id;
            }
            unset($tableq);
        }

        return ($tableid);
    }
    // --------------------------------------------------------------------


    /**
     * delete a document reference from this folder
     *
     * @param int $docid document ident for the deletion
     * @param bool $noprepost if true then the virtuals methods {@link DirHooks::PREREMOVE} and {@link DirHooks::POSTREMOVE} are not called
     * @param bool $nocontrol if true no test acl "modify"
     *
     * @return string error message, if no error empty string
     * @api remove a document reference from this folder
     *
     */
    public function removeDocument($docid, $noprepost = false, $nocontrol = false)
    {
        $err = '';
        if (!$nocontrol) {
            $err = $this->canModify();
            if ($err != "") {
                return $err;
            }
        }
        // use pre virtual method
        if (!$noprepost) {
            $err = $this->getHooks()->trigger(DirHooks::PREREMOVE, $docid);
        }
        if ($err != "") {
            return $err;
        }

        $doc = SEManager::getDocument($docid);
        $docid = $doc->initid;

        if ($err != "") {
            return $err;
        }
        // search original query
        $qf = new \QueryDir($this->dbaccess, array(
            $this->initid,
            $docid
        ));
        if (!($qf->isAffected())) {
            $err = sprintf(
                _("cannot delete link : initial query not found for doc %d in folder %d"),
                $docid,
                $this->initid
            );
        }

        if ($err != "") {
            return $err;
        }

        if ($qf->qtype == "M") {
            $err = sprintf(
                _("cannot delete link for doc %d in folder %d : the document comes from a user query. Delete initial query if you want delete this document"),
                $docid,
                $this->initid
            );
        }

        if ($err != "") {
            return $err;
        }
        $qf->Delete();

        if ($doc->prelid == $this->initid) {
            $doc->prelid = "";
            $doc->modify(true, array(
                "prelid"
            ), true);
        }

        $this->addLog('delcontent', array(
            "insert" => array(
                "id" => $doc->id,
                "title" => $doc->title
            )
        ));
        $this->addHistoryEntry(sprintf(_("Document %s umounted"), $doc->title), \DocHisto::INFO, "MODCONTAIN");
        $doc->addHistoryEntry(sprintf(
            _("Document unlinked of %s folder"),
            $this->title,
            \DocHisto::INFO,
            "MOVEUNLINK"
        ));
        // use post virtual method
        if (!$noprepost) {
            $this->updateFldRelations();
            $err = $this->getHooks()->trigger(DirHooks::POSTREMOVE, $docid);
        }

        return $err;
    }

    /**
     * move a document from me to a folder
     *
     * @param integer $docid the document identifier to move
     * @param integer $movetoid target destination
     *
     * @return string error message (empty if null)
     */
    public function moveDocument($docid, $movetoid)
    {
        $err = $this->canModify();
        if ($err == "") {
            $fromtoid = $this->initid;
            /** @var \Anakeen\SmartStructures\Dir\DirHooks $da */
            $da = SEManager::getDocument($movetoid);
            if ($da && $da->isAlive()) {
                if (method_exists($da, "insertDocument")) {
                    $err = $da->insertDocument($docid);
                    if ($err == "") {
                        if (($fromtoid) && ($fromtoid != $movetoid)) {
                            if ($this->isAlive()) {
                                if (method_exists($this, "removeDocument")) {
                                    $err = $this->removeDocument($docid);
                                    if ($err == "") {
                                        $doc = SEManager::getDocument($docid, true);
                                        if ($doc && $doc->isAlive()) {
                                            $doc->prelid = $da->initid;
                                            $err = $doc->modify(true, array(
                                                "prelid"
                                            ), true);
                                        }
                                    }
                                } else {
                                    $err = sprintf(_("document %s is not a folder"), $this->getTitle());
                                }
                            }
                        } else {
                            if ($err == "") {
                                $doc = SEManager::getDocument($docid, true);
                                if ($doc && $doc->isAlive()) {
                                    $doc->prelid = $da->initid;
                                    $err = $doc->modify(true, array(
                                        "prelid"
                                    ), true);
                                }
                            }
                        }
                    }
                } else {
                    $err = sprintf(_("document %s is not a folder"), $da->getTitle());
                }
            }
        }
        return $err;
    }


    public function hasNoRestriction()
    {
        if (!$this->authfam) {
            $this->getAuthorizedFamilies();
        }
        return ($this->norestrict);
    }

    /**
     * return families that can be use in insertion
     *
     * @param int $classid : restrict for same usefor families
     * @param bool $verifyCreate set to true if you want to get only families the user can create (icreate acl)
     *
     * @return array
     */
    public function getAuthorizedFamilies($classid = 0, $verifyCreate = false)
    {
        if (!$this->authfam) {
            $tfamid = $this->getMultipleRawValues("FLD_FAMIDS");
            $tsubfam = $this->getMultipleRawValues("FLD_SUBFAM");
            $allbut = $this->getRawValue("FLD_ALLBUT");

            if (($allbut != "1") && ((count($tfamid) === 0) || ((count($tfamid) == 1) && (empty($tfamid[0]))))) {
                $this->norestrict = true;
                return array();
            }

            $this->norestrict = false;
            $tclassdoc = array();
            if ($allbut != "1") {
                $tallfam = DirLib::getClassesDoc(
                    $this->dbaccess,
                    ContextManager::getCurrentUser()->id,
                    $classid,
                    "TABLE"
                );

                foreach ($tallfam as $cdoc) {
                    $tclassdoc[$cdoc["id"]] = $cdoc;
                    //    $tclassdoc += $this->GetChildFam($cdoc["id"]);
                }
                // suppress undesirable families
                reset($tfamid);
                foreach ($tfamid as $k => $famid) {
                    unset($tclassdoc[intval($famid)]);
                    if ($tsubfam[$k] != "yes") {
                        $tnofam = $this->GetChildFam(intval($famid));
                        foreach ($tnofam as $ka => $va) {
                            unset($tclassdoc[intval($ka)]);
                        }
                    }
                }
            } else {
                //add families
                foreach ($tfamid as $k => $famid) {
                    $tfdoc = SEManager::getRawDocument($famid);
                    if ($tfdoc && ((!$verifyCreate) || controlTdoc($tfdoc, 'icreate'))) {
                        $tclassdoc[intval($famid)] = array(
                            "id" => ($tsubfam[$k] == "no") ? (-intval($famid)) : intval($famid),
                            "title" => $this->getTitle($famid)
                        );
                    }
                    if ($tsubfam[$k] != "no") {
                        $tclassdoc += $this->GetChildFam(intval($famid));
                    }
                }
            }
            $this->authfam = $tclassdoc;
        }
        return $this->authfam;
    }

    /**
     * return families that can be use in insertion
     *
     * @param int $classid : restrict for same usefor families
     *
     * @return bool
     */
    public function isAuthorized($classid)
    {
        if (!$this->authfam) {
            $this->getAuthorizedFamilies();
        }
        if ($this->norestrict) {
            return true;
        }
        if (!$classid) {
            return true;
        }

        if (isset($this->authfam[$classid])) {
            return true;
        }

        return false;
    }

    /**
     * return document includes in folder
     *
     * @param bool $controlview if false all document are returned else only visible for current user  document are return
     * @param array $filter to add list sql filter for selected document
     * @param int|string $famid family identifier to restrict search
     * @param string $qtype type os result TABLE|LIST|ITEM
     * @param string $trash
     *
     * @return array array of document array
     */
    public function getContent($controlview = true, array $filter = array(), $famid = "", $qtype = "TABLE", $trash = "")
    {
        if ($controlview) {
            $uid = ContextManager::getCurrentUser()->id;
        } else {
            $uid = 1;
        }
        $tdoc = \Anakeen\SmartStructures\Dir\DirLib::internalGetDocCollection(
            $this->dbaccess,
            $this->initid,
            0,
            "ALL",
            $filter,
            $uid,
            $qtype,
            $famid,
            false,
            "title",
            true,
            $trash
        );
        return $tdoc;
    }

    /**
     * update folder relations
     */
    public function updateFldRelations()
    {
        return; //inhibit folder relation (too slow for great folder)
    }

    /**
     * return number of item in the static folder
     *
     * @param bool $onlyprimary set to true if you wnat only document linked by primary relation
     *
     * @return int -1 if it is not a static folder
     */
    public function count($onlyprimary = false)
    {
        if ($onlyprimary) {
            $tdoc = $this->getPrimaryChild();
            if ($tdoc) {
                return count($tdoc);
            }
        } else {
            $q = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \QueryDir::class);
            $tv = $q->Query(0, 0, "TABLE", "select childid from fld where dirid=" . $this->initid . " and qtype='S'");
            if (is_array($tv)) {
                return count($tv);
            }
        }
        return -1;
    }

    /**
     * return array of document identificators included in folder
     *
     * @return array of initial identificators (initid)
     */
    public function getContentInitid()
    {
        $query = sprintf("select childid from fld where dirid=%d and qtype='S'", $this->initid);
        $initids = array();
        DbManager::query($query, $initids, true, false);

        return $initids;
    }

    /**
     * get  document which primary relation is this folder
     *
     *
     * @return array of doc  (array document)
     */
    public function getPrimaryChild()
    {
        $filter[] = "prelid=" . $this->initid;
        return $this->getContent(true, $filter);
    }


    /**
     * delete all document which primary relation is the folder (recurively)
     * different of {@link Dir::Clear()}
     * all document are put in the trash (zombie mode)
     *
     * @return array of possible errors. Empty array means no errors
     */
    public function deleteItems()
    {
        $filter[] = "prelid=" . $this->initid;
        $lpdoc = $this->getContent(false, $filter, "", "ITEM");

        $terr = array();
        while ($doc = getNextDoc($this->dbaccess, $lpdoc)) {
            $coulddelete = true;
            if ($doc->doctype == 'D') {
                /** @var \Anakeen\SmartStructures\Dir\DirHooks $doc */
                $terr = array_merge($terr, $doc->deleteItems());
                foreach ($terr as $err) {
                    if ($err != "") {
                        $coulddelete = false;
                    }
                }
            }
            if ($coulddelete) {
                $terr[$doc->id] = $doc->delete();
            }
        }
        $this->addHistoryEntry(_("Folder cleared"), \DocHisto::INFO, "MODCONTAIN");
        return $terr;
    }

    /**
     * copy (clone) all documents which primary relation is the folder (recurively)
     * the others documents are just linked
     * all document are put in $indirid folder id
     *
     * @param int $indirid the folder where put the copies
     *
     * @return array of possible errors. Empty array means no errors
     */
    public function copyItems($indirid)
    {
        $filter = array();
        $lpdoc = $this->getContent(false, $filter, "", "ITEM");

        $terr = array();
        $fld = SEManager::getDocument($indirid);
        if ($fld->doctype == 'D') {
            /** @var \Anakeen\SmartStructures\Dir\DirHooks $fld */
            $err = $fld->control("modify");
            if ($err == "") {
                while ($doc = getNextDoc($this->dbaccess, $lpdoc)) {
                    if ($doc->prelid == $this->initid) {
                        // copy
                        $copy = $doc->duplicate();
                        if (is_object($copy)) {
                            $fld->insertDocument($copy->initid);
                            if ($doc->doctype == 'D') {
                                /** @var \Anakeen\SmartStructures\Dir\DirHooks $doc */
                                $terr = array_merge($terr, $doc->copyItems($copy->id));
                            }
                        }
                    } else {
                        // link
                        $fld->insertDocument($doc->initid);
                    }
                }
            }
        }
        return $terr;
    }

    /**
     * delete the folder and its containt
     * different of {@link Dir::Clear()}
     * all document are put in the trash (zombie mode)
     *
     * @return string error message, if no error empty string
     */
    public function deleteRecursive()
    {
        $err = $this->controlDeleteAccess(); // test before try recursive deletion
        if ($err != "") {
            return $err;
        }
        $coulddelete = true;
        $terr = $this->deleteItems();
        $err = "";
        foreach ($terr as $err1) {
            if ($err1 != "") {
                $coulddelete = false;
                $err .= "\n$err1";
            }
        }
        if ($coulddelete) {
            $err = $this->delete();
        }
        return $err;
    }

    /**
     * restore all document which primary relation is the folder (recurively)
     *
     *
     * @return array an array of errors
     */
    public function reviveItems()
    {
        $filter[] = "prelid=" . $this->initid;
        $lpdoc = $this->getContent(true, $filter, "", "ITEM", "only");
        $terr = array();
        while ($doc = getNextDoc($this->dbaccess, $lpdoc)) {
            if ($doc->defDoctype == 'D') {
                /**
                 * @var \Anakeen\SmartStructures\Dir\DirHooks $doc
                 */
                $terr = array_merge($terr, $doc->reviveItems());
            }
            $terr[$doc->id] = $doc->undelete();
        }
        return $terr;
    }
}

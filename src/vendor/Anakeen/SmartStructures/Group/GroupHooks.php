<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * Specials methods for GROUP family
 *
 */

namespace Anakeen\SmartStructures\Group;

use Anakeen\SmartHooks;

class GroupHooks extends \SmartStructure\Dir
{

    /**
     * reconstruct mail group & recompute parent group
     *
     * @return void
     */
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->removeListeners(SmartHooks::POSTSTORE);
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            //* reconstruct mail group & recompute parent group
            $err = $this->setGroupMail();
            $this->refreshParentGroup();
            return $err;
        });
    }

    /**
     * recompute only parent group
     *
     * @apiExpose
     *
     * @return string error message, if no error empty string
     */
    public function refreshGroup()
    {
        global $refreshedGrpId; // to avoid inifinitive loop recursion
        $err = "";
        if (!isset($refreshedGrpId[$this->id])) {
            $err = $this->setGroupMail();
            $err .= $this->modify();
            $this->specPostInsert();
            $refreshedGrpId[$this->id] = true;
        }
        return $err;
    }

    /**
     * update groups table in USER database
     *
     * @param      $docid
     * @param bool $multiple
     *
     * @return string error message
     */
    public function postInsertDocument($docid, $multiple = false)
    {
        if ($multiple == false) {
            $this->setGroupMail();
            $this->refreshMembers();
            $this->specPostInsert();
        }
        return "";
    }

    /**
     * update groups table in USER database
     *
     * @param $tdocid
     *
     * @return string error message
     */
    public function postInsertMultipleDocuments($tdocid)
    {
        $this->setGroupMail();
        $this->refreshMembers();
        $this->specPostInsert();
        return "";
    }

    /**
     * update groups table in USER database before suppress
     *
     * @param      $docid
     * @param bool $multiple
     *
     * @return string error message
     */
    public function postRemoveDocument($docid, $multiple = false)
    {
        $this->setGroupMail();
        $this->refreshMembers();
        $this->specPostInsert();
        return "";
    }

    /**
     * special method for child classes
     * call after insert user in group
     *
     * @return string error message
     */
    public function specPostInsert()
    {
        return "";
    }

    /**
     * compute the mail of the group
     * concatenation of each user mail and group member mail
     *
     *
     * @param bool $nomail
     *
     * @return string error message, if no error empty string
     * @throws \Anakeen\Database\Exception
     * @throws \Anakeen\Search\Exception
     */
    public function setGroupMail($nomail = false)
    {
        $err = "";
        $tmail = array();

        if (!$nomail) {
            $nomail = ($this->getRawValue("grp_hasmail") == "no");
        }
        if (!$nomail) {
            $s = new \SearchDoc($this->dbaccess);
            $s->useCollection($this->initid);
            $r = $s->search();
            foreach ($r as $account) {
                $mail = $account["us_mail"];
                if (!$mail) {
                    $account["grp_mail"];
                }
                if ($mail) {
                    $tmail[] = $mail;
                }
            }
            $gmail = implode(", ", array_unique($tmail));
            $this->SetValue("GRP_MAIL", $gmail);
        }

        if ($this->getRawValue("grp_hasmail") == "no") {
            $this->clearValue("GRP_MAIL");
        }

        return $err;
    }

    /**
     * recompute parent group and its ascendant
     *
     * @return array/array parents group list refreshed
     * @see refreshGroup()
     */
    public function refreshParentGroup()
    {

        $sqlfilters[] = sprintf("in_textlist(grp_idgroup,'%s')", $this->id);
        // $sqlfilters[]="fromid !=".getFamIdFromName($this->dbaccess,"IGROUP");
        $tgroup = \Anakeen\SmartStructures\Dir\DirLib::internalGetDocCollection(
            $this->dbaccess,
            0,
            "0",
            "ALL",
            $sqlfilters,
            1,
            "LIST",
            \Anakeen\Core\SEManager::getFamilyIdFromName("GROUP")
        );

        $tpgroup = array();
        $tidpgroup = array();
        /**
         * @var \SmartStructure\Group $v
         */
        foreach ($tgroup as $k => $v) {
            $v->refreshGroup();
            $tpgroup[] = $v->title;
            $tidpgroup[] = $v->id;
        }

        $this->SetValue("GRP_IDPGROUP", implode("\n", $tidpgroup));
        return $tgroup;
    }

    /**
     * refresh members of the group from USER database
     */
    public function refreshMembers()
    {
        // 2)groups
        $tu = \Anakeen\SmartStructures\Dir\DirLib::internalGetDocCollection($this->dbaccess, $this->initid, "0", "ALL", array(), 1, "TABLE", "GROUP");
        $tmemid = array();
        $tmem = array();
        if (count($tu) > 0) {
            foreach ($tu as $k => $v) {
                $tmemid[] = $v["id"];
                $tmem[] = $v["title"];
            }
            $this->SetValue("GRP_IDGROUP", $tmemid);
        } else {
            $this->clearValue("GRP_IDGROUP");
        }
        $this->modify();
    }

    public function refreshMailMembersOnChange()
    {
        // Recompute mail/members when the hasmail/hasmembers enum is changed
        if ($this->getOldRawValue('GRP_HASMAIL') !== false || $this->getOldRawValue('GRP_HASMEMBERS') !== false) {
            $err = $this->refreshGroup();
            if ($err != '') {
                return $err;
            }
        }
        return '';
    }
}

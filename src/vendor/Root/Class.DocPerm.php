<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Document permissions
 *
 * @author Anakeen
 * @version $Id: Class.DocPerm.php,v 1.15 2007/06/14 15:48:25 eric Exp $
 * @package FDL
 */
/**
 */

include_once("Class.DbObj.php");
/**
 * Managing permissions of documents
 * @package FDL
 *
 */
class DocPerm extends DbObj
{
    public $fields = array(
        "docid",
        "userid",
        "upacl"
    );
    
    public $id_fields = array(
        "docid",
        "userid"
    );
    public $docid;
    public $userid;
    public $upacl;
    public $uperm;
    
    public $dbtable = "docperm";
    
    public $order_by = "docid";
    
    public $sqlcreate = "
create table docperm ( 
                     docid int check (docid > 0),
                     userid int check (userid > 1),
                     upacl int  not null
                   );
create unique index idx_perm on docperm(docid, userid);";
    
    public function preSelect($tid)
    {
        if (count($tid) == 2) {
            $this->docid = $tid[0];
            $this->userid = $tid[1];
        }
    }
    
    public function preInsert()
    {
        if ($this->userid == 1) {
            return _("not perm for admin");
        }
        return '';
    }
    
    public function preUpdate()
    {
        return $this->preInsert();
    }
    /**
     * return account vector for current user
     * to be use in getaperm sql function
     * @static
     * @param int $uid user identifier
     * @param bool $strict set to true to not use substitute
     * @return string
     */
    public static function getMemberOfVector($uid = 0, $strict = false)
    {
        if ($uid == 0) {
            $user=\Anakeen\Core\ContextManager::getCurrentUser();
            if (!$user) {
                throw new \Dcp\Core\Exception("CORE0022");
            }
            if ($strict) {
                $mof = $user->getStrictMemberOf();
            } else {
                $mof = $user->getMemberOf();
            }
            $mof[] = $user->id;
        } else {
            $mof = \Anakeen\Core\Account::getUserMemberOf($uid, $strict);
            $mof[] = $uid;
        }
        return '{' . implode(',', $mof) . '}';
    }
    /**
     * @static
     * @param int $profid profil identifier
     * @param int $userid user identifier
     * @param bool $strict set to true to not use substitute
     * @return int
     */
    public static function getUperm($profid, $userid, $strict = false)
    {
        if ($userid == 1) {
            return -1;
        }
        $userMember = DocPerm::getMemberOfVector($userid, $strict);
        $sql = sprintf("select getaperm('%s',%d) as uperm", $userMember, $profid);
        \Anakeen\Core\DbManager::query($sql, $uperm, true, true);
        if ($uperm === false) {
            return 0;
        }
        
        return $uperm;
    }
    
    public static function getStrictUperm($profid, $userid)
    {
        if ($userid == 1) {
            return -1;
        }
        $userMember = DocPerm::getMemberOfVector($userid);
        $sql = sprintf("select getaperm('%s',%d) as uperm", $userMember, $profid);

        \Anakeen\Core\DbManager::query($sql, $uperm, true, true);
        if ($uperm === false) {
            return 0;
        }
        
        return $uperm;
    }
    /**
     * control access at $pos position (direct or indirect) (green or grey)
     * @param $pos
     * @return bool
     */
    public function ControlU($pos)
    {
        if ($this->uperm == 0) {
            $this->uperm = $this->getUperm($this->docid, $this->userid);
        }
        return ($this->ControlMask($this->uperm, $pos));
    }
    // --------------------------------------------------------------------
    
    /**
     * @param $pos
     * @deprecated no need now. Control process has changed
     * @return bool
     */
    public function ControlG($pos)
    {
        return false;
    }
    /**
     * control access at $pos position direct inly (green)
     * @param $pos
     * @return bool
     */
    public function ControlUp($pos)
    {
        // --------------------------------------------------------------------
        if ($this->isAffected()) {
            return ($this->ControlMask($this->upacl, $pos));
        }
        return false;
    }
    // --------------------------------------------------------------------
    public function ControlMask($acl, $pos)
    {
        return (($acl & (1 << ($pos))) != 0);
    }
    /**
     * no control for anyone
     */
    public function UnSetControl()
    {
        $this->upacl = 0;
    }
    /**
     * set positive ACL in specified position
     * @param int $pos column number (0 is the first right column)
     */
    public function SetControlP($pos)
    {
        $this->upacl = intval($this->upacl) | (1 << $pos);
    }
    /**
     * unset positive ACL in specified position
     * @param int $pos column number (0 is the first right column)
     */
    public function UnSetControlP($pos)
    {
        $this->upacl = $this->upacl & (~(1 << $pos));
    }
    public static function getPermsForDoc($docid)
    {
        $sql = sprintf("SELECT docid, userid, upacl FROM docperm WHERE docid = %d ORDER BY docid, userid, upacl", $docid);
        $res = array();
        \Anakeen\Core\DbManager::query($sql, $res, false, false);
        return $res;
    }
}

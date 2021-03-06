<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Document permissions
 *
 * @author  Anakeen
 * @version $Id: Class.DocPerm.php,v 1.15 2007/06/14 15:48:25 eric Exp $
 * @package FDL
 */
/**
 */

/**
 * Managing permissions of documents
 *
 * @package FDL
 *
 */
class DocPermExt extends DbObj
{
    public $fields
        = array(
            "docid",
            "userid",
            "acl"
        );

    public $id_fields
        = array(
            "docid",
            "userid",
            "acl"
        );
    public $docid;
    public $userid;
    public $acl;

    public $dbtable = "docpermext";

    public $order_by = "docid";

    public $sqlcreate = "
create table docpermext (
                     docid int check (docid > 0),
                     userid int check (userid > 1),
                     acl text  not null
                   );
create unique index idx_permext on docpermext(docid, userid,acl);";

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
     * @static
     *
     * @param int    $userid user identifier
     * @param string $acl    acl name to control
     * @param int    $profid profil identifier
     * @param bool   $strict set to true to not use substitute
     *
     * @return int
     */
    public static function isGranted($userid, $acl, $profid, $strict = false)
    {
        if ($userid == 1) {
            return true;
        }
        $gids = \Anakeen\Core\Account::getUserMemberOf($userid, $strict);
        $gids[] = $userid;
        $sql = sprintf("select * from docpermext where docid=%d and acl='%s' and userid in (%s)", $profid, pg_escape_string($acl), implode(',', $gids));
        \Anakeen\Core\DbManager::query($sql, $result);
        //print_r($sql);
        return (count($result) > 0);
    }

    public static function hasExtAclGrant($docid, $accountId, $aclName)
    {
        static $grants = null;
        if ($grants === null && $grants[$docid] === null) {
            \Anakeen\Core\DbManager::query(sprintf("select * from docpermext where docid=%d", $docid), $qgrants);
            $grants[$docid] = $qgrants;
        }
        foreach ($grants[$docid] as $aGrant) {
            if ($aGrant["acl"] == $aclName && $aGrant["userid"] == $accountId) {
                return 'green';
            }
        }
        $mof = \Anakeen\Core\Account::getUserMemberOf($accountId);
        if ($mof) {
            foreach ($grants[$docid] as $aGrant) {
                if ($aGrant["acl"] == $aclName && in_array($aGrant["userid"], $mof)) {
                    return 'grey';
                }
            }
        }
        return '';
    }

    public static function getPermsForDoc($docid)
    {
        $sql = sprintf("SELECT docid, userid, acl FROM docpermext WHERE docid = %d ORDER BY docid, userid, acl", $docid);
        $res = array();
        \Anakeen\Core\DbManager::query($sql, $res, false, false);
        return $res;
    }
}

<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * Function utilities to manipulate users
 *
 */

namespace Anakeen\SmartStructures\Igroup;

use Anakeen\Core\Account;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\Utils\Date;

/**
 * refresh a set of group
 * @param array $tg the groups which has been modify by insertion/deletion of user
 * @return
 */
class IgroupLib
{
    public static function refreshGroups(
        $groupIdList,
        $refresh = false,
        &$currentPath = array(),
        &$groupDepth = array()
    ) {
        /**
         * @var \Group $wg
         */
        static $wg = null;

        if (!$wg) {
            $wg = new \Group("", 2);
        } // working group;
        // Iterate over given groups list
        foreach ($groupIdList as $groupId) {
            // Detect loops in groups
            if (array_search($groupId, $currentPath)) {
                error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf(
                    "Loop detected in group with id '%s' (path=[%s])",
                    $groupId,
                    join('-', $currentPath)
                ));
                continue;
            }
            // Get direct parent groups list
            $parentGroupIdList = $wg->getParentsGroupId($groupId);
            // Compute depth of current group and recursively compute depth on parent groups
            array_push($currentPath, $groupId);
            if (isset($groupDepth[$groupId])) {
                $groupDepth[$groupId] = max($groupDepth[$groupId], count($currentPath));
            } else {
                $groupDepth[$groupId] = count($currentPath);
            }
            self::refreshGroups($parentGroupIdList, $refresh, $currentPath, $groupDepth);
            array_pop($currentPath);
        }
        // End of groups traversal
        if (count($currentPath) <= 0) {
            // We can now refresh the groups based on their ascending depth
            uasort($groupDepth, function ($a, $b) {
                return ($a - $b);
            });
            foreach ($groupDepth as $group => $depth) {
                self::refreshOneGroup($group, $refresh);
            }
        }

        return $groupIdList;
    }


    /**
     * Recompute all group mail where account is referenced
     * @param int $userid system user id
     * @throws \Anakeen\Database\Exception
     */
    public static function refreshMailGroupsOfUser(int $userid)
    {
        // Select group where grp_hasmail is yes and user is referenced
        $sql = sprintf(
            "select ug.id from users uu, users ug, family.igroup as ig " .
            "where uu.memberof @> ARRAY[ug.id] and uu.id=%d and ug.accounttype='G' and ug.fid=ig.id and ig.grp_hasmail = 'yes'",
            $userid
        );
        DbManager::query($sql, $groups, true);
        foreach ($groups as $groupId) {
            self::refreshMailGroup($groupId);
        }
    }

    /**
     * Get group mail for a specific group
     * @param int $groupId System group identifier
     * @param bool $rawFormat if true add name before email address, else only email address
     * @return string
     * @throws \Anakeen\Database\Exception
     */
    public static function getMailGroup(int $groupId, $rawFormat = false)
    {
        // Aggregate mail address of all users of the group (recursive)
        if ($rawFormat === true) {
            $sql = sprintf(
                "select string_agg(distinct mail,', ' order by mail)" .
                " from users where memberof @> '{%d}' and mail is not null and accounttype = 'U'",
                $groupId
            );
        } else {
            $sql = sprintf(
                "select string_agg('\"' || replace(trim(coalesce(firstname,'') || ' ' || coalesce(lastname,'')), '\"', '-')  || '\" <' ||mail || '>',', ' order by mail)" .
                " from users where memberof @> '{%d}' and mail is not null and accounttype = 'U'",
                $groupId
            );
        }
        DbManager::query($sql, $mailGroup, true, true);
        return $mailGroup;
    }

    /**
     * Recompute group mail for a specific group
     * @param int $groupId System group identifier
     * @throws \Anakeen\Database\Exception
     */
    public static function refreshMailGroup(int $groupId)
    {
        $mailGroup = self::getMailGroup($groupId, true);

        // Update system account
        DbManager::query(sprintf("update users set mail='%s' where id=%d", pg_escape_string($mailGroup), $groupId));
        // Update Smart Igroup
        $mailGroup = self::getMailGroup($groupId);
        DbManager::query(sprintf(
            "update doc127 set grp_mail='%s', mdate='%s' where us_whatid=%d",
            pg_escape_string($mailGroup),
            Date::getNow(true),
            $groupId
        ));
    }


    public static function refreshOneGroup($gid, $refresh)
    {
        $g = new Account("", $gid);
        if ($g->fid > 0 && $g->accounttype == 'G') {
            /**
             * @var \SmartStructure\Igroup $doc
             */

            $doc = SEManager::getDocument($g->fid);
            if ($doc && $doc->isAlive()) {
                if ($refresh) {
                    $doc->refreshMembers();
                }
                $doc->setGroupMail();
                $doc->modify();
                $doc->specPostInsert();
                $doc->setValue("grp_isrefreshed", "1");
                $doc->modify(true, array(
                    "grp_isrefreshed"
                ), true);
            }
        }
    }
}

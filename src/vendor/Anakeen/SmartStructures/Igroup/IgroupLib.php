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

use Anakeen\Core\SEManager;

/**
 * refresh a set of group
 * @param array $tg the groups which has been modify by insertion/deletion of user
 * @return
 */
class IgroupLib
{

    public static function refreshGroups($groupIdList, $refresh = false, &$currentPath = array(), &$groupDepth = array())
    {
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
                error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Loop detected in group with id '%s' (path=[%s])", $groupId, join('-', $currentPath)));
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


    public static function refreshOneGroup($gid, $refresh)
    {
        $g = new \Anakeen\Core\Account("", $gid);
        if ($g->fid > 0 && $g->accounttype == 'G') {
            /**
             * @var \SmartStructure\Igroup $doc
             */

            $doc = SEManager::getDocument($g->fid);
            if ($doc && $doc->isAlive()) {
                if ($refresh) {
                    $doc->refreshMembers();
                }
                $doc->SetGroupMail();
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
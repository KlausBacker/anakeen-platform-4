<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * Group order, all group have one user with same name, and one role with same name
 *
 *                         +--------+           +----------+
 *                         +   A    |           |     F    |
 *                +--------+        +----------->          |
 *                |        +-----+--+           +----------+
 *                |              |  +-------+
 *                |              |          |
 *                |        +-----v----+    +v-----------+
 *                |        |          |    |            |
 *                |        |     B    |    |     C      |
 *                |        +--------+-+    +----+-------+
 *                |                 |           |
 *                |                 |           |                      +-----------+
 *                |               +-v-----------v--+                   |           |
 *                |               |        D       |                   |   G       |
 *                |               |                |                   +-----------+
 *                |               +----+-----------+
 *                |                    |
 *                |            +-------v----------+
 *                +------------>                  |
 *                             |        E         |
 *                             +------------------+
 */

namespace Dcp\Pu;

use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;

/**
 * @author  Anakeen
 * @package Dcp\Pu
 */
class TestRoleMove extends TestCaseDcpCommonFamily
{
    protected static $outputDir;

    /**
     * import TST_FAMSETVALUE family
     * @return string[]
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_role_move.csv"
        );
    }

    /**
     * @dataProvider dataInitGroup
     */
    public function testInitGroup($group)
    {
        $this->analyzeGroupUserAndRole($group);
    }

    /**
     * @dataProvider dataInitUser
     */
    public function testInitUser($user)
    {
        $this->analyzeUserGroupAndRole($user);
    }

    /**
     * @dataProvider dataAddUserGToGroupA
     */
    public function testAddUserGToGroupA($user)
    {
        $gUser = SEManager::getDocument("IUSER_G");
        $aGroup = SEManager::getDocument("GROUP_A");
        /* @var $aGroup \SmartStructure\IGROUP */
        $aGroup->insertDocument($gUser->getPropertyValue("id"));
        $this->analyzeUserGroupAndRole($user);
    }

    /**
     * @dataProvider dataRemoveGUser
     */
    public function testRemoveUserGFromGroupGAndAddItToGroupE($user, $group)
    {
        $gUser = SEManager::getDocument("IUSER_G");
        $eGroup = SEManager::getDocument("GROUP_E");
        $gGroup = SEManager::getDocument("GROUP_G");
        /* @var $eGroup \SmartStructure\IGROUP */
        /* @var $gGroup \SmartStructure\IGROUP */
        $eGroup->insertDocument($gUser->getPropertyValue("id"));
        $gGroup->removeDocument($gUser->getPropertyValue("id"));
        $this->analyzeUserGroupAndRole($user);
        $this->analyzeGroupUserAndRole($group);
    }

    /**
     * @dataProvider addGroupGToGroupE
     */
    public function testAddGroupGToGroupE($user, $group)
    {
        $eGroup = SEManager::getDocument("GROUP_E");
        $gGroup = SEManager::getDocument("GROUP_G");
        /* @var $eGroup \SmartStructure\IGROUP */
        /* @var $gGroup \SmartStructure\IGROUP */
        $eGroup->insertDocument($gGroup->getPropertyValue("id"));
        $eTogGroup = new \Group(self::$dbaccess, array($eGroup->getRawValue("us_whatid")));
        $eTogGroup->resetAccountMemberOf(true);
        $this->analyzeUserGroupAndRole($user);
        $this->analyzeGroupUserAndRole($group);
    }

    /**
     * @dataProvider dataRemoveCFromA
     */
    public function testRemoveCFromA($user, $group)
    {
        $cGroup = SEManager::getDocument("GROUP_C");
        $aGroup = SEManager::getDocument("GROUP_A");
        /* @var $cGroup \SmartStructure\IGROUP */
        /* @var $aGroup \SmartStructure\IGROUP */
        $aGroup->removeDocument($cGroup->getPropertyValue("id"));
        $cInternalGroup = new \Group(self::$dbaccess, array($cGroup->getRawValue("us_whatid")));
        $cInternalGroup->resetAccountMemberOf(true);
        $this->analyzeUserGroupAndRole($user);
        $this->analyzeGroupUserAndRole($group);
    }

    /**
     * Analyze a user against the role and group definition
     *
     * @param  array $user contains "name" user logical name, roles : array of role logical name, groups array of group logical name
     * @return void
     */
    protected function analyzeUserGroupAndRole($user)
    {
        $dbaccess = self::$dbaccess;
        $userDoc = SEManager::getDocument($user["name"]);
        /* @var $userDoc \_IUSER */
        $currentRoles = $userDoc->getAccount()->getAllRoles();
        $currentRoles = array_map(function ($role) {
            return $role["login"];
        }, $currentRoles);
        $this->assertEmpty(array_diff($user["roles"], $currentRoles), sprintf("User %s have not all needed roles (%s instead of %s)",
            $user["name"], var_export($currentRoles, true), var_export($user["roles"], true)));
        $this->assertEmpty(array_diff($currentRoles, $user["roles"]), sprintf("User %s have more than all needed roles (%s instead of %s)",
            $user["name"], var_export($currentRoles, true), var_export($user["roles"], true)));
        $groups = $user["groups"];
        $groups = array_map(function ($groupName) use ($dbaccess) {
            return array("name" => $groupName, "id" => \Anakeen\Core\SEManager::getIdFromName($groupName));
        }, $groups);
        $userGroups = $userDoc->getAllUserGroups();
        foreach ($groups as $currentGroupId) {
            $this->assertTrue(in_array($currentGroupId["id"], $userGroups), "User {$user["name"]} should be in {$currentGroupId['name']}");
        }
    }

    /**
     * Analyze a user against the role and users definition
     *
     * @return void
     */
    protected function analyzeGroupUserAndRole($group)
    {
        $currentGroup = SEManager::getDocument($group["name"]);
        /* @var \SmartStructure\IGROUP $currentGroup */
        $currentRoles = $currentGroup->getAccount()->getAllRoles();
        $currentRoles = array_map(function ($role) {
            return $role["login"];
        }, $currentRoles);
        $this->assertEmpty(array_diff($group["roles"], $currentRoles), sprintf("Group %s have not all needed roles (%s instead of %s)",
            $group["name"], var_export($currentRoles, true), var_export($group["roles"], true)));
        $this->assertEmpty(
            array_diff($currentRoles, $group["roles"]),
            sprintf(
                "Group %s have more than all needed roles (%s instead of %s)",
                $group["name"],
                var_export($currentRoles, true),
                var_export($group["roles"], true)
            )
        );
        $usersName = $group["users"];
        /* @var \SmartStructure\IUSER $userDoc */
        foreach ($usersName as $userName) {
            $userDoc = SEManager::getDocument($userName);
            $groups = $userDoc->getAllUserGroups();
            $this->assertTrue(in_array($currentGroup->getPropertyValue("id"), $groups), "User $userName should be in {$group['name']}");
        }
    }

    public function dataInitGroup()
    {
        $groups = <<<'JSON'
[
    {
        "group" : {
            "name" : "GROUP_A",
            "users" : ["IUSER_A", "IUSER_B", "IUSER_C", "IUSER_D", "IUSER_E", "IUSER_F"],
            "roles" : ["role_a", "role_a1"]
        }
    },
    {
        "group" : {
            "name" : "GROUP_B",
            "users" : ["IUSER_B", "IUSER_D", "IUSER_E"],
            "roles" : ["role_a", "role_a1", "role_b"]
        }
    },
    {
        "group" : {
            "name" : "GROUP_C",
            "users" : ["IUSER_C", "IUSER_D", "IUSER_E"],
            "roles" : ["role_a", "role_a1", "role_c"]
        }
    },
    {
        "group" : {
            "name" : "GROUP_D",
            "users" : ["IUSER_D", "IUSER_E"],
            "roles" : ["role_a", "role_a1", "role_b", "role_c", "role_d"]
        }
    },
    {
        "group" : {
            "name" : "GROUP_E",
            "users" : ["IUSER_E"],
            "roles" : ["role_a", "role_a1", "role_b", "role_c", "role_d", "role_e"]
        }
    },
    {
        "group" : {
            "name" : "GROUP_F",
            "users" : ["IUSER_F"],
            "roles" : ["role_a", "role_a1", "role_f"]
        }
    },
    {
        "group" : {
            "name" : "GROUP_G",
            "users" : ["IUSER_G"],
            "roles" : ["role_g"]
        }
    }
]
JSON;
        return json_decode($groups, true);
    }

    public function dataInitUser()
    {
        $users = <<<'JSON'
[
    {
        "user" : {
            "name" :   "IUSER_A",
            "groups" : ["GROUP_A"],
            "roles" :   ["role_user_a", "role_a", "role_a1"]
        }
    },
    {
        "user" : {
            "name" :  "IUSER_B",
            "groups" : ["GROUP_A", "GROUP_B"],
            "roles" :  ["role_user_b", "role_a", "role_a1", "role_b"]
        }
    },
    {
        "user" : {
            "name" :  "IUSER_C",
            "groups" : ["GROUP_A", "GROUP_C"],
            "roles" :  ["role_user_c", "role_a", "role_a1", "role_c"]
        }
    },
    {
        "user" : {
            "name" :  "IUSER_D",
            "groups" : ["GROUP_A", "GROUP_B", "GROUP_D"],
            "roles" :  ["role_user_d", "role_a", "role_a1", "role_b", "role_c", "role_d"]
        }
    },
    {
        "user" : {
            "name" :  "IUSER_E",
            "groups" : ["GROUP_A", "GROUP_B", "GROUP_D", "GROUP_E", "GROUP_C"],
            "roles" :  ["role_user_e", "role_a", "role_a1", "role_b", "role_c", "role_d", "role_e"]
        }
    },
    {
        "user" : {
            "name" :  "IUSER_F",
            "groups" : ["GROUP_A", "GROUP_F"],
            "roles" :  ["role_user_f", "role_a", "role_a1", "role_f"]
        }
    },
    {
        "user" : {
            "name" : "IUSER_G",
            "groups" : ["GROUP_G"],
            "roles" : ["role_user_g", "role_g"]
        }
    }
]
JSON;
        return json_decode($users, true);
    }

    public function dataAddUserGToGroupA()
    {
        $users = <<<'JSON'
[
   {
       "user" : {
           "name" : "IUSER_G",
           "groups" : ["GROUP_G", "GROUP_A"],
           "roles" : ["role_user_g", "role_g", "role_a", "role_a1"]
       }
   }
]
JSON;
        return json_decode($users, true);
    }

    public function dataRemoveGUser()
    {
        $users = <<<'JSON'
[
   {
       "user" : {
           "name" : "IUSER_G",
           "groups" : ["GROUP_A", "GROUP_B", "GROUP_D", "GROUP_E"],
           "roles" :  ["role_user_g", "role_a", "role_a1", "role_b", "role_c", "role_d", "role_e"]
       },
       "group" : {
           "name" : "GROUP_A",
           "users" : ["IUSER_A", "IUSER_B", "IUSER_C", "IUSER_D", "IUSER_E", "IUSER_F", "IUSER_G"],
           "roles" : ["role_a", "role_a1"]
       }
   }
]
JSON;
        return json_decode($users, true);
    }

    public function addGroupGToGroupE()
    {
        $users = <<<'JSON'
[
    {
        "user" :  {
            "name" :   "IUSER_G",
            "groups" : ["GROUP_A", "GROUP_B", "GROUP_C", "GROUP_D", "GROUP_E", "GROUP_G"],
            "roles" :  ["role_user_g", "role_a", "role_a1", "role_b", "role_c", "role_d", "role_e", "role_g"]
        },
        "group" : {
            "name" :  "GROUP_G",
            "users" : ["IUSER_G"],
            "roles" : ["role_g", "role_a", "role_a1", "role_b", "role_c", "role_d", "role_e"]
        }
    },
    {
        "user" :  {
            "name" :   "IUSER_G",
            "groups" : ["GROUP_A", "GROUP_B", "GROUP_C", "GROUP_D", "GROUP_E", "GROUP_G"],
            "roles" :  ["role_user_g", "role_a", "role_a1", "role_b", "role_c", "role_d", "role_e", "role_g"]
        },
        "group" : {
            "name" :  "GROUP_E",
            "users" : ["IUSER_G", "IUSER_E"],
            "roles" : ["role_a", "role_a1", "role_b", "role_c", "role_d", "role_e"]
        }
    }
]
JSON;
        return json_decode($users, true);
    }

    public function dataRemoveCFromA()
    {
        $users = <<<'JSON'
[
    {
        "user" :  {
            "name" :   "IUSER_C",
            "groups" : ["GROUP_C"],
            "roles" :  ["role_user_c", "role_c"]
        },
        "group" : {
            "name" :  "GROUP_C",
            "users" : ["IUSER_C"],
            "roles" : ["role_c"]
        }
    },
    {
        "user" :  {
            "name" :   "IUSER_E",
            "groups" : ["GROUP_A", "GROUP_B", "GROUP_D", "GROUP_E"],
            "roles" :  ["role_user_e", "role_a", "role_a1", "role_b", "role_d", "role_e", "role_c"]
        },
        "group" : {
            "name" :  "GROUP_A",
            "users" : ["IUSER_A", "IUSER_B", "IUSER_D", "IUSER_E", "IUSER_F"],
            "roles" : ["role_a", "role_a1"]
        }
    }
]
JSON;
        return json_decode($users, true);
    }


}

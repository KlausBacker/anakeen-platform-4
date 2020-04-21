<?php

namespace Anakeen\Pu\Accounts;

use Anakeen\Core\AccountManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\Utils\Postgres;
use Anakeen\Pu\Config\TestCaseConfig;

class PuGroups extends TestCaseConfig
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importAccountFile(__DIR__ . "/Inputs/accountsData.xml");
    }

    /**
     * Test Field definition import
     *
     * @dataProvider dataGroupMemberOf
     *
     * @param string $login
     * @param array $expectedMembers
     * @throws \Anakeen\Core\Exception
     * @throws \Anakeen\Database\Exception
     */
    public function testGroupMemberOf($login, array $expectedMembers)
    {
        // DbManager::query("select login, id, memberof from users where login ~ 'gtst'", $gr);
        // print_r($gr);
        $g = AccountManager::getAccount($login);

        $systemExepectedMembers = $this->loginToIds($expectedMembers);
        $memberOf = Postgres::stringToArray($g->memberof);
        sort($memberOf);
        $this->assertEquals($systemExepectedMembers, $memberOf, "Error for $login");
    }


    /**
     * Test Account add into group
     *
     * @dataProvider dataAccountAddGroup
     *
     * @param $targetGroupLogin
     * @param $accountToAddLogin
     * @param array $expectedLoginMembers
     * @throws \Anakeen\Core\Exception
     * @throws \Anakeen\Database\Exception
     */
    public function testAccountAddGroup($targetGroupLogin, $accountToAddLogin, array $expectedLoginMembers)
    {
        // DbManager::query("select login, id, memberof from users where login ~ 'gtst'", $gr);
        // print_r($gr);
        $target = AccountManager::getAccount($targetGroupLogin);
        $toadd = AccountManager::getAccount($accountToAddLogin);

        $g = new \Group();
        $g->idgroup = $target->id;
        $g->iduser = $toadd->id;
        $err = $g->add();

        $this->assertEmpty($err, $err);

        foreach ($expectedLoginMembers as $login => $expectedMembers) {
            $a = AccountManager::getAccount($login);

            $systemExepectedMembers = ($expectedMembers);
            $memberOf = $this->idsToLogin(Postgres::stringToArray($a->memberof));
            sort($expectedMembers);
            sort($systemExepectedMembers);


            $this->assertEquals(
                $systemExepectedMembers,
                $memberOf,
                "Error for $login in target $targetGroupLogin => $accountToAddLogin "
            );
        }
    }

    /**
     * Test Account remove from group
     *
     * @dataProvider dataAccountRemoveFromGroup
     *
     * @param $targetGroupLogin
     * @param $accountToAddLogin
     * @param array $expectedLoginMembers
     * @throws \Anakeen\Core\Exception
     * @throws \Anakeen\Database\Exception
     */
    public function testAccountRemoveFromGroup(
        $targetGroupLogin,
        $accountToRemoveFromLogin,
        array $expectedLoginMembers
    ) {
        // DbManager::query("select login, id, memberof from users where login ~ 'gtst'", $gr);
        // print_r($gr);
        $target = AccountManager::getAccount($targetGroupLogin);
        $todel = AccountManager::getAccount($accountToRemoveFromLogin);
        $g = new \Group();
        $g->iduser = $target->id;
        $err = $g->suppressUser($todel->id);

        $this->assertEmpty($err, $err);

        foreach ($expectedLoginMembers as $login => $expectedMembers) {
            $a = AccountManager::getAccount($login);

            $systemExepectedMembers = ($expectedMembers);
            $memberOf = $this->idsToLogin(Postgres::stringToArray($a->memberof));
            sort($expectedMembers);
            sort($systemExepectedMembers);
            $this->assertEquals(
                $systemExepectedMembers,
                $memberOf,
                "Error for $login in target $targetGroupLogin => $accountToRemoveFromLogin "
            );
        }
    }

    protected function loginToIds(array $logins)
    {
        $sql = sprintf("select id from users where login in ('%s') order by id", implode("','", $logins));
        DbManager::query($sql, $results, true);
        return $results;
    }

    protected function idsToLogin(array $ids)
    {
        if (!$ids) {
            return [];
        }
        $sql = sprintf("select login from users where id in ('%s') order by login", implode("','", $ids));
        DbManager::query($sql, $results, true);
        return $results;
    }

    //region Test Data providers
    protected function getOriginalMembers()
    {
        return [
            "gtst_101" => [],
            "gtst_102" => [],
            "gtst_201" => ["gtst_101"],
            "gtst_202" => ["gtst_101", "gtst_102", "gtst_103"],
            "gtst_203" => ["gtst_102"],
            "gtst_301" => ["gtst_101", "gtst_201"],
            "gtst_302" => ["gtst_101", "gtst_102", "gtst_201"],
            "gtst_303" => ["gtst_101", "gtst_102", "gtst_103", "gtst_201", "gtst_202"],
            "gtst_304" => ["gtst_101", "gtst_102", "gtst_103", "gtst_202"],
            "gtst_305" => ["gtst_102", "gtst_203"],
            "gtst_r101" => ["rtst_001"],
            "gtst_r201" => ["rtst_001", "gtst_r101", "rtst_002"],
            "gtst_r301" => ["rtst_001", "gtst_r101", "rtst_002", "gtst_r201"],
            "u0006-0" => [],
            "u0006-1" => ["gtst_101"],
            "u0006-2" => [
                "gtst_101",
                "gtst_201",
                "gtst_102",
                "gtst_103",
                "gtst_202",
                "gtst_301",
                "gtst_302",
                "gtst_303"
            ],
            "u0006-3" => ["gtst_301", "gtst_101", "gtst_201"],
            "u0006-4" => ["gtst_302", "gtst_101", "gtst_102", "gtst_201"],
            "u0006-5" => ["gtst_303", "gtst_101", "gtst_102", "gtst_103", "gtst_201", "gtst_202"],
            "u0006-6" => ["gtst_304", "gtst_101", "gtst_102", "gtst_103", "gtst_202"],
            "u0006-7" => ["gtst_305", "gtst_102", "gtst_203"],
        ];
    }

    public function dataAccountRemoveFromGroup()
    {
        $originals = $this->getOriginalMembers();

        return [
            [
                "gtst_104",
                "u0006-0",
                array_merge($originals, [])
            ],

            [
                "gtst_203",
                "gtst_305",
                array_merge($originals, [
                    "gtst_305" => [],
                    "u0006-7" => ["gtst_305"],
                ])
            ],
            [
                "gtst_202",
                "gtst_303",
                array_merge($originals, [
                    "gtst_303" => ["gtst_101", "gtst_201"],
                    "u0006-2" => [
                        "gtst_101",
                        "gtst_102",
                        "gtst_201",
                        "gtst_301",
                        "gtst_302",
                        "gtst_303"
                    ],
                    "u0006-5" => ["gtst_303", "gtst_101", "gtst_201"],
                ])
            ],
            [
                "gtst_103",
                "gtst_202",
                array_merge($originals, [
                    "gtst_202" => ["gtst_101", "gtst_102"],
                    "gtst_303" => ["gtst_101", "gtst_102", "gtst_201", "gtst_202"],
                    "gtst_304" => ["gtst_101", "gtst_102", "gtst_202"],
                    "u0006-2" => [
                        "gtst_101",
                        "gtst_201",
                        "gtst_102",
                        "gtst_202",
                        "gtst_301",
                        "gtst_302",
                        "gtst_303"
                    ],
                    "u0006-5" => ["gtst_303", "gtst_101", "gtst_102", "gtst_201", "gtst_202"],
                    "u0006-6" => ["gtst_304", "gtst_101", "gtst_102", "gtst_202"],
                ])
            ]
        ];
    }

    public function dataAccountAddGroup()
    {
        $originals = $this->getOriginalMembers();

        return [
            [
                "gtst_104",
                "u0006-0",
                array_merge($originals, [
                    "u0006-0" => ["gtst_104"], // Modified
                ])
            ],
            [
                "gtst_202",
                "u0006-0",
                array_merge($originals, [
                    "u0006-0" => ["gtst_202", "gtst_101", "gtst_102", "gtst_103"], // Modified
                ])
            ],
            [
                "gtst_201",
                "gtst_202",
                array_merge($originals, [
                    "gtst_202" => ["gtst_101", "gtst_102", "gtst_103", "gtst_201"],
                    "gtst_304" => ["gtst_101", "gtst_102", "gtst_103", "gtst_202", "gtst_201"],
                    "u0006-6" => ["gtst_304", "gtst_101", "gtst_102", "gtst_103", "gtst_202", "gtst_201"],
                ])
            ],
            [
                "gtst_201",
                "u0006-1",
                array_merge($originals, [
                    "u0006-1" => ["gtst_101", "gtst_201"],
                ])
            ],
            [
                "gtst_202",
                "gtst_203",
                array_merge($originals, [
                    "gtst_203" => ["gtst_202", "gtst_101", "gtst_102", "gtst_103"],
                    "gtst_305" => ["gtst_203", "gtst_202", "gtst_101", "gtst_102", "gtst_103"],
                    "u0006-7" => ["gtst_305", "gtst_203", "gtst_202", "gtst_101", "gtst_102", "gtst_103"],
                ])
            ],
            [
                "gtst_203",
                "gtst_202",
                array_merge($originals, [
                    "gtst_202" => ["gtst_203", "gtst_101", "gtst_102", "gtst_103"],
                    "gtst_303" => ["gtst_203", "gtst_101", "gtst_102", "gtst_103", "gtst_201", "gtst_202"],
                    "gtst_304" => ["gtst_203", "gtst_101", "gtst_102", "gtst_103", "gtst_202"],
                    "u0006-2" => [
                        "gtst_203",
                        "gtst_101",
                        "gtst_201",
                        "gtst_102",
                        "gtst_103",
                        "gtst_202",
                        "gtst_301",
                        "gtst_302",
                        "gtst_303"
                    ],
                    "u0006-5" => ["gtst_203", "gtst_303", "gtst_101", "gtst_102", "gtst_103", "gtst_201", "gtst_202"],
                    "u0006-6" => ["gtst_203", "gtst_304", "gtst_101", "gtst_102", "gtst_103", "gtst_202"],
                ])
            ],
            [
                "rtst_001",
                "gtst_202",
                array_merge($originals, [
                    "gtst_202" => ["rtst_001", "gtst_101", "gtst_102", "gtst_103"],
                    "gtst_303" => ["rtst_001", "gtst_101", "gtst_102", "gtst_103", "gtst_201", "gtst_202"],
                    "gtst_304" => ["rtst_001", "gtst_101", "gtst_102", "gtst_103", "gtst_202"],
                    "u0006-2" => [
                        "rtst_001",
                        "gtst_101",
                        "gtst_201",
                        "gtst_102",
                        "gtst_103",
                        "gtst_202",
                        "gtst_301",
                        "gtst_302",
                        "gtst_303"
                    ],
                    "u0006-5" => ["rtst_001", "gtst_303", "gtst_101", "gtst_102", "gtst_103", "gtst_201", "gtst_202"],
                    "u0006-6" => ["rtst_001", "gtst_304", "gtst_101", "gtst_102", "gtst_103", "gtst_202"],
                ])
            ],
        ];
    }


    public function dataGroupMemberOf()
    {
        $originals = $this->getOriginalMembers();
        $data = [];
        foreach ($originals as $login => $members) {
            $data[] = [$login, $members];
        }
        return $data;
    }

    //endregion
}

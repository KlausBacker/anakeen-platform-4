<?php

namespace Anakeen\Pu\Accounts;

use Anakeen\Core\AccountManager;
use Anakeen\Core\SEManager;
use Anakeen\Pu\Config\TestCaseConfig;
use SmartStructure\Igroup;
use SmartStructure\Fields\Iuser as IUserFields;
use SmartStructure\Fields\Igroup as IGroupFields;

class PuMailGroups extends TestCaseConfig
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importAccountFile(__DIR__ . "/Inputs/accountsMailGroupData.xml");
        self::importDocument(__DIR__ . "/Inputs/accountsMailGroupConfig.xml");
        self::importAccountFile(__DIR__ . "/Inputs/accountsMailUserData.xml");
    }

    /**
     * Test raw mail
     *
     * @dataProvider dataMailGroup
     *
     * @param string $login
     * @param string $expectedMail
     * @throws \Anakeen\Core\Exception
     */
    public function testMailGroup($login, string $expectedMail)
    {
        $g = AccountManager::getAccount($login);

        $this->assertEquals($expectedMail, $g->getMail(), "Not the good email for $login");
    }


    /**
     * Test update user mail
     *
     * @dataProvider dataMailUpdateUser
     *
     * @param string $login
     * @param string $newMail
     * @param array $expectedMails
     * @throws \Anakeen\Core\Exception
     */
    public function testMailUpdateUser($login, string $newMail, array $expectedMails)
    {
        $u = AccountManager::getAccount($login);
        $su = SEManager::getDocument($u->fid);
        $su->setValue(IUserFields::us_extmail, $newMail);
        $err = $su->store();
        $this->assertEmpty($err, "Error when update user $login");
        $this->assertEquals($newMail, $su->getRawValue(IUserFields::us_mail), "Error when update mail");
        foreach ($expectedMails as $gLogin => $expectedMail) {
            $g = AccountManager::getAccount($gLogin);
            $sg = SEManager::getDocument($g->fid);

            $this->assertEquals(
                $expectedMail,
                $sg->getRawValue(IGroupFields::grp_mail),
                "Not the good email for $login"
            );
            $this->assertEquals(
                $this->getRawMail($expectedMail),
                $g->getMail(),
                "Not the good raw system email for $login"
            );
        }
    }


    /**
     * Test update user mail
     *
     * @dataProvider dataMailUpdateUser
     *
     * @param string $login
     * @param string $newMail
     * @param array $expectedMails
     * @throws \Anakeen\Core\Exception
     */
    public function testMailSystemUpdateUser($login, string $newMail, array $expectedMails)
    {
        $u = AccountManager::getAccount($login);
        $u->mail = $newMail;
        $err = $u->modify();
        $this->assertEmpty($err, "Error when update user $login");
        $su = SEManager::getDocument($u->fid);

        $this->assertEquals($newMail, $su->getRawValue(IUserFields::us_mail), "Error when update mail");
        foreach ($expectedMails as $gLogin => $expectedMail) {
            $g = AccountManager::getAccount($gLogin);
            $sg = SEManager::getDocument($g->fid);

            $this->assertEquals(
                $expectedMail,
                $sg->getRawValue(IGroupFields::grp_mail),
                "Not the good email for $gLogin"
            );
            $this->assertEquals(
                $this->getRawMail($expectedMail),
                $g->getMail(),
                "Not the good raw system email for $gLogin"
            );
        }
    }


    /**
     * Test add user impact for mail
     *
     * @dataProvider dataMailAddAccount
     *
     * @param string $login
     * @param string $insertToLogin
     * @param array $expectedMails
     * @throws \Anakeen\Core\Exception
     */
    public function testMailAddAccount($login, $insertToLogin, array $expectedMails)
    {
        $u = AccountManager::getAccount($login);
        $su = SEManager::getDocument($u->fid);

        $insertTo = AccountManager::getAccount($insertToLogin);
        /** @var Igroup $sg */
        $sg = SEManager::getDocument($insertTo->fid);

        $err = $sg->insertDocument($su->id);
        $this->assertEmpty($err, "Error when add user $login to $insertToLogin ");

        foreach ($expectedMails as $gLogin => $expectedMail) {
            $g = AccountManager::getAccount($gLogin);
            $sg = SEManager::getDocument($g->fid);

            $this->assertEquals(
                $expectedMail,
                $sg->getRawValue(IGroupFields::grp_mail),
                "Not the good email for $gLogin"
            );
            $this->assertEquals(
                $this->getRawMail($expectedMail),
                $g->getMail(),
                "Not the good raw system email for $gLogin"
            );
        }
    }


    /**
     * Test remove user impact for mail
     *
     * @dataProvider dataMailRemoveAccount
     *
     * @param string $login
     * @param string $deleteToLogin
     * @param array $expectedMails
     * @throws \Anakeen\Core\Exception
     */
    public function testMailRemoveAccount($login, $deleteToLogin, array $expectedMails)
    {
        $u = AccountManager::getAccount($login);
        $su = SEManager::getDocument($u->fid);

        $insertTo = AccountManager::getAccount($deleteToLogin);
        /** @var Igroup $sg */
        $sg = SEManager::getDocument($insertTo->fid);

        $err = $sg->removeDocument($su->id);
        $this->assertEmpty($err, "Error when remove user $login from $deleteToLogin ");

        foreach ($expectedMails as $gLogin => $expectedMail) {
            $g = AccountManager::getAccount($gLogin);
            $sg = SEManager::getDocument($g->fid);

            $this->assertEquals(
                $expectedMail,
                $sg->getRawValue(IGroupFields::grp_mail),
                "Not the good email for $gLogin"
            );
            $this->assertEquals(
                $this->getRawMail($expectedMail),
                $g->getMail(),
                "Not the good raw system email for $gLogin"
            );
        }
    }


    /**
     * Test Complete mail
     *
     * @dataProvider dataSmartMailGroup
     *
     * @param string $id
     * @param string $expectedMail
     */
    public function testSmartMailGroup($id, string $expectedMail)
    {
        $g = SEManager::getDocument($id);
        /** @var Igroup $g */

        $this->assertEquals($expectedMail, $g->getMail(), "Not the good email for $id");
    }


    private function getRawMail($mail)
    {
        if (preg_match_all("/<([^>]*)>/", $mail, $regs)) {
            return implode(", ", $regs[1]);
        }
        return "";
    }

    public function dataMailGroup()
    {
        return [
            ["gtst_301", "r2d2@example.net"],
            ["gtst_201", "jane.doe@example.net, r2d2@example.net"],
            [
                "gtst_101",
                "han.solo@example.net, jane.doe@example.net, john.snow@example.net, leia.skywaker@example.net, r2d2@example.net"
            ],
            ["gtst_102", "han.solo@example.net, leia.skywaker@example.net, obiwan.kenobi@example.net"],
            ["gtst_222", "leia.skywaker@example.net"],
            ["gtst_000", "han.solo@example.net, leia.skywaker@example.net, obiwan.kenobi@example.net"]
        ];
    }

    public function dataSmartMailGroup()
    {
        return [
            [
                "GTST_101",
                '"Han Solo" <han.solo@example.net>, "Jane Doe" <jane.doe@example.net>, ' .
                '"John Snow" <john.snow@example.net>, "Leia Skywalker" <leia.skywaker@example.net>, "R2 D2" <r2d2@example.net>'
            ],
            ["GTST_301", '"R2 D2" <r2d2@example.net>'],
            ["GTST_201", '"Jane Doe" <jane.doe@example.net>, "R2 D2" <r2d2@example.net>'],
            [
                "GTST_102",
                '"Han Solo" <han.solo@example.net>, "Leia Skywalker" <leia.skywaker@example.net>, "Obiwan Kenobi" <obiwan.kenobi@example.net>'
            ],
            ["GTST_222", '"Leia Skywalker" <leia.skywaker@example.net>']
        ];
    }

    public function dataMailUpdateUser()
    {
        return [
            [
                "u0001",
                "u0001@somewhere.net",
                [
                    "gtst_101" => '"Han Solo" <han.solo@example.net>, "Jane Doe" <jane.doe@example.net>, ' .
                        '"John Snow" <john.snow@example.net>, "Leia Skywalker" <leia.skywaker@example.net>, "R2 D2" <r2d2@example.net>',
                    "gtst_301" => '"R2 D2" <r2d2@example.net>',

                    "gtst_102" =>
                        '"Han Solo" <han.solo@example.net>, "Leia Skywalker" <leia.skywaker@example.net>, "Obiwan Kenobi" <obiwan.kenobi@example.net>',

                    "gtst_201" => '"Jane Doe" <jane.doe@example.net>, "R2 D2" <r2d2@example.net>',
                    "gtst_222" => '"Leia Skywalker" <leia.skywaker@example.net>'
                ]
            ],
            [
                "u0002",
                "u0002@somewhere.net",
                [
                    "gtst_101" => '"Han Solo" <han.solo@example.net>, "Jane Doe" <jane.doe@example.net>, ' .
                        '"Leia Skywalker" <leia.skywaker@example.net>, "R2 D2" <r2d2@example.net>, "John Snow" <u0002@somewhere.net>',
                    "gtst_102" =>
                    '"Han Solo" <han.solo@example.net>, "Leia Skywalker" <leia.skywaker@example.net>, "Obiwan Kenobi" <obiwan.kenobi@example.net>',
                    "gtst_301" => '"R2 D2" <r2d2@example.net>',
                    "gtst_201" => '"Jane Doe" <jane.doe@example.net>, "R2 D2" <r2d2@example.net>',
                    "gtst_222" => '"Leia Skywalker" <leia.skywaker@example.net>'
                ]
            ],
            [
                "u0004",
                "r3d3@somewhere.net",
                [
                    "gtst_101" => '"Han Solo" <han.solo@example.net>, "Jane Doe" <jane.doe@example.net>, ' .
                        '"John Snow" <john.snow@example.net>, "Leia Skywalker" <leia.skywaker@example.net>, "R2 D2" <r3d3@somewhere.net>',
                    "gtst_102" =>
                    '"Han Solo" <han.solo@example.net>, "Leia Skywalker" <leia.skywaker@example.net>, "Obiwan Kenobi" <obiwan.kenobi@example.net>',
                    "gtst_301" => '"R2 D2" <r3d3@somewhere.net>',
                    "gtst_201" => '"Jane Doe" <jane.doe@example.net>, "R2 D2" <r3d3@somewhere.net>',
                    "gtst_222" => '"Leia Skywalker" <leia.skywaker@example.net>'
                ]
            ]
        ];
    }

    public function dataMailAddAccount()
    {
        return [
            [
                "u0001",
                "gtst_102",
                [
                    "gtst_101" => '"Han Solo" <han.solo@example.net>, "Jane Doe" <jane.doe@example.net>, ' .
                        '"John Snow" <john.snow@example.net>, "Leia Skywalker" <leia.skywaker@example.net>, "R2 D2" <r2d2@example.net>',
                    "gtst_102" =>
                        '"Han Solo" <han.solo@example.net>, "John Doe" <john.doe@example.net>, '.
                        '"Leia Skywalker" <leia.skywaker@example.net>, "Obiwan Kenobi" <obiwan.kenobi@example.net>',
                    "gtst_301" => '"R2 D2" <r2d2@example.net>',
                    "gtst_201" => '"Jane Doe" <jane.doe@example.net>, "R2 D2" <r2d2@example.net>',
                    "gtst_222" => '"Leia Skywalker" <leia.skywaker@example.net>'
                ]
            ],
            [
                "u0001",
                "gtst_222",
                [
                    "gtst_101" => '"Han Solo" <han.solo@example.net>, "Jane Doe" <jane.doe@example.net>, "John Doe" <john.doe@example.net>, ' .
                        '"John Snow" <john.snow@example.net>, "Leia Skywalker" <leia.skywaker@example.net>, "R2 D2" <r2d2@example.net>',
                    "gtst_102" =>
                        '"Han Solo" <han.solo@example.net>, "John Doe" <john.doe@example.net>, '.
                        '"Leia Skywalker" <leia.skywaker@example.net>, "Obiwan Kenobi" <obiwan.kenobi@example.net>',
                    "gtst_301" => '"R2 D2" <r2d2@example.net>',
                    "gtst_201" => '"Jane Doe" <jane.doe@example.net>, "R2 D2" <r2d2@example.net>',
                    "gtst_222" => '"John Doe" <john.doe@example.net>, "Leia Skywalker" <leia.skywaker@example.net>'
                ]
            ],

        ];
    }
    public function dataMailRemoveAccount()
    {
        return [
            [
                "u0005",
                "gtst_102",
                [
                    "gtst_101" => '"Han Solo" <han.solo@example.net>, "Jane Doe" <jane.doe@example.net>, ' .
                        '"John Snow" <john.snow@example.net>, "Leia Skywalker" <leia.skywaker@example.net>, "R2 D2" <r2d2@example.net>',
                    "gtst_102" =>

                        '"Leia Skywalker" <leia.skywaker@example.net>, "Obiwan Kenobi" <obiwan.kenobi@example.net>',
                    "gtst_301" => '"R2 D2" <r2d2@example.net>',
                    "gtst_201" => '"Jane Doe" <jane.doe@example.net>, "R2 D2" <r2d2@example.net>',
                    "gtst_222" => '"Leia Skywalker" <leia.skywaker@example.net>'
                ]
            ],
            [
                "u0004",
                "gtst_301",
                [
                    "gtst_101" => '"Han Solo" <han.solo@example.net>, "Jane Doe" <jane.doe@example.net>, ' .
                        '"John Snow" <john.snow@example.net>, "Leia Skywalker" <leia.skywaker@example.net>',
                    "gtst_102" =>
                        '"Han Solo" <han.solo@example.net>, '.
                        '"Leia Skywalker" <leia.skywaker@example.net>, "Obiwan Kenobi" <obiwan.kenobi@example.net>',
                    "gtst_301" => '',
                    "gtst_201" => '"Jane Doe" <jane.doe@example.net>',
                    "gtst_222" => '"Leia Skywalker" <leia.skywaker@example.net>'
                ]
            ],

        ];
    }
}

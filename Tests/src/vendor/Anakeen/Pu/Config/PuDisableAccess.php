<?php

namespace Anakeen\Pu\Config;

use Anakeen\Core\AccountManager;
use Anakeen\Core\ContextManager;
use Anakeen\Exception;
use Anakeen\SmartElementManager;

class PuDisableAccess extends TestCaseConfig
{

    protected $testNumber = 0;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importAccountFile(__DIR__ . "/Inputs/tst_006.users.xml");
        self::importConfigurationFile(__DIR__ . "/Inputs/tst_006.struct.xml");
        self::importDocument(__DIR__ . "/Inputs/tst_006.elements.xml");
    }

    /**
     * Test Field definition import
     *
     * @dataProvider dataAccessControl
     *
     * @param string $structureName
     * @param array  $expectedFields
     *
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public function testAccessControl($name, $login, $expectedError)
    {
        $u = AccountManager::getAccount($login);
        ContextManager::sudo($u);
        try {
            $element = SmartElementManager::getDocument($name);
            $err = $element->setValue("tst_title", "Yo" . $this->testNumber++);
            if (!$err) {
                $err = $element->store();
                if (!$err) {
                    /** @var \SmartStructure\Dir $element */
                    $err = $element->insertDocument($element->initid);
                    if (!$err) {
                        if (!$err) {
                            $err = $element->removeDocument($element->initid);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $err = $e->getMessage();
        }

        ContextManager::exitSudo();
        if ($expectedError) {
            $this->assertContains($expectedError, $err);
        } else {
            $this->assertEmpty($err);
        }
    }

    /**
     * Test Field definition import
     *
     * @dataProvider dataDisableControl
     *
     *
     */
    public function testDisableControl($name, $login)
    {
        $u = AccountManager::getAccount($login);
        ContextManager::sudo($u);

        $element = SmartElementManager::getDocument($name);
        $element->disableAccessControl(true);
        $err = $element->setValue("tst_title", "Yo" . $this->testNumber++);
        if (!$err) {
            $err = $element->store();
            if (!$err) {
                /** @var \SmartStructure\Dir $element */
                $err = $element->insertDocument($element->initid);
                if (!$err) {
                    if (!$err) {
                        $err = $element->removeDocument($element->initid);
                    }
                }
            }
        }


        ContextManager::exitSudo();
        $this->assertEmpty($err);
    }

    public function dataAccessControl()
    {
        return [
            [
                "element" => "tst_006-1",
                "login" => "admin",
                "error" => ""
            ],
            [
                "element" => "tst_006-1",
                "login" => "u0006-0",
                "error" => "\"view\""
            ],
            [
                "element" => "tst_006-1",
                "login" => "u0006-1",
                "error" => "\"modify\""
            ],
            [
                "element" => "tst_006-1",
                "login" => "u0006-2",
                "error" => "\"edit\""
            ],
            [
                "element" => "tst_006-1",
                "login" => "u0006-3",
                "error" => ""
            ]
        ];
    }

    public function dataDisableControl()
    {
        return [
            [
                "element" => "tst_006-1",
                "login" => "admin",
            ],
            [
                "element" => "tst_006-1",
                "login" => "u0006-1",
            ],
            [
                "element" => "tst_006-1",
                "login" => "u0006-2",
            ],
            [
                "element" => "tst_006-1",
                "login" => "u0006-3",
            ]
        ];
    }
}

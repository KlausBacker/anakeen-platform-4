<?php

namespace Anakeen\Pu\TestMailTemplate;

use Anakeen\Core\SEManager;
use Dcp\Pu\TestCaseDcpCommonFamily;
use SmartStructure\Mailtemplate;

class TestMailTemplateExtraKeys extends TestCaseDcpCommonFamily
{


    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importDocument(__DIR__ . "/Data/TestMailTemplateData.xml");
    }

    protected static function getConfigFile()
    {
        return array(
            __DIR__ . "/Data/TestMailTemplatekey.struct.xml",
            __DIR__ . "/Data/MyMessageExtraKey.xml"
        );
    }

    /**
     * @dataProvider dataAddExtraKeys
     * @param string $smartElementName
     * @param string $mailtemplateName
     * @param array $expectedValues
     * @param array $notExpectedValues
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Anakeen\Database\Exception
     * @throws \Anakeen\Exception
     */
    public function testAddExtraKeys(
        string $smartElementName,
        string $mailtemplateName,
        array $expectedValues,
        array $notExpectedValues = []
    ) {
        $doc = SEManager::getDocument($smartElementName);

        $this->assertNotEmpty($doc, "Cannot find $smartElementName.");
        $mailTemplate = SEManager::getDocument($mailtemplateName);
        $this->assertNotEmpty($doc, "Cannot find $mailtemplateName.");
        /** @var Mailtemplate $mailTemplate */
        $message = $mailTemplate->getMailMessage($doc);
        // test if value is in $mailMessage->body

        foreach ($expectedValues as $expectedValue) {
            $this->assertStringContainsString($expectedValue, $message->body->data);
        }
        foreach ($notExpectedValues as $notExpectedValue) {
            $this->assertStringNotContainsString($notExpectedValue, $message->body->data);
        }
    }

    /**
     * @dataProvider dataAddExtraKeysInSubject
     * @param string $smartElementName
     * @param string $mailtemplateName
     * @param string $expectedSubject
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Anakeen\Database\Exception
     * @throws \Anakeen\Exception
     */
    public function testAddExtraKeysInSubject(
        string $smartElementName,
        string $mailtemplateName,
        string $expectedSubject
    ) {
        $doc = SEManager::getDocument($smartElementName);

        $this->assertNotEmpty($doc, "Cannot find $smartElementName.");
        $mailTemplate = SEManager::getDocument($mailtemplateName);
        $this->assertNotEmpty($doc, "Cannot find $mailtemplateName.");
        /** @var Mailtemplate $mailTemplate */
        $message = $mailTemplate->getMailMessage($doc);

        $this->assertEquals($expectedSubject, $message->subject);
    }

    public function dataAddExtraKeysInSubject()
    {
        return [
            [
                "TST_MYKEY_01",
                "MY_MESSAGE_EXTRA_KEY",
                "Un sujet"
            ],

            [
                "TST_MYKEY_01",
                "MY_MESSAGE_EXTRA_KEY_SUM",
                "Un sujet 600"
            ],
            [
                "TST_MYKEY_01",
                "MY_MESSAGE_EXTRA_KEY_IF2",
                "Un sujet en or"
            ]
        ];
    }

    /** @noinspection HtmlUnknownAttribute */
    public function dataAddExtraKeys()
    {
        return [
            [
                "TST_MYKEY_01",
                "MY_MESSAGE_EXTRA_KEY",
                ['<span name="number1">678</span>', '<span name="number2">-78</span>'],
            ],

            [
                "TST_MYKEY_01",
                "MY_MESSAGE_EXTRA_KEY_IF1",
                ['<span name="number1">678</span>', '<span name="number2">-78</span>'],
            ],

            [
                "TST_MYKEY_02",
                "MY_MESSAGE_EXTRA_KEY_IF1",
                ['<span name="number2">7</span>'],
                ['<span name="number1">',],
            ],

            [
                "TST_MYKEY_03",
                "MY_MESSAGE_EXTRA_KEY_IF1",
                ['<span name="number2">-71</span>'],
                ['<span name="number1">'],

            ],
            [
                "TST_MYKEY_01",
                "MY_MESSAGE_EXTRA_KEY_IF2",
                ['<span name="number1">678</span>', '<span name="number2">-78</span>'],
            ],

            [
                "TST_MYKEY_02",
                "MY_MESSAGE_EXTRA_KEY_IF2",
                [],
                ['<span name="number1">', '<span name="number2">'],
            ],

            [
                "TST_MYKEY_03",
                "MY_MESSAGE_EXTRA_KEY_IF2",
                ['<span name="number2">-71</span>'],
                ['<span name="number1">-5</span>'],

            ],

            [
                "TST_MYKEY_03",
                "MY_MESSAGE_EXTRA_KEY_SUM",
                ['La somme <b>-76</b>', '<span name="custom">Voilà 10 <b> 12 </b></span>'],
                [],
            ],

            [
                "TST_MYKEY_02",
                "MY_MESSAGE_EXTRA_KEY_SUM",
                ['La somme <b>2</b>'],
                [],
            ],

            [
                "TST_MYKEY_02",
                "MY_MESSAGE_EXTRA_KEY_BLOCK1",
                ['<ul><li>K1</li><li>K2</li></ul>'],
                [],
            ]
        ];
    }
}

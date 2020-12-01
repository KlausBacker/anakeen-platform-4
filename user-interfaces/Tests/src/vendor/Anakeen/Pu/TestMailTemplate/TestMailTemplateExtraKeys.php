<?php


namespace Anakeen\Pu\TestMailTemplate;

use Anakeen\Core\SEManager;
use Anakeen\SmartStructures\Mailtemplate\SentMessage;
use Dcp\Pu\TestCaseDcpCommonFamily;

class TestMailTemplateExtraKeys extends TestCaseDcpCommonFamily
{
    protected static function getConfigFile()
    {
        return array(
            "PU_data_dcp_mail_template.xml"
        );
    }

    public function testAddExtraKeys()
    {
        $doc = SEManager::createDocument("TEST_MAIL_TEMPLATE_DOCUMENT");
        $mailTemplate = SEManager::getDocument("MY_MESSAGE_EXTRA_KEY");
        if ($mailTemplate) {
            $message = $mailTemplate->getMailMessage($doc);
            // test if value is in $mailMessage->body
            $this->assertStringContainsString("TST_EXTRA_KEY_MAIL_TEMPLATE", $message->body->data);
            $this->assertStringContainsString("true", $message->body->data);
            $this->assertStringContainsString("TST,EXTRA,KEY,MAIL,TEMPLATE", $message->body->data);
        }
    }
}

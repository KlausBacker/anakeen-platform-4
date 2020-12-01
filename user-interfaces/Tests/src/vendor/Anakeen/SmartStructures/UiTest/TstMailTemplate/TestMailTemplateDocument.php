<?php


namespace Anakeen\SmartStructures\UiTest\TstMailTemplate;

use Anakeen\SmartStructures\Mailtemplate\IMailTemplateAdditionalKeys;
use SmartStructure\Mailtemplate;

class TestMailTemplateDocument extends \Anakeen\SmartElement implements IMailTemplateAdditionalKeys
{
    public function getMailTemplateAdditionalKeys(Mailtemplate $template)
    {
        return array("MYKEY1" => "TST_EXTRA_KEY_MAIL_TEMPLATE", "MYKEY2" => true, "MYKEY3" => ["TST", "EXTRA", "KEY", "MAIL", "TEMPLATE"]);
    }
}
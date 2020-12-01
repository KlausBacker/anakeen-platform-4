<?php


namespace Anakeen\Pu\TestMailTemplate\Data;

use Anakeen\SmartStructures\Mailtemplate\IMailTemplateAdditionalKeys;
use SmartStructure\Mailtemplate;

class TestMailTemplateBehaviour extends \Anakeen\SmartElement implements IMailTemplateAdditionalKeys
{
    public function getMailTemplateAdditionalKeys(Mailtemplate $template)
    {
        return [
            "Custom subject" => "Un sujet en or",
            "Custom words" => "Voilà 10 <b> 12 </b>",
            "N1GT10" => intval($this->getRawValue("tst_number1")) > 10,
            "N2LT0" => intval($this->getRawValue("tst_number2")) < 0,
            "sumOf12" => intval($this->getRawValue("tst_number1")) + intval($this->getRawValue("tst_number2")),

            "TWOKEYS" => [["keyone"=>"K1"],["keyone"=>"K2"]]
        ];
    }
}
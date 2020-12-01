<?php


namespace Anakeen\SmartStructures\Mailtemplate;


use SmartStructure\Mailtemplate;

interface IMailTemplateAdditionalKeys
{
    /**
     * @param Mailtemplate $template
     * @return array ["key": string => bool|string|[string] ]
     */
    public function getMailTemplateAdditionalKeys(MailTemplate $template);
}
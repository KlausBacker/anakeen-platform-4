<?php


namespace Anakeen\SmartStructures\Mailtemplate;

use SmartStructure\Mailtemplate;

interface IMailTemplateAdditionalKeys
{
    /**
     * Return additionals keys to be used in body and subject of mail templates
     * These keys can be used like smart element field defaults keys
     * Must be referenced under brackets in body or subject email
     *
     * @param Mailtemplate $template the mail template used to compose email
     * @return array ["key": string => bool|string|string[] ] The additionnals keys.
     */
    public function getMailTemplateAdditionalKeys(MailTemplate $template);
}

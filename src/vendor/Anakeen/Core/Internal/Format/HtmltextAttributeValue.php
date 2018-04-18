<?php

namespace Anakeen\Core\Internal\Format;

use \Anakeen\Core\DocManager;

class HtmltextAttributeValue extends StandardAttributeValue
{
    const defaultStyle = 'D';
    const isoStyle = 'I';
    const isoWTStyle = 'U';
    const frenchStyle = 'F';

    public function __construct(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $v, $stripHtmlTag = false)
    {
        parent::__construct($oa, $v);
        if ($stripHtmlTag) {
            $this->displayValue = html_entity_decode(strip_tags($this->displayValue), ENT_NOQUOTES, 'UTF-8');
        }
    }
}

<?php

namespace Anakeen\Core\Internal\Format;

use \Anakeen\Core\SEManager;

class ThesaurusAttributeValue extends DocidAttributeValue
{
    public static $thcDoc = null;
    public static $thcDocTitle = array();

    public function __construct(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $v, \Anakeen\Core\Internal\SmartElement & $doc, $iconsize = 24, $relationNoAccessText = '')
    {
        parent::__construct($oa, $v, $doc, $iconsize, $relationNoAccessText);
        if ($this->visible) {
            if (isset(self::$thcDocTitle[$this->value])) {
                // use local cache
                $this->displayValue = self::$thcDocTitle[$this->value];
            } else {
                if (self::$thcDoc === null) {
                    self::$thcDoc = createTmpDoc("", "THCONCEPT");
                }
                $rawValue = getTDoc("", $this->value);
                self::$thcDoc->affect($rawValue);
                $this->displayValue = self::$thcDoc->getTitle();
                // set local cache
                self::$thcDocTitle[$this->value] = $this->displayValue;
            }
        }
    }
}

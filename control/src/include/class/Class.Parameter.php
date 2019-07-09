<?php
/*
 * @author Anakeen
 * @package FDL
 */
/**
 * Parameter Class
 * @author Anakeen
 */

class Parameter
{
    
    public $name;
    public $label;
    public $default;
    public $type;
    public $needed;
    public $volatile;
    public $oninstall;
    public $onupgrade;
    public $onedit;
    public $values; // Used for enum type parameters.
    public $value;
    
    public function getVisibility($operation)
    {
        $visibility = '';
        switch ($operation) {
            case 'install':
                $visibility = ($this->oninstall != '') ? $this->oninstall : 'W';
                break;

            case 'upgrade':
                $visibility = ($this->onupgrade != '') ? $this->onupgrade : 'H';
                if ($this->needed == 'Y' && $this->value == '') {
                    $visibility = 'W';
                }
                break;

            case 'parameter':
                $visibility = ($this->onedit != '') ? $this->onedit : 'R';
                break;
        }
        return $visibility;
    }
    
    /**
     * Replace unsupported XML chars:
     * - Replace control chars with their corresponding Unicode pictogram from the Control Pictures Block.
     * - Replace unsupported XML chars with the Unicode replacement symbol.
     *
     * @param $str
     * @return string
     */
    public static function cleanXMLUTF8($str)
    {
        /*
         * Pass #1
         *
         * Map invalid control chars to theirs corresponding pictogram from the Control Pictures block:
         * - https://codepoints.net/control_pictures
        */
        $str2 = preg_replace_callback('/(?P<char>[\x{00}-\x{08}\x{0B}\x{0C}\x{0E}-\x{1F}])/u', function ($m) {
            return "\xe2\x90" . chr(0x80 + ord($m['char']));
        }
            , $str);
        if ($str2 === null) {
            /* str is not a valid UTF8 string, so we return the original string */
            return $str;
        }
        /*
         * Pass #2
         *
         * Replace unsupported XML chars
        */
        $str2 = preg_replace('/[^\x{09}\x{0A}\x{0D}\x{20}-\x{d7ff}\x{e000}-\x{fffd}\x{10000}-\x{10ffff}]/u', "\xef\xbf\xbd", $str2);
        if ($str2 === null) {
            /* str is not a valid UTF8 string, so we return the original string */
            return $str;
        }
        return $str2;
    }
}

<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 31/05/14
 * Time: 14:08
 */

namespace Dcp\Ui;

class JsonHandler
{
    
    protected static $_messages = array(
        JSON_ERROR_NONE => 'No error has occurred',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed ',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
    );
    /**
     * @param string $value
     * @param int $options
     * @return string
     */
    public static function encode($value, $options = 0)
    {
        $result = json_encode($value, $options);
        
        if ($result !== false) {
            return $result;
        }
        
        throw new DecodeException(static::$_messages[json_last_error() ]);
    }
    /**
     * @param $value
     * @param int $options
     */
    public static function encodeForHTML($value, $options = 0)
    {
        if ($options === 0) {
            $options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP;
        }
        return self::encode($value, $options);
    }
    
    public static function decode($json, $assoc = false)
    {
        $result = json_decode($json, $assoc);
        
        if ($result !== false) {
            return $result;
        }
        
        throw new DecodeException(static::$_messages[json_last_error() ]);
    }
    
    public static function decodeAsArray($json)
    {
        return self::decode($json, true);
    }
}

class DecodeException extends \RuntimeException
{
}

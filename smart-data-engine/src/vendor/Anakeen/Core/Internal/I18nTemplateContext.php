<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Core\Internal;

use Anakeen\Core\ContextManager;

class I18nTemplateContext implements \ArrayAccess
{
    public $i18n;
    /**
     * Template extra keys
     * @var array
     */
    protected $keys = array();

    public function __construct()
    {
        $this->i18n = function ($s) {
            return self::_i18n($s);
        };
    }

    /**
     * Translate text using gettext context if exists
     * @param string $s text to translate
     *
     * @return string
     */
    protected static function _i18n($s)
    {
        if (!$s) {
            return '';
        }
        if (preg_match("/^([^(::)]+)::(.+)$/", $s, $reg)) {
            $i18n = ___($reg[2], $reg[1]);
            if ($i18n === $reg[1]) {
                $i18n = _($s);
                if ($i18n === $s) {
                    return $reg[1];
                }
            }
            return $i18n;
        }
        return _($s);
    }

    public function userLocale()
    {
        $localeId = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_LANG");
        $config = ContextManager::getLocaleConfig($localeId);
        return $config["culture"];
    }

    /**
     *
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     * @return boolean true on success or false on failure.
     *                      </p>
     *                      <p>
     *                      The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->keys);
    }

    /**
     *
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     * @return mixed Can return all value types.
     */
    public function &offsetGet($offset)
    {
        $x = &$this->keys[$offset];
        if (is_callable($x)) {
            return call_user_func($x);
        }
        return $x;
    }

    /**
     *
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     * @return $this
     */
    public function offsetSet($offset, $value)
    {
        $this->keys[$offset] = $value;
        return $this;
    }

    /**
     *
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     * @return $this
     */
    public function offsetUnset($offset)
    {
        unset($this->keys[$offset]);
        return $this;
    }
}

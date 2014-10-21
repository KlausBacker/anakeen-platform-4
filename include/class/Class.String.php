<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package CONTROL
*/
/**
 * Set of classes to handle automatic Text/HTML string serialization.
 *
 * The idea is to create new Text() or HTML() objects without
 * caring about the presentation.
 *
 * Then, the objects are serialized to text according the presentation
 * mode (CLI or Web) and text is properly escaped or rendered:
 *
 *       | CLI output        | Web output
 * ------+-------------------+--------------------------
 *  text | <passthrough>     | Apply htmlspecialchars()
 * ------+-------------------+--------------------------
 *  html | Strip/render HTML | <passthrough>
 *       | to text           |
 *
 */
namespace String;

class Exception extends \Exception
{
}

interface Stringable {
    public function __construct($str);
    public function __toString();
}

class HTML implements Stringable
{
    protected $str = '';
    public function __construct($str)
    {
        $this->str = $str;
    }
    public function __toString()
    {
        return (string)$this->renderString();
    }
    protected function renderString()
    {
        if (PHP_SAPI == 'cli') {
            return $this->textRenderString();
        }
        return $this->htmlRenderString();
    }
    protected function textRenderString()
    {
        $str = $this->str;
        $str = preg_replace('|</p>|i', "\n\n", $str);
        $str = preg_replace('|<br\s*/?>|i', "\n", $str);
        $str = preg_replace('|<[^>]+>|', "", $str);
        return $str;
    }
    protected function htmlRenderString()
    {
        return $this->str;
    }
}

class Text implements Stringable
{
    public function __construct($str)
    {
        $this->str = $str;
    }
    public function __toString()
    {
        return (string)$this->renderString();
    }
    protected function renderString()
    {
        if (PHP_SAPI == 'cli') {
            return $this->textRenderString();
        }
        return $this->htmlRenderString();
    }
    protected function textRenderString()
    {
        return $this->str;
    }
    protected function htmlRenderString()
    {
        return htmlspecialchars($this->str);
    }
}

class sprintf
{
    protected $args = array();
    public function __construct()
    {
        $this->args = func_get_args();
    }
    public function __toString()
    {
        $args = array();
        foreach ($this->args as $arg) {
            $args[] = (string)$arg;
        }
        return call_user_func_array('sprintf', $this->args);
    }
}

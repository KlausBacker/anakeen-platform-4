<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class ItemMenu extends ElementMenu implements \JsonSerializable
{
    protected $url = '';
    protected  $target = '_self';
    protected $confirmationText=null;
    
    public function __construct($identifier, $label, $url = '')
    {
        parent::__construct($identifier, $label);
        $this->url = $url;
    }
    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }
    /**
     * Set url target : default is _self
     * @param string $target
     * @return $this
     */
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }


    /**
     * If text is not null, ask a confirmation before send request
     * @param string|null $text confirmation text
     * @return $this
     */
    public function useConfirm($text) {
        $this->confirmationText=$text;
        return $this;
    }
    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json["type"] = "itemMenu";
        $json["url"] = $this->url;
        $json["target"] = $this->target;
        $json["confirmationText"] = $this->confirmationText;
        return $json;
    }
}

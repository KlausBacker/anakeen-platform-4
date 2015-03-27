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
    protected $target = '_self';
    protected $confirmationText = null;
    protected $confirmationOptions = null;
    protected $targetOptions = null;
    public function __construct($identifier, $label = '', $url = '')
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
     * @param string $target window target
     * @param MenuTargetOptions $dialogOptions
     * @return $this
     */
    public function setTarget($target, MenuTargetOptions $dialogOptions = null)
    {
        $this->target = $target;
        if ($dialogOptions === null) {
            $dialogOptions = new MenuTargetOptions();
        }
        $this->targetOptions = $dialogOptions;
        return $this;
    }
    /**
     * If text is not null, ask a confirmation before send request
     * @param string|null $question confirmation text
     * @param \Dcp\Ui\MenuConfirmOptions $options additionnal options (confirmTitle, confirmOkMessage, confirmCancelMEssage, windowWidth, windowHeight)
     * @return $this
     */
    public function useConfirm($question, MenuConfirmOptions $options = null)
    {
        $this->confirmationText = $question;
        if ($options === null) {
            $options = new MenuConfirmOptions();
        }
        $this->confirmationOptions = $options;
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
        $json["targetOptions"] = $this->targetOptions;
        $json["confirmationText"] = $this->confirmationText;
        $json["confirmationOptions"] = $this->confirmationOptions;
        return $json;
    }
}

class MenuConfirmOptions extends MenuTargetOptions
{
    public $confirmButton = null;
    public $cancelButton = null;
    
    public function __construct()
    {
        $this->cancelButton = ___("Cancel", "UiMenu");
        $this->confirmButton = ___("Confirm", "UiMenu");
    }
}
class MenuTargetOptions
{
    /**
     * @var string title of window
     */
    public $title = null;
    public $windowWidth = "300px";
    public $windowHeight = "200px";
    public $modal = false;
}

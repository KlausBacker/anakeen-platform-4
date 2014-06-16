<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class ElementMenu implements \JsonSerializable
{
    const VisibilityVisible = "visible";
    const VisibilityHidden = "hidden";
    const VisibilityDisabled = "disabled";
    /**
     * @var null
     */
    protected $id = null;
    /**
     * @var string
     */
    public $label = '';
    /**
     * @var string
     */
    public $tooltipLabel = '';
    /**
     * @var string
     */
    public $htmlAttributes = '';
    /**
     * @var string
     */
    public $visibility = self::VisibilityVisible;
    /**
     * @var string
     */
    public $iconPath = '';
    /**
     * @var int
     */
    public $iconSize = 12;
    
    public function __construct($identifier, $label = '')
    {
        $this->id = $identifier;
        $this->label = $label;
    }
    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }
    /**
     * @param string $tooltipLabel
     * @return $this
     */
    public function setTooltipLabel($tooltipLabel)
    {
        $this->tooltipLabel = $tooltipLabel;
        return $this;
    }
    /**
     * Set a custom css class to element menu
     * @param string $attrid Html attribute name
     * @param string $value attribute value
     * @return $this
     */
    public function setHtmlAttribute($attrid, $value)
    {
        $this->htmlAttributes[$attrid] = $value;
        return $this;
    }
    /**
     * Define visibility element : visible, hidden or disabled
     * @param string $visibility
     * @return $this
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
        return $this;
    }
    /**
     * @param string $imagePath local server relative path to image
     * @param int $imageWidth image width in pixel
     * @return $this
     */
    public function setIcon($imagePath, $imageWidth = 12)
    {
        $this->iconPath = $imagePath;
        $this->iconSize = $imageWidth;
        return $this;
    }
    
    protected function getIconUrl()
    {
        if ($this->iconPath) {
            if ($this->iconSize > 0) {
                return sprintf('resizeimg.php?img=%s&size=%d', urlencode($this->iconPath) , $this->iconSize);
            } else {
                return $this->iconSize;
            }
        } else {
            return $this->iconPath;
        }
    }
    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return array(
            "id" => $this->id,
            "type" => "listMenu",
            "label" => $this->label,
            "tooltipLabel" => $this->tooltipLabel,
            "htmlAttributes" => $this->htmlAttributes,
            "visibility" => $this->visibility,
            "iconUrl" => $this->getIconUrl()
        );
    }
}

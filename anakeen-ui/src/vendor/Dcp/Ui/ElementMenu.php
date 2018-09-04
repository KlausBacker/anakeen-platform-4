<?php

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
     * Label text of element
     * @var string
     */
    protected $textLabel = '';
    /**
     * Formated label of elemet
     * @var string
     */
    protected $htmlLabel = '';
    /**
     * @var string right,left,top,bottom
     */
    protected $tooltipLabel = '';
    /**
     * @var string
     */
    protected $tooltipPlacement = '';
    /**
     * @var bool
     */
    protected $tooltipHtml = false;
    /**
     * @var array
     */
    protected $htmlAttributes = array();
    /**
     * @var string
     */
    protected $visibility = self::VisibilityVisible;
    /**
     * @var string
     */
    protected $iconPath = '';
    /**
     * Text displayed before label
     * @var string
     */
    protected $beforeLabelHtmlText = '';
    /**
     * @var bool if an important menu
     */
    protected $isImportant = false;
    /**
     * @var int
     */
    protected $iconSize = 12;

    public function __construct($identifier, $label = '')
    {
        $this->id = $identifier;
        $this->textLabel = $label;
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * set raw text to the menu
     * @param string $label
     * @return $this
     */
    public function setTextLabel($label)
    {
        $this->textLabel = $label;
        return $this;
    }

    /**
     * set html fragment to the menu
     * @param string $label
     * @return $this
     */
    public function setHtmlLabel($label)
    {
        $this->htmlLabel = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getHtmlLabel()
    {
        return $this->htmlLabel;
    }

    /**
     * Set tooltip for the menu
     * @param string $tooltipLabel
     * @param string $placement "top","right","left", "bottom"
     * @param bool   $html      set to true if content is html fragment
     * @return $this
     */
    public function setTooltipLabel($tooltipLabel, $placement = "", $html = false)
    {
        $this->tooltipLabel = $tooltipLabel;
        $this->tooltipPlacement = $placement;
        $this->tooltipHtml = $html;
        return $this;
    }

    /**
     * Set a custom html attribute to element menu
     * @param string $attrid Html attribute name
     * @param string $value  attribute value
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
     * @param string $imagePath  local server relative path to image
     * @param int    $imageWidth image width in pixel
     * @return $this
     */
    public function setIcon($imagePath, $imageWidth = 12)
    {
        $this->iconPath = $imagePath;
        $this->iconSize = $imageWidth;
        return $this;
    }

    /**
     * Add a html tag before label
     * Only use if no icon is set
     * @param $htmtText
     * @return $this
     */
    public function setBeforeContent($htmtText)
    {
        $this->beforeLabelHtmlText = $htmtText;
        return $this;
    }

    /**
     * Get before content label
     * @return string
     */
    public function getBeforeContent()
    {
        return $this->beforeLabelHtmlText;
    }

    /**
     * @return string
     */
    public function getTextLabel()
    {
        return $this->textLabel;
    }

    /**
     * @return string
     */
    public function getTooltipLabel()
    {
        return $this->tooltipLabel;
    }

    /**
     * @return string
     */
    public function getTooltipPlacement()
    {
        return $this->tooltipPlacement;
    }

    /**
     * @return boolean
     */
    public function isTooltipHtml()
    {
        return $this->tooltipHtml;
    }

    /**
     * @return array
     */
    public function getHtmlAttributes()
    {
        return $this->htmlAttributes;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @return string
     */
    public function getIconPath()
    {
        return $this->iconPath;
    }

    /**
     * @return string
     */
    public function getBeforeLabelHtmlText()
    {
        return $this->beforeLabelHtmlText;
    }

    /**
     * @return boolean
     */
    public function isIsImportant()
    {
        return $this->isImportant;
    }

    /**
     * @return int
     */
    public function getIconSize()
    {
        return $this->iconSize;
    }

    /**
     * Set important status
     * Means that menu will not hide when window size is tiny
     * @param bool $isImportant
     */
    public function setImportant($isImportant)
    {
        $this->isImportant = (bool)$isImportant;
    }

    protected function getIconUrl()
    {
        if ($this->iconPath) {
            if ($this->iconSize > 0) {
                return sprintf('api/v2/images/assets/sizes/%d/%s', $this->iconSize, $this->iconPath);
            }
        }
        return $this->iconPath;
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
            "label" => $this->textLabel,
            "htmlLabel" => $this->htmlLabel,
            "tooltipLabel" => $this->tooltipLabel,
            "tooltipPlacement" => $this->tooltipPlacement,
            "tooltipHtml" => $this->tooltipHtml,
            "htmlAttributes" => empty($this->htmlAttributes) ? null : $this->htmlAttributes,
            "visibility" => $this->visibility,
            "beforeContent" => $this->beforeLabelHtmlText,
            "important" => $this->isImportant,
            "iconUrl" => $this->getIconUrl()
        );
    }
}

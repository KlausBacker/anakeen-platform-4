<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class RenderOptions implements \JsonSerializable
{
    
    protected $options = array();
    protected $textOptions = null;
    protected $arrayOptions = null;
    protected $enumOptions = null;
    protected $commonOptions = null;
    
    public function __construct()
    {
        $this->setCustomOption(CommonRenderOptions::type, array(
            CommonRenderOptions::showEmptyContentOption => null,
            CommonRenderOptions::labelPositionOption => CommonRenderOptions::leftPosition,
            "linkTitle" => "",
            "linkTarget" => "_self",
            "linkConfirm" => false,
            "linkTextConfirm" => ""
        ));
        $this->setCustomOption("types", array(
            "account" => array(
                "noAccessText" => ___("Account access deny", 'ddui')
            ) ,
            "date" => array(
                "format" => _("Y-m-d")
            ) ,
            "docid" => array(
                "noAccessText" => ___("Information access deny", 'ddui')
            ) ,
            EnumRenderOptions::type => array(
                "boolColor" => "",
                EnumRenderOptions::useFirstChoiceOption => true
            ) ,
            "file" => array(
                "downloadInline" => false
            ) ,
            "image" => array(
                "downloadInline" => false,
                "width" => "80px"
            ) ,
            "money" => array(
                "format" => "%!.2n"
            ) ,
            TextRenderOptions::type => array(
                TextRenderOptions::sizeOption => null,
                TextRenderOptions::formatOption => "%s"
            ) ,
            "time" => array(
                "format" => "%H:%M"
            ) ,
            "timestamp" => array(
                "format" => _("Y-m-d") . " %H:%M"
            )
        ));
    }
    /**
     * Set custom option
     * @param string $optName
     * @param mixed $optValue
     */
    public function setCustomOption($optName, $optValue)
    {
        $this->options[$optName] = $optValue;
    }
    /**
     * @param string $attrid
     * @return CommonRenderOptions
     */
    public function commonOption($attrid = '')
    {
        if ($this->commonOptions === null) {
            $this->commonOptions = new CommonRenderOptions($this);
        }
        $this->commonOptions->setScope($attrid);
        return $this->commonOptions;
    }
    /**
     * @param string $attrid
     * @return TextRenderOptions
     */
    public function text($attrid = '')
    {
        if ($this->textOptions === null) {
            $this->textOptions = new TextRenderOptions($this);
        }
        $this->textOptions->setScope($attrid);
        return $this->textOptions;
    }
    /**
     * @param string $attrid
     * @return TextRenderOptions
     */
    public function typeArray($attrid = '')
    {
        if ($this->arrayOptions === null) {
            $this->arrayOptions = new ArrayRenderOptions($this);
        }
        $this->arrayOptions->setScope($attrid);
        return $this->arrayOptions;
    }
    /**
     * @param string $attrid
     * @return EnumRenderOptions
     */
    public function enum($attrid = '')
    {
        if ($this->enumOptions === null) {
            $this->enumOptions = new EnumRenderOptions($this);
        }
        $this->enumOptions->setScope($attrid);
        return $this->enumOptions;
    }
    /**
     * Add an option for an attribute type
     * @param string $attrType attribute type
     * @param string $optName option name
     * @param string $optValue option value
     */
    public function setAttributeTypeOption($attrType, $optName, $optValue)
    {
        if ($attrType === "common") {
            
            $this->options[$attrType][$optName] = $optValue;
        } else {
            $this->options["types"][$attrType][$optName] = $optValue;
        }
    }
    /**
     * Apply option to a specific attribute
     * @param string $attrId attribute identifier
     * @param string $optName option name
     * @param string $optValue option value
     */
    public function setAttributeScopeOption($attrId, $optName, $optValue)
    {
        $this->options["attributes"][$attrId][$optName] = $optValue;;
    }
    /**
     * Add new option
     * @param CommonRenderOptions $opt
     */
    public function setOption(CommonRenderOptions $opt)
    {
        if ($opt->getScope()) {
            $this->setAttributeScopeOption($opt->getScope() , $opt->getLocalOptionName() , $opt->getLocalOptionValue());
        } else {
            $this->setAttributeTypeOption($opt::type, $opt->getLocalOptionName() , $opt->getLocalOptionValue());
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
        return $this->options;
    }
}


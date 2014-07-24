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
    protected $docidOptions = null;
    protected $enumOptions = null;
    protected $accountOptions = null;
    protected $thesaurusOptions = null;
    protected $intOptions = null;
    protected $doubleOptions = null;
    protected $longtextOptions = null;
    protected $htmltextOptions = null;
    protected $timeOptions = null;
    protected $dateOptions = null;
    protected $timestampOptions = null;
    protected $commonOptions = null;
    
    public function __construct()
    {
        $this->setCustomOption(CommonRenderOptions::type, array(
            CommonRenderOptions::showEmptyContentOption => null,
            CommonRenderOptions::labelPositionOption => CommonRenderOptions::leftPosition,
            CommonRenderOptions::autoCompleteHtmlLabelOption => "",
            CommonRenderOptions::inputHtmlTooltip => "",
            CommonRenderOptions::htmlLinkOption => new HtmlLinkOptions()
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
            HtmltextRenderOptions::type => array(
                HtmltextRenderOptions::toolbarOption => "Simple",
                HtmltextRenderOptions::toolbarStartupExpandedOption => false,
                HtmltextRenderOptions::heightOption => "100px"
            ) ,
            LongtextRenderOptions::type => array(
                LongtextRenderOptions::displayedLineNumberOption => 0
            ) ,
            IntRenderOptions::type => array(
                IntRenderOptions::maxOption => 2147483647,
                IntRenderOptions::minOption => - 2147483647,
            ) ,
            DoubleRenderOptions::type => array(
                DoubleRenderOptions::maxOption => null,
                DoubleRenderOptions::minOption => null,
                DoubleRenderOptions::decimalPrecisionOption => 2
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
     * @return dateRenderOptions
     */
    public function date($attrid = '')
    {
        if ($this->dateOptions === null) {
            $this->dateOptions = new DateRenderOptions($this);
        }
        $this->dateOptions->setScope($attrid);
        return $this->dateOptions;
    }
    /**
     * @param string $attrid
     * @return TimestampRenderOptions
     */
    public function time($attrid = '')
    {
        if ($this->timeOptions === null) {
            $this->timeOptions = new TimeRenderOptions($this);
        }
        $this->timeOptions->setScope($attrid);
        return $this->timeOptions;
    }
    /**
     * @param string $attrid
     * @return TimestampRenderOptions
     */
    public function timestamp($attrid = '')
    {
        if ($this->timestampOptions === null) {
            $this->timestampOptions = new TimestampRenderOptions($this);
        }
        $this->timestampOptions->setScope($attrid);
        return $this->timestampOptions;
    }
    /**
     * @param string $attrid
     * @return HtmltextRenderOptions
     */
    public function htmltext($attrid = '')
    {
        if ($this->htmltextOptions === null) {
            $this->htmltextOptions = new HtmltextRenderOptions($this);
        }
        $this->htmltextOptions->setScope($attrid);
        return $this->htmltextOptions;
    }
    /**
     * @param string $attrid
     * @return IntRenderOptions
     */
    public function int($attrid = '')
    {
        if ($this->intOptions === null) {
            $this->intOptions = new IntRenderOptions($this);
        }
        $this->intOptions->setScope($attrid);
        return $this->intOptions;
    }
    /**
     * @param string $attrid
     * @return DoubleRenderOptions
     */
    public function double($attrid = '')
    {
        if ($this->doubleOptions === null) {
            $this->doubleOptions = new DoubleRenderOptions($this);
        }
        $this->doubleOptions->setScope($attrid);
        return $this->doubleOptions;
    }
    /**
     * @param string $attrid
     * @return ArrayRenderOptions
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
     * @return DocidRenderOptions
     */
    public function docid($attrid = '')
    {
        if ($this->docidOptions === null) {
            $this->docidOptions = new DocidRenderOptions($this);
        }
        $this->docidOptions->setScope($attrid);
        return $this->docidOptions;
    }
    /**
     * @param string $attrid
     * @return LongtextRenderOptions
     */
    public function longtext($attrid = '')
    {
        if ($this->longtextOptions === null) {
            $this->longtextOptions = new LongtextRenderOptions($this);
        }
        $this->longtextOptions->setScope($attrid);
        return $this->longtextOptions;
    }
    /**
     * @param string $attrid
     * @return AccountRenderOptions
     */
    public function account($attrid = '')
    {
        if ($this->accountOptions === null) {
            $this->accountOptions = new AccountRenderOptions($this);
        }
        $this->accountOptions->setScope($attrid);
        return $this->accountOptions;
    }
    /**
     * @param string $attrid
     * @return ThesaurusRenderOptions
     */
    public function thesaurus($attrid = '')
    {
        if ($this->thesaurusOptions === null) {
            $this->thesaurusOptions = new ThesaurusRenderOptions($this);
        }
        $this->thesaurusOptions->setScope($attrid);
        return $this->thesaurusOptions;
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
     * Add an option for an attribute type
     * @param string $attrType attribute type
     * @param string $optName option name
     * @return mixed
     */
    public function getAttributeTypeOption($attrType, $optName)
    {
        if ($attrType === "common") {
            if ($this->options[$attrType][$optName]) {
                return $this->options[$attrType][$optName];
            }
        } else {
            if (isset($this->options["types"][$attrType][$optName])) {
                return $this->options["types"][$attrType][$optName];
            }
        }
        return null;
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
     * Get option to a specific attribute
     * @param string $attrId attribute identifier
     * @param string $optName option name
     * @return mixed
     */
    public function getAttributeScopeOption($attrId, $optName)
    {
        if (isset($this->options["attributes"][$attrId][$optName])) {
            return $this->options["attributes"][$attrId][$optName];
        }
        return null;
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
     * @param $attrId
     * @return array|null
     */
    public function getAttributeOptions($attrId)
    {
        if (isset($this->options["attributes"][$attrId])) {
            return $this->options["attributes"][$attrId];
        }
        return null;
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


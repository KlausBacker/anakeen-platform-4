<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class RenderOptions implements \JsonSerializable
{
    
    protected $options = array();
    protected $documentOptions = null;
    protected $textOptions = null;
    protected $arrayOptions = null;
    protected $docidOptions = null;
    protected $enumOptions = null;
    protected $colorOptions = null;
    protected $accountOptions = null;
    protected $thesaurusOptions = null;
    protected $intOptions = null;
    protected $imageOptions = null;
    protected $moneyOptions = null;
    protected $fileOptions = null;
    protected $doubleOptions = null;
    protected $longtextOptions = null;
    protected $frameOptions = null;
    protected $passwordOptions = null;
    protected $htmltextOptions = null;
    protected $timeOptions = null;
    protected $tabOptions = null;
    protected $dateOptions = null;
    protected $timestampOptions = null;
    protected $commonOptions = null;
    protected $labels = array();
    
    public function __construct()
    {
        $imageLinkOption = new HtmlLinkOptions();
        $imageLinkOption->target = "_dialog";
        $imageLinkOption->windowHeight = "300px";
        $imageLinkOption->windowWidth = "400px";
        $this->setCustomOption(CommonRenderOptions::type, array(
            CommonRenderOptions::showEmptyContentOption => null,
            CommonRenderOptions::labelPositionOption => CommonRenderOptions::autoPosition,
            CommonRenderOptions::autoCompleteHtmlLabelOption => "",
            CommonRenderOptions::inputHtmlTooltip => "",
            CommonRenderOptions::htmlLinkOption => new HtmlLinkOptions()
        ));
        $this->setCustomOption("types", array(
            "account" => array(
                "noAccessText" => ___("Account access deny", 'ddui')
            ) ,
            "date" => array() ,
            "docid" => array(
                "noAccessText" => ___("Information access deny", 'ddui')
            ) ,
            EnumRenderOptions::type => array(
                EnumRenderOptions::displayOption => EnumRenderOptions::listDisplay,
                EnumRenderOptions::useFirstChoiceOption => false,
                EnumRenderOptions::useSourceUriOption => false,
                EnumRenderOptions::useOtherChoiceOption => false
            ) ,
            FileRenderOptions::type => array(
                FileRenderOptions::contentDispositionOption => FileRenderOptions::fileAttachmentDisposition
            ) ,
            
            ImageRenderOptions::type => array(
                ImageRenderOptions::htmlLinkOption => $imageLinkOption,
                ImageRenderOptions::contentDispositionOption => ImageRenderOptions::fileInlineDisposition,
                ImageRenderOptions::thumbnailWidthOption => 48,
            ) ,
            HtmltextRenderOptions::type => array(
                HtmltextRenderOptions::toolbarOption => "Simple",
                HtmltextRenderOptions::toolbarStartupExpandedOption => true,
                HtmltextRenderOptions::heightOption => "120px"
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
                DoubleRenderOptions::decimalPrecisionOption => null
            ) ,
            MoneyRenderOptions::type => array(
                MoneyRenderOptions::maxOption => null,
                MoneyRenderOptions::minOption => null,
                MoneyRenderOptions::decimalPrecisionOption => 2,
                MoneyRenderOptions::currencyOption => '€'
            ) ,
            TextRenderOptions::type => array(
                TextRenderOptions::maxLengthOption => null,
                TextRenderOptions::formatOption => "{{displayValue}}"
            ) ,
            ArrayRenderOptions::type => array(
                ArrayRenderOptions::rowCountThresholdOption => - 1,
                ArrayRenderOptions::arrayBreakPointsOption => array(
                    "transpositionRule" => ArrayRenderOptions::transpositionRule,
                    "upRule" => ArrayRenderOptions::upRule
                )
            ) ,
            "time" => array() ,
            "timestamp" => array()
        ));
    }
    /**
     * Set custom option
     *
     * @param string $optName
     * @param mixed  $optValue
     */
    public function setCustomOption($optName, $optValue)
    {
        $this->options[$optName] = $optValue;
    }
    /**
     * Set common option
     *
     * @param string $attrid attribute identifier
     *
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
     * Set options for document
     * @return DocumentRenderOptions
     */
    public function document()
    {
        if ($this->documentOptions === null) {
            $this->documentOptions = new DocumentRenderOptions($this);
        }
        $this->documentOptions->setScope("document");
        return $this->documentOptions;
    }
    /**
     * @param string $attrid attribute identifier
     *
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
     * @param string $attrid attribute identifier
     *
     * @return ImageRenderOptions
     */
    public function image($attrid = '')
    {
        if ($this->imageOptions === null) {
            $this->imageOptions = new ImageRenderOptions($this);
        }
        $this->imageOptions->setScope($attrid);
        return $this->imageOptions;
    }
    /**
     * @param string $attrid attribute identifier
     *
     * @return FileRenderOptions
     */
    public function file($attrid = '')
    {
        if ($this->fileOptions === null) {
            $this->fileOptions = new FileRenderOptions($this);
        }
        $this->fileOptions->setScope($attrid);
        return $this->fileOptions;
    }
    /**
     * @param string $attrid attribute identifier
     *
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
     * @param string $attrid attribute identifier
     *
     * @return TimeRenderOptions
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
     * @param string $attrid attribute identifier
     *
     * @return TabRenderOptions
     */
    public function tab($attrid = '')
    {
        if ($this->tabOptions === null) {
            $this->tabOptions = new TabRenderOptions($this);
        }
        $this->tabOptions->setScope($attrid);
        return $this->tabOptions;
    }
    /**
     * @param string $attrid attribute identifier
     *
     * @return FrameRenderOptions
     */
    public function frame($attrid = '')
    {
        if ($this->frameOptions === null) {
            $this->frameOptions = new FrameRenderOptions($this);
        }
        $this->frameOptions->setScope($attrid);
        return $this->frameOptions;
    }
    /**
     * @param string $attrid attribute identifier
     *
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
     * @param string $attrid attribute identifier
     *
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
     * @param string $attrid attribute identifier
     *
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
     * @param string $attrid attribute identifier
     *
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
     * @param string $attrid attribute identifier
     *
     * @return MoneyRenderOptions
     */
    public function money($attrid = '')
    {
        if ($this->moneyOptions === null) {
            $this->moneyOptions = new MoneyRenderOptions($this);
        }
        $this->moneyOptions->setScope($attrid);
        return $this->moneyOptions;
    }
    /**
     * @param string $attrid attribute identifier
     *
     * @return ArrayRenderOptions
     */
    public function arrayAttribute($attrid = '')
    {
        if ($this->arrayOptions === null) {
            $this->arrayOptions = new ArrayRenderOptions($this);
        }
        $this->arrayOptions->setScope($attrid);
        return $this->arrayOptions;
    }
    /**
     * @param string $attrid attribute identifier
     *
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
     * @param string $attrid attribute identifier
     *
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
     * @param string $attrid attribute identifier
     *
     * @return PasswordRenderOptions
     */
    public function password($attrid = '')
    {
        if ($this->passwordOptions === null) {
            $this->passwordOptions = new PasswordRenderOptions($this);
        }
        $this->passwordOptions->setScope($attrid);
        return $this->passwordOptions;
    }
    /**
     * @param string $attrid attribute identifier
     *
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
     * @param string $attrid attribute identifier
     *
     * @return ColorRenderOptions
     */
    public function color($attrid = '')
    {
        if ($this->colorOptions === null) {
            $this->colorOptions = new ColorRenderOptions($this);
        }
        $this->colorOptions->setScope($attrid);
        return $this->colorOptions;
    }
    /**
     * @param string $attrid attribute identifier
     *
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
     * @param string $attrid attribute identifier
     *
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
     *
     * @param string $attrType attribute type
     * @param string $optName  option name
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
     *
     * @param string $attrType attribute type
     * @param string $optName  option name
     *
     * @return mixed
     */
    public function getAttributeTypeOption($attrType, $optName)
    {
        if ($attrType === "common") {
            if (!empty($this->options[$attrType][$optName])) {
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
     *
     * @param string $attrId   attribute identifier attribute identifier
     * @param string $optName  option name
     * @param string $optValue option value
     */
    public function setAttributeScopeOption($attrId, $optName, $optValue)
    {
        $this->options["attributes"][$attrId][$optName] = $optValue;;
    }
    /**
     * Apply option to a specific scope
     *
     * @param string $scope    context to record option
     * @param string $optName  option name
     * @param string $optValue option value
     */
    public function setScopeOption($scope, $optName, $optValue)
    {
        $this->options[$scope][$optName] = $optValue;
    }
    /**
     * Get option from a specific scope
     *
     * @param string $scope    context to record option
     * @param string $optName  option name
     * @return mixed the option value
     */
    public function getScopeOption($scope, $optName)
    {
        if (isset($this->options[$scope][$optName])) {
            return $this->options[$scope][$optName];
        }
        return null;
    }
    /**
     * Get option to a specific attribute
     *
     * @param string $attrId  attribute identifier attribute identifier
     * @param string $optName option name
     *
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
     *
     * @param BaseRenderOptions $opt
     */
    public function setOption(BaseRenderOptions $opt)
    {
        if ($opt->getScope()) {
            $this->setAttributeScopeOption($opt->getScope() , $opt->getLocalOptionName() , $opt->getLocalOptionValue());
        } else {
            $this->setAttributeTypeOption($opt::type, $opt->getLocalOptionName() , $opt->getLocalOptionValue());
        }
    }
    /**
     * @param $attrId
     *
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
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *       which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return $this->options;
    }
}


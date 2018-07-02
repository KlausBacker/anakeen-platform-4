<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

use Anakeen\Core\SmartStructure\BasicAttribute;
use Anakeen\SmartStructures\Dsearch\Routes\Attributes;

class RenderAttributeVisibilities implements \JsonSerializable
{
    const InvisibleVisibility = "I";
    const HiddenVisibility = "H";
    const ReadOnlyVisibility = "R";
    const ReadWriteVisibility = "W";
    const WriteOnlyVisibility = "O";
    const ArrayStaticVisibility = "U";
    const StaticWriteVisibility = "S";
    /**
     * @var \Anakeen\Core\Internal\SmartElement 
     */
    protected $document;
    
    protected $visibilities = array();
    protected $finalVisibilities = array();
    public function __construct(\Anakeen\Core\Internal\SmartElement $document)
    {
        $this->document = $document;
    }
    /**
     * Return visibilities array, indexed by attribute identifier
     * @return array
     */
    public function getVisibilities()
    {
        $this->refreshVisibility();
        unset($this->finalVisibilities[\Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD]);
        return $this->finalVisibilities;
    }
    /**
     * Affect new visibility to an attribute
     * This visibility is more prioritary than mask
     * @param string $attributeId attribute identifier
     * @param string $visibility one of I,H,O,R,W,S
     * @return $this
     * @throws Exception
     */
    public function setVisibility($attributeId, $visibility)
    {
        $allowVis = array(
            self::InvisibleVisibility,
            self::HiddenVisibility,
            self::ReadOnlyVisibility,
            self::ReadWriteVisibility,
            self::WriteOnlyVisibility,
            self::StaticWriteVisibility,
            self::ArrayStaticVisibility
        );
        if (!in_array($visibility, $allowVis)) {
            throw new Exception("UI0103", $visibility, implode(', ', $allowVis));
        }
        $oa = $this->document->getAttribute($attributeId);
        if (!$oa) {
            throw new Exception("UI0102", $attributeId, $this->document->getTitle());
        }
        $this->visibilities[$oa->id] = $visibility;
        $this->document->mid = - 1; // set mask id to -1 to signal that specific visibility is applied
        return $this;
    }
    /**
     * Recompute all attributes visibility according to parent visibility
     */
    protected function refreshVisibility()
    {
        $oas = $this->document->getAttributes();
        foreach ($oas as $v) {
            if ($v->usefor === "Q") {
                continue;
            }

            $this->finalVisibilities[$v->id] = isset($this->visibilities[$v->id]) ? $this->visibilities[$v->id] : $this->getDefaultVisibility($v);
        }
        
        foreach ($oas as $v) {
            if ($v->usefor !== "Q" && $v->type == "frame") {
                if (isset($v->fieldSet) && isset($this->visibilities[$v->fieldSet->id])) {
                    $this->computeVisibility($v);
                }
            }
        }
        foreach ($oas as $v) {
            if ($v->usefor !== "Q" && $v->type == "array") {
                $this->computeVisibility($v);
            }
        }
        foreach ($oas as $v) {
            if ($v->usefor !== "Q" && $v->type != "tab" && $v->type != "frame" && $v->type != "array") {
                if (isset($v->fieldSet)) {
                    $this->computeVisibility($v);
                }
            }
        }
    }

    protected function getDefaultVisibility(BasicAttribute $v) {
        //$v->mvisibility
        if ($v->id === \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD) {
            return self::HiddenVisibility;
        }
        switch  ($v->access) {
            case BasicAttribute::READ_ACCESS:
                return self::ReadOnlyVisibility;
            case BasicAttribute::WRITE_ACCESS:
                return self::WriteOnlyVisibility;
            case BasicAttribute::READWRITE_ACCESS:
                return self::ReadWriteVisibility;
            case BasicAttribute::NONE_ACCESS:
                return self::HiddenVisibility;
            default:
                throw new Exception(sprintf("Wrong attribute access \"%s\" for\"%s\"", $v->access, $v->id));
        }
    }

    /**
     * Recompute attribute visibility according to parent visibility
     * @param \Anakeen\Core\SmartStructure\BasicAttribute $oa attribute to recompute
     */
    protected function computeVisibility(\Anakeen\Core\SmartStructure\BasicAttribute $oa)
    {
        $this->finalVisibilities[$oa->id] =  \Anakeen\Core\Utils\MiscDoc::ComputeVisibility($this->finalVisibilities[$oa->id], $this->finalVisibilities[$oa->fieldSet->id], isset($oa->fieldSet->fieldSet) ? $this->finalVisibilities[$oa->fieldSet->fieldSet->id] : '');
    }
    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        
        return $this->getVisibilities();
    }
}

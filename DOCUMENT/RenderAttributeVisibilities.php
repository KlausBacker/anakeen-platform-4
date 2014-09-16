<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class RenderAttributeVisibilities
{
    const InvisibleVisibility = "I";
    const HiddenVisibility = "H";
    const ReadOnlyVisibility = "R";
    const ReadWriteVisibility = "W";
    const WriteOnlyVisibility = "O";
    const StaticWriteVisibility = "S";
    /**
     * @var \Doc
     */
    protected $document;
    
    protected $visibilities = array();
    protected $finalVisibilities = array();
    public function __construct(\Doc $document)
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
        
        return $this->finalVisibilities;
    }
    /**
     * Affect new visibility to an attribute
     * This visibility is more prioritary than mask
     * @param string $attributeId
     * @param string $visibility
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
            self::StaticWriteVisibility
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
            $this->finalVisibilities[$v->id] = isset($this->visibilities[$v->id]) ? $this->visibilities[$v->id] : $v->mvisibility;
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
    /**
     * Recompute attribute visibility according to parent visibility
     * @param \BasicAttribute $oa attribute to recompute
     */
    protected function computeVisibility(\BasicAttribute $oa)
    {
        $this->finalVisibilities[$oa->id] = ComputeVisibility($this->finalVisibilities[$oa->id], $this->finalVisibilities[$oa->fieldSet->id], isset($oa->fieldSet->fieldSet) ? $this->finalVisibilities[$oa->fieldSet->fieldSet->id] : '');
    }
    /**
     * Set visibilies to document attribute proerties
     */
    public function applyToDocumentMask()
    {
        $this->refreshVisibility();
        $oas = $this->document->getAttributes();
        foreach ($oas as & $v) {
            
            if (!empty($this->finalVisibilities[$v->id])) {
                $v->mvisibility = $this->finalVisibilities[$v->id];
            }
        }
    }
}

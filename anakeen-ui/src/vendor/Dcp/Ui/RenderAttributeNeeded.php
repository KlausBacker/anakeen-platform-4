<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class RenderAttributeNeeded implements \JsonSerializable
{
    /**
     * @var \Anakeen\Core\Internal\SmartElement
     */
    protected $document;

    protected $needed = array();

    public function __construct(\Anakeen\Core\Internal\SmartElement $document)
    {
        $this->document = $document;
        $this->defaultNeeded();
    }

    /**
     * Return visibilities array, indexed by attribute identifier
     * @return array
     */
    public function getNeeded()
    {
        return $this->needed;
    }

    /**
     * Affect new needed property to an attribute
     * This property is more prioritary than mask
     * @param string $attributeId attribute identifier
     * @param bool   $isNeeded    attribut is needed or not
     * @return $this
     * @throws Exception
     */
    public function setNeeded($attributeId, $isNeeded)
    {

        $oa = $this->document->getAttribute($attributeId);
        if (!$oa) {
            throw new Exception("UI0104", $attributeId, $this->document->getTitle());
        }

        if (!$oa->isNormal || $oa->type === "array") {
            throw new Exception("UI0105", $attributeId, $this->document->getTitle());
        }

        $this->needed[$oa->id] = $isNeeded;
        $this->document->mid = -1; // set mask id to -1 to signal that specific need is applied
        return $this;
    }

    /**
     * Recompute all attributes needed according to attribute structure information
     */
    protected function defaultNeeded()
    {
        $oas = $this->document->getNormalAttributes();
        foreach ($oas as $v) {
            if ($v->usefor === "Q") {
                continue;
            }
            $this->needed[$v->id] = $v->needed;
        }
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        // Return only true value : false is the default
        return array_filter($this->getNeeded(), function ($v) {
            return ($v === true);
        });
    }
}

<?php
/**
 * Attribute Document Object Definition
 *
 */

namespace  Anakeen\Core\SmartStructure;
/**
 * Attribute Document Class
 *
 */
class Attributes
{
    /**
     * @var BasicAttribute[]
     */
    public $attr = array();
    public $fromname = '';
    public $isOrdered = false;
    protected $absoluteOrders = [];
    const HIDDENFIELD = "FIELD_HIDDENS";
    /**
     * @var array
     */
    public $fields = array();
    /**
     * @var array family ancestors ids
     */
    public $fromids = array();
    
    public function __construct()
    {
        $this->attr[self::HIDDENFIELD] = new \Anakeen\Core\SmartStructure\FieldSetAttribute(self::HIDDENFIELD, 0, "hiddens");
    }
    /**
     * @param string $id attribute identifier
     * @return BasicAttribute|false
     */
    public function getAttr($id)
    {
        if (isset($this->attr[$id])) {
            return $this->attr[$id];
        }
        if (isset($this->attr[strtolower($id) ])) {
            return $this->attr[$id];
        }
        
        return false;
    }
    /**
     * get attributes ids
     */
    public function getAttrIds()
    {
        return array_keys($this->attr);
    }
    /**
     * return all the attributes except frame & menu & action
     * @param bool $onlyopt
     * @return NormalAttribute[]
     */
    public function GetNormalAttributes($onlyopt = false)
    {
        $tsa = array();
        if (isset($this->attr)) {
            foreach ($this->attr as & $v) {
                if ((isset($v->isNormal)) && ((($v->usefor != "Q") && (!$onlyopt)) || (($v->usefor == "O") && ($onlyopt)))) {
                    $tsa[$v->id] = & $v;
                }
            }
        }
        return $tsa;
    }

    /**
     * return all the family parameters except frame & menu & action
     * @return NormalAttribute[]
     */
    public function getParamAttributes()
    {
        $tsa = array();
        if (isset($this->attr)) {
            reset($this->attr);
            foreach ($this->attr as $k => $v) {
                if ($v && $v->isNormal && ($v->usefor === "Q")) {
                    $tsa[$v->id] = $v;
                }
            }
        }
        return $tsa;
    }
    /**
     * get attributes included in an arrary
     * @return NormalAttribute[]|false
     */
    public function getArrayElements($id)
    {
        $a = $this->getAttr($id);
        
        if ($a && ($a->type == "array")) {
            if ($a->usefor != "Q") {
                $tsa = $this->GetNormalAttributes();
            } else {
                $tsa = $this->getParamAttributes();
            }
            $ta = array();
            foreach ($tsa as $k => $v) {
                if ($v->fieldSet->id == $id) {
                    $ta[$v->id] = $v;
                }
            }
            return $ta;
        }
        return false;
    }
    
    public function orderAttributes($force = true)
    {
        if (!$this->isOrdered || $force) {
            $this->absoluteOrders[self::HIDDENFIELD] = 0;
            $tmpSort = $this->attr;
            $this->attr = [];
            asort($this->absoluteOrders);
            foreach ($this->absoluteOrders as $aid => $absorder) {
                $this->attr[$aid] = & $tmpSort[$aid];
            }
            $this->isOrdered = true;
        }
    }
    /**
     * Add volatile attribute to document structure
     *
     * @param \Anakeen\Core\SmartStructure\BasicAttribute $attr
     */
    public function addAttribute(\Anakeen\Core\SmartStructure\BasicAttribute $attr)
    {
        $this->attr[$attr->id] = $attr;
        if (is_numeric($attr->ordered)) {
            $this->absoluteOrders[$attr->id] = $attr->ordered;
        } else {
            $this->absoluteOrders[$attr->id] = (count($this->absoluteOrders) + 1) * 10;
        }
    }
}

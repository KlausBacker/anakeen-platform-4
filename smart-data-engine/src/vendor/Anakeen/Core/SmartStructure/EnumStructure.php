<?php

namespace Anakeen\Core\SmartStructure;

class EnumStructure
{
    /**
     * @var string enum key
     */
    public $key;
    public $label;
    /**
     * @var bool
     */
    public $disabled;
    /**
     * @var int enum order
     *  first order is 1
     * last order is -1 (or 0)
     */
    public $absoluteOrder;
    public $orderBeforeThan;
    /**
     * @var EnumLocale[]
     */
    public $localeLabel;

    public function affect(array $o)
    {
        $this->key = null;
        $this->label = null;
        $this->disabled = false;
        $this->relativeOrder = null;
        $this->orderBeforeThan = null;
        $this->localeLabel = array();
        foreach ($o as $k => $v) {
            if ($k != "localeLabel") {
                $this->$k = $v;
            } else {
                foreach ($v as $locale) {
                    $this->localeLabel[] = new EnumLocale($locale["lang"], $locale["label"]);
                }
            }
        }
    }
}

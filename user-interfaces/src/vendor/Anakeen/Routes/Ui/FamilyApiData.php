<?php

namespace Anakeen\Routes\Ui;

class FamilyApiData extends \Anakeen\Routes\Core\Lib\DocumentApiData
{
    /**
     * Get the attribute info
     *
     * @param \Anakeen\Core\SmartStructure\BasicAttribute $attribute
     * @param int $order
     * @return array
     */
    public function getAttributeInfo(\Anakeen\Core\SmartStructure\BasicAttribute $attribute, $order = 0)
    {
        $info = parent::getAttributeInfo($attribute, $order);
        if ($attribute->format) {
            $info["typeFormat"] = $attribute->format;
        }
        return $info;
    }
}

<?php

namespace Anakeen\Routes\Ui;

class FamilyApiData extends \Anakeen\Routes\Core\DocumentApiData
{
    /**
     * Get the attribute info
     *
     * @param \BasicAttribute $attribute
     * @param int $order
     * @return array
     */
    public function getAttributeInfo(\BasicAttribute $attribute, $order = 0)
    {
        $info = parent::getAttributeInfo($attribute, $order);
        if ($attribute->format) {
            $info["typeFormat"] = $attribute->format;
        }
        return $info;
    }
}

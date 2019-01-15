<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class SeparatorMenu extends ElementMenu
{
    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json["type"] = "separatorMenu";
        return $json;
    }
}

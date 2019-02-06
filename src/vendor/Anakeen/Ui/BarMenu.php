<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class BarMenu implements \JsonSerializable
{
    use TMenuContent;
    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        
        $json = array();
        foreach ($this->content as $element) {
            $json[] = $element;
        }
        
        return $json;
    }
}

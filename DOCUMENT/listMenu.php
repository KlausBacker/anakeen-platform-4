<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class ListMenu extends ElementMenu
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
        $json = parent::jsonSerialize();
        $json["type"] = "listMenu";
        $json["content"] = array();
        $hasIcon = false;
        foreach ($this->content as $element) {
            if ($element->getIconUrl()) {
                $hasIcon = true;
                break;
            }
        }
        if ($hasIcon) {
            // add transparent icon on all element
            foreach ($this->content as $element) {
                if (!$element->getIconUrl() && !$element->getBeforeContent()) {
                    $element->setIcon("Images/1x1.gif", 0);
                }
            }
        }
        
        foreach ($this->content as $element) {
            $json["content"][] = $element;
        }
        
        return $json;
    }
}

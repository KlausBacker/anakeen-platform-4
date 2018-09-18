<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Search\Filters;

use Anakeen\Core\SmartStructure;
use Anakeen\Core\SmartStructure\BasicAttribute;
use Anakeen\Core\SmartStructure\NormalAttribute;

class StandardAttributeFilter
{
    protected $attributeId;
    protected $compatibleType = array(
        'text',
        'htmltext',
        'longtext',
        'int',
        'double',
        'money',
        'date',
        'timestamp',
        'time',
        'enum',
        'docid',
        'account',
        'thesaurus'
    );
    public function __construct($attrId)
    {
        $this->attributeId = strtolower($attrId);
    }
    /**
     * Verify attribute compatibility
     * @param \SearchDoc $search
     * @return NormalAttribute the attribute object
     */
    public function verifyCompatibility(\SearchDoc & $search)
    {
        $fam = $search->getFamily();
        if (!$fam) {
            throw new Exception("FLT0001");
        }
        $attr = $this->getPropAttribute($fam, $this->attributeId);
        if (!$attr) {
            throw new Exception("FLT0002", $this->attributeId, $fam->name);
        }
        if (!in_array($attr->type, $this->compatibleType)) {
            throw new Exception("FLT0003", $this->attributeId, $attr->type, $fam->name);
        }
        return $attr;
    }
    /**
     * Return the attribute object of the request attribute name or
     * a "fake" attribute object if it's a property.
     *
     * @param SmartStructure $fam
     * @param $attrId
     * @return BasicAttribute|NormalAttribute|bool
     */
    protected function getPropAttribute(SmartStructure & $fam, $attrId)
    {
        if ($fam->getPropertyValue($attrId) === false) {
            $attr = $fam->getAttribute($this->attributeId);
        } else {
            $attr = new BasicAttribute("title", $fam->id, "title");
            $attr->type = "text";
        }
        return $attr;
    }
}

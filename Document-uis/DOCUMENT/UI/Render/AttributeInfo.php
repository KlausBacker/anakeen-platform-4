<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

namespace Dcp\Ui;

class AttributeInfo implements \JsonSerializable
{
    /**
     * @var string attribute identifier
     */
    protected $id = '';
    /**
     * @var string attribute visibility
     */
    protected $visibility = "W";
    /**
     * @var string attribute label
     */
    protected $label = '';
    /**
     * @var string attribute type
     */
    protected $type = 'text';
    /**
     * @var int attribute order
     */
    protected $logicalOrder = 0;
    /**
     * @var bool is attribute has multiple value
     */
    protected $multiple = false;
    /**
     * @var array attribute options
     */
    protected $options = array();
    /**
     * @var bool attribute is needed (value must not be empty)
     */
    protected $needed = false;
    /**
     * @var array output identifiers for helps
     */
    protected $helpOutputs;
    /**
     * @var string
     */
    protected $defaultValue = '';
    /**
     * @var array only for enum, items of enums
     */
    protected $enumItems = array();
    /**
     * @var string parent attribute identifier
     */
    protected $parent = null;
    /**
     * @var \TextAttributeValue|\DocidAttributeValue|\FileAttributeValue Value
     */
    protected $attributeValue = null;
    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
    /**
     * @param string $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }
    /**
     * @return array
     */
    public function getEnumItems()
    {
        return $this->enumItems;
    }
    /**
     * @param array $enumItems
     */
    public function setEnumItems($enumItems)
    {
        $this->enumItems = $enumItems;
    }
    /**
     * @return array
     */
    public function getHelpOutputs()
    {
        return $this->helpOutputs;
    }
    /**
     * @param array $helpOutputs
     */
    public function setHelpOutputs($helpOutputs)
    {
        $this->helpOutputs = $helpOutputs;
    }
    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }
    /**
     * @return int
     */
    public function getLogicalOrder()
    {
        return $this->logicalOrder;
    }
    /**
     * @param int $logicalOrder
     */
    public function setLogicalOrder($logicalOrder)
    {
        $this->logicalOrder = $logicalOrder;
    }
    /**
     * @return boolean
     */
    public function isMultiple()
    {
        return $this->multiple;
    }
    /**
     * @param boolean $multiple
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;
    }
    /**
     * @return boolean
     */
    public function isNeeded()
    {
        return $this->needed;
    }
    /**
     * @param boolean $needed
     */
    public function setNeeded($needed)
    {
        $this->needed = $needed;
    }
    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }
    /**
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }
    /**
     * @param string $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }
    /**
     * @param string $visibility
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }
    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    /**
     * @return \DocidAttributeValue|\FileAttributeValue|\TextAttributeValue
     */
    public function getAttributeValue()
    {
        return $this->attributeValue;
    }
    /**
     * @param \DocidAttributeValue|\FileAttributeValue|\TextAttributeValue $attributeValue
     */
    public function setAttributeValue($attributeValue)
    {
        $this->attributeValue = $attributeValue;
    }
    
    public function importData(array $info)
    {
        foreach ($info as $k => $v) {
            $this->$k = $v;
        }
    }
    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return array_filter(get_object_vars($this) , function ($v)
        {
            return ($v !== null);
        });
    }
}


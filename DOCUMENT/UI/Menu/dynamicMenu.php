<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class DynamicMenu extends ElementMenu
{
    /**
     * @var \Closure
     */
    protected $contentDefinition = null;
    
    protected $url = '';
    
    public function setUrl($url)
    {
        $this->url = $url;
    }
    /**
     * Record definition function
     * @param \Closure $definition
     */
    public function setContent(\Closure $definition)
    {
        $this->contentDefinition = $definition;
        $this->url = sprintf("?app=DOCUMENT&action=SUBMENU&id={{document.properties.id}}&menu=%s", urlencode($this->id));
    }
    /**
     * Return instanciated dynamic menu
     * Invoke definition function
     * @return ListMenu|null
     */
    public function getContent()
    {
        if ($this->contentDefinition) {
            $menuList = new ListMenu($this->id, $this->textLabel);
            /** @noinspection PhpUndefinedMethodInspection */
            $this->contentDefinition->__invoke($menuList);
            return $menuList;
        }
        return null;
    }
    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json["type"] = "dynamicMenu";
        $json["url"] = $this->url;
        
        return $json;
    }
}

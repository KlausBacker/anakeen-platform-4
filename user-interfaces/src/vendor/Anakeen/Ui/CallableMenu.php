<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

use Anakeen\Routes\Ui\CallMenuResponse;
use phpDocumentor\Reflection\Types\Boolean;
use Slim\Http\Request;
use Slim\Http\Response;

class CallableMenu extends ElementMenu
{
    /**
     * @var \Closure
     */
    protected $contentDefinition = null;
    protected $method="PUT";
    protected $unsafe=false;
    
    protected $url = '';
    
    public function setUrl($url)
    {
        $this->url = $url;
    }
    /**
     * Record definition function
     * @param \Closure $definition
     */
    public function setCallable(\Closure $definition)
    {
        $this->contentDefinition = $definition;
        
        $this->url = sprintf("/api/v2/smart-elements/{{document.properties.id}}/views/{{document.properties.viewId}}/menus/%s/call", urlencode($this->id));
    }
    /**
     * Return instanciated dynamic menu
     * Invoke definition function
     */
    public function callMenuRequest(Request $request, Response $response) : CallMenuResponse
    {
        if ($this->contentDefinition) {
            /** @noinspection PhpUndefinedMethodInspection */
            $messages= $this->contentDefinition->__invoke($request, $response);

            return $messages;
        }
        return null;
    }
    /**
     * Return closure function set by setContent method
     * @see setCallable
     * @return \Closure
     */
    public function getClosure()
    {
        return $this->contentDefinition;
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
        $json["type"] = "callableMenu";
        $json["url"] = $this->url;
        $json["method"] = $this->method;
        
        return $json;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return CallableMenu
     *
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param bool $unsafe
     * @return CallableMenu
     * activate or desactivate the tests
     * made according to the http method
     */
    public function setUnsafe(bool $unsafe)
    {
        $this->unsafe = $unsafe;
        return $this;
    }

    /**
     * @param bool $unsafe
     * @return bool
     * return value of unsafe
     */
    public function getUnsafe(): bool
    {
        return $this->unsafe;
    }
}

<?php

namespace Anakeen\Router;

class Exception extends \Dcp\Exception implements \JsonSerializable
{
    protected $httpStatus = 400;
    protected $httpMessage = "Anakeen Exception";
    protected $data = null;
    protected $userMessage = '';
    protected $uri = "";
    protected $headers = array();

    /**
     * @param string $userMessage
     */
    public function setUserMessage($userMessage)
    {
        $this->userMessage = $userMessage;
    }

    /**
     * @return string
     */
    public function getUserMessage()
    {
        return $this->userMessage;
    }

    /**
     * @return null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Add
     *
     * @param null $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Return the http message
     *
     * @return string
     */
    public function getHttpMessage()
    {
        return $this->httpMessage;
    }

    /**
     * Return the http status
     *
     * @return int
     */
    public function getHttpStatus()
    {
        return (int)$this->httpStatus;
    }

    /**
     *
     * @param int    $httpStatus
     * @param string $httpMessage
     */
    public function setHttpStatus($httpStatus, $httpMessage = "")
    {
        $this->httpStatus = $httpStatus;
        $this->httpMessage = $httpMessage;
    }

    /**
     * Add an URI indication
     *
     * @param $uri
     */
    public function setURI($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Return the URI indication
     *
     */
    public function getURI()
    {
        return $this->uri;
    }

    /**
     * Add an header
     *
     * @param $key
     * @param $value
     *
     * @internal param $uri
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    /**
     * Return the URI indication
     *
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array
     */
    public function jsonSerialize()
    {
        $data = [
            "success" => false,
            "exceptionMessage" => $this->getMessage(),
            "message" => $this->getDcpMessage(),
            "data" => $this->getData(),
            "code" => $this->getDcpCode(),
            "uri" => $this->getURI(),
            "userMessage" => $this->getUserMessage(),
        ];

        return $data;
    }
}

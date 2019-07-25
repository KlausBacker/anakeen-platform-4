<?php

namespace Anakeen;

/**
 * @brief  Exception class
 * use exceptionCode to identifiy correctly exception
 * @class  Exception
 * @author Anakeen
 */
class Exception extends \Exception implements \JsonSerializable
{
    protected $httpStatus = 400;
    protected $httpMessage = "Anakeen Exception";
    protected $data = null;
    protected $userMessage = '';
    protected $uri = "";
    protected $headers = array();
    private $dcpCode = '';
    private $httpHeader = array();

    /**
     * Redefined exception : message text is mandatory now
     *
     * @param string $message error message or code error
     * @param int $argCode
     * @param \Throwable|mixed $previous
     */
    public function __construct($message, $argCode = 0, $previous = null)
    {
        $code = $message;
        if ($code && (preg_match('/^([A-Z]+)([0-9]+)$/u', $code, $reg))) {
            $tArgs = array(
                $code
            );
            $nargs = func_num_args();
            for ($ip = 1; $ip < $nargs; $ip++) {
                $tArgs[] = func_get_arg($ip);
            }
            $msg = \ErrorCode::getError(...$tArgs);

            if ($msg) {
                $message = $msg;
            }
            $this->dcpCode = $code;
        }
        if ($argCode && is_numeric($argCode)) {
            $intcode = intval($argCode);
        } else {
            $intcode = 0;
        }
        if (is_a($previous, \Throwable::class)) {
            parent::__construct($message, $intcode, $previous);
        } else {
            parent::__construct($message, $intcode);
        }
    }

    /**
     * return code error from constant of ErrorCode Class
     *
     * @return string
     */
    public function getDcpCode()
    {
        if ($this->dcpCode) {
            return $this->dcpCode;
        }
        if (preg_match("/^{([^}]+)/u", $this->message, $reg)) {
            return $reg[1];
        }
        return '';
    }

    /**
     * return code error from constant of ErrorCode Class
     *
     * @return string
     */
    public function getDcpMessage()
    {
        if (preg_match("/^{([^}]+)} *(.*)$/ums", $this->message, $reg)) {
            return $reg[2];
        }
        return $this->message;
    }

    /**
     */
    public function __toString()
    {
        return __CLASS__ . ": {$this->message}";
    }

    public function addHttpHeader($header)
    {
        $this->httpHeader[] = $header;
    }

    public function getHttpHeader()
    {
        return implode("\n", $this->httpHeader);
    }

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
     * @param int $httpStatus
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

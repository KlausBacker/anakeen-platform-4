<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Routes\Core\Lib;

class ApiMessage implements \JsonSerializable
{
    const SUCCESS = "success";
    const ERROR = "error";
    const MESSAGE = "message";
    const NOTIFICATION = "notification";
    const WARNING = "warning";
    const NOTICE = "notice";
    const DEBUG = "debug";

    public $type = self::MESSAGE;
    public $contentText = '';
    public $contentHtml = '';
    public $code = '';
    public $uri = '';
    public $data = null;


    public function __construct($message = "", $type = self::MESSAGE)
    {
        $this->contentText = $message;
        $this->type = $type;
    }

    public function jsonSerialize()
    {
        $values = array(
            "type" => $this->type
        );
        if (!empty($this->contentText)) {
            $values["contentText"] = $this->contentText;
        }
        if (!empty($this->code)) {
            $values["code"] = $this->code;
        }
        if (!empty($this->contentHtml)) {
            $values["contentHtml"] = $this->contentHtml;
        }
        if (!empty($this->contentHtml)) {
            $values["contentHtml"] = $this->contentHtml;
        }
        if (!empty($this->uri)) {
            $values["uri"] = $this->uri;
        }
        if (!empty($this->data)) {
            $values["data"] = $this->data;
        }
        return $values;
    }

    public function __toString()
    {
        $msg = $this->contentText;
        if ($this->contentHtml) {
            $msg .= "<p>" . $this->contentHtml . "</p>";
        }
        return sprintf("%s (%s): %s", $this->type, $this->code, $msg);
    }
}

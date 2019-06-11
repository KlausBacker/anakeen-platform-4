<?php

namespace Anakeen\Routes\Ui;

use Anakeen\Routes\Core\Lib\ApiMessage;

class CallMenuResponse
{
    /**
     * @var ApiMessage
     */
    protected $message;
    protected $data=[];
    protected $reload=false;

    /**
     * @return bool
     */
    public function needReload(): bool
    {
        return $this->reload;
    }

    /**
     * @param bool $reload
     * @return CallMenuResponse
     */
    public function setReload(bool $reload): CallMenuResponse
    {
        $this->reload = $reload;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return CallMenuResponse
     */
    public function setData(array $data):CallMenuResponse
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return ApiMessage
     */
    public function getMessage() : ApiMessage
    {
        return $this->message;
    }

    /**
     * @param ApiMessage $message
     * @return CallMenuResponse
     */
    public function setMessage(ApiMessage $message): CallMenuResponse
    {
        $this->message = $message;
        return $this;
    }
}

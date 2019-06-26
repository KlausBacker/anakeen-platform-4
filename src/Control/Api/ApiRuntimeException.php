<?php


namespace Control\Api;


class ApiRuntimeException extends \Exception implements \JsonSerializable
{

    public function jsonSerialize()
    {

        $data = [
            "success" => false,
            "exceptionMessage" => $this->getMessage(),
        ];

        return $data;

    }
}
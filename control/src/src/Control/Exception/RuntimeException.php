<?php


namespace Control\Exception;


class RuntimeException extends \Exception implements \JsonSerializable
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
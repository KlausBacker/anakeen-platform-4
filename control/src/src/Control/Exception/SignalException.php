<?php


namespace Control\Exception;


class SignalException extends \Exception implements \JsonSerializable
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
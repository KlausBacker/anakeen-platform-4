<?php

namespace Anakeen;

use Anakeen\Router\ApiV2Response;
use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;

class SmartAutocomplete
{

    /**
     * @var SmartAutocompleteRequest
     */
    protected $autoRequest;
    /**
     * @var SmartAutocompleteResponse
     */
    protected $autoResponse;
    /**
     * @var \Slim\Http\response
     */
    protected $httpResponse;
    protected $entryData = [];
    /**
     * @var \Closure
     */
    protected $entryLabelCallback;
    /**
     * @var array|object|null
     */
    protected $requestData;

    public function __construct(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->autoRequest = new SmartAutocompleteRequest();
        $this->autoRequest->setHttpRequest($request);
        $this->httpResponse = $response;
        $this->autoResponse = new SmartAutocompleteResponse();

        $this->requestData = $request->getParsedBody();
    }

    public function getFilterValue()
    {
        return $this->autoRequest->getFilterValue();
    }

    public function setEntryData(array $data)
    {
        $this->entryData = $data;
        $keys = [];
        foreach ($this->entryData as $entryDatum) {
            $keys = array_unique(array_merge(array_keys($entryDatum)));

        }
        $outputs = [];
        foreach ($keys as $key) {
            $outputs[$key] = $key;
        }
        $this->autoResponse->setOutputs($outputs);
        return $this;
    }


    public function getResponse()
    {
        return ApiV2Response::withData($this->httpResponse, $this->getData());
    }


    public function setEntryLabel(\Closure $entryData)
    {
        $this->entryLabelCallback = $entryData;
    }


    public function getInputValue($inputName)
    {
        $index = -1;
        if (isset($this->requestData["index"]) && $this->requestData["index"] !== "-1") {

             //   $index = intval($this->requestData["index"]);

        }
        $formInputs = $this->requestData["inputs"]??[];


        if (isset($formInputs[$inputName])) {
            if ($index < 0) {
                if (isset($formInputs[$inputName][0])) {
                    // It is a real multiple values
                    return array_map(function ($data) {
                        if (array_key_exists("value", $data)) {
                            return $data["value"];
                        } else {
                            if (isset($data[0])) {
                                return array_map(function ($subdata) {
                                    if (array_key_exists("value", $subdata)) {
                                        return $subdata["value"];
                                    } else {
                                        return $subdata;
                                    }
                                }, $data);
                            }
                            return $data;
                        }
                    }, $formInputs[$inputName]);
                } else {
                    return $formInputs[$inputName]["value"];
                }
            } else {
                if (isset($formInputs[$inputName][$index])) {
                    return $formInputs[$inputName][$index]["value"];
                } else {
                    return $formInputs[$inputName]["value"];
                }
            }
        }
        return null;
    }
    
    protected function getEntryLabel(array $entryData)
    {
        return call_user_func($this->entryLabelCallback, $entryData);
    }

    protected function getData()
    {
        $this->autoResponse->setData([]);
        foreach ($this->entryData as $entryDatum) {
            $this->autoResponse->appendEntry(
                $this->getEntryLabel($entryDatum),
                $entryDatum
            );
        }
        return $this->autoResponse->getData();
    }
}

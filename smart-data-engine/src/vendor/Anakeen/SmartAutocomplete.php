<?php

namespace Anakeen;

use Anakeen\Core\Utils\Strings;
use Anakeen\Router\ApiV2Response;
use Anakeen\Routes\Core\Lib\ApiMessage;

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
    /**
     * @var ApiMessage[]
     */
    protected $messages;
    /**
     * @var string
     */
    protected $entryLabelTemplate;
    /**
     * @var \Mustache_Engine
     */
    protected $mustache;

    public function __construct(\Slim\Http\request $request, \Slim\Http\response $response)
    {
        $this->autoRequest = new SmartAutocompleteRequest();
        $this->autoRequest->setHttpRequest($request);
        $this->httpResponse = $response;
        $this->autoResponse = new SmartAutocompleteResponse();

        $this->requestData = $request->getParsedBody();
    }

    /**
     * Get input form value where autocomplete is attached
     * @return string
     */
    public function getFilterValue()
    {
        return $this->autoRequest->getFilterValue();
    }

    /**
     * Set all data to returned to auitocomplete
     * Each values must be indexed to match output reference
     * @param array $data an array of values (indexed values)
     * @return $this
     */
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

    /**
     * Add a message to display to autocomplete
     * @param ApiMessage $msg
     * @return $this
     */
    public function addMessage(ApiMessage $msg)
    {
        $this->messages[] = $msg;
        return $this;
    }

    /**
     * Return formatted response for autocomplete request
     * @return \Slim\Http\response
     */
    public function getResponse()
    {
        $error = $this->autoResponse->getError();
        if ($error) {
            $message = new ApiMessage();
            $message->contentHtml = $error;
            $message->type = ApiMessage::ERROR;
            $this->addMessage($message);
        }
        return ApiV2Response::withData($this->httpResponse, $this->getData(), $this->messages);
    }


    /**
     * Add an error message to display to the list selector
     * @param string $msg Error message
     * @return $this
     */
    public function setError(string $msg)
    {
        $this->autoResponse->setError($msg);
        return $this;
    }

    /**
     * Define the display label use in list selector
     * Can be a closure or a mustache string
     * @param \Closure|string $entryData
     * @return $this
     */
    public function setEntryLabel($entryData)
    {
        if ($entryData instanceof \Closure) {
            $this->entryLabelTemplate = null;
            $this->entryLabelCallback = $entryData;
        } elseif (is_string($entryData)) {
            $this->mustache = new \Mustache_Engine();
            $this->entryLabelTemplate = $entryData;
            $this->entryLabelCallback = null;
        } else {
            throw new Exception("Only string or closure are accepted for autocomplete label");
        }

        return $this;
    }

    /**
     * Return input value from smart form
     *
     * @param string $inputName name of input send in the request
     * @return array|mixed|null
     */
    public function getInputValue(string $inputName)
    {
        $formInputs = $this->requestData["inputs"] ?? [];

        if (isset($formInputs[$inputName])) {
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
        }
        return null;
    }

    protected function getEntryLabel(array $entryData)
    {
        if ($this->entryLabelCallback !== null) {
            return call_user_func($this->entryLabelCallback, $entryData);
        } elseif ($this->entryLabelTemplate !== null) {
            return $this->mustache->render($this->entryLabelTemplate, $entryData);
        } else {
            $encodedData = array_map(function ($item) {
                if (is_array($item)) {
                    $item=$item["displayValue"]??$item["value"];
                }
                return Strings::xmlEncode($item);
            }, $entryData);

            return "<span>".implode("</span> - <span>", $encodedData)."</span>";
        }
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

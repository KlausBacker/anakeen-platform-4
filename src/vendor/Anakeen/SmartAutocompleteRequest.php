<?php

namespace Anakeen;

class SmartAutocompleteRequest
{
    /**
     * @var \Slim\Http\request
     */
    protected $httpRequest;
    protected $contentParameters;
    protected $index = -1;


    /**
     * @return \Slim\Http\request
     */
    public function getHttpRequest(): \Slim\Http\request
    {
        return $this->httpRequest;
    }

    /**
     * @param \Slim\Http\request $httpRequest
     */
    public function setHttpRequest(\Slim\Http\request $httpRequest): void
    {
        $this->contentParameters = $httpRequest->getParsedBody();
        if (isset($this->contentParameters["index"])) {
            $this->index = intval($this->contentParameters["index"]);
        }
        $this->httpRequest = $httpRequest;
    }

    public function getFilterValue(): string
    {
        if (!empty($this->contentParameters["filter"]["filters"][0]["value"])) {
            return $this->contentParameters["filter"]["filters"][0]["value"];
        }
        return "";
    }

    public function getIndex() : int
    {
        return $this->index;
    }
}

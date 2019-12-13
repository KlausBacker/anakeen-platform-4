<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Search\SearchElements;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;

class SmartElementClean
{
    protected $errors;
    protected $tag;

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     */
    public function __invoke(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        $args
    ) {

        $this->initParameters($request, $args);

        $this->cleanSmartElement();

        return ApiV2Response::withMessages($response, $this->errors);
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->tag = $args['tag'] ?? null;
        if (empty($this->tag)) {
            $exception = new Exception("ANKTEST004", 'tag');
            $exception->setHttpStatus("400", "smart element identifier is required");
            throw $exception;
        }
    }

    protected function cleanSmartElement()
    {
        $search = new SearchElements('');
        $search->overrideAccessControl();
        $search->addFilter('atags is not null');
        $search->addFilter("atags ->> 'ank_test' = '%s'", pg_escape_string($this->tag));
        $search->search();

        print_r($search->getResults());
        $list = $search->getResults();
        $this->errors = [];

        foreach ($list as $docid => $doc) {
            $error = $doc->delete();
            if (!empty($error)) {
                $this->errors[] = $error;
            }
        }
    }
}

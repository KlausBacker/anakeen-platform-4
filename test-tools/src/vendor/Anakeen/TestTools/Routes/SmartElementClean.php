<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Search\SearchElements;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;

class SmartElementClean
{
    protected $errors;

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
        
        return ApiV2Response::withMessages($response, $this->errors);
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $tag = $args['tag'] ?? null;
        if (empty($tag)) {
            $exception = new Exception("ANKTEST004", 'tag');
            $exception->setHttpStatus("400", "smart element identifier is required");
            throw $exception;
        }

        if (!empty($tag)) {
            $search = new SearchElements('');
            $search->addFilter('atags is not null');
            $search->addFilter("atags ->> 'ank_test' = '%s'", pg_escape_string($args['tag']));
            $search->search();
            $list = $search->getResults();
            $this->errors = [];

            foreach ($list as $docid => $doc) {
                $error = $doc->delete();
                if (!empty($error)) {
                    $this->errors[] = $error;
                }
            }
        } else {
            $exception = new Exception("ANKTEST005", $search->id, $error);
            $exception->setHttpStatus("400", "test tag is required");
            throw $exception;
        }
    }
}

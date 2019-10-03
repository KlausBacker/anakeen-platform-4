<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Search\SearchElements;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;

class SmartElementClean
{
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
        if (!empty($args['tag'])) {
            $search = new SearchElements('');
            $search->addFilter('atags is not null');
            $search->addFilter("atags ->> 'ank_test' = '%s'", pg_escape_string($args['tag']));
            $search->search();
            $list = $search->getResults();
            $errors = [];

            foreach ($list as $docid => $doc) {
                $error = $doc->delete();
                if (!empty($error)) {
                    $errors[] = $error;
                }
            }

            return ApiV2Response::withMessages($response, $errors);
        } else {
            $exception = new Exception("ANKTEST005", $search->id, $error);
            $exception->setHttpStatus("400", "test tag is required");
            throw $exception;
        }
    }
}

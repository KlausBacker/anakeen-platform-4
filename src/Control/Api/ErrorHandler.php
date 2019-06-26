<?php


namespace Control\Api;


class ErrorHandler
{
    const status=400;
    const message="Anakeen Control Runtime";
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $exception)
    {
        if (is_a($exception, \Control\Exception\RuntimeException::class)) {
            /**
             * @var \Control\Exception\RuntimeException $exception
             */
            $response = $response->withStatus(self::status, self::message);
            return $response->withJson($exception);
        }
        throw $exception;
    }
}
<?php

namespace Dcp\Router;

use Dcp\Core\Utils\ErrorMessage;

class ErrorHandler
{

    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param \Exception          $exception
     *
     * @return \Slim\Http\response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $exception)
    {

        if (is_a($exception, "\\Anakeen\\Router\\Exception")) {
            /**
             * @var \Anakeen\Router\Exception $exception
             */
            return self::getFailApiResponse($request, $response, $exception);
        } else {

            $exceptionMsg = \Dcp\Core\LogException::logMessage($exception, $errId);
            $response=$response->withStatus(500);
            return self::getResponsePage($request, $response, $exceptionMsg, $errId);
        }

    }

    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $exceptionMsg
     * @param string              $errId
     *
     * @return \Slim\Http\response
     */
    protected static function getResponsePage(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        $exceptionMsg,
        $errId = ""
    ) {
        $accept = $request->getHeaderLine("HTTP_ACCEPT");

        $useHtml = (preg_match("@\\btext/html\\b@", $accept));

        if ($useHtml) {
            return $response
                ->withHeader('Content-Type', 'text/html')
                ->write(ErrorMessage::getHtml($exceptionMsg, $errId));
        } else {
            $useJSON = (preg_match("@\\bapplication/json\\b@", $accept));
            if ($useJSON) {
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->write(ErrorMessage::getJson($exceptionMsg, $errId));
            } else {
                return $response
                    ->withHeader('Content-Type', 'text/plain')
                    ->write(ErrorMessage::getText($exceptionMsg, $errId));
            }
        }
    }


    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $exceptionMsg
     * @param string              $errId
     *
     * @return \Slim\Http\response
     */
    protected static function getFailApiResponse(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        \Anakeen\Router\Exception $exception
    ) {
        $accept = $request->getHeaderLine("HTTP_ACCEPT");

        $useHtml = (preg_match("@\\btext/html\\b@", $accept));

        $response=$response->withStatus($exception->getHttpStatus());
        if ($useHtml) {
            return $response
                ->withHeader('Content-Type', 'text/html')
                ->write(ErrorMessage::getHtml($exceptionMsg, $errId));
        } else {
            $useJSON = (preg_match("@\\bapplication/json\\b@", $accept));
            if ($useJSON) {
                return $response
                    ->withJson($exception);
            } else {
                return $response
                    ->withHeader('Content-Type', 'text/plain')
                    ->write(ErrorMessage::getText($exceptionMsg, $errId));
            }
        }
    }
}

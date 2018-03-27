<?php

namespace Dcp\Router;

class NotHandler
{

    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     *
     * @return \Slim\Http\response
     */
    public static function NotFound(\Slim\Http\request $request, \Slim\Http\response $response)
    {
        $args = ["title" => sprintf(___("Page not found", "ank"))];
        $response = $response->withStatus(404);
        return self::getResponsePage($request, $response, $args, "CORE/Layout/notFound.html");
    }

    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param  array              $methods
     *
     * @return \Slim\Http\response
     */
    public static function NotAllowed(\Slim\Http\request $request, \Slim\Http\response $response, $methods)
    {
        $args = [
            "title" => sprintf(
                ___("Method \"%s\" Not Allowed. Must be one of: %s", "ank"),
                $request->getMethod(),
                implode(", ", $methods)
            )
        ];
        $response = $response->withStatus(405);

        return self::getResponsePage($request, $response, $args, "CORE/Layout/notAllowed.html");
    }

    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param array               $args
     * @param                     $layout
     *
     * @return \Slim\Http\response
     */
    protected static function getResponsePage(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        array $args,
        $layout
    ) {
        $accept = $request->getHeaderLine("HTTP_ACCEPT");

        $useHtml = preg_match("@\\btext/html\\b@", $accept);

        if ($useHtml) {
            return $response->withHeader('Content-Type', 'text/html')
                ->write(self::getHtml($args, $layout));
        } else {
            $useJSON = (preg_match("@\\bapplication/json\\b@", $accept));
            if ($useJSON) {
                return $response->withHeader('Content-Type', 'application/json')
                    ->write(self::getJson($args));
            } else {
                return $response->withHeader('Content-Type', 'text/plain')
                    ->write(self::getText($args));
            }
        }
    }

    public static function getHtml(array $args, $layout)
    {
        $lay = new \Layout($layout);
        foreach ($args as $k => $arg) {
            $lay->set($k, $arg);
        }

        return $lay->gen();
    }

    public static function getText(array $args)
    {

        return implode(", ", $args);
    }

    public static function getJson($args)
    {
        $error = ["success" => false, "error" => self::getText($args)];
        return json_encode($error);
    }
}

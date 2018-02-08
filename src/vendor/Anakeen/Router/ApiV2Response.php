<?php

namespace Dcp\Router;

class ApiV2Response
{
    /**
     * Return normalize output for http api
     *
     * @param \Slim\Http\response $response
     * @param mixed               $data
     * @param array               $messages
     *
     * @return \Slim\Http\response
     */
    public static function withData(\Slim\Http\response $response, $data, $messages = [])
    {
        $return = ["success" => true, "data" => $data, "messages" => $messages];

        return $response->withJson($return);
    }

    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $eTag
     *
     * @return \Slim\Http\response
     */
    public static function withEtag(\Slim\Http\request $request, \Slim\Http\response $response, $eTag)
    {
        /**
         * @var \Slim\Container $container
         */
        $container=$request->getAttribute("container");

        // Need to clear headers set by session.cache_limiter='nocache'
        header_remove("Cache-Control");
        header_remove("Pragma");
        header_remove("Expires");

        /**
         * @var \Slim\HttpCache\CacheProvider $cache
         */
        /** @noinspection PhpUndefinedFieldInspection */
        $cache=$container->cache;
        return $cache->withEtag($response, base64_encode($eTag));
    }


    public static function matchEtag(\Slim\Http\request $request, $etag)
    {
        if ($etag) {
            $ifNoneMatch = $request->getHeaderLine('If-None-Match');
            if ($ifNoneMatch) {
                $ifNoneMatch=base64_decode($ifNoneMatch);
                $etagList = preg_split('@\s*,\s*@', $ifNoneMatch);
                if (in_array($etag, $etagList) || in_array('*', $etagList)) {
                    return true;
                }
            }
        }
        return false;
    }
}

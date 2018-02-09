<?php

namespace Dcp\Router;

use Anakeen\Core\FileMime;
use Anakeen\Router\Exception;

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
     * @param \Slim\Http\request $request
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
        $container = $request->getAttribute("container");

        // Need to clear headers set by session.cache_limiter='nocache'
        header_remove("Cache-Control");
        header_remove("Pragma");
        header_remove("Expires");

        /**
         * @var \Slim\HttpCache\CacheProvider $cache
         */
        /** @noinspection PhpUndefinedFieldInspection */
        $cache = $container->cache;
        return $cache->withEtag($response, base64_encode($eTag));
    }


    public static function matchEtag(\Slim\Http\request $request, $etag)
    {
        if ($etag) {
            $ifNoneMatch = $request->getHeaderLine('If-None-Match');
            if ($ifNoneMatch) {
                $ifNoneMatch = base64_decode($ifNoneMatch);
                $etagList = preg_split('@\s*,\s*@', $ifNoneMatch);
                if (in_array($etag, $etagList) || in_array('*', $etagList)) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function withFile(
        \Slim\Http\response $response,
        $filePath,
        $fileName = "",
        $inline = false,
        $mime = ""
    ) {
        if (!$fileName) {
            $fileName = basename($filePath);
        }
        if (!file_exists($filePath)) {
            throw new Exception("ROUTES0115", basename($filePath));
        }
        // Double quote not supported by all browsers - replace by minus
        $name = str_replace('"', '-', $fileName);
        $uName = iconv("UTF-8", "ASCII//TRANSLIT", $name);
        $name = rawurlencode($name);
        if (!$mime) {
            $mime = FileMime::getSysMimeFile(realpath($filePath), $fileName);
        }
        $fileMimeConfig = new \Dcp\FileMimeConfig();

        if ($inline && !$fileMimeConfig->isInlineAllowed($mime)) {
            /* Override requested inline mode as it is forbidden */
            $inline = false;
        }
        $ct = sprintf(";filename=\"%s\";filename*=UTF-8''%s", $uName, $name);

        if ($inline) {
            $response = $response->withHeader("Content-Disposition", "inline" . $ct);
        } else {
            $response = $response->withHeader("Content-Disposition", "attachment" . $ct);
        }
        if ($mime) {
            $response = $response->withHeader("Content-type", $mime);
        }

        return $response->write(file_get_contents($filePath));
    }
}

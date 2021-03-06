<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\Settings;
use Anakeen\Router\Exception;

/**
 * Class LayoutAsset
 *
 * Return js or css from Application layout directory
 *
 * @note    Used by route : GET /assets/{asset}
 * @package Anakeen\Routes\Core
 */
class LayoutAsset
{
    /**
     * Return js or css from Application layout directory
     *
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param  array              $args like CORE:welcome.css
     *
     * @return \Slim\Http\response
     *
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $ref = $args["asset"];

        if (!preg_match("/\\.(css|js)$/", $ref, $reg)) {
            throw new Exception(sprintf("Ref \"%s\" not an asset", $ref));
        }
        $assetType = $reg[1];


        $file = $this->getCacheAsset($ref);
        if ($file) {
            /*  header_remove("Cache-Control");
              header_remove("Pragma");
              header_remove("Expires");*/
            $response = $response->withHeader("Cache-Control", "private, max-age=86400, stale-while-revalidate=604800");
        }
        if (!$file) {
            header(sprintf("HTTP/1.1 404 ref [%s] not found", $ref));
            $response = $response->withStatus(404, sprintf("Ref [%s] not found", $ref));
        } else {
            $response->write(file_get_contents($file));
        }


        switch ($assetType) {
            case "css":
                $response = $response->withHeader("content-type", "text/css");
                break;
            case "js":
                $response = $response->withHeader("content-type", "application/javascript");
                break;
        }

        return $response;
    }


    protected function getCacheAsset($ref)
    {
        $assetDir = sprintf("%s/%s/assets/", DEFAULT_PUBDIR, Settings::CacheDir);
        $file = sprintf("%s/%s", $assetDir, $ref);
        if (is_link($file)) {
            return $file;
        }
        return null;
    }
}

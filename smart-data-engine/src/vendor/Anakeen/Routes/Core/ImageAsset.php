<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Settings;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;

/**
 * Class ImageAsset
 *
 * Download image from public assets
 *
 * @note    Used by route : GET /api/v2/images/assets/original/{image}
 * @note    Used by route : GET /api/v2/images/assets/sizes/{size:[0-9x]+[cfs]?}/{image}
 * @package Anakeen\Routes\Core
 */
class ImageAsset
{
    const CACHEIMGDIR = Settings::CacheDir . "image/";
    const DEFAULTIMG = "core-noimage.png";
    protected $size;
    protected $imageFileName;

    /**
     * Download resized image
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param array               $args
     *
     * @return mixed
     * @throws Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        try {
            $location = $this->getSourceImage();
        } catch (Exception $e) {
            $location = sprintf("%s/public/CORE/Images/core-noimage.png", ContextManager::getRootDirectory());
            $response = $response->withStatus(404, "Image not found");
        }

        if ($this->size !== null) {
            $dest = $this->getDestinationCacheImage($this->imageFileName, $this->size);

            if (!file_exists($dest)) {
                $outFile = Lib\Files::resizeLocalImage($location, $dest, $this->size);
            } else {
                $outFile = $dest;
            }
        } else {
            $tsize = getimagesize($location);
            if (!$tsize) {
                throw new Exception("ROUTES0114", $this->imageFileName);
            }
            // original file
            $outFile = $location;
        }

        $response = ApiV2Response::withEtag($request, $response, filemtime($location));
        return ApiV2Response::withFile($response, $outFile, "", true);
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->size = $args["size"] ?? null;
        if (strpos($args["image"], '..') !== false) {
            throw new Exception("ROUTES0115", $this->imageFileName);
        }
        $this->imageFileName = ($args["image"]);
    }

    protected function getDestinationCacheImage($localimage, $size)
    {
        $imgDir = sprintf("%s/%s", DEFAULT_PUBDIR, self::CACHEIMGDIR);
        if (!is_dir($imgDir)) {
            mkdir($imgDir);
        }
        $basedest = sprintf(
            "%s/%s/Images_%s-%s",
            DEFAULT_PUBDIR,
            self::CACHEIMGDIR,
            $size,
            str_replace("/", "_", $localimage)
        );
        return $basedest;
    }

    /**
     * Get images from "Images" folder
     *
     * @return string
     * @throws Exception
     */
    protected function getSourceImage()
    {
        $location = sprintf("Images/%s", $this->imageFileName);
        if (!file_exists($location)) {
            // Use Another Default Image
            $location = sprintf("Images/%s", self::DEFAULTIMG);
            if (!file_exists($location)) {
                throw new Exception("ROUTES0115", $location);
            }
        }
        return $location;
    }
}

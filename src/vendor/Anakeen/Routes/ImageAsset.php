<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\Exception;
use Dcp\Router\ApiV2Response;

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
    const CACHEIMGDIR = "var/cache/image/";
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
        $imageId = $args["image"];
        $this->size = $args["size"];
        $this->imageFileName = urldecode($imageId);

        $location = $this->getSourceImage();

        if ($this->size !== null) {
            $dest = $this->getDestinationCacheImage($this->imageFileName, $this->size);

            if (!file_exists($dest)) {
                $outFile = Files::resizeLocalImage($location, $dest, $this->size);
            } else {
                $outFile = $dest;
            }
        } else {
            $tsize = getimagesize($location);
            if (!$tsize) {
                throw new Exception("ROUTES0114", $imageId);
            }
            // original file
            $outFile = $location;
        }

        $response=ApiV2Response::withEtag($request, $response, filemtime($location));
        return ApiV2Response::withFile($response, $outFile, "", true);
    }


    protected function getDestinationCacheImage($localimage, $size)
    {
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
            throw new Exception("ROUTES0115", $this->imageFileName);
        }
        return $location;
    }
}

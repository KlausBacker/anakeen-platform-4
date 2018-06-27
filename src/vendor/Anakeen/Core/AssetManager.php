<?php

namespace Anakeen\Core;

use Anakeen\Core\Utils\FileMime;

class AssetManager
{
    public static function getAssetLink($file)
    {
        $wversion = ContextManager::getParameterValue("WVERSION");
        if (!is_file($file)) {
            throw new \Dcp\Exception("Asset File $file not found");
        }
        $path = realpath($file);

        $encodedEntry = hash("md5", $path.$wversion);
        $fileExtension = FileMime::getFileExtension($path);
        $assetDir= sprintf("%s/%s/assets/", DEFAULT_PUBDIR, Settings::CacheDir);
        $dest = sprintf("%s%s.%s", $assetDir, $encodedEntry, $fileExtension);
        if (!is_dir($assetDir)) {
            mkdir($assetDir);
        }
        if (!is_link($dest)) {
            if (!symlink($path, $dest)) {
                 throw new \Dcp\Exception(sprintf("Asset : Cannot link [%s] -> [%s]", $path, $dest));
            }
        }
        return sprintf("/assets/%s.%s", $encodedEntry, $fileExtension);
    }
}

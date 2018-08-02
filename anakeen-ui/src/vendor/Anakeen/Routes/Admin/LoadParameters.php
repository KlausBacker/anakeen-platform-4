<?php

namespace Anakeen\Routes\Admin;

use Anakeen\Core\ContextManager;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;

/**
 * Class Autocomplete
 *
 * @note    Used by route : POST /api/v2/documents/{docid}/autocomplete/{attrid}
 * @package Anakeen\Routes\Ui
 */
class LoadParameters
{
    const CUSTOMDIR = "/Apps/DOCUMENT/customRender.d";

    /**
     * Reset RENDER_PARAMETERS application parameters
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return mixed
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $directory = DEFAULT_PUBDIR . self::CUSTOMDIR;
        if (is_dir($directory)) {
            $directoryIterator = new \DirectoryIterator($directory);
            $jsonIterator = new \RegexIterator($directoryIterator, "/.*\.json$/");
            $jsonList = array();
            foreach ($jsonIterator as $currentFile) {
                $jsonList[] = $currentFile->getPathName();
            }
            sort($jsonList);
            $rules = array();
            foreach ($jsonList as $currentPath) {
                $currentRule = file_get_contents($currentPath);
                $currentRule = json_decode($currentRule, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Unable to read and decode " . $currentPath);
                }
                $rules = array_replace_recursive($rules, $currentRule);
            }

            ContextManager::setParameterValue("Ui", "RENDER_PARAMETERS", json_encode($rules));
        } else {
            throw new Exception(sprintf("No found directory : %s", $directory));
        }

        return ApiV2Response::withData($response, $rules);
    }
}

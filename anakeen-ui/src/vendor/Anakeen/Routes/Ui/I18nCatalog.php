<?php

namespace Anakeen\Routes\Ui;

use Anakeen\Core\ContextManager;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;

/**
 * Class I18nCatalog
 *
 * @note    Used by route : GET /api/v2/i18n/{catalog}
 * @package Anakeen\Routes\Ui
 */
class I18nCatalog
{
    protected $userLocale = null;


    /**
     * Get translation catalog
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
        $currentLocale = $this->getUserLocale();
        $shortLocale = strtok($currentLocale, '_');
        $resourceId = $args["catalog"];
        if ($resourceId === "_all") {
            $file = sprintf("%s/locale/%s/js/catalog.js", DEFAULT_PUBDIR, $shortLocale);
        } else {
            $file = sprintf("%s/locale/%s/js/catalog-%s_%s.js", DEFAULT_PUBDIR, $shortLocale, $resourceId, $shortLocale);
        }
        if (!file_exists($file)) {
            $exception = new Exception("CRUDUI0009", $file);
            $exception->setHttpStatus("404", "Catalog file not found");
            throw $exception;
        }
        $catalog = json_decode(file_get_contents($file), true);
        $data = array(
            "locale" => \Anakeen\Core\ContextManager::getLocaleConfig($currentLocale),
            "catalog" => $catalog
        );
        $response = ApiV2Response::withEtag($request, $response, $this->getEtagInfo());
        return ApiV2Response::withData($response, $data);
    }

    /**
     *
     */
    protected function getUserLocale()
    {
        if ($this->userLocale === null) {

            $this->userLocale = ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde,  "CORE_LANG");

            if (empty($this->userLocale)) {
                $this->userLocale = "fr_FR";
            }
        }
        return $this->userLocale;
    }

    /**
     * Return etag info
     *
     * @return null|string
     */
    public function getEtagInfo()
    {
        $version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        return $version . " " . $this->getUserLocale();
    }
}

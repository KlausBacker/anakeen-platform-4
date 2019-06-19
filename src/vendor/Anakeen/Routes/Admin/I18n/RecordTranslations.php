<?php


namespace Anakeen\Routes\Admin\I18n;

use Anakeen\Router\ApiV2Response;

class RecordTranslations
{
    protected $msgid = null;
    protected $attrNewValue = null;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->msgid = $request->getQueryParam("msgid");
        $this->attrNewValue = $request->getQueryParam("value");
    }

    public function doRequest()
    {
        return "";
    }
}

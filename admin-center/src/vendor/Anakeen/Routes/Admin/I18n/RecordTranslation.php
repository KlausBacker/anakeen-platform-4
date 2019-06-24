<?php


namespace Anakeen\Routes\Admin\I18n;

use Anakeen\Router\ApiV2Response;

/**
 * Class RecordTranslation
 *
 * @note Used by route : PUT /api/v2/admin/i18n/{lang}/{msgctxt}/{msgid}
 */
class RecordTranslation
{
    protected $msgid = null;
    protected $msgctxt = null;
    protected $lang = null;
    protected $newTranslation = null;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->msgid = $args["msgid"];
        $this->msgctxt = $args["msgctxt"];
        $this->lang = $args["lang"];
        $this->newTranslation = $request->getParsedBody();
    }

    public function doRequest()
    {
        return "";
    }
}

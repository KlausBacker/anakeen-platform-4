<?php


namespace Anakeen\Routes\Admin\I18n;

use Anakeen\Core\ContextManager;
use Anakeen\Router\ApiV2Response;

class ImportTranslationsFile
{
    const OVERRIDE_FILE = "custom/1_override.po";
    protected $lang = null;
    protected $data;
    protected $customPoFile;
    protected $filePath;
    protected $result;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->result);
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->lang = strtolower(substr($args["lang"], 0, 2));
        $this->data = file_get_contents($_FILES["file"]["tmp_name"]);
        $this->customPoFile = sprintf("%s/locale/%s/LC_MESSAGES/custom-catalog.po", ContextManager::getRootDirectory(), $this->lang);
        $this->filePath = sprintf("%s/locale/%s/LC_MESSAGES/src/%s", ContextManager::getRootDirectory(), $this->lang, self::OVERRIDE_FILE);
        $custom = file_put_contents($this->customPoFile, $this->data);
        $override = file_put_contents($this->filePath, $this->data);
        $this->result = $custom && $override;
    }
}

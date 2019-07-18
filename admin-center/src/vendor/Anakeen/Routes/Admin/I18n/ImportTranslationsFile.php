<?php

namespace Anakeen\Routes\Admin\I18n;

use Anakeen\Exception;
use Anakeen\Router\ApiV2Response;

class ImportTranslationsFile
{
    const OVERRIDE_FILE = "custom/1_override.po";
    protected $lang = null;
    protected $customPoFile;
    protected $filePath;
    protected $result;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->result);
    }/** @noinspection PhpUnusedParameterInspection */
    /** @noinspection PhpUnusedParameterInspection */

    /**
     * @param \Slim\Http\request $request
     * @param $args
     * @throws Exception
     * @throws \Anakeen\Script\Exception
     */
    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->lang = strtolower(substr($args["lang"], 0, 2));
        $path = $_FILES["file"]["tmp_name"];

        exec("msgfmt --statistics -c -v -o /dev/null " . escapeshellarg($path), $output, $return);
        if ($return === 0) {
            $this->filePath = RecordTranslation::getOverrideFilepath($this->lang);
            $backup=null;
            if (file_exists($this->filePath)) {
                $backup = $this->filePath . ".ibak";
                copy($this->filePath, $backup);
            }

            try {
                exec(sprintf("msgfmt %s -o - | msgunfmt -o %s", escapeshellarg($path), escapeshellarg($this->filePath)), $output, $return);
                if ($return !== 0) {
                    throw new \Anakeen\Core\Exception("Cannot copy po file");
                }
                $system = new \Anakeen\Script\System();
                $system->localeGen();
                $system->refreshJsVersion();
            } catch (\Exception $e) {
                if (is_file($backup)) {
                    if ($backup) {
                        rename($backup, $this->filePath);
                    } else {
                        unlink($backup);
                    }
                }
                // Restore old translations
                $system = new \Anakeen\Script\System();
                $system->localeGen();
            }
        } else {
            throw new Exception("The po file format is not correct");
        }
    }
}

<?php

namespace Anakeen\Routes\Devel;

use Anakeen\Core\ContextManager;
use Anakeen\Router\ApiV2Response;
use Dcp\Exception;

/** @noinspection PhpIncludeInspection */
require_once "vendor/Anakeen/Routes/Devel/Lib/vendor/autoload.php";

/**
 * Get All Enumerate Items
 *
 * @note Used by route : GET /api/v2/devel/i18n/
 */
class I18n
{

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
    }

    public function doRequest()
    {
        $data = [];

        $langs = ["fr", "en"];

        $tmpPo = sprintf("%s/%s.po", ContextManager::getTmpDir(), uniqid("i18n"));
        foreach ($langs as $lang) {
            $cmd = sprintf("msgunfmt %s/locale/%s/LC_MESSAGES/main-catalog.mo > %s", escapeshellarg(ContextManager::getRootDirectory()), $lang, escapeshellarg($tmpPo));

            exec($cmd, $output, $status);
            if ($status !== 0) {
                throw new Exception("Fail retrieve locale");
            }
            if (!file_exists($tmpPo)) {
                throw new Exception("Fail retrieve locale results");
            }
            $parser = new \PoParser\Parser();
            $parser->read($tmpPo);
            $entries = $parser->getEntriesAsArrays();

            foreach ($entries as $msgid => $entry) {
                if (!$msgid) {
                    continue;
                }
                $key = sprintf("%s-%s", $entry["msgctxt"], $msgid);

                if (!isset($data[$key])) {
                    $data[$key] = [
                        "msgctxt" => $entry["msgctxt"],
                        "msgid" => $msgid
                    ];
                }

                $data[$key]["$lang"] = implode("\n", $entry["msgstr"]);
            }
        }

        usort($data, function ($a, $b) {
            $cmp = strcmp($a["msgctxt"], $b["msgctxt"]);
            if ($cmp !== 0) {
                return $cmp;
            } else {
                return strcmp($a["msgid"], $b["msgid"]);
            }
        });

        return array_values($data);
    }
}

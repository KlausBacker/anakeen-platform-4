<?php

namespace Anakeen\Routes\Admin\I18n;

use Anakeen\Core\ContextManager;
use Anakeen\Core\SmartStructure\Attributes;
use Anakeen\Router\ApiV2Response;
use Anakeen\Exception;
use Anakeen\Search\SearchElements;

/** @noinspection PhpIncludeInspection */
require_once "vendor/Anakeen/Routes/Devel/Lib/vendor/autoload.php";

/**
 * Get All Enumerate Items
 *
 * @note Used by route : GET /api/v2/devel/i18n/
 */
class Translations
{

    protected $lang;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->lang = strtolower(substr($args["lang"], 0, 2));
    }

    public function doRequest()
    {
        $data = $this->getRecordedTranslations();

        $this->addSmartStructureLocale($data);

        usort($data, function ($a, $b) {
            $cmp = strcmp($a["msgctxt"], $b["msgctxt"]);
            if ($cmp !== 0) {
                return $cmp;
            } else {
                return strcmp($a["msgid"], $b["msgid"]);
            }
        });
        return $data;
    }

    protected function addSmartStructureLocale(&$data)
    {
        $s = new SearchElements(-1);
        $structures = $s->search()->getResults();

        foreach ($structures as $structure) {
            $fields = $structure->getAttributes();
            foreach ($fields as $field) {
                if ($field->id !== Attributes::HIDDENFIELD) {
                    $key = sprintf("%s-%s", $structure->name, $field->id);
                    if (!isset($data[$key])) {
                        $data[$key] = [
                            "section" => "SmartStructure",
                            "msgctxt" => $structure->name,
                            "msgid" => $field->id,
                            "msgstr" => $field->labelText
                        ];
                    } else {
                        $data[$key]["section"] = "SmartStructure";
                    }
                }
            }
        }
    }

    protected function getRecordedTranslations()
    {
        $data = [];


        $tmpPo = sprintf("%s/%s.po", ContextManager::getTmpDir(), uniqid("i18n"));

        $cmd = sprintf("msgunfmt %s/locale/%s/LC_MESSAGES/main-catalog.mo > %s", escapeshellarg(ContextManager::getRootDirectory()), $this->lang, escapeshellarg($tmpPo));

        exec($cmd, $output, $status);
        if ($status !== 0) {
            throw new Exception("Fail retrieve locale");
        }
        if (!file_exists($tmpPo)) {
            throw new Exception("Fail retrieve locale results");
        }
        $fileHandler = new \Sepia\PoParser\SourceHandler\FileSystem($tmpPo);
        $poParser = new \Sepia\PoParser\Parser($fileHandler);
        $catalog = $poParser->parse();

        $entries = $catalog->getEntries();
        foreach ($entries as $entry) {
            $key = sprintf("%s-%s", $entry->getMsgCtxt(), $entry->getMsgId());

            if (!isset($data[$key])) {
                $data[$key] = [
                    "section" => "",
                    "msgctxt" => $entry->getMsgCtxt(),
                    "msgid" => $entry->getMsgId()
                ];
            }

            $data[$key]["msgstr"] = $entry->getMsgStr();
            if (($entry->getMsgIdPlural())) {
                $data[$key]["plural"] = $entry->getMsgStrPlurals();
            }
        }


        return $data;
    }
}

<?php

namespace Anakeen\Routes\Admin\I18n;

use Anakeen\Core\ContextManager;
use Anakeen\Core\SmartStructure\Attributes;
use Anakeen\Core\Utils\Strings;
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
    protected $filters = array();
    const PAGESIZE = 50;
    protected $take = self::PAGESIZE;
    protected $skip = 0;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        $data["data"] = $this->doRequest();
        $data["requestParameters"]["take"] = $this->take;
        $data["requestParameters"]["skip"] = $this->skip;
        $data["requestParameters"]["total"] = count($data["data"]);
        $data["data"] = array_slice($data["data"], $this->skip, $this->take);
        return ApiV2Response::withData($response, $data);
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->lang = strtolower(substr($args["lang"], 0, 2));
        $this->filters = $request->getQueryParam("filter")["filters"];
        if ($request->getQueryParam("take") === "all") {
            $this->take = $request->getQueryParam("take");
        } else {
            $this->take = intval($request->getQueryParam("take", self::PAGESIZE));
        }
        $this->skip = intval($request->getQueryParam("skip", 0));
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
                    if (empty($this->filters) || $this->filterContainsStructure($structure, $field, $this->filters)) {
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
        $i = 0;
        foreach ($entries as $entry) {
            $key = sprintf("%s-%s", $entry->getMsgCtxt(), $entry->getMsgId());
            if (empty($this->filters) || $this->filterContainsTranslations($entry, $this->filters)) {
                if (!isset($data[$key])) {
                    $data[$key] = [
                        "gridId" => $i++,
                        "section" => "",
                        "msgctxt" => $entry->getMsgCtxt(),
                        "msgid" => $entry->getMsgId(),
                    ];
                }
                $data[$key]["msgstr"] = $entry->getMsgStr();
                if (($entry->getMsgIdPlural())) {
                    $data[$key]["plural"] = $entry->getMsgStrPlurals();
                }
            }
        }
        return $data;
    }

    private function filterContainsTranslations($entry, $filters)
    {
        $filterPassed = false;
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                error_log(print_r("filtering...", true));
                $filterField = $filter["field"];
                $filterValue = $filter["value"];
                $entryValue;
                switch ($filterField) {
                    case "msgctxt":
                        error_log(print_r("Translations : msgctxt", true));
                        $entryValue = $entry->getMsgCtxt();
                        break;
                    case "msgid":
                        error_log(print_r("Translations : msgid", true));
                        $entryValue = $entry->getMsgId();
                        break;
                    case "msgstr":
                        error_log(print_r("Translations : msgstr", true));
                        $entryValue = $entry->getMsgStr();
                        break;
                    case "section":
                        error_log(print_r("Translations : section", true));
                        $entryValue = "";
                        break;
                    default:
                        break;
                }
                if (strpos(Strings::unaccent(strtolower($entryValue)), Strings::unaccent(strtolower($filterValue))) !== false) {
                    $filterPassed = true;
                }
            }
        }
        return $filterPassed;
    }

    private function filterContainsStructure($structure, $field, $filters)
    {
        $filterPassed = false;
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                error_log(print_r("filtering...", true));
                $filterField = $filter["field"];
                $filterValue = $filter["value"];
                $entryValue;
                switch ($filterField) {
                    case "msgctxt":
                        error_log(print_r("Structure : msgctxt", true));
                        $entryValue = $structure->name;
                        break;
                    case "msgid":
                        error_log(print_r("Structure : msgid", true));
                        $entryValue = $field->id;
                        break;
                    case "msgstr":
                        error_log(print_r("Structure : msgstr", true));
                        $entryValue = $field->labelText;
                        break;
                    case "section":
                        error_log(print_r("Structure : section", true));
                        $entryValue = "SmartStructure";
                        break;
                    default:
                        break;
                }
                if (strpos(Strings::unaccent(strtolower($entryValue)), Strings::unaccent(strtolower($filterValue))) !== false) {
                    $filterPassed = true;
                }
            }
        }
        return $filterPassed;
    }
}

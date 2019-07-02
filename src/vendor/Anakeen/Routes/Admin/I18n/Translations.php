<?php

namespace Anakeen\Routes\Admin\I18n;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\SmartStructure\Attributes;
use Anakeen\Core\Utils\Strings;
use Anakeen\Router\ApiV2Response;
use Anakeen\Exception;
use Anakeen\Search\SearchElements;
use Sepia\PoParser\Catalog\Entry;

/** @noinspection PhpIncludeInspection */
require_once "vendor/Anakeen/Routes/Devel/Lib/vendor/autoload.php";

/**
 * Get All Enumerate Items
 *
 * @note Used by route : GET /api/v2/admin/i18n/{lang}
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
        if ($request->getQueryParam("filter") && isset($request->getQueryParam("filter")["filters"])) {
            $this->filters = $request->getQueryParam("filter")["filters"];
        }
        if ($request->getQueryParam("take") === "all") {
            $this->take = $request->getQueryParam("take");
        } else {
            $this->take = intval($request->getQueryParam("take", self::PAGESIZE));
        }
        $this->skip = intval($request->getQueryParam("skip", 0));
    }

    /**
     * @return array
     * @throws Exception
     */
    public function doRequest()
    {
        $data = $this->getRecordedTranslations();

        $this->addSmartStructureLocale($data);
        $this->addEnumLocale($data);

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
                                "msgstr" => $field->labelText,
                                "override" => null
                            ];
                        } else {
                            $data[$key]["section"] = "SmartStructure";
                        }
                    }
                }
            }
        }
    }


    protected function addEnumLocale(&$data)
    {
        DbManager::query("select * from docenum", $enums);

        foreach ($enums as $enum) {
            $key = sprintf("%s-%s", $enum["name"], $enum["key"]);
            if (empty($this->filters) || $this->filterContainsEnum($enum, $this->filters)) {
                if (!isset($data[$key])) {
                    $data[$key] = [
                        "section" => "Enum",
                        "msgctxt" => $enum["name"],
                        "msgid" => $enum["key"],
                        "msgstr" => $enum["label"],
                        "override" => null
                    ];
                } else {
                    $data[$key]["section"] = "Enum";
                }
            }
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function getRecordedTranslations()
    {
        $data = [];

        $customPoFile = sprintf("%s/locale/%s/LC_MESSAGES/custom-catalog.po", ContextManager::getRootDirectory(), $this->lang);
        if (!file_exists($customPoFile)) {
            throw new Exception("Fail retrieve custom locale results");
        }


        $originPoFile = sprintf("%s/locale/%s/LC_MESSAGES/origin-catalog.po", ContextManager::getRootDirectory(), $this->lang);
        if (!file_exists($originPoFile)) {
            throw new Exception("Fail retrieve origin locale results");
        }

        $fileHandler = new \Sepia\PoParser\SourceHandler\FileSystem($customPoFile);
        $poParser = new \Sepia\PoParser\Parser($fileHandler);
        $customCatalog = $poParser->parse();


        $originFileHandler = new \Sepia\PoParser\SourceHandler\FileSystem($originPoFile);
        $originParser = new \Sepia\PoParser\Parser($originFileHandler);
        $originalCatalog = $originParser->parse();


        $entries = $originalCatalog->getEntries();
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
                    $data[$key]["pluralid"] = $entry->getMsgIdPlural();
                    $data[$key]["plurals"] = $entry->getMsgStrPlurals();
                }
                $customEntry = $customCatalog->getEntry($entry->getMsgId(), $entry->getMsgCtxt());
                if ($customEntry) {
                    $data[$key]["override"] = $customEntry->getMsgStr();
                } else {
                    $data[$key]["override"] = null;
                }
            }
        }
        return $data;
    }

    private function filterContainsTranslations(Entry $entry, $filters)
    {
        $filterPassed = true;
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $filterField = $filter["field"];
                $filterValue = $filter["value"];
                $entryValue = null;
                switch ($filterField) {
                    case "msgctxt":
                        $entryValue = $entry->getMsgCtxt();
                        break;
                    case "msgid":
                        $entryValue = $entry->getMsgId();
                        break;
                    case "msgstr":
                        $entryValue = $entry->getMsgStr();
                        break;
                    case "section":
                        $entryValue = "";
                        break;
                    default:
                        break;
                }
                if (strpos(Strings::unaccent(strtolower($entryValue)), Strings::unaccent(strtolower($filterValue))) === false) {
                    return false;
                }
            }
        }
        return $filterPassed;
    }

    private function filterContainsStructure($structure, $field, $filters)
    {
        $filterPassed = true;
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $filterField = $filter["field"];
                $filterValue = $filter["value"];
                $entryValue = null;
                switch ($filterField) {
                    case "msgctxt":
                        $entryValue = $structure->name;
                        break;
                    case "msgid":
                        $entryValue = $field->id;
                        break;
                    case "msgstr":
                        $entryValue = $field->labelText;
                        break;
                    case "section":
                        $entryValue = "SmartStructure";
                        break;
                    default:
                        break;
                }
                if (strpos(Strings::unaccent(strtolower($entryValue)), Strings::unaccent(strtolower($filterValue))) === false) {
                    return false;
                }
            }
        }
        return $filterPassed;
    }

    private function filterContainsEnum($enum, $filters)
    {
        $filterPassed = true;
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $filterField = $filter["field"];
                $filterValue = $filter["value"];
                $entryValue = null;
                switch ($filterField) {
                    case "msgctxt":
                        $entryValue = $enum["name"];
                        break;
                    case "msgid":
                        $entryValue = $enum["key"];
                        break;
                    case "msgstr":
                        $entryValue = $enum["label"];
                        break;
                    case "section":
                        $entryValue = "Enum";
                        break;
                    default:
                        break;
                }
                if (strpos(Strings::unaccent(strtolower($entryValue)), Strings::unaccent(strtolower($filterValue))) === false) {
                    return false;
                }
            }
        }
        return $filterPassed;
    }
}

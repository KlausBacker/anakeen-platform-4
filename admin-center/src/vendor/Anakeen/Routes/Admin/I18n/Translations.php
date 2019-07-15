<?php

namespace Anakeen\Routes\Admin\I18n;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\Attributes;
use Anakeen\Core\Utils\Strings;
use Anakeen\Router\ApiV2Response;
use Anakeen\Exception;
use Anakeen\Search\SearchElements;
use Anakeen\SmartStructures\Wdoc\WDocHooks;
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
    protected $sort;
    protected $direction;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        $data["data"] = $this->doRequest();
        $data["requestParameters"]["sort"] = $this->sort;
        $data["requestParameters"]["take"] = $this->take;
        $data["requestParameters"]["skip"] = $this->skip;
        $data["requestParameters"]["total"] = count($data["data"]);
        if ($this->sort) {
            if ($this->sort["dir"] === "asc") {
                $this->direction = SORT_ASC;
            } else {
                $this->direction = SORT_DESC;
            }
            array_multisort(array_column($data["data"], $this->sort["field"]), $this->direction, $data["data"]);
        }
        $data["data"] = array_slice($data["data"], $this->skip, $this->take);

        return ApiV2Response::withData($response, $data);
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->lang = strtolower(substr($args["lang"], 0, 2));
        if ($request->getQueryParam("sort")) {
            $this->sort = $request->getQueryParam("sort")[0];
        }
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
        $this->addWorkflowLocale($data);
        $this->addEnumLocale($data);

        $this->addOverride($data);

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

    protected function addoverride(&$data)
    {
        $customPoFile = sprintf("%s/locale/%s/LC_MESSAGES/custom-catalog.po", ContextManager::getRootDirectory(), $this->lang);
        if (!file_exists($customPoFile)) {
            throw new Exception("Fail retrieve custom locale results");
        }

        $fileHandler = new \Sepia\PoParser\SourceHandler\FileSystem($customPoFile);
        $poParser = new \Sepia\PoParser\Parser($fileHandler);
        $customCatalog = $poParser->parse();

        foreach ($data as &$datum) {
            $customEntry = $customCatalog->getEntry($datum["msgid"], $datum["msgctxt"]);
            if ($customEntry) {
                $datum["override"] = $customEntry->getMsgStr();
                if ($customEntry->getMsgIdPlural()) {
                    $val1 = $customEntry->getMsgStrPlurals()[0] ?? "";
                    $val2 = $customEntry->getMsgStrPlurals()[1] ?? "";
                    $datum["override"] = [$val1,$val2];
                } else {
                    $datum["override"] = $customEntry->getMsgStr();
                }
            }
        }
    }

    protected function addSmartStructureLocale(&$data)
    {
        $s = new SearchElements(-1);
        $structures = $s->search()->getResults();
        foreach ($structures as $structure) {
            $fields = $structure->getAttributes();
            foreach ($fields as $field) {
                if ($field->id !== Attributes::HIDDENFIELD) {
                    $entry = [
                        "msgid" => $field->id,
                        "msgstr" => $field->labelText,
                        "msgctxt" => $structure->name,
                        "section" => "SmartStructure"
                    ];
                    $key = sprintf("%s-%s", $entry["msgctxt"], $entry["msgid"]);
                    if (empty($this->filters) || $this->filterContainsEntry($entry, $this->filters)) {
                        if ($field->structureId == $structure->id) {
                            if (!isset($data[$key])) {
                                $data[$key] = [
                                    "msgctxt" => $entry["msgctxt"],
                                    "msgid" => $entry["msgid"],
                                    "msgstr" => $entry["msgstr"],
                                    "override" => null
                                ];
                            }
                        }
                    }
                    if (isset($data[$key])) {
                        $data[$key]["section"] = "SmartStructure";
                    }
                }
            }
        }
    }

    protected function addWorkflowLocale(&$data)
    {
        $s = new SearchElements(-1);
        $s->addFilter("usefor ~ 'W'");
        $structures = $s->search()->getResults();
        foreach ($structures as $structure) {
            $workflow = SEManager::createDocument($structure->name);
            /** @var WDocHooks $workflow */

            if ($workflow->graphModelName) {
                $states = $workflow->getStates();
                foreach ($states as $state) {
                    $entry = [
                        "msgid" => $state,
                        "msgctxt" => sprintf("%s:state", $workflow->graphModelName),
                        "msgstr" => $workflow->stepLabels[$state]["state"] ?? "",
                        "section" => "Workflow"
                    ];
                    $key = sprintf("%s-%s", $entry["msgctxt"], $entry["msgid"]);
                    if (empty($this->filters) || $this->filterContainsEntry($entry, $this->filters)) {
                        if (!isset($data[$key])) {
                            $data[$key] = [
                                "msgctxt" => $entry["msgctxt"],
                                "msgid" => $entry["msgid"],
                                "msgstr" => $entry["msgstr"],
                            ];
                        }
                    }
                    if (isset($data[$key])) {
                        $data[$key]["section"] = "Workflow";
                        $data[$key]["defaultstr"] = $workflow->stepLabels[$state]["state"] ?? "";
                    }

                    // Activity
                }
                $transitions = $workflow->cycle;
                foreach ($transitions as $transition) {
                    $entry = [
                        "msgid" => $transition["t"],
                        "msgstr" => $workflow->transitions[$transition["t"]]["label"] ?? "",
                        "msgctxt" => sprintf("%s:transition", $workflow->graphModelName),
                        "defaultstr" => $workflow->transitions[$transition["t"]]["label"] ?? "",
                        "section" => "Workflow"
                    ];
                    $key = sprintf("%s-%s", $entry["msgctxt"], $entry["msgid"]);
                    if (empty($this->filters) || $this->filterContainsEntry($entry, $this->filters)) {
                        if (!isset($data[$key])) {
                            $data[$key] = [
                                "msgctxt" => $entry["msgctxt"],
                                "msgid" => $entry["msgid"],
                                "msgstr" => $entry["msgstr"],
                                "override" => null
                            ];
                        }
                    }
                    if (isset($data[$key])) {
                        $data[$key]["section"] = "Workflow";
                        $data[$key]["defaultstr"] = $workflow->transitions[$transition["t"]]["label"] ?? "";
                    }
                }
            }
        }
    }


    protected function addEnumLocale(&$data)
    {
        DbManager::query("select * from docenum", $enums);

        foreach ($enums as $enum) {
            $entry = [
                "msgid" => $enum["key"],
                "msgstr" => $enum["label"],
                "msgctxt" => $enum["name"],
                "section" => "Enum"
            ];
            $key = sprintf("%s-%s", $entry["msgctxt"], $entry["msgid"]);
            if (empty($this->filters) || $this->filterContainsEntry($entry, $this->filters)) {
                if (!isset($data[$key])) {
                    $data[$key] = [
                        "msgctxt" => $entry["msgctxt"],
                        "msgid" => $entry["msgid"],
                        "msgstr" => $entry["msgstr"],
                        "override" => null
                    ];
                }
            }
            if (isset($data[$key])) {
                $data[$key]["section"] = "Enum";
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

        $originPoFile = sprintf("%s/locale/%s/LC_MESSAGES/origin-catalog.po", ContextManager::getRootDirectory(), $this->lang);
        if (!file_exists($originPoFile)) {
            throw new Exception("Fail retrieve origin locale results");
        }

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

    private function filterContainsEntry($entry, $filters)
    {
        $filterPassed = true;
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $filterField = $filter["field"];
                $filterValue = $filter["value"];
                $entryValue = null;

                $entryValue = $entry[$filterField] ?? "";

                if (strpos(Strings::unaccent(strtolower($entryValue)), Strings::unaccent(strtolower($filterValue))) === false) {
                    return false;
                }
            }
        }
        return $filterPassed;
    }
}

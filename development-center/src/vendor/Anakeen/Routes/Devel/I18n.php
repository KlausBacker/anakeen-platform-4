<?php

namespace Anakeen\Routes\Devel;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\EnumManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\Attributes;
use Anakeen\Core\Utils\Glob;
use Anakeen\Core\Utils\Strings;
use Anakeen\Router\ApiV2Response;
use Anakeen\Exception;
use Anakeen\Search\SearchElements;
use Anakeen\SmartStructures\Wdoc\WDocHooks;
use Sepia\PoParser\SourceHandler\FileSystem;

/** @noinspection PhpIncludeInspection */
require_once "vendor/Anakeen/Routes/Devel/Lib/vendor/autoload.php";

/**
 * Get All Enumerate Items
 *
 * @note Used by route : GET /api/v2/devel/i18n/
 */
class I18n
{
    const langs = ["fr", "en"];
    const COMPONENT_SECTION="Component";

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
        $data = $this->getRecordedTranslations();
        $this->addPoOriginFiles($data);
        $this->addJsonTranslations($data);
        $this->addSmartStructureLocale($data);
        $this->addWorkflowLocale($data);
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

    protected function addJsonTranslations(&$data)
    {
        $rootPathLength = strlen(ContextManager::getRootDirectory());
        foreach (self::langs as $lang) {
            $vueGlob = sprintf("%s/locale/%s/vuejs/src/*.json", ContextManager::getRootDirectory(), $lang);
            foreach (glob($vueGlob) as $filename) {
                $vueData = json_decode(file_get_contents($filename), true);

                $ctx = strtok(basename($filename), ".");
                foreach ($vueData as $entryID => $entryMsg) {
                    $key = self::COMPONENT_SECTION . $entryID;
                    $entry = [
                        "msgctxt" => $ctx,
                        "msgid" => $entryID,
                    ];
                    if (empty($this->filters) || $this->filterContainsEntry($entry, $this->filters)) {
                        $data[$key] = $entry;
                    }
                    if (isset($data[$key])) {
                        $data[$key]["files"][] = "." . substr($filename, $rootPathLength);
                    }
                    $data[$key]["$lang"] = $entryMsg;
                }
            }
        }
    }

    protected function addSmartStructureLocale(&$data)
    {
        $s = new SearchElements(-1);
        $structures = $s->search()->getResults();
        foreach (self::langs as $lang) {
            foreach ($structures as $structure) {
                $fields = $structure->getAttributes();
                foreach ($fields as $field) {
                    if ($field->id !== Attributes::HIDDENFIELD) {
                        $entry = [
                            "msgid" => $field->id,
                            "msgstr" => $field->labelText,
                            "msgctxt" => $structure->name,
                        ];
                        $key = sprintf("%s-%s", $entry["msgctxt"], $entry["msgid"]);
                        if (empty($this->filters) || $this->filterContainsEntry($entry, $this->filters)) {
                            if ($field->structureId == $structure->id) {
                                if (!isset($data[$key])) {
                                    $data[$key] = [
                                        "msgctxt" => $entry["msgctxt"],
                                        "msgid" => $entry["msgid"],
                                        "$lang" => $entry["msgstr"],
                                    ];
                                }
                            }
                        }
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
        foreach (self::langs as $lang) {
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
                        ];
                        $key = sprintf("%s-%s", $entry["msgctxt"], $entry["msgid"]);
                        if (empty($this->filters) || $this->filterContainsEntry($entry, $this->filters)) {
                            if (!isset($data[$key])) {
                                $data[$key] = [
                                    "msgctxt" => $entry["msgctxt"],
                                    "msgid" => $entry["msgid"],
                                    "$lang" => $entry["msgstr"],
                                ];
                            }
                        }
                    }
                    $transitions = $workflow->cycle;
                    foreach ($transitions as $transition) {
                        $entry = [
                            "msgid" => $transition["t"],
                            "msgstr" => $workflow->transitions[$transition["t"]]["label"] ?? "",
                            "msgctxt" => sprintf("%s:transition", $workflow->graphModelName),
                        ];
                        $key = sprintf("%s-%s", $entry["msgctxt"], $entry["msgid"]);
                        if (empty($this->filters) || $this->filterContainsEntry($entry, $this->filters)) {
                            if (!isset($data[$key])) {
                                $data[$key] = [
                                    "msgctxt" => $entry["msgctxt"],
                                    "msgid" => $entry["msgid"],
                                    "$lang" => $entry["msgstr"],
                                ];
                            }
                        }
                    }
                }
            }
        }
    }


    protected function addEnumLocale(&$data)
    {
        DbManager::query(sprintf("select * from docenum where key != '%s'", EnumManager::EXTENDABLEKEY), $enums);
        foreach (self::langs as $lang) {
            foreach ($enums as $enum) {
                $entry = [
                    "msgid" => $enum["key"],
                    "msgstr" => $enum["label"],
                    "msgctxt" => $enum["name"],
                ];
                $key = sprintf("%s-%s", $entry["msgctxt"], $entry["msgid"]);
                if (empty($this->filters) || $this->filterContainsEntry($entry, $this->filters)) {
                    if (!isset($data[$key])) {
                        $data[$key] = [
                            "msgctxt" => $entry["msgctxt"],
                            "msgid" => $entry["msgid"],
                            "$lang" => $entry["msgstr"],
                        ];
                    }
                }
            }
        }
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

                if (strpos(
                    Strings::unaccent(strtolower($entryValue)),
                    Strings::unaccent(strtolower($filterValue))
                ) === false) {
                    return false;
                }
            }
        }
        return $filterPassed;
    }
    protected function addPoOriginFiles(&$data)
    {
        $poPattern = sprintf("%s/locale/**/*po", ContextManager::getRootDirectory());
        $poFiles = Glob::glob($poPattern);
        $rootPathLength = strlen(ContextManager::getRootDirectory());

        foreach ($poFiles as $poFile) {
            $fileHandler = new \Sepia\PoParser\SourceHandler\FileSystem($poFile);
            $poParser = new \Sepia\PoParser\Parser($fileHandler);
            $catalog = $poParser->parse();
            $entries = $catalog->getEntries();

            foreach ($entries as $entry) {
                $key = sprintf("%s-%s", $entry->getMsgCtxt(), $entry->getMsgId());

                if ($entry->isFuzzy() || $entry->isObsolete()) {
                    continue;
                }
                if (isset($data[$key])) {
                    $data[$key]["files"][] = '.' . substr($poFile, $rootPathLength);
                }
            }
        }
    }
    protected function getRecordedTranslations()
    {
        $data = [];
        foreach (self::langs as $lang) {
            $originPoFile = sprintf(
                "%s/locale/%s/LC_MESSAGES/main-catalog.po",
                ContextManager::getRootDirectory(),
                $lang
            );
            if (!file_exists($originPoFile)) {
                throw new Exception("Fail retrieve origin locale results");
            }

            $originFileHandler = new \Sepia\PoParser\SourceHandler\FileSystem($originPoFile);
            $originParser = new \Sepia\PoParser\Parser($originFileHandler);
            $originalCatalog = $originParser->parse();


            $entries = $originalCatalog->getEntries();
            $i = 0;
            foreach ($entries as $entry) {
                if ($entry->isFuzzy()) {
                    //continue;
                }
                if ($entry->isObsolete() === false) {
                    $key = sprintf("%s-%s", $entry->getMsgCtxt(), $entry->getMsgId());
                    if (empty($this->filters) || $this->filterContainsTranslations($entry, $this->filters)) {
                        if (!isset($data[$key])) {
                            $data[$key] = [
                                "msgctxt" => $entry->getMsgCtxt(),
                                "msgid" => $entry->getMsgId(),
                            ];
                        }
                        $data[$key]["$lang"] = $entry->getMsgStr();
                        if (($entry->getMsgIdPlural())) {
                            $data[$key]["$lang"] = implode("\n", $entry->getMsgStrPlurals());
                        }
                    }
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
                if (strpos(
                    Strings::unaccent(strtolower($entryValue)),
                    Strings::unaccent(strtolower($filterValue))
                ) === false) {
                    return false;
                }
            }
        }
        return $filterPassed;
    }
}

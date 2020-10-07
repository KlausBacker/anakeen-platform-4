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
        $langs = ["fr", "en"];
        foreach ($langs as $lang) {
            $jsonPattern = sprintf("%s/locale/%s/**/*.json", ContextManager::getRootDirectory(), $lang);
            $jsonFiles = Glob::glob($jsonPattern);
            foreach ($jsonFiles as $jsonFile) {
                $jsonCtxt = basename($jsonFile, "." . pathinfo($jsonFile)["extension"]);
                $jsonContent = file_get_contents($jsonFile);
                $items = json_decode($jsonContent, true);
                foreach ($items as $key => $val) {
                    $key = sprintf("%s-%s", $jsonCtxt, $key);
                    if (!isset($data[$key])) {
                        $data[$key] = [
                            "msgctxt" => $jsonCtxt,
                            "msgid" => $key,
                        ];
                    }
                    if (isset($data[$key])) {
                        $data[$key]["files"][] = "." . substr($jsonFile, $rootPathLength);
                    }
                    $data[$key]["$lang"] = $val;
                }
            }
        }
    }

    protected function addSmartStructureLocale(&$data)
    {
        $langs = ["fr", "en"];
        $s = new SearchElements(-1);
        $structures = $s->search()->getResults();
        foreach ($langs as $lang) {
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
        $langs = ["fr", "en"];
        $s = new SearchElements(-1);
        $s->addFilter("usefor ~ 'W'");
        $structures = $s->search()->getResults();
        foreach ($langs as $lang) {
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
        $langs = ["fr", "en"];
        DbManager::query(sprintf("select * from docenum where key != '%s'", EnumManager::EXTENDABLEKEY), $enums);
        foreach ($langs as $lang) {
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
            $fileHandler = new \Sepia\PoParser\SourceHandler\FileSystem($tmpPo);
            $poParser = new \Sepia\PoParser\Parser($fileHandler);
            $catalog = $poParser->parse();

            $entries = $catalog->getEntries();
            foreach ($entries as $entry) {
                $key = sprintf("%s-%s", $entry->getMsgCtxt(), $entry->getMsgId());

                if (!isset($data[$key])) {
                    $data[$key] = [
                        "msgctxt" => $entry->getMsgCtxt(),
                        "msgid" => $entry->getMsgId()
                    ];
                }

                if (($entry->getMsgIdPlural())) {
                    $data[$key]["$lang"] = implode("\n", $entry->getMsgStrPlurals());
                } else {
                    $data[$key]["$lang"] = $entry->getMsgStr();
                }
            }
        }


        return $data;
    }
}

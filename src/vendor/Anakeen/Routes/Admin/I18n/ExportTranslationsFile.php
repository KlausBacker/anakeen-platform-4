<?php

namespace Anakeen\Routes\Admin\I18n;

use Anakeen\Core\ContextManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Exception;
use Sepia\PoParser\Catalog\Entry;

/** @noinspection PhpIncludeInspection */
require_once "vendor/Anakeen/Routes/Devel/Lib/vendor/autoload.php";

/**
 * Get All Enumerate Items
 *
 * @note Used by route : GET /api/v2/admin/i18n/{lang}/custom.po
 */
class ExportTranslationsFile extends Translations
{
    protected $date;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        $this->date = date("Y-m-d H:i:s");
        $filePath = $this->doRequest();
        $fileName = sprintf("Custom Translations %s %s %s.po", $this->lang, ContextManager::getParameterValue("Core", "CORE_CLIENT"), $this->date);
        return ApiV2Response::withFile($response, $filePath, $fileName, false, "text/plain");
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->lang = strtolower(substr($args["lang"], 0, 2));
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
            if ($a["override"] !== null && $b["override"] === null) {
                return -1;
            }
            if ($a["override"] === null && $b["override"] !== null) {
                return 1;
            }
            $cmp = strcmp($a["msgctxt"], $b["msgctxt"]);
            if ($cmp !== 0) {
                return $cmp;
            } else {
                return strcmp($a["msgid"], $b["msgid"]);
            }
        });

        $catalog = $this->initCatalog($data);
        $compiler = new \Sepia\PoParser\PoCompiler();

        $poFile = sprintf("%s/custom%s.po", ContextManager::getTmpDir(), uniqid("custom"));
        file_put_contents($poFile, $compiler->compile($catalog));
        return $poFile;
    }

    protected function initCatalog($data)
    {

        $originPoFile = sprintf("%s/locale/%s/LC_MESSAGES/custom-catalog.po", ContextManager::getRootDirectory(), $this->lang);
        if (!file_exists($originPoFile)) {
            throw new Exception("Fail retrieve origin locale results");
        }
        $originFileHandler = new \Sepia\PoParser\SourceHandler\FileSystem($originPoFile);
        $originParser = new \Sepia\PoParser\Parser($originFileHandler);
        $originalCatalog = $originParser->parse();

        foreach ($data as $datum) {
            if ($datum["override"] === null) {
                $entry = new Entry($datum["msgid"], $datum["msgstr"]);
                if ($datum["msgctxt"]) {
                    $entry->setMsgCtxt($datum["msgctxt"]);
                }
                if (!empty($datum["plurals"])) {
                    $entry->setMsgIdPlural($datum["pluralid"]);
                    $entry->setMsgStrPlurals($datum["plurals"]);
                }
                $entry->setFlags(['fuzzy']);
                $originalCatalog->addEntry($entry);
            }
        }

        $headEntry = $originalCatalog->getHeader();
        $headData = $originalCatalog->getHeaders();
        $headData = array_filter($headData, function ($a) {
            return stripos($a, "PO-Revision-Date") === false && stripos($a, "Project-Id-Version") === false;
        });
        array_unshift($headData, sprintf("Project-Id-Version: %s", ContextManager::getParameterValue("Core", "CORE_CLIENT")));
        $headData[] = sprintf("PO-Revision-Date: Override for %s", $this->date);

        $headEntry->setHeaders($headData);


        $originalCatalog->addHeaders($headEntry);

        return $originalCatalog;
    }
}

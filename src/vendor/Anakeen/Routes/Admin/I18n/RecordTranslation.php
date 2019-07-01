<?php


namespace Anakeen\Routes\Admin\I18n;

use Anakeen\Core\ContextManager;
use Anakeen\Exception;
use Anakeen\Router\ApiV2Response;

/** @noinspection PhpIncludeInspection */
require_once "vendor/Anakeen/Routes/Admin/Lib/vendor/autoload.php";

/**
 * Class RecordTranslation
 *
 * @note Used by route : PUT /api/v2/admin/i18n/{lang}/{msgctxt}/{msgid}
 */
class RecordTranslation
{
    const OVERRIDE_FILE = "custom/1_override.po";
    protected $msgid = null;
    protected $msgctxt = null;
    protected $lang = null;
    protected $msgstr = null;
    /** @var  \Sepia\PoParser\SourceHandler\FileSystem */
    protected $fileHandler;
    /**
     * @var \Sepia\PoParser\Catalog\Catalog
     */
    protected $catalog;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    public function doRequest()
    {
        $poFile = $this->getOverrideFile();
        $this->initOverrideEntries($poFile);

        $this->setEntry();
        $this->savePoFile();
        $this->reinitMainPo();
        return "";
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->msgid = $args["msgid"];
        $this->msgctxt = $args["msgctxt"] ?? "";
        $this->lang = $args["lang"];
        $data = $request->getParsedBody();
        $this->msgstr = $data["msgstr"] ?? "";
    }

    protected function initPoFile($filePath)
    {
        $msgInit = sprintf('msgid ""
msgstr ""
"Project-Id-Version: Override translations\n"
"Language: %s\n"
"PO-Revision-Date: %s"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"', substr($this->lang, 0, 2), date('Y-m-d H:i:s'));
        if (!file_put_contents($filePath, $msgInit)) {
            throw new Exception(sprintf("Cannot write \"%s\" po file", $filePath));
        }
    }

    protected function getOverrideFile()
    {
        $filePath = sprintf("%s/locale/%s/LC_MESSAGES/src/%s", ContextManager::getRootDirectory(), $this->lang, self::OVERRIDE_FILE);
        if (!file_exists($filePath)) {
            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath));
            }
            $this->initPoFile($filePath);
        }
        return $filePath;
    }

    protected function setEntry()
    {
        // Update entry
        $entry = $this->catalog->getEntry($this->msgid, $this->msgctxt);
        if (!$entry) {
            $entry = new \Sepia\PoParser\Catalog\Entry($this->msgid, $this->msgstr);
            if ($this->msgctxt) {
                $entry->setMsgCtxt($this->msgctxt);
            }
            $this->catalog->addEntry($entry);
        } else {
            $entry->setMsgStr($this->msgstr);
        }
    }

    protected function initOverrideEntries($poFile)
    {
        $this->fileHandler = new \Sepia\PoParser\SourceHandler\FileSystem($poFile);
        $poParser = new \Sepia\PoParser\Parser($this->fileHandler);
        $this->catalog = $poParser->parse();
    }

    protected function savePoFile()
    {
        $compiler = new \Sepia\PoParser\PoCompiler();
        $this->fileHandler->save($compiler->compile($this->catalog));
    }

    protected function reinitMainPo()
    {
        $system = new \Anakeen\Script\System();
        $system->localeGen();
    }
}

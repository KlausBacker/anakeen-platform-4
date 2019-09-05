<?php

namespace Anakeen\Core;

use Anakeen\Exception;

/**
 * Class RecordTranslation
 *
 * @note Used by route : PUT /api/v2/admin/i18n/{lang}/{msgctxt}/{msgid}
 */
class EnumCustomLocale
{
    const OVERRIDE_DIR = "custom";
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
    /**
     * @var string
     */
    protected $plurals;
    /**
     * @var string
     */
    private $plural;
    /**
     * @var string
     */
    protected $pluralid;


    public function __construct($enumName)
    {
        $this->msgctxt = $enumName;
    }


    public function addEntry($EnumKey, $enumLabel, $lang)
    {
        $this->msgid = $EnumKey;
        $this->lang = substr($lang, 0, 2);
        $this->msgstr = $enumLabel;

        $poFile = $this->getOverrideFile();

        $backupFile = $this->copyBackup();
        $this->initOverrideEntries($poFile);
        $this->setEntry();
        $this->savePoFile();
        try {
            $this->analyzePoFile($poFile);
        } catch (\Anakeen\Exception $e) {
            // Restore original
            rename($backupFile, $poFile);
        }
    }

    public function deletePoFile($lang)
    {
        $this->lang = substr($lang, 0, 2);

        $poFile = $this->getOverrideFile();
        unlink($poFile);
    }

    protected function initPoFile($filePath)
    {
        $msgInit = sprintf(
            'msgid ""
msgstr ""
"Project-Id-Version: Override translations enum %s\n"
"Language: %s\n"
"PO-Revision-Date: %s\n"
"Language-Team: Override\n"
"MIME-Version: 1.0\n"
"Last-Translator: %s\n"
"MIME-Version: 1.0\n"
"Plural-Forms: nplurals=2; plural=(n > 1);\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"',
            $this->msgctxt,
            substr($this->lang, 0, 2),
            date('Y-m-d H:i:s'),
            ContextManager::getCurrentUser()->login
        );
        if (!file_put_contents($filePath, $msgInit)) {
            throw new Exception(sprintf("Cannot write \"%s\" po file", $filePath));
        }
    }

    protected function getOverrideFile()
    {
        $filePath = self::getOverrideFilepath($this->lang);
        if (!file_exists($filePath)) {
            if (!is_dir(dirname($filePath))) {
                if (!@mkdir(dirname($filePath))) {
                    throw new \Anakeen\Core\Exception(sprintf("cannot create dir [%s]", dirname($filePath)));
                }
            }
            $this->initPoFile($filePath);
        }
        return $filePath;
    }


    public function getOverrideFilepath($lang)
    {
        return sprintf(
            "%s/locale/%s/LC_MESSAGES/src/%s/enum%s.po",
            ContextManager::getRootDirectory(),
            $lang,
            self::OVERRIDE_DIR,
            $this->msgctxt
        );
    }


    protected function copyBackup()
    {
        $filePath = self::getOverrideFile();
        $backup = $filePath . uniqid(".bak");
        copy($filePath, $backup);
        return $backup;
    }

    protected function setEntry()
    {
        $this->msgstr = str_replace("\n", "\\n\n", $this->msgstr);

        // Update entry
        $entry = $this->catalog->getEntry($this->msgid, $this->msgctxt);
        $this->msgid = str_replace("\\n", "\\n\n", $this->msgid);
        if (!$entry) {
            $entry = new \Sepia\PoParser\Catalog\Entry($this->msgid, $this->msgstr);
            if ($this->msgctxt) {
                $entry->setMsgCtxt($this->msgctxt);
            }
            if (strpos($this->msgid, "%") !== false) {
                $entry->setFlags(["php-format"]);
            }
            if ($this->pluralid) {
                $entry->setMsgIdPlural($this->pluralid);
                $entry->setMsgStrPlurals($this->msgstr);
            }

            $this->catalog->addEntry($entry);
        } else {
            if ($entry->isFuzzy()) {
                $flags = $entry->getFlags();
                $flags = array_filter($flags, function ($flag) {
                    return $flag !== "fuzzy";
                });
                $entry->setFlags($flags);
            }

            if ($this->pluralid) {
                $entry->setMsgStrPlurals($this->msgstr ?: []);
            } else {
                $entry->setMsgStr($this->msgstr);
            }
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

    protected function analyzePoFile($poFile)
    {
        exec(sprintf("msgfmt --statistics -c -v -o /dev/null %s 2>&1", escapeshellarg($poFile)), $output, $return);

        if ($return !== 0) {
            foreach ($output as &$line) {
                $line = substr($line, strpos($line, ":"));
            }
            unlink($poFile);
            throw new \Anakeen\Core\Exception(implode("\n", $output));
        }
    }

    protected function reinitMainPo($backupFile)
    {
        $poFile = $this->getOverrideFile();
        $backupOrigin = $poFile . uniqid(".cbak");
        try {
            copy($backupFile, $poFile);
            $system = new \Anakeen\Script\System();
            $system->localeGen();
            unlink($backupFile);
        } catch (\Exception $e) {
            copy($backupOrigin, $poFile);
            unlink($backupOrigin);
            unlink($backupFile);
            $system = new \Anakeen\Script\System();
            $system->localeGen();

            throw new \Anakeen\Core\Exception("Po file error", 0, $e);
        }
    }
}

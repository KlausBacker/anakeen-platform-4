<?php

namespace Anakeen\Routes\Admin\I18n;

use Anakeen\Core\ContextManager;
use Anakeen\Exception;

class RecordComponentTranslation
{
    const OVERRIDE_FILE = "custom/1_override.json";
    protected $lang;

    public function __construct($lang)
    {
        $this->lang = $lang;
    }

    public function save($msgid, $msgstr)
    {
        $componentOverrideFile = $this->getComponentOverrideFile();
        $data = json_decode(file_get_contents($componentOverrideFile), true);
        $data[$msgid] = $msgstr;
        if (!file_put_contents($componentOverrideFile, json_encode($data, JSON_PRETTY_PRINT))) {
            throw new Exception(sprintf("Cannot update custom catalog \"%s\"", $componentOverrideFile));
        }
    }

    public static function getComponentOverrideFilepath($lang)
    {
        return $filePath = sprintf(
            "%s/locale/%s/vuejs/src/%s",
            ContextManager::getRootDirectory(),
            $lang,
            self::OVERRIDE_FILE
        );
    }

    protected function getComponentOverrideFile()
    {
        $filePath = self::getComponentOverrideFilepath($this->lang);
        if (!file_exists($filePath)) {
            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath));
            }
            $this->initJSonFile($filePath);
        }
        return $filePath;
    }

    protected function initJSonFile($path)
    {
        if (!file_put_contents($path, '{}')) {
            throw new Exception(sprintf("Cannot init custom catalog \"%s\"", $path));
        }
    }
}

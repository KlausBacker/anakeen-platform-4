<?php


namespace Anakeen\Routes\Admin\Enum;

use Anakeen\Core\DbManager;
use Anakeen\Core\EnumManager;
use Anakeen\Router\ApiV2Response;
use String\sprintf;

class EnumerateUpdate
{
    private $id = '';
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->parseParams($request, $args);
        return ApiV2Response::withData($response, $this->doRequest($request));
    }

    protected function doRequest(\Slim\Http\request $request)
    {
        $modifications = $request->getParsedBody()["data"];
        $enumName = $request->getParsedBody()["enumName"];

        foreach ($modifications as $newData) {
            $key = $newData["key"];
            $label = $newData["label"];
            $active = $newData["active"];
            $type = $newData["type"];
            switch ($type) {
                case "add":
                    $this->addEnum($key, $label, $active);
                    break;
                case "update":
                    $this->updateEnum($enumName, $key, $label, $active);
                    break;
            }
        }
    }

    private function parseParams(\Slim\Http\request $request, $args)
    {
        $this->id = $args["id"];
    }

    private function addEnum($key, $label, $active)
    {
        // "disabled" field in DB accept only one character
        $activeChar = $active === "enable" ? '' : 't';

        // If the entry is marked as disabled
        if ($activeChar === 't') {
            $queryPattern = <<<'SQL'
INSERT INTO docenum (name, key, label, disabled, eorder)
VALUES ('%s', '%s', '%s', '%s', (SELECT MAX(eorder)+1 from docenum WHERE name = 'dir-fld_allbut'))
SQL;
            $query = sprintf($queryPattern, $this->id, $key, $label, $activeChar);
        } else {
            $queryPattern = <<<'SQL'
INSERT INTO docenum (name, key, label, eorder)
VALUES ('%s', '%s', '%s', (SELECT MAX(eorder)+1 from docenum WHERE name = 'dir-fld_allbut'))
SQL;
            $query = sprintf($queryPattern, $this->id, $key, $label);
        }
        DbManager::query($query, $output);
    }

    private function updateEnum($enumName, $key, $label, $active)
    {
        $activeChar = $active === "enable" ? null : 't';

        if($activeChar === 't'){
            $queryPattern = <<<'SQL'
UPDATE docenum SET label = '%s', disabled = '%s' WHERE name = '%s' AND key = '%s'
SQL;
            $query = sprintf($queryPattern, $label, $activeChar, $enumName, $key);
        }
        else {
            $queryPattern = <<<'SQL'
UPDATE docenum SET label = '%s', disabled = null WHERE name = '%s' AND key = '%s'
SQL;
            $query = sprintf($queryPattern, $label, $enumName, $key);
        }
        DbManager::query($query, $output);
    }
}
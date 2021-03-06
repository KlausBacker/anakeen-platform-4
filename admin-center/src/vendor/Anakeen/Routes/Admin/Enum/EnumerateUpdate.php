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
        $this->deleteEnums();
        foreach ($modifications as $newData) {
            $this->addEnum($newData["key"], $newData["label"], $newData["active"], $newData["eorder"]);
        }
        return $modifications;
    }

    private function parseParams(\Slim\Http\request $request, $args)
    {
        $this->id = $args["id"];
    }

    private function addEnum($key, $label, $active, $eorder)
    {
        // "disabled" field in DB accept only one character
        $activeChar = $active === "enable" ? '' : 't';

        // If the entry is marked as disabled
        if ($activeChar === 't') {
            $queryPattern = <<<'SQL'
INSERT INTO docenum (name, key, label, disabled, eorder) VALUES ('%s', '%s', '%s', '%s', '%s')
SQL;
            $query = sprintf(
                $queryPattern,
                pg_escape_string($this->id),
                pg_escape_string($key),
                pg_escape_string($label),
                pg_escape_string($activeChar),
                pg_escape_string($eorder)
            );
        } else {
            $queryPattern = <<<'SQL'
INSERT INTO docenum (name, key, label, eorder) VALUES ('%s', '%s', '%s', '%s')
SQL;
            $query = sprintf(
                $queryPattern,
                pg_escape_string($this->id),
                pg_escape_string($key),
                pg_escape_string($label),
                pg_escape_string($eorder)
            );
        }
        DbManager::query($query, $output);
    }

    private function deleteEnums()
    {
        $queryPattern = <<<'SQL'
DELETE FROM docenum WHERE docenum.name = '%s'
SQL;
        $query = sprintf($queryPattern, pg_escape_string($this->id));
        DbManager::query($query, $output);
        // ToDo : Manage error /w output result
    }
}

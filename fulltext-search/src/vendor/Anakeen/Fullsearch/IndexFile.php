<?php


namespace Anakeen\Fullsearch;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\QueryDb;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Exception;
use Anakeen\TransformationEngine\Client;
use Anakeen\TransformationEngine\Manager;
use Anakeen\Vault\FileInfo;

class IndexFile
{
    public static function sendIndexRequest(
        SmartElement $se,
        string $domainName,
        SearchFileConfig $fielInfo,
        $index = -1
    ) {
        $te = new Client();

        if ($index >= 0) {
             $fileValues = $se->getMultipleRawValues($fielInfo->field);
            $fileValue=$fileValues[$index];
        } else {
            $fileValue = $se->getRawValue($fielInfo->field);
        }
        if ($fileValue) {
            /** @var FileInfo $fileInfo */
            $fileInfo = $se->getFileInfo($fileValue, "", "object");

            $callback = sprintf("/api/v2/fullsearch/domains/%s/smart-elements/%d", $domainName, $se->id);
            $callurl = Manager::getOpenTeUrl($callback);
            $te->sendTransformation("utf8", "", $fileInfo->path, $callurl, $info);

            self::recordTeRequest($info["tid"], $info["status"], $se->id, $fielInfo->field, $index);
        }
    }

    protected static function recordTeRequest($tid, $status, $seid, $fieldid, $index = -1)
    {
        $fileRecord = new FileContentDatabase();
        $fileRecord->taskid = $tid;
        $fileRecord->status = $status;
        $fileRecord->docid = $seid;
        $fileRecord->field = $fieldid;
        $fileRecord->index = $index;
        $err = $fileRecord->add();
        if ($err) {
            throw new Exception($err);
        }
    }

    /**
     * @return FileContentDatabase[]
     * @throws \Anakeen\Database\Exception
     */
    public static function getWaitingRequest()
    {
        $fileRecord = new QueryDb("", FileContentDatabase::class);
        $fileRecord->addQuery("status = 'W'");
        $results = $fileRecord->query();

        if ($fileRecord->nb === 0 ) {
            $results=[];
        }
        return $results;
    }

    public static function recordTeFileresult($taskid)
    {
        $record = new FileContentDatabase("", $taskid);
        if (!$record->isAffected()) {
            throw new Exception("FSEA0007", $taskid);
        }
        $ot = new \Anakeen\TransformationEngine\Client();
        $err = $ot->getInfo($taskid, $info);
        if ($err) {
            throw new Exception("FSEA0008",$err);
        }
        $filename = tempnam(ContextManager::getTmpDir(), 'txt-');
        $err=  $ot->getTransformation($taskid, $filename);

        if ($err) {
            throw new Exception($err);
        }
        $record->status=$info["status"];
        $record->textcontent=preg_replace('/\s+/', ' ',file_get_contents($filename));
        $err=$record->modify();
        if ($err) {
            throw new Exception("FSEA0009",$err);
        }
    }
}

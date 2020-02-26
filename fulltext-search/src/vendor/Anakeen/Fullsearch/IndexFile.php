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
        SearchFileConfig $fieldInfo,
        $index = -1
    ) {
        $te = new Client();

        if ($index >= 0) {
            $fileValues = $se->getMultipleRawValues($fieldInfo->field);
            $fileValue = $fileValues[$index];
        } else {
            $fileValue = $se->getRawValue($fieldInfo->field);
        }

        if ($fileValue) {
            /** @var FileInfo $fileInfo */
            $fileInfo = $se->getFileInfo($fileValue, "", "object");

            if ($fileInfo) {
                if (file_exists($fileInfo->path)) {
                    if (!FileContentDatabase::isUptodate($fileInfo)) {
                        $callback = sprintf("/api/v2/fullsearch/domains/%s/smart-elements/%d", $domainName, $se->id);
                        $callurl = Manager::getOpenTeUrl($callback);

                        //$mb=microtime(true);
                        $err = $te->sendTransformation("utf8", "", $fileInfo->path, $callurl, $info);

                        //  printf("\tSend %dms, %s\n",(microtime(true)-$mb) *1000, $fileInfo->name);

                        if ($err) {
                            self::recordTeError(
                                $fileInfo->id_file,
                                $err
                            );
                            throw new Exception("FSEA0010", $err);
                        }
                        self::recordTeRequest(
                            $info["tid"],
                            $info["status"],
                            $fileInfo->id_file
                        );

                        return true;
                    }
                } else {
                    self::recordTeError(
                        $fileInfo->id_file,
                        sprintf("file \"%s\ not found : \"%s\"", $fileInfo->name, $fileInfo->path)
                    );
                }
            } else {
                if (preg_match(PREGEXPFILE, $fileValue, $reg)) {
                    self::recordTeError(
                        $reg["vid"],
                        sprintf("file \"%s\ (#%s) not referenced in vault", $reg["name"], $reg["vid"])
                    );
                } else {
                    throw new Exception("FSEA0011", $fileValue, $fieldInfo->field, $se->id);
                }
            }
        }
        return false;
    }

    protected static function recordTeRequest($tid, $status, $fileid)
    {
        // Delete old file index
        // @TODO how remove deleted files ?
        FileContentDatabase::deleteFileIndex($fileid);
        $fileRecord = new FileContentDatabase();
        $fileRecord->taskid = $tid;
        $fileRecord->status = $status;
        $fileRecord->fileid = $fileid;
        $err = $fileRecord->add();
        if ($err) {
            throw new Exception($err);
        }
    }


    protected static function recordTeError($fileid, $err)
    {
        FileContentDatabase::deleteFileIndex($fileid);
        $fileRecord = new FileContentDatabase();
        $fileRecord->status = "K";
        $fileRecord->fileid = $fileid;
        $fileRecord->textcontent = $err;
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

        if ($fileRecord->nb === 0) {
            $results = [];
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
            throw new Exception("FSEA0008", $err);
        }

        $filename = tempnam(ContextManager::getTmpDir(), 'txt-');
        $err = $ot->getTransformation($taskid, $filename);

        if ($err) {
            throw new Exception($err);
        }
        $record->status = $info["status"];
        $record->textcontent = preg_replace('/\s+/', ' ', file_get_contents($filename));
        $err = $record->modify();
        if ($err) {
            throw new Exception("FSEA0009", $err);
        }
    }
}

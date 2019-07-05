<?php


namespace Control\Internal;


use Control\Exception\RuntimeException;

class Log
{

    protected static $data = [];

    public static function getLogData($maxData=0)
    {
        $jobDir = self::getLogDir();
        $files=array_reverse(glob($jobDir . "/*.json"));

        $jobData=ModuleJob::getJobData();
        if ($jobData && !empty($jobData["log"])) {
            self::$data=$jobData["log"];
        }

        var_dump(self::$data);
        foreach ($files as $filename) {
            if ($maxData <= 0 || count(self::$data) < $maxData) {
                self::recordData($filename);
            }
        }
        usort(self::$data, function ($a, $b) {
            return strcmp($a["date"], $b["date"]);
        });

        if ($maxData>0) {
            self::$data= array_slice(self::$data, -($maxData));
        }
        return self::$data;
    }

    protected static function recordData($jsonFile)
    {
        $log = json_decode(file_get_contents($jsonFile), true);
        self::$data = array_merge(self::$data, $log["log"]);
    }


    public static function copyFile($file, $copy = false)
    {
        $jobDir = self::getLogDir();
        $archiveName = sprintf("%s/job-%s.json", $jobDir, date('Ymd-His'));
        if ($copy === false) {
            rename($file, $archiveName);
        } else {
            copy($file, $archiveName);
        }
    }


    protected static function getLogDir()
    {
        $logdir = realpath(__DIR__ . "/../../..") . "/var";
        if (!is_dir($logdir)) {
            if (!mkdir($logdir)) {
                throw new RuntimeException(sprintf("Cannot create directory \"%s\".", $logdir));
            }
        }
        $logdir .= "/log";
        if (!is_dir($logdir)) {
            if (!mkdir($logdir)) {
                throw new RuntimeException(sprintf("Cannot create directory \"%s\".", $logdir));
            }
        }
        return $logdir;
    }
}
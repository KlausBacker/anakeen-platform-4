<?php

namespace Dcp;

use Anakeen\Core\DbManager;
use Anakeen\Exception;

class VaultManager
{
    protected static $vault = null;

    /**
     * @return \VaultFile
     */
    protected static function getVault()
    {
        if (self::$vault === null) {
            self::$vault = new \VaultFile("", "FREEDOM");
        }
        return self::$vault;
    }

    /**
     * return various informations for a file stored in VAULT
     * @param int    $idfile    vault file identifier
     * @param string $teng_name transformation engine name
     * @return \VaultFileInfo
     */
    public static function getFileInfo($idfile, $teng_name = "")
    {
        self::getVault()->show($idfile, $info, $teng_name);
        if (!$info) {
            return null;
        }
        return $info;
    }

    /**
     * return various informations for a file stored in VAULT
     * @param string $filepath
     * @param string $ftitle
     * @param bool   $public_access set to true to store uncontrolled files like icons
     * @return int
     * @throws Exception
     */
    public static function storeFile($filepath, $ftitle = "", $public_access = false)
    {
        $err = self::getVault()->store($filepath, $public_access, $vid);
        if ($err) {
            throw new Exception("VAULT0001", $filepath, $err);
        }
        if ($ftitle != "") {
            self::getVault()->rename($vid, $ftitle);
        }
        return $vid;
    }

    /**
     * return various informations for a file stored in VAULT
     * @param string $filepath
     * @param string $ftitle
     * @throws Exception
     * @return int return vault identifier
     */
    public static function storeTemporaryFile($filepath, $ftitle = "")
    {
        if (!\Anakeen\Router\AuthenticatorManager::$session || !\Anakeen\Router\AuthenticatorManager::$session->id) {
            throw new Exception("VAULT0003");
        }
        $err = self::getVault()->store($filepath, false, $vid, $fsname = '', $te = "", 0, $tmp = \Anakeen\Router\AuthenticatorManager::$session->id);

        if ($err) {
            throw new Exception("VAULT0002", $err);
        }
        if ($ftitle != "") {
            self::getVault()->rename($vid, $ftitle);
        }
        return $vid;
    }

    /**
     * Delete id_tmp propertty of identified files
     * @param array $vids vault identifiers list
     */
    public static function setFilesPersitent(array $vids)
    {
        if (count($vids) > 0) {
            $sql = sprintf("update vaultdiskstorage set id_tmp = null where id_tmp is not null and id_file in (%s)", implode(",", array_map(function ($x) {
                return intval($x);
            }, $vids)));

            DbManager::query($sql);
        }
    }

    /**
     * Destroy file from vault
     * The file is physicaly deleted
     * @param int $vid vault file identifier
     * @throws Exception
     */
    public static function destroyFile($vid)
    {
        $info = self::getFileInfo($vid);
        if ($info === null) {
            throw new Exception("VAULT0004", $vid);
        }

        self::getVault()->destroy($vid);
    }

    /**
     * Delete vault temporary files where create date is less than interval
     * @param int $dayInterval number of day
     */
    public static function destroyTmpFiles($dayInterval = 2)
    {
        $sql = sprintf("select id_file from vaultdiskstorage where id_tmp is not null and id_tmp != '' and cdate < (now() - INTERVAL '%d day');", $dayInterval);

        DbManager::query($sql, $result, true);
        foreach ($result as $vid) {
            self::destroyFile($vid);
        }
    }

    /**
     * Set access date to now
     * @param  int $idfile vault file identifier
     */
    public static function updateAccessDate($idfile)
    {
        self::getVault()->updateAccessDate($idfile);
    }

    /**
     * Recompute directory file size from database informations
     * @throws Db\Exception
     */
    public static function recomputeDirectorySize()
    {
        $sql = "update vaultdiskdirstorage set size=(select sum(size) from vaultdiskstorage where id_dir=vaultdiskdirstorage.id_dir) where isfull;";
        DbManager::query($sql);
        $sql = "update vaultdiskdirstorage set size=0 where isfull and size is null;";
        DbManager::query($sql);
    }
}

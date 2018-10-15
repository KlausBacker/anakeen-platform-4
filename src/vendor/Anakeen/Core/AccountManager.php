<?php

namespace Anakeen\Core;

class AccountManager
{
    static protected $sysIds=[];
    public static function getIdFromLogin(string $login): int
    {
        $sql=sprintf("select id from users where login='%s'", pg_escape_string($login));
        DbManager::query($sql, $idUser, true, true);
        if (!$idUser) {
            $idUser=0;
        }
        return $idUser;
    }


    /**
     * Return system account from login, null if login not found
     * @param string $login
     * @return Account|null
     * @throws \Dcp\Core\Exception
     */
    public static function getAccount(string $login) {
        $u= new Account("");
        $u->setLoginName($login);
        if ($u->isAffected()) {
            return $u;
        }
        return null;
    }
    /**
     * Return system id from SmartElement Id
     * @param int $fid
     * @return int
     * @throws \Dcp\Db\Exception
     */
    public static function getIdFromSEId(int $fid): int
    {
        if (isset(self::$sysIds[$fid])) {
            return self::$sysIds[$fid];
        }
        $sql=sprintf("select id from users where fid='%d'", $fid);
        DbManager::query($sql, $idUser, true, true);
        if (!$idUser) {
            $idUser=0;
        }
        self::$sysIds[$fid]=$idUser;
        return  self::$sysIds[$fid];
    }
}
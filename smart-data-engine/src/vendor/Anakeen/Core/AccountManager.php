<?php

namespace Anakeen\Core;

class AccountManager
{
    protected static $sysIds = [];

    public static function getIdFromLogin(string $login): int
    {
        $sql = sprintf("select id from users where login='%s'", pg_escape_string($login));
        DbManager::query($sql, $idUser, true, true);
        if (!$idUser) {
            $idUser = 0;
        }
        return $idUser;
    }


    /**
     * Return login from system id
     * @param int $systemId the system account id
     * @return string the login of the account
     * @throws \Anakeen\Database\Exception
     */
    public static function getLoginFromId(int $systemId): string
    {
        $sql = sprintf("select login from users where id=%d", $systemId);
        DbManager::query($sql, $login, true, true);
        if (!$login) {
            $login = 0;
        }
        return $login;
    }


    /**
     * Return system account from login, null if login not found
     * @param string $login
     * @return Account|null
     * @throws \Anakeen\Core\Exception
     */
    public static function getAccount(string $login)
    {
        $u = new Account("");
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
     * @throws \Anakeen\Database\Exception
     */
    public static function getIdFromSEId(int $fid): int
    {
        if (isset(self::$sysIds[$fid])) {
            return self::$sysIds[$fid];
        }
        $sql = sprintf("select id from users where fid='%d'", $fid);
        DbManager::query($sql, $idUser, true, true);
        if (!$idUser) {
            $idUser = 0;
        }
        self::$sysIds[$fid] = $idUser;
        return self::$sysIds[$fid];
    }

    /**
     * return simple information about current user
     * @param string $info "fid" (id of relative smart element), "id" (system id), "login"
     * @return string|null
     * @throws \Anakeen\Exception
     */
    public static function getCurrentUserInfo($info = "fid")
    {
        $u = ContextManager::getCurrentUser();
        if ($u) {
            $values = $u->getValues();
            return $values[$info] ?? null;
        }
        return null;
    }
}

<?php

namespace Anakeen\Core;

class AccountManager
{
    public static function getIdFromLogin(string $login): int
    {
        $sql=sprintf("select id from users where login='%s'", pg_escape_string($login));
        DbManager::query($sql, $idUser, true, true);
        if (!$idUser) {
            $idUser=0;
        }
        return $idUser;
    }
}
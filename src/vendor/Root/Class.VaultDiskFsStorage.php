<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * @author  Anakeen
 * @package FDL
 */
// ---------------------------------------------------------------
// $Id: Class.VaultDiskFsStorage.php,v 1.5 2006/12/06 11:12:13 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/Class/Class.VaultDiskFsStorage.php,v $
// ---------------------------------------------------------------
//
//
// ---------------------------------------------------------------

class VaultDiskFsStorage extends VaultDiskFs
{
    public function __construct($dbaccess = '', $id_fs = '')
    {
        $this->specific = "storage";
        parent::__construct($dbaccess, $id_fs);
    }
}

<?php


class VaultDiskDirStorage extends VaultDiskDir
{
    public function __construct($dbaccess = '', $id_dir = '')
    {
        parent::__construct($dbaccess, $id_dir, "storage");
    }
}

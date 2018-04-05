<?php


class VaultFileInfo
{
    /**
     * @var int vault identifier
     */
    public $id_file;
    /**
     * @var string file basename
     */
    public $name;
    /**
     * @var int file size in bytes
     */
    public $size;
    public $public_access;
    public $mime_t;
    /**
     * @var string system mime file
     */
    public $mime_s;
    /**
     * @var string creation date (YYYY-MM-DD HH:MM:SS)
     */
    public $cdate;
    /**
     * @var string modification date (YYYY-MM-DD HH:MM:SS)
     */
    public $mdate;
    /**
     * @var string last access date (YYYY-MM-DD HH:MM:SS)
     */
    public $adate;
    public $teng_state;
    public $teng_lname;
    public $teng_vid;
    public $teng_comment;
    /**
     * @var string complete path to file
     */
    public $path;
    public $id_tmp;
}

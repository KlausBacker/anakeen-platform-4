<?php
/**
 * Retrieve and store file in Vault for unix fs
 */

namespace Anakeen\Vault;

use \Anakeen\LogManager;

class DiskStorage extends \Anakeen\Core\Internal\DbObj
{
    public $fields
        = array(
            "id_file",
            "id_fs",
            "id_dir",
            "public_access",
            "size",
            "name",
            "id_tmp",

            "mime_t", // file mime type text
            "mime_s", // file mime type system
            "cdate", // creation date
            "mdate", // modification date
            "adate", // access date
            "teng_state", // Transformation Engine state
            "teng_lname", // Transformation Engine logical name (VIEW, THUMBNAIL, ....)
            "teng_id_file", // Transformation Engine source file id
            "teng_comment", // Comment for transformation

        );
    public $id_fields
        = array(
            "id_file"
        );
    public $dbtable = "vaultdiskstorage";
    public $sqlcreate = "create table vaultdiskstorage  ( 
                                     id_file          bigint not null, primary key (id_file),
                                     id_fs            int,
                                     id_dir           int,
                                     public_access    bool,
                                     size             int,
                                     name             text,
                                     id_tmp           text,
                                     mime_t           text DEFAULT '',
                                     mime_s           text DEFAULT '',

                                     cdate            timestamp DEFAULT null,
                                     mdate            timestamp DEFAULT null,
                                     adate            timestamp DEFAULT null,
 
                                     teng_state       int DEFAULT 0,
                                     teng_lname       text DEFAULT '',
                                     teng_id_file     bigint DEFAULT -1,
                                     teng_comment     text DEFAULT ''

                               );
           CREATE INDEX vault_iddir on vaultdiskstorage (id_dir);
           CREATE INDEX vault_teng on vaultdiskstorage (teng_state);
           CREATE INDEX vault_tengid on vaultdiskstorage (teng_id_file);";
    public $id_file;
    public $id_fs;
    public $id_dir;
    public $name;
    /**
     * Indicate if file is a temporary file : set to session user id
     *
     * @var string
     */
    public $id_tmp;
    public $size;
    public $public_access;
    public $mime_t;
    public $mime_s;
    public $cdate;
    public $mdate;
    public $adate;
    public $teng_state;
    public $teng_lname;
    public $teng_id_file;
    public $teng_comment;

    public $storage = 1;
    /**
     * @var \Anakeen\Vault\DiskFsStorage
     */
    public $fs;

    // --------------------------------------------------------------------
    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        parent::__construct($dbaccess, $id, $res, $dbid);
        $this->fs = new \Anakeen\Vault\DiskFsStorage($this->dbaccess);
    }

    /**
     * set fs object
     */
    public function complete()
    {
        if (!$this->fs) {
            $this->fs = new \Anakeen\Vault\DiskFsStorage($this->dbaccess);
        }
        $this->fs->select($this->id_fs);
    }

    public function preInsert()
    {
        $this->id_file = $this->getNewVaultId();
        return '';
    }

    /**
     * Get a new cryptographically random id for vault identifier
     *
     * Throws an exception:
     * - if no cryptographically random bytes could be obtained from openssl:
     *   this might occurs on broken or old system.
     * - if architecture int size is not supported.
     *
     * @return int The new id (bigint)
     * @throws \Anakeen\Exception
     */
    public function getNewVaultId()
    {
        $newId = '';
        while (empty($newId)) {
            $bytes = openssl_random_pseudo_bytes(PHP_INT_SIZE);
            if ($bytes === false) {
                throw new \Anakeen\Exception(sprintf("Unable to get cryptographically strong random bytes from openssl: your system might be broken or too old."));
            }
            /*
             * We are going to perform a bitmask operation, so we should ensure
             * that the correct number of requested bytes have been returned.
            */
            if (strlen($bytes) !== PHP_INT_SIZE) {
                throw new \Anakeen\Exception(sprintf("Unable to get cryptographically strong random bytes from openssl: your system might be broken or too old."));
            }
            /*
             * Set leftmost bit to 0 to prevent having negative values
            */
            if (PHP_INT_SIZE == 4) {
                $bytes = ($bytes & "\x7f\xff\xff\xff");
                $int = unpack("Nint4", $bytes);
                $int = $int['int4'];
            } elseif (PHP_INT_SIZE == 8) {
                $bytes = ($bytes & "\x7f\xff\xff\xff" . "\xff\xff\xff\xff");
                /*
                 * "J" format is only supported on PHP >= 5.6.2, so we need to
                 * manually unpack 2 int4 (with format "N") and reconstruct the
                 * final int8.
                */
                $upper_int = unpack("Nint4", substr($bytes, 0, 4));
                $lower_int = unpack("Nint4", substr($bytes, 4, 4));
                $int = ($upper_int['int4'] << 32) + $lower_int['int4'];
            } else {
                throw new \Anakeen\Exception(sprintf("Unsupported PHP_INT_SIZE '%d'.", PHP_INT_SIZE));
            }
            /*
             * If the integer is negative, then something is wrong
             * with this code...
            */
            if ($int < 0) {
                throw new \Anakeen\Exception(sprintf("Unexpected negative integer value with PHP_INT_SIZE '%d' and binary data '0x%s'.", PHP_INT_SIZE, bin2hex($bytes)));
            }
            /*
             * Check if this id is already in use
            */
            $sql = <<<'SQL'
SELECT id_file FROM %s WHERE id_file = %d LIMIT 1
SQL;

            $err = $this->query(sprintf($sql, pg_escape_identifier($this->dbtable), $int));
            if ($err) {
                throw new \Anakeen\Database\Exception("DB0104", $err);
            }
            if ($this->numrows() === 0) {
                /*
                 * The id is not already in use, so we can use it
                */
                $newId = $int;
            }
        }
        return $newId;
    }

    // --------------------------------------------------------------------
    public function listFiles(&$list)
    {
        // --------------------------------------------------------------------
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $list = $query->Query(0, 0, "TABLE");
        $fc = $query->nb;
        return $fc;
    }

    public function seemsUtf8($Str)
    {
        return preg_match('!!u', $Str);
    }

    /**
     * Add new file in VAULT
     *
     * @param string $infile        complete server path of file to store
     * @param bool   $public_access set true if can be access without any permission
     * @param int &  $idf           new file identifier
     * @param string $fsname        name of the VAULT to store (can be empty=>store in one of available VAULT)
     * @param string $te_lname      transformation engine name
     * @param int    $te_id_file    transformation engine file result identifier
     *
     * @return string error message (empty if OK)
     */
    public function store($infile, $public_access, &$idf, $fsname = "", $te_lname = "", $te_id_file = 0)
    {
        if (!is_file($infile)) {
            return \ErrorCode::getError('FILE0007', $infile);
        }
        if (!is_readable($infile)) {
            return \ErrorCode::getError('FILE0008', $infile);
        }
        $this->size = filesize($infile);
        $msg = $this->fs->setFreeFs($this->size, $id_fs, $id_dir, $f_path, $fsname);
        if ($msg != '') {
            LogManager::error("Can't find free entry in vault. [reason $msg]");
            return ($msg);
        }
        $this->id_fs = $id_fs;
        $this->id_dir = $id_dir;
        // printf("\nDIR:%s\n", $id_dir);
        $this->public_access = $public_access;
        $this->name = self::myBasename($infile);
        if (!$this->seemsUtf8($this->name)) {
            $this->name = utf8_encode($this->name);
        }

        $this->mime_t = \Anakeen\Core\Utils\FileMime::getTextMimeFile($infile);
        $this->mime_s = \Anakeen\Core\Utils\FileMime::getSysMimeFile($infile, $this->name);
        $this->cdate = $this->mdate = $this->adate = date("c", time());

        $this->teng_state = '';
        $this->teng_lname = $te_lname;
        $this->teng_id_file = $te_id_file;

        $msg = $this->add();
        if ($msg != '') {
            return ($msg);
        }

        $this->fs->closeCurrentDir();
        $idf = $this->id_file;

        $f = self::vaultfilename($f_path, $infile, $this->id_file);
        if (!@copy($infile, $f)) {
            // Free entry
            LogManager::error(sprintf(_("Failed to copy %s to %s"), $infile, $f));
            return (sprintf(_("Failed to copy %s to vault"), $infile));
        }

        LogManager::debug("File $infile stored in $f");
        return "";
    }

    protected static function vaultfilename($fspath, $name, $id)
    {
        return str_replace('//', '/', $fspath . "/" . $id . "." . \Anakeen\Core\Utils\FileMime::getFileExtension($name));
    }

    /**
     * Get the \Anakeen\Vault\DiskStorage transforming object corresponding to the current object
     *
     * @param string                       $te_name transformation engine name
     * @param  \Anakeen\Vault\DiskStorage &$ngf     returned object
     *
     * @return string error message (empty if OK)
     *
     * @deprecated no usage
     * @throws \Anakeen\Database\Exception
     */
    public function getEngineObject($te_name, &$ngf)
    {
        if (!$this->isAffected()) {
            return _("vault file is not initialized");
        }
        $err = '';
        $q = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $q->AddQuery("teng_id_file=" . $this->id_file);
        $q->AddQuery("teng_lname='" . pg_escape_string($te_name) . "'");
        $tn = $q->Query();
        if ($q->nb === 0) {
            $ngf = new \Anakeen\Vault\DiskStorage($this->dbaccess);
            $ngf->teng_id_file = $this->id_file;
            $ngf->teng_lname = $te_name;
            $size = 1;
            $ngf->fs->setFreeFs($size, $id_fs, $id_dir, $f_path, $fsname = '');
            $ngf->cdate = $ngf->mdate = $ngf->adate = date("c", time());
            $ngf->id_fs = $id_fs;
            $ngf->id_dir = $id_dir;
            $ngf->size = 0;
            $err = $ngf->add();
            if ($err) {
                return $err;
            }
        } else {
            $ngf = $tn[0];
        }
        return $err;
    }

    /**
     * @param int                    $id_file    vault file identifier
     * @param \Anakeen\Vault\FileInfo $f_infos
     * @param string                 $teng_lname engine name
     *
     * @return string
     */
    public function show($id_file, &$f_infos, $teng_lname = "")
    {
        // --------------------------------------------------------------------
        $this->id_file = -1;
        if ($teng_lname != "") {
            $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
            $query->AddQuery(sprintf("teng_id_file = E'%s'::bigint", pg_escape_string($id_file)));
            $query->AddQuery(sprintf("teng_lname = E'%s'", pg_escape_string($teng_lname)));

            $t = $query->Query(0, 0, "TABLE");

            if ($query->nb > 0) {
                $this->select($t[0]["id_file"]);
            }
        }

        if (($this->id_file == -1) && ($teng_lname == "")) {
            $this->select($id_file);
        }

        if ($this->id_file != -1) {
            $this->fs->show($this->id_fs, $this->id_dir, $f_path);
            $f_infos = new \Anakeen\Vault\FileInfo();
            $f_infos->id_file = $this->id_file;
            $f_infos->name = $this->name;
            $f_infos->size = $this->size;
            $f_infos->public_access = $this->public_access;
            $f_infos->mime_t = $this->mime_t;
            $f_infos->mime_s = $this->mime_s;
            $f_infos->cdate = $this->cdate;
            $f_infos->mdate = $this->mdate;
            $f_infos->adate = $this->adate;
            $f_infos->id_tmp = $this->id_tmp;
            $f_infos->teng_state = $this->teng_state;
            $f_infos->teng_lname = $this->teng_lname;
            $f_infos->teng_vid = $this->teng_id_file;
            $f_infos->teng_comment = $this->teng_comment;
            $f_infos->path = self::vaultfilename($f_path, $this->name, $this->id_file);

            return '';
        } else {
            return (_("file does not exist in vault"));
        }
    }

    public function updateAccessDate($id_file)
    {
        $err = '';
        if ($this->id_file != $id_file) {
            $this->select($id_file);
        }
        if ($this->isAffected()) {
            $this->adate = date("c", time());
            $err = $this->modify(true, array(
                "adate"
            ), true);
        }
        return $err;
    }

    /**
     * return the complete path in file system
     *
     * @return string the path
     */
    public function getPath()
    {
        $this->fs->show($this->id_fs, $this->id_dir, $f_path);
        return self::vaultfilename($f_path, $this->name, $this->id_file);
    }

    // --------------------------------------------------------------------
    public function destroy($id)
    {
        // --------------------------------------------------------------------
        $msg = $this->show($id, $inf);
        if ($msg == '') {
            @unlink($inf->path);
            $msg = $this->fs->delEntry($this->id_fs, $this->id_dir);
            $this->Delete();
        }

        return $msg;
    }

    // --------------------------------------------------------------------
    public function save($infile, $public_access, $idf)
    {
        $err = '';
        $vf = new \Anakeen\Vault\VaultFile($this->dbaccess);
        if ($vf->show($idf, $info) == "") {
            /**
             * @var \Anakeen\Vault\FileInfo $info
             */
            $path = str_replace("//", "/", $info->path);

            $this->size = filesize($infile);
            // Verifier s'il y a assez de places ???
            $this->public_access = $public_access;

            $this->mdate = date("c", time());

            $msg = $this->modify();
            if ($msg != '') {
                return ($msg);
            }

            if (!copy($infile, $path)) {
                $err = sprintf(_("Cannot copy file %s to %s"), $infile, $path);
            } else {
                $this->fs->select($this->id_fs);
                LogManager::debug("File $infile saved in $path");

                $this->resetTEFiles();
            }
        } else {
            $err = sprintf("cannot save file : invalid vault descriptor %s", $idf);
        }
        return $err;
    }


    protected static function myBasename($p)
    {
        //return basename($p);
        $r = strrpos($p, "/");
        return ($r !== false) ? substr($p, $r + 1) : $p;
    }

    /**
     * reset all files product by transform engine
     */
    public function resetTEFiles()
    {
        if (\Anakeen\Core\Internal\Autoloader::classExists('Anakeen\TransformationEngine\Client')) {
            $sql = <<<SQL
UPDATE %s SET teng_state = %d WHERE teng_id_file = %s
SQL;

            $up = sprintf(
                $sql,
                pg_escape_identifier($this->dbtable),
                pg_escape_literal(\Anakeen\TransformationEngine\Client::status_inprogress),
                pg_escape_literal($this->id_file)
            );
            $this->query($up);
        }
    }
}

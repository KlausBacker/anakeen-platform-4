<?php
/**
 * Directories for vault files
 */

namespace Anakeen\Vault;

define("VAULT_MAXENTRIESBYDIR", 1000);
define("VAULT_MAXDIRBYDIR", 100);

class DiskDirStorage extends \Anakeen\Core\Internal\DbObj
{
    public $fields = array(
        "id_dir",
        "id_fs",
        "isfull",
        "size",
        "l_path"
    );
    public $id_fields = array(
        "id_dir"
    );
    public $id_dir;
    public $id_fs;
    public $l_path;

    public $dbtable = "vaultdiskdirstorage";
    public $order_by = "";
    public $seq = "seq_id_vaultdiskdirstorage";
    public $sqlcreate = <<<'SQL'

           create table vaultdiskdirstorage  ( id_dir     int not null,
                                 primary key (id_dir),
				 id_fs    int,
				 isfull   bool,
				 size   bigint,
                                 l_path varchar(2048)
                               );
           create sequence seq_id_vaultdiskdirstorage start 10;
           CREATE INDEX vault_isfullstorage on vaultdiskdirstorage (isfull);
SQL;

    public $specific;
    public $isfull;
    public $size;
    protected $dirsToClose = [];


    /**
     * return name of next directory
     * 1/1 => 1/2
     * 1/10 => 2/1
     * 1/2  = 1/3
     *
     * @param string $d path  to file
     * @param int $max
     *
     * @return string
     */
    public function nextdir($d, $max = VAULT_MAXDIRBYDIR)
    {
        $td = explode('/', $d);
        $dend = intval(end($td));
        $ak = array_keys($td);
        $lastkey = end($ak);
        if ($dend < $max) {
            $td[$lastkey]++;
        } else {
            $good = false;
            $key = $lastkey;
            while (($key >= 0) && (!$good)) {
                $prev = intval(prev($td));
                $td[$key] = 1;
                $key--;
                if ($prev) {
                    if ($prev < $max) {
                        $td[$key]++;
                        $good = true;
                    }
                }
            }
            if (!$good) {
                $td = array_fill(0, count($td) + 1, 1);
            }
        }
        return implode('/', $td);
    }

    public function complete()
    {
        $this->isfull = ($this->isfull === 't');
    }


    public function setFreeDir($fs)
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $id_fs = $fs["id_fs"];

        // Lock directory : force each process to use its proper dir
        $sql = sprintf(
            "select * from %s where id_fs=%d and (not isfull or isfull is null) and pg_try_advisory_xact_lock(id_dir, %d) order by id_fs limit 1 for update;",
            pg_escape_identifier($this->dbtable),
            $id_fs,
            unpack("i", "VLCK") [1]
        );

        $err = "";
        $dirs = $query->Query(0, 0, "TABLE", $sql);

        $this->dirsToClose = [];
        if ($query->nb > 0) {
            $needNewOneDir = true;
            foreach ($dirs as $dir) {
                $this->select($dir["id_dir"]);

                $sql = sprintf("SELECT count(*) FROM vaultdiskstorage WHERE id_dir=%d", $this->id_dir);
                $t = $query->Query(0, 0, "TABLE", $sql);

                $count = intval($t[0]["count"]);
                if ($count >= (VAULT_MAXENTRIESBYDIR - 1)) {
                    $this->dirsToClose[] = $this->id_dir;
                    if ($count < VAULT_MAXENTRIESBYDIR) {
                        $needNewOneDir = false;
                        break;
                    }
                } else {
                    $needNewOneDir = false;
                    break;
                }
            }
            if ($needNewOneDir) {
                $err = $this->createDirectory($fs);
            }
        } else {
            $err = $this->createDirectory($fs);
        }
        return $err;
    }

    public function closeDir()
    {
        $err = '';
        foreach ($this->dirsToClose as $dirid) {
            if ($dirid) {
                $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
                $sql = sprintf("SELECT sum(size) FROM vaultdiskstorage WHERE id_dir=%d", $dirid);
                $t = $query->Query(0, 0, "TABLE", $sql);
                if ($query->nb > 0) {
                    $this->select($dirid);
                    $this->isfull = 't';
                    $this->size = $t[0]["sum"];
                    $err .= $this->modify();
                }
            }
        }
        $this->dirsToClose = [];
        return $err;
    }

    protected function createDirectory($fs)
    {
        $id_fs = $fs["id_fs"];
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $t = $query->Query(
            0,
            0,
            "TABLE",
            "SELECT * from vaultdiskdirstorage where id_fs=" . intval($id_fs) . " order by id_dir desc limit 1"
        );
        if ($t) {
            $lpath = $t[0]["l_path"];
        } else {
            $lpath = "";
        }
        $npath = $this->nextdir($lpath);
        $rpath = $fs["r_path"];

        $absDir = sprintf("%s/%s", $rpath, $npath);

        while (is_dir($absDir)) {
            $npath = $this->nextdir($npath);
            $absDir = sprintf("%s/%s", $rpath, $npath);
        }

        $this->id_dir = "";
        $this->id_fs = $id_fs;
        $this->l_path = $npath;
        $this->isfull = 'f';
        $this->size = null;
        $err = $this->add();
        if ($err == "") {
            $dirpath = $rpath . "/" . $npath;
            if (!is_dir($dirpath)) {
                mkdir($dirpath, \Anakeen\Vault\VaultFile::VAULT_DMODE, true);
            }
        } else {
            error_log("Vault dirs full");
            return sprintf(_("cannot extend vault: %s"), $err);
        }
        return $err;
    }


    public function preInsert()
    {
        if ($this->Exists($this->l_path, $this->id_fs)) {
            return (_("Directory already exists"));
        }
        $this->query("select nextval ('" . $this->seq . "')");
        $arr = $this->fetchArray(0);
        $this->id_dir = $arr["nextval"];
        return '';
    }


    public function exists($path, $id_fs)
    {

        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $query->basic_elem->sup_where = array(
            "l_path='" . $path . "'",
            "id_fs=" . $id_fs
        );
        $query->Query(0, 0, "TABLE");
        return ($query->nb > 0);
    }


    public function delEntry()
    {
        if ($this->isfull) {
            $this->isfull = false;
            $this->Modify();
        }
    }
}

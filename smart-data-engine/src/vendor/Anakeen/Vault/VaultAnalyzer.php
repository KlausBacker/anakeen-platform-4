<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Vault;

use Anakeen\Core\DbManager;
use Anakeen\Core\Utils\PgFetchArrayIterator;

class VaultAnalyzer
{
    const STMT_DELETE_ID_FILE = 'delete_id_file';
    const STMT_INSERT_TMP = 'insert_tmp';

    protected static $savePointSeq = 0;
    protected $verbose = true;

    protected $_conn = null;

    public function __construct()
    {
        $this->sqlConnect();
    }

    /**
     * @param boolean $verbose
     */
    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;
    }

    public function summary()
    {
        $report = array();

        $res = $this->sqlQuery('SELECT count(id_file) AS count, sum(size) AS size, pg_size_pretty(sum(size)) AS size_pretty FROM vaultdiskstorage');
        $t = pg_fetch_array($res, null, PGSQL_ASSOC);
        if ($t === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        $report['all'] = $t;

        $q = <<<'SQL'
SELECT count(id_file) AS count, sum(size) AS size, pg_size_pretty(sum(size)) AS size_pretty 
FROM vaultdiskstorage 
WHERE NOT EXISTS (SELECT 1 FROM docvaultindex WHERE vaultid = id_file)
SQL;
        $res = $this->sqlQuery($q);

        $t = pg_fetch_array($res, null, PGSQL_ASSOC);
        if ($t === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        $report['orphan'] = $t;

        $q = <<<'SQL'
SELECT count(id_file) AS count, sum(size) AS size, pg_size_pretty(sum(size)) AS size_pretty 
FROM vaultdiskstorage WHERE EXISTS (SELECT 1 FROM docvaultindex WHERE vaultid = id_file)
SQL;
        $res = $this->sqlQuery($q);
        $t = pg_fetch_array($res, null, PGSQL_ASSOC);
        if ($t === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        $report['used'] = $t;

        return $report;
    }

    public function analyzePhysicalFiles()
    {
        $report = array();

        $query = <<<'SQL'
WITH files AS (
SELECT id_file, vdfs.r_path AS vault_root, l_path || '/' || vds.id_file ||
	CASE WHEN name ~ E'\\.[^.]+$' THEN regexp_replace(name, E'.*(\\.[^.]+)$', E'\\1')
		ELSE '.nop'
	END AS filename
FROM vaultdiskfsstorage AS vdfs, vaultdiskdirstorage AS vdds, vaultdiskstorage AS vds
WHERE
	vds.id_fs = vdfs.id_fs
	AND vds.id_dir = vdds.id_dir
	AND vdds.id_fs = vds.id_fs
)
SELECT files.*, dvi.* FROM files LEFT OUTER JOIN docvaultindex AS dvi ON vaultid = id_file
ORDER BY id_file, docid
;
SQL;

        $res = $this->sqlQuery($query);
        $count = pg_num_rows($res);
        if ($count <= -1) {
            throw new VaultAnalyzerException("Invalid result count '%s'.", $count);
        }

        $report['count'] = $count;
        $report['iterator'] = new PgFetchArrayIterator($res);

        return $report;
    }

    protected function getFamilies()
    {
        $set = array();
        $s = new \Anakeen\Search\Internal\SearchSmartData('', -1);
        $s->setObjectReturn(true);
        $s->setOrder('id');
        /**
         * @var \Anakeen\Core\SmartStructure [] $iterator
         */
        $iterator = $this->searchErrors($s);
        foreach ($iterator as $fam) {
            /* Fetch file or image attributes */
            $attrList = array();
            foreach ($fam->getAttributes() as $attr) {
                if (!$attr || ($attr->type != 'file' && $attr->type != 'image' && $attr->type != 'htmltext')) {
                    continue;
                }
                if ($attr->usefor != 'Q') {
                    $attrList[] = $attr->id;
                }
            }
            $set[$fam->id] = array(
                'id' => $fam->id,
                'name' => $fam->name,
                'title' => $fam->title,
                'file_attributes' => $attrList,
                'vid' => \Anakeen\Core\Utils\VidExtractor::getVidsFromDocFam($fam)
            );
        }
        return $set;
    }

    protected function searchErrors(\Anakeen\Search\Internal\SearchSmartData $searchDoc)
    {
        $searchDoc->search();
        if (($err = $searchDoc->searchError()) !== '') {
            throw new \Anakeen\Search\Exception($err);
        }
        return $searchDoc->getDocumentList();
    }

    public function checkDocVaultIndex(&$report)
    {
        return $this->_regenerateDocVaultIndex(true, $report);
    }

    public function regenerateDocVaultIndex(&$report)
    {
        return $this->_regenerateDocVaultIndex(false, $report);
    }

    protected function _regenerateDocVaultIndex($check, &$report)
    {
        $mode = ($check ? 'Checking' : 'Re-indexing');

        $point = $this->newPoint();
        $this->sqlSavePoint($point);

        $this->verbose("[+] Locking tables...\n");
        $this->sqlQuery("LOCK TABLE docvaultindex, doc * IN ACCESS EXCLUSIVE MODE");
        $this->sqlQuery("CREATE TEMPORARY TABLE tmp_docvaultindex (LIKE docvaultindex) ON COMMIT DROP");
        $this->sqlQuery("CREATE TEMPORARY TABLE tmp2_docvaultindex (LIKE docvaultindex) ON COMMIT DROP");
        $this->sqlPrepare(self::STMT_INSERT_TMP, "INSERT INTO tmp_docvaultindex(docid, vaultid) VALUES ($1, $2)");
        $this->verbose("[+] Done.\n");

        $this->verbose("[+] Analyzing dead entries in docvaultindex...\n");
        $this->sqlQuery("DELETE FROM docvaultindex WHERE NOT EXISTS (SELECT 1 FROM doc WHERE id = docid)");
        $this->verbose("|+] Done.\n");

        $this->verbose("[+] Analyzing families...\n");
        $families = $this->getFamilies();
        $this->verbose("[+] Done.\n");

        $famIndex = 0;
        foreach ($families as $famid => & $fam) {
            $famIndex++;
            $this->verbose("[+] (%d/%d) %s family '%s'...\n", $famIndex, count($families), $mode, $fam['name']);

            foreach ($fam['vid'] as $vid) {
                $this->sqlExec(self::STMT_INSERT_TMP, array(
                    $famid,
                    $vid
                ));
            }

            $this->verbose("[+] Done.\n");

            $relname = sprintf("doc%d", $famid);
            $res = $this->sqlQuery(sprintf("SELECT count(id) FROM ONLY %s", pg_escape_identifier($relname)));
            $row = pg_fetch_row($res, 0);
            $count = $row[0];
            unset($res);
            $addColumns = '';
            if (count($fam['file_attributes']) > 0) {
                $addColumns = array();
                foreach ($fam['file_attributes'] as $attrid) {
                    $addColumns[] = pg_escape_identifier($attrid);
                }
                $addColumns = ", " . join(", ", $addColumns);
            }
            if ($count <= 0) {
                continue;
            }
            $this->verbose("[+] %s '%d' documents from family '%s'...\n", $mode, $count, $fam['name']);
            $pom = null;
            if ($this->verbose) {
                $pom = (new ConsoleProgressOMeter())->setMax($count)->setInterval(1000)->start();
            }
            $rows = new PgFetchArrayIterator($this->sqlQuery(sprintf("SELECT id, icon %s FROM ONLY %s", $addColumns, pg_escape_identifier($relname))));
            $rowIndex = 0;
            foreach ($rows as $row) {
                $rowIndex++;
                $vidList = \Anakeen\Core\Utils\VidExtractor::getVidsFromRawDoc($row, $fam['file_attributes']);
                foreach ($vidList as $vid) {
                    $this->sqlExec(self::STMT_INSERT_TMP, array(
                        $row['id'],
                        $vid
                    ));
                }
                if ($pom) {
                    $pom->progress($rowIndex);
                }
            }
            if ($pom) {
                $pom->finish();
            }
            $this->verbose("[+] Done.\n");
        }
        unset($fam);

        $this->verbose("\n");

        /* De-duplicate entries */
        $this->sqlQuery("INSERT INTO tmp2_docvaultindex (docid, vaultid) SELECT DISTINCT ON (docid, vaultid) docid, vaultid FROM tmp_docvaultindex");
        /* Copy de-duplicated entries to tmp_docvaultindex */
        $this->sqlQuery("DROP TABLE tmp_docvaultindex");
        $this->sqlQuery("ALTER TABLE tmp2_docvaultindex RENAME TO tmp_docvaultindex");


        /* New */
        $q=<<< 'SQL'
SELECT * FROM tmp_docvaultindex AS d1 WHERE NOT EXISTS (SELECT 1 FROM docvaultindex AS d2 WHERE d2.docid = d1.docid AND d2.vaultid = d1.vaultid) ORDER BY docid, vaultid
SQL;

        $res = $this->sqlQuery($q);
        $new = array(
            'count' => pg_num_rows($res),
            'iterator' => new PgFetchArrayIterator($res)
        );
        /* Missing */
        $q=<<< 'SQL'
SELECT * FROM docvaultindex AS d1 WHERE NOT EXISTS (SELECT 1 FROM tmp_docvaultindex AS d2 WHERE d2.docid = d1.docid AND d2.vaultid = d1.vaultid) ORDER BY docid, vaultid
SQL;
        $res
            = $this->sqlQuery($q);

        $missing = array(
            'count' => pg_num_rows($res),
            'iterator' => new PgFetchArrayIterator($res)
        );

        $report = array(
            'new' => $new,
            'missing' => $missing
        );

        if ($check) {
            $this->sqlRollbackPoint($point);
            return ($report['new']['count'] === 0 && $report['missing']['count'] === 0);
        } else {
            /* Reset content of docvaultindex with new content from tmp_docvaultindex */
            $this->verbose("[+] Committing docvaultindex...\n");
            $this->sqlQuery("DELETE FROM docvaultindex");
            $this->sqlQuery("INSERT INTO docvaultindex (docid, vaultid) SELECT docid, vaultid FROM tmp_docvaultindex");
            $this->sqlQuery("DROP TABLE tmp_docvaultindex");
            $this->sqlCommitPoint($point);
            $this->verbose("[+] Done.\n");
        }
        return true;
    }

    protected function verbose($format)
    {
        if ($this->verbose) {
            call_user_func_array("printf", func_get_args());
        }
    }

    public function cleanDocVaultIndex()
    {
        $report = array();

        $res = $this->sqlQuery("DELETE FROM docvaultindex WHERE NOT EXISTS (SELECT 1 FROM doc WHERE id = docid) RETURNING *");
        $count = pg_num_rows($res);

        $report['count'] = $count;
        $report['iterator'] = new PgFetchArrayIterator($res);

        return $report;
    }

    public function deleteIdFile($vid)
    {
        $report = array();

        $res = $this->sqlExec(self::STMT_DELETE_ID_FILE, array(
            $vid
        ));
        $count = pg_num_rows($res);

        $report['count'] = $count;
        $report['iterator'] = new PgFetchArrayIterator($res);

        return $report;
    }

    public function analyzeOrphans()
    {
        $report = array();

        $query
            = <<<'EOF'
SELECT
    id_file, vdfs.r_path AS vault_root, l_path || '/' || vds.id_file ||
	    CASE WHEN name ~ E'\\.[^.]+$' THEN regexp_replace(name, E'.*(\\.[^.]+)$', E'\\1')
		    ELSE '.nop'
		END AS filename
	FROM vaultdiskfsstorage AS vdfs, vaultdiskdirstorage AS vdds, vaultdiskstorage AS vds
	WHERE
	    vds.id_fs = vdfs.id_fs
	    AND vds.id_dir = vdds.id_dir
	    AND vdds.id_fs = vds.id_fs
	    AND NOT EXISTS (SELECT 1 FROM docvaultindex WHERE vaultid = id_file)
;
EOF;

        $res = $this->sqlQuery($query);
        $count = pg_num_rows($res);
        if ($count <= -1) {
            throw new VaultAnalyzerException("Invalid result count '%s'.", $count);
        }

        $report['count'] = $count;
        $report['iterator'] = new PgFetchArrayIterator($res);

        return $report;
    }

    protected function sqlDisconnect()
    {
        pg_close($this->_conn);
    }

    protected function sqlConnect()
    {
        $this->_conn = \Anakeen\Core\DbManager::getDbId();

        $this->sqlPrepare(self::STMT_DELETE_ID_FILE, 'DELETE FROM vaultdiskstorage WHERE id_file = $1 RETURNING *');
    }

    protected function sqlSavePoint($point)
    {
        try {
            DbManager::savePoint($point);
        } catch (\Anakeen\Database\Exception $e) {
            throw new VaultAnalyzerException(sprintf("Error in %s: %s", __METHOD__, $e->getMessage()));
        }
    }

    protected function sqlCommitPoint($point)
    {
        try {
            DbManager::commitPoint($point);
        } catch (\Anakeen\Database\Exception $e) {
            throw new VaultAnalyzerException(sprintf("Error in %s: %s", __METHOD__, $e->getMessage()));
        }
    }

    protected function sqlRollbackPoint($point)
    {
        try {
            DbManager::rollbackPoint($point);
        } catch (\Anakeen\Database\Exception $e) {
            throw new VaultAnalyzerException(sprintf("Error in %s: %s", __METHOD__, $e->getMessage()));
        }
    }

    protected function newPoint()
    {
        return sprintf("%s:%d", __CLASS__, self::$savePointSeq++);
    }

    protected function sqlQuery($query)
    {
        $res = pg_query($this->_conn, $query);
        if ($res === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        return $res;
    }

    protected function sqlCount($query)
    {
        $res = pg_query($this->_conn, $query);
        if ($res === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        $row = pg_fetch_row($res);
        if ($row === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        return $row[0];
    }

    protected function sqlExec($stmt, $argv)
    {
        $res = pg_execute($this->_conn, $stmt, $argv);
        if ($res === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        return $res;
    }

    protected function sqlPrepare($stmt, $query)
    {
        static $first=[];
        if (empty($first[$stmt])) {
            $res = pg_prepare($this->_conn, $stmt, $query);
            if ($res === false) {
                throw new VaultAnalyzerException(pg_last_error($this->_conn));
            }
            $first[$stmt]=true;
        }
    }
}

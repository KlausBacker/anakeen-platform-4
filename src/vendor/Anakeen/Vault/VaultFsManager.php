<?php


namespace Anakeen\Vault;

use Anakeen\Core\DbManager;

/**
 *  Manager vault fs
 */
class VaultFsManager
{
    public static function getInfo(DiskFsStorage $fs)
    {
        $sqlfs = "id_fs=" . doubleval($fs->id_fs) . " and ";

        DbManager::query("select count(id_file),sum(size) from vaultdiskstorage where id_fs='" . $fs->id_fs . "'", $nf);
        $nf0 = $nf[0];
        $used_size = $nf0["sum"];

        //Orphean
        DbManager::query("SELECT count(id_file), sum(size) FROM vaultdiskstorage WHERE $sqlfs NOT EXISTS (SELECT 1 FROM docvaultindex WHERE vaultid = id_file AND docid > 0)", $no);


        //trash files
        DbManager::query(
            "SELECT count(id_file), sum(size) FROM (SELECT id_file, size " .
            "FROM vaultdiskstorage, docvaultindex, docread " .
            "WHERE $sqlfs id_file = vaultid AND docid = id AND doctype = 'Z' GROUP BY (id_file)) AS r;",
            $nt
        );

        $max = doubleval($fs->max_size);
        $free = doubleval($max - $used_size);
        $realused = doubleval($max - $free);

        $fsInfo = array(
            "fsid" => $fs->id_fs,
            "path" => $fs->r_path
        );


        $fsInfo["metrics"] = array(
            "totalSize" => $max,
            "usedSize" => doubleval($realused),
            "totalCount" => doubleval($nf0["count"]),
            "repartition" => [
                "usefulSize" => $realused - doubleval($no[0]["sum"]) - doubleval($nt[0]["sum"]),
                "orphanSize" => doubleval($no[0]["sum"]),
                "trashSize" => doubleval($nt[0]["sum"]),
                "usefulCount" => doubleval($nf0["count"]) - doubleval($no[0]["count"]) - doubleval($nt[0]["count"]),
                "orphanCount" => doubleval($no[0]["count"]),
                "trashCount" => doubleval($nt[0]["count"]),
            ]
        );


        $fsInfo["humanMetrics"] = array(
            "totalSize" => self::humanreadsize($fsInfo["metrics"]["totalSize"]),
            "usedSize" => self::humanreadsize($fsInfo["metrics"]["usedSize"]),
            "count" => doubleval($nf0["count"]),
            "repartition" => [
                "usefulSize" => self::humanreadsize($fsInfo["metrics"]["repartition"]["usefulSize"]),
                "orphanSize" => self::humanreadsize($fsInfo["metrics"]["repartition"]["orphanSize"]),
                "trashSize" => self::humanreadsize($fsInfo["metrics"]["repartition"]["trashSize"]),
            ]
        );

        $computedSized = $fs->getSize();


        $fsInfo["computedMetrics"] = [
            "usedSize" => $computedSized
        ];


        $df = self::df($fs->r_path);
        $fsInfo["disk"] = [
            "totalSize" => $df['total'],
            "usedSize" => $df['used'],
        ];
        return $fsInfo;
    }

    protected static function humanreadsize($bytes)
    {

        $neg = ($bytes < 0) ? "-" : "";
        $bytes = abs($bytes);

        if ($bytes < 1024) {
            return $neg . sprintf(___("%d bytes", "vault"), $bytes);
        }
        if ($bytes < 10240) {
            return $neg . sprintf(___("%.02f KB", "vault"), $bytes / 1024);
        }
        if ($bytes < 1048576) {
            return $neg . sprintf(___("%d KB", "vault"), $bytes / 1024);
        }
        if ($bytes < 10485760) {
            return $neg . sprintf(___("%.02f MB", "vault"), $bytes / 1048576);
        }
        if ($bytes < 1048576 * 1024) {
            return $neg . sprintf(___("%d MB", "vault"), $bytes / 1048576);
        }
        if ($bytes < 1048576 * 10240) {
            return $neg . sprintf(___("%.02f GB", "vault"), $bytes / 1048576 / 1024);
        }
        if ($bytes < 1048576 * 1048576) {
            return $neg . sprintf(___("%d GB", "vault"), $bytes / 1048576 / 1024);
        }
        return $neg . sprintf(___("%d TB", "vault"), $bytes / 1048576 / 1048576);
    }

    protected static function humanreadpc($pc)
    {
        /* if ($pc < 1) return sprintf("%.02f%%", $pc); */
        if ($pc < 1 && $pc > 0) {
            return "1%";
        }
        $pc = floor($pc);
        return sprintf("%d%%", $pc);
    }

    protected static function df($path)
    {
        if (!is_dir($path)) {
            return [
                'total' => 0,
                'free' => 0,
                'used' => 0
            ];
        }

        $df = array(
            'total' => disk_total_space($path),
            'free' => disk_free_space($path)
        );
        $df['used'] = ($df['total'] !== false && $df['free'] !== false) ? doubleval($df['total'] - $df['free']) : 'N/A';
        $df['%free'] = ($df['free'] !== false && $df['total'] !== false) ? self::humanreadpc(100 * $df['free'] / $df['total']) : 'N/A';
        $df['total'] = ($df['total'] !== false) ? doubleval($df['total']) : 'N/A';
        $df['free'] = ($df['free'] !== false) ? self::humanreadsize($df['free']) : 'N/A';
        return $df;
    }
}

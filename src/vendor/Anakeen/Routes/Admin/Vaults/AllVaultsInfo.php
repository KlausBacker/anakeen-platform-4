<?php

namespace Anakeen\Routes\Admin\Vaults;

use Anakeen\Routes\Core\Lib\ApiMessage;

/**
 * @note use by route GET /api/v2/admin/vaults/
 */
class AllVaultsInfo
{
    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     *
     * @return \Slim\Http\response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response)
    {
        $fsInfo = [];
        $messages = [];
        $q = new \Anakeen\Core\Internal\QueryDb("", \Anakeen\Vault\DiskFsStorage::class);
        $q->order_by="id_fs";
        $fsList = $q->query();
        if ($q->nb > 0) {
            /** @var \Anakeen\Vault\DiskFsStorage $fsItem */
            foreach ($fsList as $key => $fsItem) {
                $info = \Anakeen\Vault\VaultFsManager::getInfo($fsItem);

                if ($info["metrics"]["usedSize"] !== $info["computedMetrics"]["usedSize"]) {
                    $messages[] = new ApiMessage(sprintf(
                        "Mismatch used size in fs #%d. Computed = %s, Cached = %s",
                        $fsItem->id_fs,
                        $info["computedMetrics"]["usedSize"],
                        $info["metrics"]["usedSize"]
                    ), ApiMessage::WARNING);
                }
                $fsInfo[] = $info;
                $fsInfo[$key]["freespace"] = $this->formatBytes($info["metrics"]["totalSize"] - $info["metrics"]["usedSize"], 2);
            }
        }
        return \Anakeen\Router\ApiV2Response::withData($response, $fsInfo, $messages);
    }

    public static function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

<?php

namespace Anakeen\Routes\Admin\Vaults;

use Anakeen\Routes\Core\Lib\ApiMessage;

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
        $fsList = $q->query();
        if ($q->nb > 0) {
            /** @var \Anakeen\Vault\DiskFsStorage $fsItem */
            foreach ($fsList as $fsItem) {
                $info = \Anakeen\Vault\VaultFsManager::getInfo($fsItem);

                if ($info["metrics"]["usedSize"] !== $info["computedMetrics"]["usedSize"]) {
                    var_dump([$info["metrics"]["usedSize"],$info["computedMetrics"]["usedSize"] ]);
                    $messages[] = new ApiMessage(sprintf(
                        "Mismatch used size in fs #%d. Computed = %s, Cached = %s",
                        $fsItem->id_fs,
                        $info["computedMetrics"]["usedSize"],
                        $info["metrics"]["usedSize"]
                    ));
                }
                $fsInfo[] = $info;
            }
        }

        return \Anakeen\Router\ApiV2Response::withData($response, $fsInfo, $messages);
    }
}

<?php

namespace Anakeen\Routes\Admin\Vaults;

use Anakeen\Exception;
use Anakeen\Routes\Core\Lib\ApiMessage;
use Anakeen\Vault\DiskFsStorage;

/**
 * @note use by route PUT /api/v2/admin/vaults/{vault}/size/{size}
 */
class VaultResize
{
    protected $size = 0;
    protected $vaultid = 0;
    /** @var DiskFsStorage */
    protected $vaultFs = null;

    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     *
     * @param                     $args
     *
     * @return \Slim\Http\response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        $fsInfo = $this->doRequest();
        $messages = [];

        $messages[] = new ApiMessage(sprintf(
            "New maximum size is %s",
            AllVaultsInfo::formatBytes($this->vaultFs->max_size)
        ));

        return \Anakeen\Router\ApiV2Response::withData($response, $fsInfo, $messages);
    }

    public function initParameters(\Slim\Http\request $request, $args)
    {
        $this->size = doubleval(trim($request->getBody()->getContents()));
        $this->vaultid = intval($args["vault"]);

        $this->vaultFs = new DiskFsStorage("", $this->vaultid);
        if (!$this->vaultFs->isAffected()) {
            $e = new Exception("Vault unknow");
            $e->setUserMessage(sprintf("Vault #%d not exists", $this->vaultid));
            throw $e;
        }
    }

    public function doRequest()
    {
        $this->vaultFs->max_size = $this->size;
        $err = $this->vaultFs->modify();
        if ($err) {
            throw new Exception($err);
        }
        return \Anakeen\Vault\VaultFsManager::getInfo($this->vaultFs);
    }
}

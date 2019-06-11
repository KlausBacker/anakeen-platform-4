<?php

namespace Anakeen\Routes\Admin\Vaults;

use Anakeen\Core\DbManager;
use Anakeen\Exception;
use Anakeen\Routes\Core\Lib\ApiMessage;
use Anakeen\Vault\DiskFsStorage;

/**
 * @note use by route POST /api/v2/admin/vaults/
 */
class VaultCreate
{
    protected $size = 0;
    protected $vaultid = 0;
    /** @var DiskFsStorage */
    protected $vaultFs = null;
    protected $path;

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
        $this->initParameters($request);
        $fsInfo = $this->doRequest();
        $messages = [];

        $messages[] = new ApiMessage(sprintf(
            "New vault created. Location is \"%s\", size is %s",
            $this->vaultFs->r_path,
            AllVaultsInfo::formatBytes($this->vaultFs->max_size)
        ), ApiMessage::SUCCESS);


        return \Anakeen\Router\ApiV2Response::withData($response, $fsInfo, $messages);
    }

    public function initParameters(\Slim\Http\request $request)
    {
        $content = $request->getParsedBody();
        $this->size = $content["size"];
        $this->path = $content["path"];

        VaultMove::checkPath($this->path);
    }

    public function doRequest()
    {
        $this->vaultFs = new DiskFsStorage();
        $this->vaultFs->max_size = $this->size;
        $this->vaultFs->r_path = $this->path;
        $err = $this->vaultFs->add();
        if ($err) {
            throw new Exception($err);
        }
        DbManager::query("update vaultdiskfsstorage set fsname = 'V'||id_fs where fsname is null");
        return \Anakeen\Vault\VaultFsManager::getInfo($this->vaultFs);
    }
}

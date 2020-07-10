<?php

namespace Anakeen\Routes\Admin\Vaults;

use Anakeen\Exception;
use Anakeen\Routes\Core\Lib\ApiMessage;
use Anakeen\Vault\DiskFsStorage;

/**
 * @note use by route PUT /api/v2/admin/vaults/{vault}/path/
 */
class VaultMove
{
    protected $newPath = 0;
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

        $messages[] = new ApiMessage(sprintf(___("New path is \"%s\"", "AdminCenterVaultManager"), $this->vaultFs->r_path));

        return \Anakeen\Router\ApiV2Response::withData($response, $fsInfo, $messages);
    }

    public function initParameters(\Slim\Http\request $request, $args)
    {
        $this->vaultid = intval($args["vault"]);
        $this->newPath = trim($request->getBody()->getContents());

        self::checkPath($this->newPath);

        $this->vaultFs = new DiskFsStorage("", $this->vaultid);
        if (!$this->vaultFs->isAffected()) {
            $e = new Exception("Vault unknow");
            $e->setUserMessage(sprintf(___("Vault \"#%d\" not exists", "AdminCenterVaultManager"), $this->vaultid));
            throw $e;
        }
    }

    public static function checkPath($path)
    {
        if (!$path || $path[0] !== '/') {
            $e = new Exception("Invalid path");
            $e->setUserMessage(sprintf(___("Path \"%s\" must be an absolute path", "AdminCenterVaultManager"), $path));
            throw $e;
        }

        if (!is_dir($path) && !is_link($path)) {
            $e = new Exception("Invalid path");
            $e->setUserMessage(sprintf(___("Path \"%s\" must reference an existing directory", "AdminCenterVaultManager"), $path));
            throw $e;
        }


        if (!is_writable($path)) {
            $e = new Exception("Invalid path");
            $e->setUserMessage(sprintf(___("Path \"%s\" is not writable", "AdminCenterVaultManager"), $path));
            throw $e;
        }
    }

    public function doRequest()
    {
        $this->vaultFs->r_path = $this->newPath;
        $err = $this->vaultFs->modify();
        if ($err) {
            throw new Exception($err);
        }

        return \Anakeen\Vault\VaultFsManager::getInfo($this->vaultFs);
    }
}

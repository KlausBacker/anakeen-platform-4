<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Utilities functions for manipulate files from VAULT
 *
 * @author Anakeen
 * @version $Id: Lib.Vault.php,v 1.23 2008/07/24 16:03:15 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

/**
 * @deprecated
 * @return bool|VaultFile
 */
function initVaultAccess()
{
    static $FREEDOM_VAULT = false;
    ;
    if (!$FREEDOM_VAULT) {
        $dbaccess = getDbAccess();
        $FREEDOM_VAULT = new VaultFile($dbaccess, "FREEDOM");
    }
    return $FREEDOM_VAULT;
}
/**
 * get url with open id to use with open authentiication
 * @deprecated
 */
function getOpenTeUrl($context = array())
{
    global $action;
    $urlindex = \Anakeen\Core\ContextManager::getApplicationParam("TE_URLINDEX");
    if ($urlindex == "") { //case DAV
        $au = \Anakeen\Core\ContextManager::getApplicationParam("CORE_URLINDEX");
        if ($au != "") {
            $urlindex = \Anakeen\Core\ContextManager::getApplicationParam("CORE_URLINDEX");
        } else {
            $scheme = \Anakeen\Core\ContextManager::getApplicationParam("CORE_EXTERNURL");
            if ($scheme == "") {
                throw new \Dcp\Exception("Need configure TE_URLINDEX");
            }
        }
    }
    $token = $action->user->getUserToken(3600 * 24, true, $context);
    if (strstr($urlindex, '?')) {
        $beg = '&';
    } else {
        $beg = '?';
    }
    $openurl = $urlindex . $beg . "authtype=open&privateid=$token";
    return $openurl;
}
/**
 * Generate a conversion of a file
 * The result is store in vault itself
 * @deprecated
 * @param string $engine the convert engine identifier (from VaultEngine Class)
 * @param int $vidin vault file identifier (original file)
 * @param int $vidout vault identifier of new stored file
 * @param boolean $isimage true is it is a image (jpng, png, ...)
 * @param int $docid original document where the file is inserted
 * @return string error message (empty if OK)
 */
function vault_generate($dbaccess, $engine, $vidin, $vidout, $isimage = false, $docid = '')
{
    $err = '';
    if (($vidin > 0) && ($vidout > 0)) {
        $tea = \Anakeen\Core\ContextManager::getApplicationParam("TE_ACTIVATE");
        if ($tea != "yes" || !\Anakeen\Core\Internal\Autoloader::classExists('Dcp\TransformationEngine\Client')) {
            return '';
        }
        global $action;
        $of = new VaultDiskStorage($dbaccess, $vidin);
        $filename = $of->getPath();
        if (!$of->isAffected()) {
            return "no file $vidin";
        }
        $ofout = new VaultDiskStorage($dbaccess, $vidout);
        $ofout->teng_state = \Dcp\TransformationEngine\Client::status_waiting; // in progress
        $ofout->modify();
        
        $urlindex = getOpenTeUrl();
        $callback = $urlindex . "&sole=Y&app=FDL&action=INSERTFILE&engine=$engine&vidin=$vidin&vidout=$vidout&isimage=$isimage&docid=$docid";
        $ot = new \Dcp\TransformationEngine\Client(\Anakeen\Core\ContextManager::getApplicationParam("TE_HOST"), \Anakeen\Core\ContextManager::getApplicationParam("TE_PORT"));
        $err = $ot->sendTransformation($engine, $vidout, $filename, $callback, $info);
        if ($err == "") {
            $tr = new TaskRequest($dbaccess);
            $tr->tid = $info["tid"];
            $tr->fkey = $vidout;
            $tr->status = $info["status"];
            $tr->comment = $info["comment"];
            $tr->uid = $action->user->id;
            $tr->uname = $action->user->firstname . " " . $action->user->lastname;
            $err = $tr->Add();
        } else {
            $vf = initVaultAccess();
            $filename = uniqid(\Anakeen\Core\ContextManager::getTmpDir() . "/txt-" . $vidout . '-');
            file_put_contents($filename, $err);
            //$vf->rename($vidout,"toto.txt");
            $infofile = null;
            $vf->Retrieve($vidout, $infofile);
            $err.= $vf->Save($filename, false, $vidout);
            @unlink($filename);
            $vf->rename($vidout, _("impossible conversion") . ".txt");
            if ($info["status"]) {
                $vf->storage->teng_state = $info["status"];
            } else {
                $vf->storage->teng_state = \Dcp\TransformationEngine\Client::status_inprogress;
            }
            $vf->storage->modify();
        }
    }
    return $err;
}

/**
 * return unique name with for a vault file
 * @param int $idfile vault file identifier
 * @param string $teng_name transformation engine name
 * @deprecated
 * @return string the unique name
 */

/**
 * send request to have text conversion of file
 * @deprecated
 */
function sendTextTransformation($dbaccess, $docid, $attrid, $index, $vid)
{
    $err = '';
    if (($docid > 0) && ($vid > 0)) {
        $tea = \Anakeen\Core\ContextManager::getApplicationParam("TE_ACTIVATE");
        if ($tea != "yes" || !\Anakeen\Core\Internal\Autoloader::classExists('Dcp\TransformationEngine\Client')) {
            return '';
        }
        $tea = \Anakeen\Core\ContextManager::getApplicationParam("TE_FULLTEXT");
        if ($tea != "yes") {
            return '';
        }
        
        global $action;
        include_once("FDL/Class.TaskRequest.php");
        $of = new VaultDiskStorage($dbaccess, $vid);
        $filename = $of->getPath();
        $urlindex = getOpenTeUrl();
        $callback = $urlindex . "&sole=Y&app=FDL&action=SETTXTFILE&docid=$docid&attrid=" . $attrid . "&index=$index";
        $ot = new \Dcp\TransformationEngine\Client(\Anakeen\Core\ContextManager::getApplicationParam("TE_HOST"), \Anakeen\Core\ContextManager::getApplicationParam("TE_PORT"));
        $err = $ot->sendTransformation('utf8', $vid, $filename, $callback, $info);
        if ($err == "") {
            $tr = new TaskRequest($dbaccess);
            $tr->tid = $info["tid"];
            $tr->fkey = $vid;
            $tr->status = $info["status"];
            $tr->comment = $info["comment"];
            $tr->uid = $action->user->id;
            $tr->uname = $action->user->firstname . " " . $action->user->lastname;
            $err = $tr->Add();
        }
    }
    return $err;
}
/**
 * send request to convert and waiting
 * @param string  $infile path to file to convert
 * @param string  $engine engine name to use
 * @param string  $outfile path where to store new file
 * @param array &$info various informations for convertion process
 * @deprecated
 * @return string error message
 */
function convertFile($infile, $engine, $outfile, &$info)
{
    global $action;
    $err = '';
    if (file_exists($infile) && ($engine != "")) {
        $tea = \Anakeen\Core\ContextManager::getApplicationParam("TE_ACTIVATE");
        if ($tea != "yes" || !\Anakeen\Core\Internal\Autoloader::classExists('Dcp\TransformationEngine\Client')) {
            return _("TE not activated");
        }
        $callback = "";
        $ot = new \Dcp\TransformationEngine\Client(\Anakeen\Core\ContextManager::getApplicationParam("TE_HOST"), \Anakeen\Core\ContextManager::getApplicationParam("TE_PORT"));
        $vid = '';
        $err = $ot->sendTransformation($engine, $vid, $infile, $callback, $info);
        if ($err == "") {
            $dbaccess = getDbAccess();
            $tr = new TaskRequest($dbaccess);
            $tr->tid = $info["tid"];
            $tr->fkey = $vid;
            $tr->status = $info["status"];
            $tr->comment = $info["comment"];
            $tr->uid = $action->user->id;
            $tr->uname = $action->user->firstname . " " . $action->user->lastname;
            $err = $tr->Add();
        }
        $tid = 0;
        if ($err == "") {
            $tid = $info["tid"];
            if ($tid == 0) {
                $err = _("no task identificator");
            }
        }
        // waiting response
        if ($err == "") {
            $status = "";
            \Anakeen\Core\Utils\System::setMaxExecutionTimeTo(3600);
            while (($status != 'K') && ($status != 'D') && ($err == "")) {
                $err = $ot->getInfo($tid, $info);
                $status = $info["status"];
                if ($err == "") {
                    switch ($info["status"]) {
                        case 'P':
                            $statusmsg = _("File:: Processing");
                            break;

                        case 'W':
                            $statusmsg = _("File:: Waiting");
                            break;

                        case 'D':
                            $statusmsg = _("File:: converted");
                            break;

                        case 'K':
                            $statusmsg = _("File:: failed");
                            break;

                        default:
                            $statusmsg = $info["status"];
                    }
                }
                
                sleep(2);
            }
            if (($err == "") && ($status == 'D')) {
                include_once("FDL/insertfile.php");
                $err = getTEFile($tid, $outfile, $info);
            }
        }
    } else {
        $err = sprintf(_("file %s not found"), $infile);
    }
    return $err;
}

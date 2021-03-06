<?php
/**
 * Transformation server engine manager
 *
 * @author  Anakeen
 * @version $Id: Class.TEClient.php,v 1.12 2007/08/14 09:39:33 eric Exp $
 * @package FDL
 */

/**
 */

namespace Anakeen\TransformationEngine;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Core\Utils\System;
use Anakeen\Exception;
use Anakeen\Router\AuthenticatorManager;
use Anakeen\Vault\DiskStorage;
use Anakeen\Vault\VaultFile;

class Manager
{
    const Ns = "TE";

    /**
     * check if TE params are set
     *
     * @return string error message, if no error empty string
     */
    public static function checkParameters()
    {
        if (ContextParameterManager::getValue(self::Ns, "TE_HOST") == ""
            || ContextParameterManager::getValue(self::Ns, "TE_PORT") == "") {
            return ___("Please set all TE parameters", "TransformationEngine");
        }
        return "";
    }


    /**
     * check if TE is accessible
     *
     * @param array $info
     * @return string error message, if no error empty string
     * @throws ClientException
     */
    public static function isAccessible(&$info)
    {
        $err = self::checkParameters();
        if ($err != '') {
            return $err;
        } else {
            $te = new \Anakeen\TransformationEngine\Client();
            $err = $te->retrieveServerInfo($info, true);
            if ($err != '') {
                return $err;
            }
        }
        return '';
    }

    /**
     * Test is TE parameter is on
     *
     * @return bool
     */
    public static function isActivated()
    {
        return \Anakeen\Core\ContextManager::getParameterValue(
            \Anakeen\TransformationEngine\Manager::Ns,
            "TE_ACTIVATE"
        ) === "yes";
    }

    /**
     * Generate a conversion of a file
     * The result is store in vault itself
     *
     * @param string $engine the convert engine identifier (from VaultEngine Class)
     * @param int $vidin vault file identifier (original file)
     * @param int $vidout vault identifier of new stored file
     * @param boolean $isimage true is it is a image (jpng, png, ...)
     * @param int $docid original document where the file is inserted
     *
     * @return string error message (empty if OK)
     * @deprecated
     *
     */
    public static function vaultGenerate($engine, $vidin, $vidout, $isimage = false, $docid = 0)
    {
        $err = '';
        if (($vidin > 0) && ($vidout > 0)) {
            if (!self::isActivated()) {
                return '';
            }
            $of = new DiskStorage("", $vidin);
            $filename = $of->getPath();
            if (!$of->isAffected()) {
                return "no file $vidin";
            }
            $ofout = new DiskStorage("", $vidout);
            $ofout->teng_state = \Anakeen\TransformationEngine\Client::status_waiting; // in progress
            $ofout->modify();

            $callback = sprintf("/api/transformationengine/%d/%d/%d", $vidin, $vidout, $docid);
            $callurl = self::getOpenTeUrl($callback);

            if ($isimage) {
                $callurl .= "&image=true";
            }
            $ot = new \Anakeen\TransformationEngine\Client();
            $err = $ot->sendTransformation($engine, $vidout, $filename, $callurl, $info);
            if ($err == "") {
                $tr = new \TaskRequest();
                $tr->tid = $info["tid"];
                $tr->fkey = $vidout;
                $tr->status = $info["status"];
                $tr->comment = $info["comment"];
                $tr->uid = ContextManager::getCurrentUser()->id;
                $tr->uname = ContextManager::getCurrentUser()->getAccountName();
                $err = $tr->add();
            } else {
                $vf = self::initVaultAccess();
                $filename = uniqid(\Anakeen\Core\ContextManager::getTmpDir() . "/txt-" . $vidout . '-');
                file_put_contents($filename, $err);
                //$vf->rename($vidout,"toto.txt");
                $infofile = null;
                $vf->Retrieve($vidout, $infofile);
                $err .= $vf->Save($filename, false, $vidout);
                @unlink($filename);
                $vf->rename($vidout, _("impossible conversion") . ".txt");
                if ($info["status"]) {
                    $vf->storage->teng_state = $info["status"];
                } else {
                    $vf->storage->teng_state = \Anakeen\TransformationEngine\Client::status_inprogress;
                }
                $vf->storage->modify();
            }
        }
        return $err;
    }

    public static function initVaultAccess()
    {
        static $THEVAULT = false;
        if (!$THEVAULT) {
            $THEVAULT = new VaultFile();
        }
        return $THEVAULT;
    }

    /**
     * get url with open id to use with open authentiication
     * @param string $pattern the url pattern
     * @param string $description description label for token
     * @return string
     * @throws Exception
     * @throws \Anakeen\Router\Exception
     */
    public static function getOpenTeUrl($pattern, $description = "Transformation Engine")
    {
        $urlindex = \Anakeen\Core\ContextManager::getParameterValue(self::Ns, "TE_URLINDEX");
        if ($urlindex == "") { //case DAV
            $au = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_URLINDEX");
            if ($au != "") {
                $urlindex = \Anakeen\Core\ContextManager::getParameterValue(
                    \Anakeen\Core\Settings::NsSde,
                    "CORE_URLINDEX"
                );
            } else {
                $scheme = \Anakeen\Core\ContextManager::getParameterValue(
                    \Anakeen\Core\Settings::NsSde,
                    "CORE_EXTERNURL"
                );
                if ($scheme == "") {
                    throw new \Anakeen\Exception("Need configure TE_URLINDEX");
                }
            }
        }

        $parse=parse_url($pattern);


        $routes = [$parse["path"]];
        $token = AuthenticatorManager::getAuthorizationToken(
            ContextManager::getCurrentUser(),
            $routes,
            3600 * 24,
            true,
            $description
        );
        if (strstr($urlindex.$pattern, '?')) {
            $beg = '&';
        } else {
            $beg = '?';
        }
        $openurl = sprintf(
            "%s%s%s%s=%s",
            $urlindex,
            $pattern,
            $beg,
            \Anakeen\Core\Internal\OpenAuthenticator::openGetId,
            $token
        );

        $pUrl = parse_url($openurl);
        if (is_array($pUrl) && isset($pUrl['path'])) {
            $pUrl['path'] = str_replace('//', '/', $pUrl['path']);
            $openurl = self::implodeUrl($pUrl);
        }

        return $openurl;
    }

    /**
     * rewrite URL from parse_url array
     * @param array $turl the url array
     * @return string
     */
    private static function implodeUrl($turl)
    {
        if (isset($turl["scheme"])) {
            $url = $turl["scheme"] . "://";
        } else {
            $url = "http://";
        }
        if (isset($turl["user"]) && isset($turl["pass"])) {
            $url.= $turl["user"] . ':' . $turl["pass"] . '@';
        }
        if (isset($turl["host"])) {
            $url.= $turl["host"];
        } else {
            $url.= "localhost";
        }
        if (isset($turl["port"])) {
            $url.= ':' . $turl["port"];
        }
        if (isset($turl["path"])) {
            $url.= $turl["path"];
        }
        if (isset($turl["query"])) {
            $url.= '?' . $turl["query"];
        }
        if (isset($turl["fragment"])) {
            $url.= '#' . $turl["fragment"];
        }

        return $url;
    }

    /**
     * return filename where is stored produced file
     * need to delete after use it
     *
     * @param string $tid task TE identifier
     * @param string $filename output file path
     * @param array $info
     *
     * @return string
     */
    public static function downloadTEFile($tid, $filename, &$info)
    {
        $ot = new \Anakeen\TransformationEngine\Client();

        $err = $ot->getInfo($tid, $info);
        if ($err == "") {
            $tr = new \TaskRequest("", $tid);
            if ($tr->isAffected()) {
                $outfile = $info["outfile"];
                $status = $info["status"];

                if (($status == 'D') && ($outfile != '')) {
                    $err = $ot->getTransformation($tid, $filename);
                }
            } else {
                $err = sprintf(___("task %s is not recorded", "tengine"), $tid);
            }
        }
        return $err;
    }

    /**
     * Send request to convert and waiting until transformation engine server has finish the transformation
     *
     * @param string $infile path to file to convert
     * @param string $engine engine name to use
     * @param string $outfile path where to store new file
     * @param array &$info various informations for convertion process
     *
     * @return string error message
     */
    public static function convertFile($infile, $engine, $outfile, &$info)
    {
        if (file_exists($infile) && ($engine != "")) {
            $callback = "";
            $ot = new \Anakeen\TransformationEngine\Client();
            $vid = '';
            $err = $ot->sendTransformation($engine, $vid, $infile, $callback, $info);
            if ($err == "") {
                $tr = new \TaskRequest();
                $tr->tid = $info["tid"];
                $tr->fkey = $vid;
                $tr->status = $info["status"];
                $tr->comment = $info["comment"];
                $tr->uid = ContextManager::getCurrentUser()->id;
                $tr->uname = ContextManager::getCurrentUser()->getAccountName();
                $err = $tr->Add();
            }
            $tid = 0;
            if ($err == "") {
                $tid = $info["tid"];
                if (empty($tid)) {
                    $err = ___("no task identificator", "tengine");
                }
            }
            // waiting response
            if ($err == "") {
                $status = "";

                System::setMaxExecutionTimeTo(3600);
                while (($status != 'K') && ($status != 'D') && ($err == "")) {
                    $err = $ot->getInfo($tid, $info);
                    $status = $info["status"];

                    sleep(2);
                }
                if (($err == "") && ($status == 'D')) {
                    $err = self::downloadTEFile($tid, $outfile, $info);
                }
            }
        } else {
            $err = sprintf(___("file %s not found", "tengine"), $infile);
        }
        return $err;
    }

    /**
     * Verify the complete configuration of TE
     * Send a task and wait response callback
     * @param int $timeout (in seconds) to wait the callback response
     * @throws ClientException
     * @throws Exception
     * @throws \Anakeen\Router\Exception
     */
    public static function checkConnection($timeout = 60)
    {
        $err = self::isAccessible($info);
        if ($err) {
            throw new Exception($err);
        }

        $tmpFile = tempnam(ContextManager::getTmpDir(), '');
        if ($tmpFile === false) {
            throw new Exception("Could not create temporary file.");
        }
        if (file_put_contents($tmpFile, 'hello world.') === false) {
            throw new Exception(sprintf("Error writing content to temporary file '%s'", $tmpFile));
        }

        $te_name = 'utf8';
        $fkey = '';
        $key = uniqid("te");

        $callback = sprintf("/api/transformationengine/tests/%s", $key);
        $callurl = Manager::getOpenTeUrl($callback);

        $te = new Client();
        $err = $te->sendTransformation($te_name, $fkey, $tmpFile, $callurl, $info);
        if ($err != '') {
            unlink($tmpFile);
            throw new Exception($err);
        }
        $resultFile = sprintf("%s/%s", ContextManager::getTmpDir(), $key);
        $t = 0;
        while ($t < $timeout) {
            sleep(1);
            if (file_exists($resultFile)) {
                break;
            }
            $t++;
        }

        $err = $te->getInfo($info["tid"], $status);
        if ($err) {
            throw new Exception("Conversion failed: " . $err);
        }

        if (!file_exists($resultFile)) {
            $err = print_r($status, true);

            throw new Exception("Timeout: callback no return - " . $err);
        } else {
            if ($status["status"] !== Client::TASK_STATE_SUCCESS) {
                $err = print_r($status, true);

                throw new Exception("Task error: " . $err);
            }
        }
        unlink($resultFile);
    }
}

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
use Anakeen\Router\AuthenticatorManager;

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
        if (ContextParameterManager::getValue(self::Ns, "E_HOST") == ""
            || ContextParameterManager::getValue(self::Ns, "TE_PORT") == "") {
            return ___("Please set all TE parameters", "TransformationEngine");
        }
        return "";
    }


    /**
     * check if TE is accessible
     *
     * @return string error message, if no error empty string
     */
    public static function isAccessible()
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
     * Generate a conversion of a file
     * The result is store in vault itself
     *
     * @deprecated
     *
     * @param string  $engine  the convert engine identifier (from VaultEngine Class)
     * @param int     $vidin   vault file identifier (original file)
     * @param int     $vidout  vault identifier of new stored file
     * @param boolean $isimage true is it is a image (jpng, png, ...)
     * @param int     $docid   original document where the file is inserted
     *
     * @return string error message (empty if OK)
     */
    public static function vaultGenerate($engine, $vidin, $vidout, $isimage = false, $docid = 0)
    {
        $err = '';
        if (($vidin > 0) && ($vidout > 0)) {
            $tea = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\TransformationEngine\Manager::Ns, "TE_ACTIVATE");
            if ($tea !== "yes") {
                return '';
            }
            $of = new \VaultDiskStorage("", $vidin);
            $filename = $of->getPath();
            if (!$of->isAffected()) {
                return "no file $vidin";
            }
            $ofout = new \VaultDiskStorage("", $vidout);
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
            $THEVAULT = new \VaultFile();
        }
        return $THEVAULT;
    }

    /**
     * get url with open id to use with open authentiication
     *
     */
    public static function getOpenTeUrl($pattern)
    {
        $urlindex = \Anakeen\Core\ContextManager::getParameterValue(self::Ns, "TE_URLINDEX");
        if ($urlindex == "") { //case DAV
            $au = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_URLINDEX");
            if ($au != "") {
                $urlindex = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_URLINDEX");
            } else {
                $scheme = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_EXTERNURL");
                if ($scheme == "") {
                    throw new \Dcp\Exception("Need configure TE_URLINDEX");
                }
            }
        }

        $routes = [$pattern];
        $token = AuthenticatorManager::getAuthorizationToken(
            ContextManager::getCurrentUser(),
            $routes,
            3600 * 24,
            true
        );
        if (strstr($urlindex, '?')) {
            $beg = '&';
        } else {
            $beg = '?';
        }
        $openurl = sprintf("%s%s%s%s=%s", $urlindex, $pattern, $beg, \Anakeen\Core\Internal\OpenAuthenticator::openGetId, $token);

        $proto = substr($openurl, 0, 6);
        $tail = str_replace('//', '/', substr($openurl, 6));

        return $proto.$tail;
    }

    /**
     * return filename where is stored produced file
     * need to delete after use it
     *
     * @param string $tid      task TE identifier
     * @param string $filename output file path
     * @param array  $info
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
     * @param string $infile  path to file to convert
     * @param string $engine  engine name to use
     * @param string $outfile path where to store new file
     * @param array &$info    various informations for convertion process
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
                if ($tid == 0) {
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
}

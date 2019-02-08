<?php

namespace Anakeen\TransformationEngine;

use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\Utils\Date;
use Anakeen\Core\Utils\FileMime;
use Anakeen\Router\ApiV2Response;
use Anakeen\Exception;
use Anakeen\Vault\VaultFile;

/**
 * Class InsertFile
 *
 * @use     by route /api/transformationengine/{vidin}/{vidout}/{elementid}
 * @package Anakeen\TransformationEngine
 */
class InsertFile
{
    protected $vidin;
    protected $vidout;
    protected $elementid;
    protected $taskid;
    protected $isImage = false;
    protected $name;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->vidin = $args["vidin"];
        $this->vidout = $args["vidout"];
        $this->elementid = $args["elementid"];
        $this->taskid = $request->getQueryParam("tid");
        $this->name = $request->getQueryParam("name");
        $this->isImage = $request->getQueryParam("image") === "yes";
    }

    /**
     *
     * @return array
     * @throws Exception
     * @throws \Anakeen\Core\DocManager\Exception
     */
    protected function doRequest()
    {
        $data = [];
        if (!$this->taskid) {
            throw new Exception("No task identifier found");
        } else {
            $filename = tempnam(ContextManager::getTmpDir(), 'txt-');
            if ($filename === false) {
                throw new Exception(sprintf("Error creating temporary file in '%s'.", ContextManager::getTmpDir()));
            } else {
                $err = Manager::downloadTEFile($this->taskid, $filename, $info);
                if ($err == "") {
                    $outfile = $info["outfile"];
                    $status = $info["status"];
                    $infoin = new \Anakeen\Vault\FileInfo();
                    $infoout = new \Anakeen\Vault\FileInfo();

                    if (($status === 'D') && ($outfile != '')) {
                        $vf = new VaultFile();
                        $vf->Retrieve($this->vidin, $infoin);
                        $vf->Retrieve($this->vidout, $infoout);
                        $vf->Save($filename, false, $this->vidout);
                        $vf->Retrieve($this->vidout, $infoout); // reload for mime
                        $ext = FileMime::getExtension($infoout->mime_s);
                        if ($ext == "") {
                            $ext = $infoout->teng_lname;
                        }
                        //	  print_r($infoout);
                        // print_r($ext);
                        if ($this->name != "") {
                            $newname = $this->name;
                        } else {
                            $pp = strrpos($infoin->name, '.');
                            $newname = substr($infoin->name, 0, $pp) . '.' . $ext;
                        }

                        $vf->Rename($this->vidout, $newname);
                        $vf->storage->teng_state = Client::status_done;
                        $vf->storage->modify();
                        $vf->show($this->vidout, $info);
                        $data["file"] =  $info->name;
                        if ($this->elementid) {
                            $doc = SEManager::getDocument($this->elementid);
                            if ($doc) {
                                $doc->addHistoryEntry(sprintf(___("Convert file %s as %s succeed", "tengine"), $infoin->name, $infoout->teng_lname), \DocHisto::NOTICE);
                            }
                        }
                    } else {
                        $vf = new VaultFile();
                        $vf->Retrieve($this->vidin, $infoin);
                        $vf->Retrieve($this->vidout, $infoout);

                        $filename2 = tempnam(ContextManager::getTmpDir(), 'txt-');
                        if ($filename2 === false) {
                            throw new Exception(sprintf("Error creating temporary file in '%s'.", ContextManager::getTmpDir()));
                        } else {
                            $error = sprintf(_("Conversion as %s has failed "), $infoout->teng_lname);
                            $error .= "\n== " . ___("See below information about conversion", "tengine") . "==\n" . print_r($info, true);
                            file_put_contents($filename2, $error);

                            $vf->Retrieve($this->vidout, $infoout);
                            $vf->Save($filename2, false, $this->vidout);
                            $basename = _("conversion error") . ".txt";
                            $vf->Rename($this->vidout, $basename);
                            $vf->storage->teng_state = Client::error_convert;
                            $vf->storage->modify();
                            if ($this->elementid) {
                                $doc = SEManager::getDocument($this->elementid);
                                if ($doc) {
                                    $doc->addHistoryEntry(sprintf(___("Convert file %s as %s failed", "tengine"), $infoin->name, $infoout->teng_lname), \DocHisto::ERROR);
                                }
                            }
                            unlink($filename2);
                            $data["error"] =  $info["$error"];
                        }
                    }
                }
                if ($this->elementid) {
                    $doc = SEManager::getDocument($this->elementid);
                    if ($doc) {
                        $doc->mdate = Date::getNow(true);
                        $doc->modify(true, ["mdate"], true); // To update cache
                    }
                }
                unlink($filename);
            }
        }
        return $data;
    }
}

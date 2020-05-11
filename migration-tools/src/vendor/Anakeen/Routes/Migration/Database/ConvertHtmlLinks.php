<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\VaultManager;
use Anakeen\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\Search\SearchElements;

/**
 * Create primary mask based on default visibilities
 */
class ConvertHtmlLinks
{
    protected $structureName;
    /**
     * @var \Anakeen\Core\SmartStructure
     */
    protected $structure;
    /**
     * @var bool
     */
    protected $includeImgdata = false;
    /**
     * @var \Closure[]
     */
    protected $pregs = [];


    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        // Include export image data url to vault file
        $this->includeImgdata = $request->getQueryParam("include") === "img-data";

        $this->pregs = [
            '!href="([^"]*(app=FDL)[^"]*;id=([0-9A-Za-z_]+)[^"]+)"!' => function ($match) {
                // from : ?sole=A&amp;app=FDL&amp;action=OPENDOC&amp;id=196463&amp;
                $docid = $match[3];
                return sprintf('href="/api/v2/smart-elements/%s.html"', $docid);
            },
            '!href="([^"]*(api/v1/documents/([0-9A-Za-z_]+))[^"]+)"!' => function ($match) {
                // API V1 HTTP
                $docid = $match[3];
                return sprintf('href="/api/v2/smart-elements/%s.html"', $docid);
            },

            '!src="([^"]*(action=EXPORTFILE)[^"]*docid=([0-9A-Za-z_]+)[^"]*attrid=([a-zA-Z0-9_]+)[^"]+index=([0-9-]+)[^"]*)"!' => function (
                $match
            ) {
                // from : ?sole=A&amp;app=FDL&amp;action=EXPORTFILE&amp;cache=no&amp;docid=196463&amp;attrid=img_file&amp;index=-1
                // to : /api/v2/smart-elements/87527/files/img_file/-1/image?inline=true
                $docid = $match[3];
                $attrid = $match[4];
                $index = $match[5];
                return sprintf(
                    'src="/api/v2/smart-elements/%s/files/%s/%d/image?inline=true"',
                    $docid,
                    $attrid,
                    $index
                );
            }
        ];
    }

    protected function doRequest()
    {

        $data = ["testedElementsCount" => 0, "convertedLinksCount" => 0];

        $sql = sprintf("select distinct on (docid)  docid from docattr where type='htmltext'");
        DbManager::query($sql, $structIds, true);

        foreach ($structIds as $structId) {
            $struct = SEManager::getFamily($structId);
            $fields = $struct->getNormalAttributes();
            $htmlFields = [];
            foreach ($fields as $field) {
                if ($field->type === "htmltext") {
                    $htmlFields[] = $field->id;
                }
            }

            $dvi = new \DocVaultIndex();
            $s = new SearchElements($structId);
            $s->overrideAccessControl();
            $s->setDistinct(false);
            $s->setLatest(false);
            $s->setOrder("id desc");
            $s->returnsOnly($htmlFields);

            $list = $s->search()->getResults();
            // print $struct->name." # ".$s->count()."\n";
            foreach ($list as $element) {
                foreach ($htmlFields as $htmlField) {
                    $oa = $struct->getAttribute($htmlField);
                    if ($oa->isMultiple()) {
                        $htmlValue = $element->getMultipleRawValues($htmlField);
                    } else {
                        $htmlValue = $element->getRawValue($htmlField);
                    }
                    $convertedHtmlValue = $this->convertHtmlLink(
                        $htmlValue,
                        $element->initid,
                        $element->revision,
                        $oa->id,
                        $count,
                        $vids
                    );
                    if ($count > 0) {
                        $data["convertedLinksCount"] += $count;

                        $err = $element->setValue($oa->id, $convertedHtmlValue);
                        if ($err) {
                            $data["warnings"][] = ["id" => intval($element->id), "field" => $oa->id, "error" => $err];
                            continue;
                        }
                        $err = $element->modify(true, [$oa->id], true);
                        if ($err) {
                            throw new Exception(sprintf("Id:%d , field \"%s\": %s", $element->id, $oa->id, $err));
                        }


                        if ($vids) {
                            // update index to attach files to smart element
                            foreach ($vids as $vid) {
                                $dvi->docid = $element->id;
                                $dvi->vaultid = $vid;
                                $err = $dvi->add();
                                if ($err) {
                                    throw new Exception($err);
                                }
                            }
                        }
                        $data["convertedIds"][] = intval($element->id);
                    }
                    $data["testedElementsCount"]++;
                }
            }
        }

        return $data;
    }

    /**
     * @param string |string[] $htmlvalue
     * @param int $eltInitid
     * @param int $revision
     * @param string $fieldId
     * @param int $count
     * @param string[] $vids
     * @return string|string[]|null
     */
    protected function convertHtmlLink($htmlvalue, $eltInitid, $revision, $fieldId, &$count, &$vids)
    {
        $vids = [];
        $count = 0;
        $pregs = $this->pregs;
        if ($this->includeImgdata) {
            $pregs['!src="data:image/([^;]+);base64,([^"]+)"!'] = function ($match) use (
                $eltInitid,
                $revision,
                $fieldId,
                &$vids
            ) {
                // data:image/png;base64,
                // record to vault
                $ext = $match[1];
                $tmpFile = \LibSystem::tempnam(null, "paste") . "." . $ext;
                if (!file_put_contents($tmpFile, base64_decode($match[2]))) {
                    throw new \Anakeen\Exception(sprintf("Cannot write tmp file \"%s\"", $tmpFile));
                }
                $vid = VaultManager::storeFile($tmpFile, "paste." . $ext);
                $vids[] = $vid;
                unlink($tmpFile);
                return sprintf(
                    'src="/api/v2/images/htmltext/%d/%d/%s/%s/paste.%s" data-vid="%s"',
                    $eltInitid,
                    $revision,
                    $fieldId,
                    $vid,
                    $ext,
                    $vid
                );
            };
        }

        return preg_replace_callback_array(
            $pregs,
            $htmlvalue,
            -1,
            $count
        );
    }
}

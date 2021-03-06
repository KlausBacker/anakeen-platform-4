<?php


namespace Anakeen\Routes\Ui;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Core\SEManager;

/**
 * Class DocumentViewCollection
 * @note    Used by route : GET /api/v2/smart-elements/{docid}/views/
 * @package Anakeen\Routes\Ui
 */
class DocumentViewCollection extends DocumentView
{


    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        $info = $this->doRequest();
        return ApiV2Response::withData($response, $info);
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {

        $this->documentId = $args["docid"];
        $this->getDocument($this->documentId);
    }

    public function doRequest(&$messages = [])
    {
        $info = array();
        if ($this->document->cvid) {
            $info = $this->getViews($this->document);
        }
        $info = array_merge($info, array_values($this->getCoreViews($this->document)));
        return array(
            "views" => $info
        );
    }

    protected function getViews(\Anakeen\Core\Internal\SmartElement $document)
    {
        $cv = SEManager::getDocument($document->cvid);
        if ($cv === null) {
            throw new Exception("CRUDUI0006", $document->cvid, $document->getTitle());
        }
        SEManager::cache()->addDocument($cv);
        /**
         * @var \SmartStructure\Cvdoc $cv
         */
        $cv->set($document);
        $views = $cv->getViews();
        $info = array();
        foreach ($views as $view) {
            $vid = $view[\SmartStructure\Fields\Cvdoc::cv_idview];
            if ($cv->control($vid) == "") {
                $prop = $this->getViewProperties($cv, $view);
                $prop["uri"] = $this->getUri($document, $vid);
                $info[] = array(
                    "properties" => $prop
                );
            }
        }
        return $info;
    }
}

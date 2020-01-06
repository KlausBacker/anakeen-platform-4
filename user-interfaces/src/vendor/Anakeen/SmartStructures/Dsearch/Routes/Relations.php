<?php

namespace Anakeen\SmartStructures\Dsearch\Routes;

use Anakeen\Core\SEManager;
use Anakeen\Exception;
use Anakeen\Router\ApiV2Response;

class Relations
{

    protected $_collection = null;
    protected $_attrid = null;
    protected $_family = null;


    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {

        $return = array();

        $this->_family = $args["family"];
        $this->_attrid = $args["attrid"];

        $slice=$request->getQueryParam("slice");
        $offset=$request->getQueryParam("offset");
        $keyword=$request->getQueryParam("keyword");

        $fdoc = SEManager::getFamily($this->_family);
        foreach ($fdoc->getNormalAttributes() as $myAttribute) {
            if ($myAttribute->id == $this->_attrid) {
                $relationId="";
                if ($myAttribute->format) {
                    $relationId=$myAttribute->format;
                } elseif ($myAttribute->type === "account") {
                    $relationId="IUSER";
                    if ($myAttribute->getOption("match") === "group") {
                        $relationId="IGROUP";
                    } elseif ($myAttribute->getOption("match") === "role") {
                        $relationId="ROLE";
                    }
                }
                if (!$relationId) {
                    throw new Exception("Not valid relation field");
                }
                $s = new \Anakeen\Search\Internal\SearchSmartData("", $relationId);
                if ($slice !== null) {
                    $s->setSlice($slice);
                }
                if ($offset !== null) {
                    $s->setStart($offset);
                }
                if (!empty($keyword)) {
                    $s->addFilter("title ~* '%s'", $keyword);
                }
                $research = $s->search();
                foreach ($research as $k => $v) {
                    $return[] = array(
                        "id" => $v["id"],
                        "htmlTitle" => htmlspecialchars($v["title"]),
                        "title" => $v["title"]
                    );
                }
            }
        }
        return ApiV2Response::withData($response, $return);
    }
}

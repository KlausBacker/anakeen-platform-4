<?php


namespace Dcp\Search\html5;


use Dcp\HttpApi\V1\Crud\DocumentCollection;
use Dcp\HttpApi\V1\Crud\Exception;
use SmartStructure\Attributes\Report;

class searchGridAttributes extends DocumentCollection
{
    const maxSlice=1000;
    protected $_collection = null;
    /**
     * @var \Anakeen\Core\SmartStructure 
     */
    protected $_searchfamily = null;

    public function create()
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405","You cannot create");

        throw $exception;
    }


    public function read($resourceId)
    {
        $searchDocument = \Dcp\HttpApi\V1\DocManager\DocManager::getDocument($resourceId);
        if (!$searchDocument) {
            $exception = new Exception("CRUD0103", __METHOD__);
            $exception->setHttpStatus("404", "Doc not found");

            throw $exception;
        }

        $this->_searchfamily=\Dcp\HttpApi\V1\DocManager\DocManager::getFamily($searchDocument->getRawValue(Report::se_famid));
        if (!$this->_searchfamily) {
            $exception = new Exception("CRUD0103", __METHOD__);
            $exception->setHttpStatus("404", "search family not found");

            throw $exception;
        }
        $attributes=self::getGridAttributes($searchDocument);
        if (is_a( $searchDocument, '\SmartStructure\Report')) {
            $footer=self::getReportFooter($searchDocument);
            $config=self::getReportConfig($searchDocument);
        } else {
            $footer=[];
            $config=self::getDefaultConfig();
        }

        //$attributes[] = array("type" => "openDoc");
        array_unshift($attributes,  array("type" => "openDoc"));

        return array(
            "attributes"=> $attributes,
            "footer"=>$footer,
            "config"=>$config
        );
    }

    public static function getGridAttributes(\Doc $searchDocument) {
        $_searchfamily=\Dcp\HttpApi\V1\DocManager\DocManager::getFamily($searchDocument->getRawValue(Report::se_famid));
        if (!$_searchfamily) {
            $exception = new Exception("CRUD0103", __METHOD__);
            $exception->setHttpStatus("404", "search family not found");

            throw $exception;
        }

          if (is_a( $searchDocument, '\SmartStructure\Report')) {
            $attributes=self::getReportAttributes($searchDocument, $_searchfamily);
        } else {
            $attributes=self::getResumeAttributes($_searchfamily);
        }
        return $attributes;
    }

    protected static function getResumeAttributes(\Doc $document) {
          $return = array();

        $return[] = array("id" => "title","withIcon" => "true");
        foreach ($document->getAbstractAttributes() as $myAttribute) {
            $return[] = array("id" => $myAttribute->id,
                "className"=>sprintf("type--%s attr--%s", $myAttribute->type, $myAttribute->id));
        }
        return $return;
    }
    protected static function getReportAttributes(\Doc $document, \Doc $_searchfamily) {
        $return = [];


        $cols=$document->getMultipleRawValues(Report::rep_idcols);
        if (empty($cols)) {
            $cols=["title"];
        }
        //$return[] = array("id" => "title","withIcon" => "true");
        foreach ($cols as $attrid) {
            $attr=$_searchfamily->getAttribute($attrid);
            if ($attr && $attr->mvisibility !== "I") {
                $attrConfig=[
                    "id"=>$attrid,
                    "sortable" => \Dcp\DocumentGrid\HTML5\REST\ColumnsDefinition::isFilterable($attr),
                    "className"=>sprintf("type--%s attr--%s", $attr->type, $attr->id)];
                if ($attr->type==="docid") {
                    $attrConfig["withIcon"]=true;
                }
                $return[]=$attrConfig;
            } elseif (!empty(\Doc::$infofields[$attrid])) {
                 $attrConfig=[
                    "id"=>$attrid,
                    "sortable" => true,
                    "className"=>sprintf("type--%s attr--%s", \Doc::$infofields[$attrid]["type"], $attrid)];
                $return[]=$attrConfig;
            }

        }
        return $return;
    }


    protected function getDefaultConfig() {
        $config["paging"]=self::maxSlice;
        $config["family"]=$this->_searchfamily->name;
        return $config;
    }

    protected function getReportConfig(\Doc $document) {
        $config=$this->getDefaultConfig();
        $limit=$document->getRawValue(Report::rep_limit);
        if ($limit) {
            $config["paging"]=intval($limit);
        }
        return $config;
    }
    protected function getReportFooter(\Doc $document) {
        $return = [];


        $cols=$document->getMultipleRawValues(Report::rep_idcols);
        $foots=$document->getMultipleRawValues(Report::rep_foots);
        //$return[] = array("id" => "title","withIcon" => "true");
        foreach ($foots as $k=>$function) {
            switch ($function) {
                case "CARD":
                    $s=new \SearchDoc();
                    $s->useCollection($document->initid);
                    $return[]=$s->onlyCount();
                    break;

                case "MOY":
                case "SUM":
                    $attrid=$cols[$k];

                    $s=new \SearchDoc("", $document->getRawValue("se_famid"));
                    $s->useCollection($document->initid);
                    $s->returnsOnly([$attrid]);
                    $results=$s->search();

                    $sum=0;
                    foreach ($results as $result) {
                        $sum += floatval($result[$attrid]);
                    }
                    // $return[]="$function $attrid $sum";
                    if ($function === "MOY") {
                        $sum= $sum/count($results);
                    }
                        $return[] = $document->getHtmlValue($this->_searchfamily->getAttribute($attrid), $sum);
                    break;
                default:
                    $return[]="";
            }
        }


        return $return;
    }
    public function update($resourceId)
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405","You cannot update");

        throw $exception;
    }


    public function delete($resourceId)
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot delete");

        throw $exception;
    }

    protected function prepareSearchDoc()
    {
        $this->_searchDoc = new \SearchDoc("",-1);

        $this->_searchDoc->setObjectReturn();


    }
}
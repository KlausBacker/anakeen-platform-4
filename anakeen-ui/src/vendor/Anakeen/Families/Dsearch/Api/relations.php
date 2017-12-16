<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 06/05/15
 * Time: 09:26
 */
namespace Dcp\Search\html5;

use Dcp\HttpApi\V1\Crud\DocumentCollection;
use Dcp\HttpApi\V1\Crud\Exception;
use Dcp\HttpApi\V1\DocManager\DocManager;

class relations extends DocumentCollection {

    protected $_collection = null;
    protected $_attrid = null;
    protected $_family = null;


    public function create()
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405","You cannot create");

        throw $exception;
    }


    public function read($resourceId)
    {

        $return = array();

        $fdoc = new_Doc("", $this->_family);
        foreach ($fdoc->getNormalAttributes() as $myAttribute) {
            if ($myAttribute->id == $this->_attrid){
                $s = new \SearchDoc("", $myAttribute->format);
                if (isset($this->contentParameters["slice"])) {
                    $s->setSlice($this->contentParameters["slice"]);
                }
                if (isset($this->contentParameters["offset"])){
                    $s->setStart($this->contentParameters["offset"]);
                }
                if (isset($this->contentParameters["keyword"]) && !empty($this->contentParameters["keyword"]))
                {
                    $s->addFilter("title ~* '%s'", $this->contentParameters["keyword"]);
                }
                $research = $s->search();
                foreach ($research as $k => $v) {
                    $return[] = array(
                        "id" => $v["id"],
                        "htmlTitle" => htmlspecialchars($v["title"]) ,
                        "title" => $v["title"]
                    );
                }
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

    public function setUrlParameters(Array $array)
    {
        parent::setUrlParameters($array);
        if (isset($this->urlParameters["familyId"])) {
            $this->_family = $this->urlParameters["familyId"];
        }
        if (isset($this->urlParameters["attributeId"])) {
            $this->_attrid = $this->urlParameters["attributeId"];
        }
    }

}
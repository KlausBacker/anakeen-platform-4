<?php 

  namespace Dcp\Search\html5;
  
  use Dcp\HttpApi\V1\Crud\DocumentsUtils;
  use Dcp\HttpApi\V1\Crud\Exception;
  use Dcp\HttpApi\V1\Crud\DocumentCollection;
  use Dcp\HttpApi\V1\DocManager\DocManager;
  
  class families extends DocumentCollection
  {
  
    protected $_collection = null;
    
    public function create()
    {
      $exception = new Exception("CRUD0103", __METHOD__);
      $exception->setHttpStatus("405","You cannot create");
      
      throw $exception;
    }
    
    
    public function read($resourceId)
    {
      $return = parent::read($resourceId);


      foreach($return["documents"] as &$currentDoc) {

        $tmpDoc = createTmpDoc("", $currentDoc["properties"]["id"]);

        $allAttributes = $tmpDoc->getNormalAttributes();
        $myAttributes = array();
        foreach ($allAttributes as $myAttribute) {

          $myAttributes[] = array("label" => $myAttribute->getLabel(), "id" => $myAttribute->id, "type" => $myAttribute->type);

        }

        $currentDoc["attributes"] = $myAttributes;
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
	
      if (isset($this->contentParameters["keyword"]) && !empty($this->contentParameters["keyword"]))
      {
          $this->_searchDoc->addFilter("title ~* '%s'", preg_quote($this->contentParameters["keyword"]));
      }
    }
    
    
  }
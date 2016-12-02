<?php
require_once ("FDL/Class.Doc.php");
function documentlistdata(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("List document family");
    $familyId = $usage->addRequiredParameter("family", "Family identifier");
    $searchTitle = $usage->addOptionalParameter("searchtitle", "Search by title");
    $searchName = $usage->addOptionalParameter("searchname", "Search by name");
    $searchId = $usage->addOptionalParameter("searchid", "Search by id");
    $slice = $usage->addOptionalParameter("slice", "slice", null, 50);
    $page = $usage->addOptionalParameter("page", "page", null, 0);
    $usage->setStrictMode(false);
    $usage->verify();
    
    $err = "";
    /**
     * @var DocFam $family
     */
    $family = new_Doc("", $familyId);
    if (!$family->isAffected()) {
        $err = ("Undefined family");
    }
    
    $search = new SearchDoc($action->dbaccess, $family->id);
    $search->setStart($slice * $page);
    $search->setSlice($slice + 1);
    $ds = new DevelSearchDocument($search, $family);
    
    if ($searchId) {
        $ds->searchById($searchId);
    } elseif ($searchName) {
        $ds->searchByName($searchName);
    } elseif ($searchTitle) {
        $ds->searchByTitle($searchTitle);
    }
    $result = $ds->getResults();
    
    $info = [];
    foreach ($result as $doc) {
        $info[] = ["id" => $doc->id, "name" => $doc->name, "title" => $doc->title, "icon" => $doc->getIcon('', 16) ];
    }
    
    header('Content-Type: application/json');
    
    if ($err) {
        header("HTTP/1.0 400 Error");
        $response = ["success" => false, "error" => $err];
    } else {
        if (count($result) > $slice) {
            $nextUrl = sprintf("?app=%s&action=%s&family=%s&slice=%d&page=%d", $action->parent->name, $action->name, $family->name, $slice, $page + 1);
        } else {
            $nextUrl = null;
        }
        
        $response = array(
            "success" => true,
            "results" => $info,
            "message" => $ds->getMessage() ,
            "start" => $slice * $page,
            "end" => $slice * ($page + 1) ,
            "next" => ["url" => $nextUrl,
            "label" => sprintf(___("Next %d %s >>>", "dcpdev") , $slice, $family->getTitle()) ]
        );
    }
    $action->lay->noparse = true;
    $action->lay->template = json_encode($response);
}

class DevelSearchDocument
{
    
    protected $search;
    protected $searchType;
    /**
     * @var DocFam
     */
    protected $family;
    protected $message;
    protected $searchKey;
    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }
    /**
     * @param SearchDoc $search
     */
    public function __construct(\SearchDoc $search, \DocFam $family)
    {
        $this->search = $search;
        $this->search->setObjectReturn(true);
        $this->search->setOrder("name, title, id");
        $this->search->returnsOnly(array(
            "name",
            "icon"
        ));
        $this->search->fromid = $family->id;
        $this->family = $family;
    }
    
    public function searchByTitle($title)
    {
        
        $this->search->addFilter("title ~* '%s'", preg_quote($title));
        $this->searchType = "title";
        $this->searchKey = $title;
        $this->search->setOrder("name, title, id");
    }
    public function searchByName($name)
    {
        
        if ($name === "*") {
            $this->search->addFilter("name is not null and name != ''");
            $this->searchType = "name*";
        } else {
            $this->search->addFilter("name ~* '%s'", preg_quote($name));
            $this->searchType = "name";
        }
        $this->search->setOrder("name, id");
        $this->searchKey = $name;
    }
    public function searchById($id)
    {
        
        $this->search->addFilter("id = %d", $id);
        $this->searchType = "id";
        $this->searchKey = $id;
    }
    
    public function getResults()
    {
        $this->search->search();
        switch ($this->searchType) {
            case 'id':
                if ($this->search->count() === 0) {
                    
                    $this->search->fromid = 0;
                    $this->search->reset();
                    $this->search->search();
                    if ($this->search->count() > 0) {
                        $this->message = sprintf(___("This document is not a member of family \"%s\"", "dcpdev") , $this->family->getTitle());
                    }
                }
                break;

            case 'name':
                if ($this->search->count() === 0) {
                    $this->search->fromid = 0;
                    $this->search->reset();
                    $this->search->search();
                    if ($this->search->count() === 1) {
                        $this->message = sprintf(___("This document is not a member of family \"%s\"", "dcpdev") , $this->family->getTitle());
                    } elseif ($this->search->count() > 0) {
                        $this->message = sprintf(___("These documents are not members of family \"%s\"", "dcpdev") , $this->family->getTitle());
                    }
                }
            default:
        }
        if ($this->search->count() === 0) {
            switch ($this->searchType) {
                case 'id':
                    $this->message = sprintf(___("No documents with id \"%s\"", "dcpdev") , $this->searchKey);
                    break;

                case 'name':
                    $this->message = sprintf(___("No documents name match \"%s\"", "dcpdev") , $this->searchKey);
                    break;

                case 'name*':
                    $this->message = sprintf(___("No \"%s\" documents has a name", "dcpdev") , $this->family->getTitle());
                    break;

                case 'title':
                    $this->message = sprintf(___("No one \"%s\" documents title match \"%s\"", "dcpdev") , $this->family->getTitle() , $this->searchKey);
                    break;

                default:
                    $this->message = sprintf(___("No one \"%s\" documents exists", "dcpdev") , $this->family->getTitle());
            }
        }
        return $this->search->getDocumentList();
}
}

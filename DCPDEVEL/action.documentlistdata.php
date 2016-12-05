<?php
require_once ("FDL/Class.Doc.php");
function documentlistdata(Action & $action)
{
    
    $err = "";
    
    $documentData = new DevelDocumentData($action);
    $ds = $documentData->getSearchResult();
    
    $s = $ds->getSearch();
    $s->returnsOnly(array(
        "name",
        "icon"
    ));
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
        $family = $ds->getFamily();
        
        if (count($result) > $documentData->getSlice()) {
            $nextUrl = sprintf("?app=%s&action=%s&family=%s&slice=%d&page=%d", $action->parent->name, $action->name, $family->name, $documentData->getSlice() , $documentData->getpage() + 1);
        } else {
            $nextUrl = null;
        }
        
        $response = array(
            "success" => true,
            "results" => $info,
            "message" => $ds->getMessage() ,
            "next" => ["url" => $nextUrl,
            "label" => sprintf(___("Next %d %s >>>", "dcpdev") , $documentData->getSlice() , $family->getTitle()) ]
        );
    }
    $action->lay->noparse = true;
    $action->lay->template = json_encode($response);
}

function exportDocuments(Action & $action)
{
    
    $documentData = new DevelDocumentData($action);
    $ds = $documentData->getSearchResult();
    $s = $ds->getSearch();
    
    $separator = $action->getArgument("csvseparator");
    if (!$separator) {
        $separator = $action->getParam("CSV_SEPARATOR", ";");
    } else {
        $action->setParamU("CSV_SEPARATOR", $separator);
    }
    
    $enclosure = $action->getArgument("csvenclosure");
    if (!$enclosure) {
        $enclosure = $action->getParam("CSV_ENCLOSURE", '"');
    } else {
        $action->setParamU("CSV_ENCLOSURE", $enclosure);
    }
    
    $s->setOrder("fromid, name, title, id");
    $s->setStart(0);
    $s->setSlice("ALL");
    $exportCollection = new Dcp\ExportCollection();
    $exportCollection->setDocumentlist($s->getDocumentList());
    $exportCollection->setCvsEnclosure($enclosure);
    $exportCollection->setCvsSeparator($separator);
    $exportCollection->setExportProfil(false);
    
    $foutname = sprintf("%s/%s.csv", getTmpDir() , uniqid("export"));
    $exportCollection->setOutputFilePath($foutname);
    
    $exportCollection->export();
    $others = $profils = [];
    if (file_exists($foutname)) {
        // Sort profil
        if (($handle = fopen($foutname, "r")) !== false) {
            while (($data = fgetcsv($handle, 0, $separator, $enclosure)) !== false) {
                if ($data && $data[0] === "PROFIL") {
                    $profils[] = $data;
                } else {
                    $others[] = $data;
                }
            }
            fclose($handle);
        }
        
        $sortFile = sprintf("%s/%s.csv", getTmpDir() , uniqid("export"));
        
        if (($handle = fopen($sortFile, "w")) !== false) {
            $contents = array_merge($others, $profils);
            
            foreach ($contents as $dataLine) {
                fputcsv($handle, $dataLine, $separator, $enclosure);
            }
            fclose($handle);
        }
        
        $fname = sprintf("%s-exports.csv", $family = $ds->getFamily()->name);
        Http_DownloadFile($sortFile, $fname, "text/csv", false, false, true);
    } else {
        $action->exitError("Error export");
    }
}

class DevelDocumentData
{
    protected $ds;
    protected $action;
    protected $slice;
    protected $page;
    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }
    /**
     * @return mixed
     */
    public function getSlice()
    {
        return $this->slice;
    }
    
    public function __construct(Action & $action)
    {
        $this->action = $action;
    }
    /**
     * @return DevelSearchDocument
     * @throws \Dcp\Exception
     */
    public function getSearchResult()
    {
        $usage = new ActionUsage($this->action);
        $usage->setDefinitionText("List document family");
        $familyId = $usage->addRequiredParameter("family", "Family identifier");
        $searchTitle = $usage->addOptionalParameter("searchtitle", "Search by title");
        $searchName = $usage->addOptionalParameter("searchname", "Search by name");
        $searchId = $usage->addOptionalParameter("searchid", "Search by id");
        $this->slice = $usage->addOptionalParameter("slice", "slice", null, 50);
        $this->page = $usage->addOptionalParameter("page", "page", null, 0);
        $usage->setStrictMode(false);
        $usage->verify();
        /**
         * @var DocFam $family
         */
        $family = new_Doc("", $familyId);
        if (!$family->isAffected()) {
            throw new \Dcp\Exception("Undefined family");
        }
        
        $search = new SearchDoc($this->action->dbaccess, $family->id);
        $search->setStart($this->slice * $this->page);
        $search->setSlice($this->slice + 1);
        $this->ds = new DevelSearchDocument($search, $family);
        
        if ($searchId) {
            $this->ds->searchById($searchId);
        } elseif ($searchName) {
            $this->ds->searchByName($searchName);
        } elseif ($searchTitle) {
            $this->ds->searchByTitle($searchTitle);
        }
        return $this->ds;
    }
}

class DevelSearchDocument
{
    
    protected $search;
    
    protected $searchType;
    /**
     * @var DocFam
     */
    protected $family;
    /**
     * @return DocFam
     */
    public function getFamily()
    {
        return $this->family;
    }
    
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
     * @param DocFam    $family
     */
    public function __construct(\SearchDoc $search, \DocFam $family)
    {
        $this->search = $search;
        $this->search->setObjectReturn(true);
        $this->search->setOrder("name, title, id");
        $this->search->fromid = $family->id;
        $this->family = $family;
    }
    
    public function searchByTitle($title)
    {
        
        $words = explode(" ", $title);
        
        foreach ($words as $word) {
            if (trim($word)) {
                
                $this->search->addFilter("title ~* '%s'", preg_quote(trim($word)));
            }
        }
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
    /**
     * @return SearchDoc
     */
    public function getSearch()
    {
        return $this->search;
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
                        $this->message = sprintf(___("No documents with id \"%s\" in family \"%s\"", "dcpdev") , $this->searchKey, $this->family->getTitle());
                        $this->message.= "\n";
                        $this->message.= sprintf(___("Get result for search id \"%s\" in all families", "dcpdev") , $this->searchKey);
                    }
                }
                break;

            case 'name':
                if ($this->search->count() === 0) {
                    $this->search->fromid = 0;
                    $this->search->reset();
                    $this->search->search();
                    
                    $this->message = sprintf(___("No documents with name \"%s\" in family \"%s\"", "dcpdev") , $this->searchKey, $this->family->getTitle());
                    $this->message.= "\n";
                    $this->message.= sprintf(___("Get results for search name \"%s\" in all families", "dcpdev") , $this->searchKey);
                    $this->message.= "\n";
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

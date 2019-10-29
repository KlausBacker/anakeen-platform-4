<?php



class TrashSearchSmartData extends Anakeen\Search\Internal\SearchSmartData
{
    public function getQueries()
    {
        $sql = <<<SQL
select distinct on(docread.initid) docread.*, dochisto.uname as deluser, dochisto.date as deldate  
from docread, dochisto 
where docread.id= dochisto.id and docread.doctype='Z' and dochisto.code = 'DELETE'
SQL;
        return [$sql];
    }
}


$s = new TrashSearchSmartData();

$s->orderby = "revision desc, title";
$list = $s->search();

foreach ($list as $data) {
    printf("%s :%s %s [%s]\n", $data['revision'], $data["initid"], $data["deluser"], $data["mdate"]);
}
print_r($s->getSearchInfo());

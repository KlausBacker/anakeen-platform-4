<?php

$s = new \Anakeen\Search\SearchElements();

$filter = new \Anakeen\Fullsearch\FilterMatch("testSearchDomain", "Bill");
//$s->setSlice(10);
$s->addFilter($filter);
$results = $s->search()->getResults();
print_r($results);

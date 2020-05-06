<?php

const filterValue = [
    "kind" => "property",
    "logic" => "and",
    "filter" => [
        "property" => "title",
        "operator" => "contain",
        "value" => "ALVA"
    ],
    "filters" => [
        [
            "kind" => "property",
            "logic" => "and",
            "filter" => [
                "property" => "title",
                "operator" => "notcontain",
                "value" => "04"
            ],
            "filters" => []
        ]
    ]
];

$s = new \Anakeen\Search\SearchElements("DEVBILL");
$s->addFilter(new Anakeen\Search\SearchCriteria\SearchCriteria(filterValue));
$res = $s->search()->getResults();
print "RESULTS : ";
print_r($s->getSearchInfo());
foreach ($res as $r) {
    print $r->getTitle() . "\n";
}

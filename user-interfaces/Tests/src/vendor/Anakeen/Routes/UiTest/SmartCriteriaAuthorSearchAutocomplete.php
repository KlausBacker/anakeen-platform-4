<?php


namespace Anakeen\Routes\UiTest;

use Anakeen\Search\Filters\Title\Contains;
use Anakeen\Search\SearchElements;
use Anakeen\SmartAutocomplete;
use Anakeen\SmartElementManager;

class SmartCriteriaAuthorSearchAutocomplete
{
    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, $args)
    {
        $autocomplete = new SmartAutocomplete($request, $response);

        // Get filter form input value
        $inputValue = $autocomplete->getFilterValue();
        $autoCompleteData = [];

        $search = new SearchElements("DEVPERSON");
        $search->setSlice(10);
        $search->addFilter(new Contains("title", $inputValue, Contains::NOCASE));
        $res = $search->search()->getResults();
        foreach ($res as $r) {
            $autocompleteEntry = [
                "Author" => [
                    "value" => $r->initid,
                    "displayValue" => "MY_PREFIX : " . $r->title
                ]
            ];
            $autoCompleteData[] = $autocompleteEntry;
        }


        $autocomplete->setEntryData($autoCompleteData);

        return $autocomplete->getResponse();
    }
}

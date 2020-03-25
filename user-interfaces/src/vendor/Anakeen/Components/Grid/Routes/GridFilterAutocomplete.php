<?php

namespace Anakeen\Components\Grid\Routes;

use Anakeen\Core\SEManager;
use Anakeen\SmartAutocomplete;
use Anakeen\SmartStructures\Wdoc\WDocHooks;
use Slim\Http\Request;
use Slim\Http\Response;
use SmartStructure\Fields\Search;

class GridFilterAutocomplete
{
    protected $searchElements = null;

    public static function stateAutocomplete(Request $request, Response $response, array $args)
    {
        $result = [];
        $autocomplete = new SmartAutocomplete($request, $response);
        $filterValue = $autocomplete->getFilterValue();

        $collectionId = $args["collectionId"];
        $collection = SEManager::getDocument($collectionId);
        if (!empty($collection)) {
            $wid = null;
            switch ($collection->defDoctype) {
                case 'C':
                    $wid = $collection->wid;
                    break;
                case 'S':
                    $fromId = $collection->getRawValue(Search::se_famid, 0);
                    $structureElement = SEManager::getDocument($fromId);
                    if (!empty($structureElement)) {
                        $wid = $structureElement->wid;
                    }
                    break;
                default:
                    break;
            }

            if (!empty($wid)) {
                /**
                 * @var WDocHooks $wdoc
                 */
                $wdoc = SEManager::getDocument($wid);
                foreach ($wdoc->getStates() as $state) {
                    if (empty($filterValue) || strpos(strtolower($wdoc->getStateLabel($state)), strtolower($filterValue)) !== false) {
                        $result[] = [
                            "stateValue" => [
                                "value" => $state,
                                "displayValue" => $wdoc->getStateLabel($state)
                            ],
                            "stateColor" => $wdoc->getColor($state),
                            "stateLabel" => $wdoc->getStateLabel($state)
                        ];
                    }
                }

                $autocomplete->setEntryData($result);
                $entryLabel = <<<HTML
            <span class="smart-element-grid-filter-form-state-entry">
                <span class="smart-element-grid-filter-form-state-entry--color" style="background-color: {{stateColor}}"></span>
                <span class="smart-element-grid-filter-form-state-entry--label">{{ stateLabel }}</span>
            </span>
HTML;
                $autocomplete->setEntryLabel($entryLabel);
            }
        }

        if (empty($result)) {
            $autocomplete->setError(___("No state found", "grid-component"));
        }
        return $autocomplete->getResponse();
    }
}

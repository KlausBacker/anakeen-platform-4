<?php

namespace Anakeen\SmartStructures\Cvdoc;

use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;

class CVDocAutocomplete
{
    /**
     * get mail address from MAILRECIPIENT Smart Structures
     *
     *
     * @param SmartAutocompleteRequest  $request
     * @param SmartAutocompleteResponse $response
     * @param array                     $args
     * @return SmartAutocompleteResponse
     */
    public static function getViews(SmartAutocompleteRequest $request, SmartAutocompleteResponse $response, $args): SmartAutocompleteResponse
    {
        $tlview = $args["labelviews"];
        if (is_array($args["idviews"])) {
            foreach ($args["idviews"] as $k => $v) {
                $currentViewId = trim($v);
                if ('' !== $currentViewId) {
                    $currentViewlabel = $tlview[$k];
                    $response->appendEntry(
                        sprintf("%s <i>(%s)</i>", xml_entity_encode($currentViewlabel), xml_entity_encode($currentViewId)),
                        [
                            $currentViewId,
                            sprintf("%s (%s)", $currentViewlabel, $currentViewId)
                        ]
                    );
                }
            }
        }
        return $response;
    }
}

<?php

namespace Anakeen\Pu\SmartStructures;

class TestAutoCompleteHook
{
    /**
     * @param SmartAutocompleteRequest $request
     * @param SmartAutocompleteResponse $response
     * @return SmartAutocompleteResponse
     */
    public static function testCallableFunction(
        SmartAutocompleteRequest $request,
        SmartAutocompleteResponse $response
    ): SmartAutocompleteResponse {
    }
}

<?php

namespace Anakeen\Pu\Config;

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

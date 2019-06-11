<?php

namespace Anakeen\SmartStructures\Task\Render;

use Anakeen\Router\Config\RouterInfo;
use Anakeen\Router\RouterManager;
use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;

class RouteAutoComplete
{

    const limit = 50;

    /**
     * sub set of Smart Structure
     *
     *
     * @param SmartAutocompleteRequest  $request
     * @param SmartAutocompleteResponse $response
     * @param                           $args
     * @return SmartAutocompleteResponse
     */
    public function __invoke(SmartAutocompleteRequest $request, SmartAutocompleteResponse $response, $args): SmartAutocompleteResponse
    {
        $routes = RouterManager::getRoutes();


        $name = strtolower($request->getFilterValue());

        ksort($routes);

        $pattern_name = preg_quote($name, "/");
        $resultCount = 0;
        /**
         * @var             $kRoute
         * @var  RouterInfo $route
         */
        foreach ($routes as $kRoute => $route) {
            if (($name == "") || (preg_match("/$pattern_name/i", $kRoute.$route->description, $reg))) {
                list($ns, $base) = explode("::", $kRoute);
                $method = null;
                if (count($route->methods) === 1) {
                    $method = $route->methods[0];
                }

                $label = sprintf(
                    "<p><code>%s : </code><b>%s</b>::<b>%s</b></p><p>&nbsp;&nbsp;&nbsp;<cite>%s</cite></p>",
                    \Anakeen\Core\Utils\Strings::xmlEncode(implode(", ", $route->methods)),
                    \Anakeen\Core\Utils\Strings::xmlEncode($ns),
                    \Anakeen\Core\Utils\Strings::xmlEncode($base),
                    \Anakeen\Core\Utils\Strings::xmlEncode($route->description)
                );
                $response->appendEntry(
                    $label,
                    [
                        [
                            "value" => $ns,
                        ],
                        [
                            "value" => $base,
                        ],
                        [
                            "value" => $method,
                        ],
                    ]
                );
                $resultCount++;
                if ($resultCount > self::limit) {
                    break;
                }
            }
        }
        return $response;
    }
}

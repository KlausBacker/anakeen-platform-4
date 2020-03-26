<?php

namespace Anakeen\Router;

class TokenAuthenticator extends \Anakeen\Core\Internal\OpenAuthenticator
{
    const AUTHORIZATION_SCHEME = "Token";

    public static function getTokenId()
    {
        if (!empty($_GET[self::openGetId])) {
            return $_GET[self::openGetId];
        }

        $hAuthorization = \Anakeen\Router\AuthenticatorManager::getAuthorizationValue();

        if (!empty($hAuthorization)) {
            if ($hAuthorization["scheme"] === self::AUTHORIZATION_SCHEME) {
                return $hAuthorization["token"];
            }
        }

        return "";
    }

    /**
     * Verify if UserToken match a recorded route
     * @param \UserToken $token
     * @return bool
     */
    public static function verifyOpenAccess(\UserToken $token)
    {
        if ($token->type !== "ROUTE") {
            return false;
        }
        $rawContext = $token->context;
        $allow = false;
        if ($rawContext === null) {
            return false;
        }

        $url = $_SERVER["REDIRECT_URL"] ?? $_SERVER["REQUEST_URI"];

        $context = unserialize($rawContext);
        $requestMethod = strtoupper($_SERVER["REQUEST_METHOD"]);

        if (is_array($context)) {
            $allow = false;
            foreach ($context as $k => $rules) {
                if (is_array($rules)) {
                    if (!empty($rules["pattern"])) {
                        $methodAllowed = false;
                        foreach ($rules["methods"] as $expectedMethod) {
                            if ($expectedMethod === "*" || strtoupper($expectedMethod) === $requestMethod) {
                                $methodAllowed = true;
                                break;
                            }
                        }

                        if ($methodAllowed) {
                            $routePattern = $rules["pattern"];
                            if (RouterLib::matchPattern($routePattern, $url)) {
                                $allow = true;
                                break;
                            }
                        }
                    }
                } else {
                    // Simple route
                    if (preg_match("/^(GET|POST|PUT|DELETE|PATCH|\\*)\\s+(.*)/", $rules, $reg)) {
                        $expectedMethod = $reg[1];
                        $routePattern = $reg[2];
                    } else {
                        continue;
                    }
                    if (strtoupper($expectedMethod) === $requestMethod) {
                        if (RouterLib::matchPattern($routePattern, $url)) {
                            $allow = true;
                            break;
                        }
                    }
                }
            }
        }

        return $allow;
    }
}

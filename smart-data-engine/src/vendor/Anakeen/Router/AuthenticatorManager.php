<?php

namespace Anakeen\Router;

class AuthenticatorManager extends \Anakeen\Core\Internal\AuthenticatorManager
{
    protected static $authType;

    protected static function getAuthenticatorClass($authtype = null, $provider = \Anakeen\Core\Internal\Authenticator::nullProvider)
    {
        if (!$authtype) {
            $authtype = self::getAuthType();
        }
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $authtype)) {
            throw new Exception(sprintf("Invalid authtype '%s'", $authtype));
        }

        $auth = null;
        $ta = AuthenticatorManager::getAuthTypeParams();
        if (!empty($ta["class"])) {
            $authClass = $ta["class"];
            if (!\Anakeen\Core\Internal\Autoloader::classExists($authClass)) {
                throw new Exception("API0100", $authtype);
            }
            $auth = new $authClass($authtype, $provider);
        } else {
            switch ($authtype) {
                case "html":
                    $auth = new \Anakeen\Core\Internal\HtmlAuthenticator($authtype, $provider);
                    break;

                case "token":
                    $auth = new TokenAuthenticator($authtype, $provider);
                    break;

                case "basic":
                    $auth = new \Anakeen\Core\Internal\BasicAuthenticator($authtype, $provider);
                    break;

                default:
                    throw new Exception("API0109", $authtype);
            }
        }

        return $auth;
    }

    /**
     * @param \Anakeen\Core\Account $userAccount account identify use for the token
     * @param array                 $routes      list of routes matches
     * @param int|\DateTime         $expiration  if it is a number, is use as a delay in seconds, if it is a DateTime object use as end validity date
     * @param bool                  $oneshot     if true the token can be used only on time (it is destroyed after use)
     * @param string                $description text description
     *
     * @return string   return the token identifier
     * @throws Exception
     */
    public static function getAuthorizationToken(\Anakeen\Core\Account $userAccount, array $routes, $expiration = -1, bool $oneshot = false, $description = "")
    {
        if ($expiration === -1) {
            $expiration = \UserToken::INFINITY;
        }

        if (count($routes) === 0) {
            throw new Exception("ROUTER0105");
        }

        foreach ($routes as $k => $rules) {
            if (is_array($rules)) {
                if (empty($rules["pattern"])) {
                    throw new Exception("ROUTER0101", $k);
                }

                $methods = $rules["methods"];
                $queries = isset($rules["query"]) ? $rules["query"] : [];
                $route = $rules["pattern"];
            } else {
                // Simple route
                if (preg_match("/^(GET|POST|PUT|DELETE|PATCH)\\s+(.*)/", $rules, $reg)) {
                    $method = $reg[1];
                    $rules = $reg[2];
                } else {
                    $method = "*";
                }

                $methods = [$method];
                $queries = [];
                $route = $rules;
            }


            if (strlen($route) < 2) {
                throw new Exception("ROUTER0102", $route);
            }
            if (empty($methods) || !is_array($methods)) {
                throw new Exception("ROUTER0103");
            }

            $sParser = new \FastRoute\RouteParser\Std;
            try {
                $sParser->parse($route);
            } catch (\FastRoute\BadRouteException $e) {
                throw new Exception("ROUTER0109", $route, $e->getMessage());
            }

            $routes[$k] = ["pattern" => $route, "methods" => $methods, "query" => $queries];
        }

        $scontext = serialize($routes);

        if (!$userAccount->isAffected()) {
            throw new Exception("ROUTER0106");
        }
        // create one
        $uk = new \UserToken("");
        $uk->userid = $userAccount->id;
        $uk->token = $uk->genToken();
        if (is_a($expiration, "\\DateTime")) {
            /**
             * @var \DateTime $expiration
             */
            $uk->expire = $expiration->format("Y-m-d H:i:s");
        } else {
            $uk->expire = $uk->setExpiration($expiration);
        }
        $uk->expendable = $oneshot;
        $uk->type = "ROUTE";
        $uk->context = $scontext;
        $uk->description = $description;
        $err = $uk->add();
        if ($err) {
            throw new Exception("ROUTER0104", $err);
        }
        $token = $uk->token;

        return $token;
    }
}

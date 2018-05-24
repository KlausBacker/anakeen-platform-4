<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Main first level function
 *
 * @author     Anakeen
 * @version    $Id: Lib.Common.php,v 1.50 2008/09/11 14:50:04 eric Exp $
 * @package    FDL
 * @subpackage CORE
 */
/**
 */
include_once("WHAT/Lib.Common.php");
/**
 * @param \Anakeen\Core\Internal\Authenticator $auth
 * @param \Anakeen\Core\Internal\Action        $action
 * @deprecated
 */
function getMainAction($auth, &$action)
{

    $CoreNull = "";

    global $_GET;
    $defaultapp = false;
    if (!getHttpVars("app")) {
        $defaultapp = true;
        $_GET["app"] = "CORE";
        if (!empty($_SERVER["FREEDOM_ACCESS"])) {
            $_GET["app"] = $_SERVER["FREEDOM_ACCESS"];
            $_GET["action"] = "";
        } else {
            $defaultapp = false;
            $_GET["action"] = "INVALID";
        }
    }

    if (isset($auth->auth_session)) {
        $session = $auth->auth_session;
    } else {
        $session = new \Anakeen\Core\Internal\Session();
        if (isset($_COOKIE[\Anakeen\Core\Internal\Session::PARAMNAME])) {
            $sess_num = $_COOKIE[\Anakeen\Core\Internal\Session::PARAMNAME];
        } else {
            $sess_num = GetHttpVars(\Anakeen\Core\Internal\Session::PARAMNAME);
        } //$_GET["session"];
        if (!$session->Set($sess_num)) {
            print "<strong>:~((</strong>";
            exit;
        };
    }
    $core = new \Anakeen\Core\Internal\Application();
    $core->Set("CORE", $CoreNull, $session);

    if (isset($_SERVER['PHP_AUTH_USER']) && ($core->user->login != $_SERVER['PHP_AUTH_USER'])) {
        // reopen a new session
        $session->Set("");
        $core->SetSession($session);
    }
    if ($defaultapp && $core->GetParam("CORE_START_APP")) {
        $_GET["app"] = $core->GetParam("CORE_START_APP");
    }

    \Dcp\Core\LibPhpini::setCoreApplication($core);
    \Dcp\Core\LibPhpini::applyLimits();
    //$core->SetSession($session);
    // ----------------------------------------
    // Init PUBLISH URL from script name
    initMainVolatileParam($core, $session);
    // ----------------------------------------
    // Init Application & Actions Objects
    $appl = new \Anakeen\Core\Internal\Application();
    $err = $appl->Set(getHttpVars("app"), $core, $session);
    if ($err) {
        print $err;
        exit;
    }
    // ----------------------------------------

    // -----------------------------------------------
    // now we are in correct protocol (http or https)
    $action = new \Anakeen\Core\Internal\Action();
    $action->Set(getHttpVars("action"), $appl);

    if ($auth) {
        $core_lang = $auth->getSessionVar('CORE_LANG');
        if ($core_lang != '') {
            $action->setParamU('CORE_LANG', $core_lang);
            $auth->setSessionVar('CORE_LANG', '');
        }
        $action->auth = &$auth;
        $core->SetVolatileParam("CORE_BASICAUTH", '&authtype=basic');
    } else {
        $core->SetVolatileParam("CORE_BASICAUTH", '');
    }

    initExplorerParam($core);
    // init for gettext
    \Anakeen\Core\ContextManager::setLanguage($action->Getparam("CORE_LANG"));

}

/**
 * @deprecated
 */
function stripUrlSlahes($url)
{
    $pos = mb_strpos($url, '://');
    return mb_substr($url, 0, $pos + 3) . preg_replace('/\/+/u', '/', mb_substr($url, $pos + 3));
}

/**
 * init user agent volatile param
 *
 * @param \Anakeen\Core\Internal\Application $app
 * @param mixed       $defaultValue
 * @deprecated
 */
function initExplorerParam(\Anakeen\Core\Internal\Application & $app, $defaultValue = false)
{
    $explorerP = getExplorerParamtersName();
    foreach ($explorerP as $ep) {
        $app->SetVolatileParam($ep, $defaultValue);
    }
    if (!empty($_SERVER["HTTP_HOST"])) {
        initExplorerWebParam($app);
    }
}

/**
 * @deprecated
 */
function getExplorerParamtersName()
{
    return array(
        "ISIE",
        "ISIE6",
        "ISIE7",
        "ISIE8",
        "ISIE9",
        "ISIE10",
        "ISAPPLEWEBKIT",
        "ISSAFARI",
        "ISCHROME"
    );
}

/**
 * set volatile patram to detect web user agent
 *
 * @deprecated see Anakeen\Core\Internal\GlobalParametersManager
 * @param \Anakeen\Core\Internal\Application $app
 */
function initExplorerWebParam(\Anakeen\Core\Internal\Application & $app)
{
    $nav = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $pos = strpos($nav, "MSIE");
    if ($app->session->Read("navigator", "") == "") {
        if ($pos !== false) {
            $app->session->Register("navigator", "EXPLORER");
            if (preg_match("/MSIE ([0-9.]+).*/", $nav, $reg)) {
                $app->session->Register("navversion", $reg[1]);
            }
        } else {
            $app->session->Register("navigator", "NETSCAPE");
            if (preg_match("|([a-zA-Z]+)/([0-9.]+).*|", $nav, $reg)) {
                $app->session->Register("navversion", $reg[2]);
            }
        }
    }

    $ISIE6 = false;
    $ISIE7 = false;
    $ISIE8 = false;
    $ISIE9 = false;
    $ISIE10 = false;
    $ISAPPLEWEBKIT = false;
    $ISSAFARI = false;
    $ISCHROME = false;
    if (preg_match('/MSIE ([0-9]+).*/', $nav, $match)) {
        switch ($match[1]) {
            case "6":
                $ISIE6 = true;
                break;

            case "7":
                $ISIE7 = true;
                break;

            case "8":
                $ISIE8 = true;
                break;

            case "9":
                $ISIE9 = true;
                break;

            case "10":
                $ISIE10 = true;
                break;
        }
    } elseif (preg_match('|\bAppleWebKit/(.*?)\b|', $nav, $match)) {
        $ISAPPLEWEBKIT = true;
        if (preg_match('|\bChrome/(.*?)\b|', $nav, $match)) {
            $ISCHROME = true;
        } elseif (preg_match('|\bSafari/(.*?)\b|', $nav, $match)) {
            $ISSAFARI = true;
        }
    }

    $app->SetVolatileParam("ISIE", ($app->session->read("navigator") == "EXPLORER"));
    $app->SetVolatileParam("ISIE6", ($ISIE6 === true));
    $app->SetVolatileParam("ISIE7", ($ISIE7 === true));
    $app->SetVolatileParam("ISIE8", ($ISIE8 === true));
    $app->SetVolatileParam("ISIE9", ($ISIE9 === true));
    $app->SetVolatileParam("ISIE10", ($ISIE10 === true));
    $app->SetVolatileParam("ISAPPLEWEBKIT", ($ISAPPLEWEBKIT === true));
    $app->SetVolatileParam("ISSAFARI", ($ISSAFARI === true));
    $app->SetVolatileParam("ISCHROME", ($ISCHROME === true));
}

/**
 * Set various core URLs params
 *
 * @param \Anakeen\Core\Internal\Application $core
 * @param \Anakeen\Core\Internal\Session     $session
 * @deprecated see Anakeen\Core\Internal\GlobalParametersManager
 */
function initMainVolatileParam(\Anakeen\Core\Internal\Application & $core, \Anakeen\Core\Internal\Session & $session = null)
{
    if (php_sapi_name() == 'cli') {
        _initMainVolatileParamCli($core);
    } else {
        _initMainVolatileParamWeb($core, $session);
    }
}

/**
 * @deprecated see Anakeen\Core\Internal\GlobalParametersManager
 */
function _initMainVolatileParamCli(\Anakeen\Core\Internal\Application & $core)
{
    $absindex = $core->GetParam("CORE_URLINDEX");

    $core_externurl = ($absindex) ? stripUrlSlahes($absindex) : ".";
    $core_mailaction = $core->getParam("CORE_MAILACTION");
    $core_mailactionurl = ($core_mailaction != '') ? ($core_mailaction) : ($core_externurl . "api/v2/documents/%INITID%.html");

    $core->SetVolatileParam("CORE_EXTERNURL", $core_externurl);
    $core->SetVolatileParam("CORE_ABSURL", $core_externurl); // absolute links
    $core->SetVolatileParam("CORE_JSURL", "WHAT/Layout");
    $core->SetVolatileParam("CORE_BASEURL", "$absindex?sole=A&");
    $core->SetVolatileParam("CORE_STANDURL", "$absindex?sole=Y&");
    $core->SetVolatileParam("CORE_MAILACTIONURL", $core_mailactionurl);
}

/**
 * @param \Anakeen\Core\Internal\Application  $core
 * @param \Anakeen\Core\Internal\Session|null $session
 * @deprecated see Anakeen\Core\Internal\GlobalParametersManager
 */
function _initMainVolatileParamWeb(\Anakeen\Core\Internal\Application & $core, \Anakeen\Core\Internal\Session & $session = null)
{
    $indexphp = basename($_SERVER["SCRIPT_NAME"]);
    $pattern = preg_quote($indexphp, "|");
    if (preg_match("|(.*)/$pattern|", $_SERVER['SCRIPT_NAME'], $reg)) {
        // determine publish url (detect ssl require)
        if (empty($_SERVER['HTTPS'])) {
            $_SERVER['HTTPS'] = "off";
        }
        if ($_SERVER['HTTPS'] != 'on') {
            $puburl = "http://" . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . $reg[1];
        } else {
            $puburl = "https://" . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . $reg[1];
        }
    } else {
        // it is not allowed
        print "<strong>:~(</strong>";
        exit;
    }
    $add_args = "";
    if (array_key_exists('authtype', $_GET)) {
        $add_args .= "&authtype=" . $_GET['authtype'];
    }
    $puburl = stripUrlSlahes($puburl);
    $urlindex = $core->getParam("CORE_URLINDEX");
    $core_externurl = ($urlindex) ? stripUrlSlahes($urlindex) : stripUrlSlahes($puburl . "/");
    $core_mailaction = $core->getParam("CORE_MAILACTION");
    $core_mailactionurl = ($core_mailaction != '') ? ($core_mailaction)
        : ($core_externurl . "?app=FDL&action=OPENDOC&mode=view");

    $sessKey = isset($session->id) ? $session->getUKey(\Anakeen\Core\ContextManager::getApplicationParam("WVERSION"))
        : uniqid(\Anakeen\Core\ContextManager::getApplicationParam("WVERSION"));
    $core->SetVolatileParam("CORE_EXTERNURL", $core_externurl);
    $core->SetVolatileParam("CORE_PUBURL", "."); // relative links
    $core->SetVolatileParam("CORE_ABSURL", stripUrlSlahes($puburl . "/")); // absolute links
    $core->SetVolatileParam("CORE_JSURL", "WHAT/Layout");
    $core->SetVolatileParam("CORE_ROOTURL", "?sole=R$add_args&");
    $core->SetVolatileParam("CORE_BASEURL", "?sole=A$add_args&");
    $core->SetVolatileParam("CORE_SBASEURL", "?sole=A&_uKey_=$sessKey$add_args&");
    $core->SetVolatileParam("CORE_STANDURL", "?sole=Y$add_args&");
    $core->SetVolatileParam("CORE_SSTANDURL", "?sole=Y&_uKey_=$sessKey$add_args&");
    $core->SetVolatileParam("CORE_ASTANDURL", "$puburl/$indexphp?sole=Y$add_args&"); // absolute links
    $core->SetVolatileParam("CORE_MAILACTIONURL", $core_mailactionurl);
}

/**
 * execute action
 * app and action http param
 *
 * @param \Anakeen\Core\Internal\Action $action
 * @param string $out
 */
function executeAction(&$action, &$out = null)
{
    $standalone = GetHttpVars("sole", "Y");
    if ($standalone != "A") {
        if ($out !== null) {
            $out = $action->execute();
        } else {
            echo($action->execute());
        }
    } else {
        if ((isset($action->parent)) && ($action->parent->with_frame != "Y")) {
            // This document is not completed : does not contain header and footer
            // HTML body result
            // achieve action
            $body = ($action->execute());
            // write HTML header
            $head = new Layout($action->GetLayoutFile("htmltablehead.xml"), $action);
            // copy JS ref & code from action to header
            //$head->jsref = $action->parent->GetJsRef();
            //$head->jscode = $action->parent->GetJsCode();
            $head->set("TITLE", _($action->parent->short_name));
            if ($out !== null) {
                $out = $head->gen();
                $out .= $body;
                $foot = new Layout($action->GetLayoutFile("htmltablefoot.xml"), $action);
                $out .= $foot->gen();
            } else {
                echo($head->gen());
                // write HTML body
                echo($body);
                // write HTML footer
                $foot = new Layout($action->GetLayoutFile("htmltablefoot.xml"), $action);
                echo($foot->gen());
            }
        } else {
            // This document is completed
            if ($out !== null) {
                $out = $action->execute();
            } else {
                echo($action->execute());
            }
        }
    }
}







<?php
/**
 * Enable to record parameters values to the database.
 */
$usage = new \Anakeen\Script\ApiUsage();

$usage->setDefinitionText("set context parameter value");
$parname = $usage->addRequiredParameter("param", "parameter name with its namespace (<NS>::<NAME>)"); // parameter name
$parval = $usage->addOptionalParameter("value", "parameter value to set. If not defined, the value will be cleared"); // parameter value (option)
$paruser = $usage->addOptionalParameter("userid", "user system id", function ($val) {
    $u = new \Anakeen\Core\Account("", $val);
    if (!$u->isAffected()) {
        return sprintf("Account %s does not exists", $val);
    }
    return "";
}); // parameter user id (option)
$default = $usage->addEmptyParameter("default", "Default value for user parameter");
$usage->verify();

$appid = 0;


$dbaccess = \Anakeen\Core\DbManager::getDbAccess();


$paramDefinition = new \Anakeen\Core\Internal\ParamDef($dbaccess, $parname);
if (!$paramDefinition->isAffected()) {
    throw new \Anakeen\Script\Exception(sprintf("Parameter \"%s\" not found", $parname));
}
if (($paramDefinition->isuser === "Y") && !$paruser && !$default) {
    throw new \Anakeen\Script\Exception(sprintf("Parameter \"%s\" is a user parameter\nMust precise request with userid or default argument", $parname));
}
if (($paramDefinition->isuser !== "Y") && $default) {
    throw new \Anakeen\Script\Exception(sprintf("Parameter \"%s\" is not a user parameter\nDefault option can only be used with user parameters", $parname));
}

$paramValue = new \Anakeen\Core\Internal\QueryDb($dbaccess, \Anakeen\Core\Internal\Param::class);
$paramValue->AddQuery(sprintf("name='%s'", pg_escape_string($parname)));

if ($paruser) {
    $paramValue->AddQuery(sprintf("type='U%d'", $paruser));
}
if ($default) {
    $paramValue->addQuery("type='G'");
}

$list = $paramValue->Query(0, 2);
if ($paramValue->nb == 0) {
    //throw new \Anakeen\Script\Exception(sprintf("Parameter value \"%s\" not found", $parname));
    $paramValue = new \Anakeen\Core\Internal\Param($dbaccess);
    $paramValue->val = $parval;
    $paramValue->name = $paramDefinition->name;
    $type = "G";
    if ($paramDefinition->isuser === "Y") {
        $type = "U" . $paruser;
    }
    $paramValue->type = $type;
    $err = $paramValue->add();
} elseif ($paramValue->nb > 1) {
    throw new \Anakeen\Script\Exception(sprintf("Too many values for parameter \"%s\"", $parname));
} else {
    /** @var \Anakeen\Core\Internal\Param $p */
    $paramValue = $list[0];
    $paramValue->val = $parval;
    $err = $paramValue->modify();
}


if ($err != "") {
    printf("Parameter %s not modified : \"%s\"\n", $paramValue->name, $err);
} else {
    $headString = "Parameter";
    if ($paramDefinition->isuser === "Y") {
        if ($paruser) {
            $u = new \Anakeen\Core\Account("", $paruser);
            if ($u->isAffected()) {
                $headString = sprintf("User \"%s\" parameter", $u->login);
            } else {
                $headString = "Parameter of unkown user";
            }
        } else {
            $headString = "User parameter";
        }
    }
    if ($paramValue->val) {
        printf("%s \"%s\" modified to \"%s\"\n", $headString, $parname, $paramValue->val);
    } else {
        printf("%s \"%s\" cleared\n", $headString, $parname);
    }
}

<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * set applicative parameter value
 *
 * analyze sub-directories presents in STYLE directory
 * @subpackage WSH
 */
/**
 */
$usage = new \Anakeen\Script\ApiUsage();

$usage->setDefinitionText("set applicative parameter value");
$parname = $usage->addRequiredParameter("param", "parameter name"); // parameter name
$parval = $usage->addOptionalParameter("value", "parameter value to set"); // parameter value (option)
$paruser = $usage->addOptionalParameter("userid", "user system id"); // parameter user id (option)
$usage->verify();

$appid = 0;


$dbaccess = \Anakeen\Core\DbManager::getDbAccess();
$param = new \Anakeen\Core\Internal\QueryDb($dbaccess, \Anakeen\Core\Internal\Param::class);
$param->AddQuery(sprintf("name='%s'", pg_escape_string($parname)));

$list = $param->Query(0, 2);
if ($param->nb == 0) {
    printf(_("Attribute %s not found\n"), $parname);
} elseif ($param->nb > 1) {
    printf(_("Attribute %s found is not alone\nMust precise request with appname arguments\n"), $parname);
} else {
    /** @var \Anakeen\Core\Internal\Param $p */
    $p = $list[0];
    $p->val = $parval;
    $err = $p->modify();
    if ($err != "") {
        printf(_("Attribute %s not modified : %s\n"), $parname, $err);
    } else {
        printf(_("Attribute %s modified to %s"), $parname, $parval);
    }
}
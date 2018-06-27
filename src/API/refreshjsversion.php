<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * regenerate js version file
 *
 * @param string $filename the file which contain new Anakeen\Core\Internal\Login or ACLs
 * @author Anakeen
 * @version $Id: refreshjsversion.php,v 1.2 2005/06/10 13:05:18 eric Exp $
 * @package FDL
 * @subpackage WSH
 */
/**
 */

$usage = new \Anakeen\Script\ApiUsage();
$usage->setDefinitionText("Regenerate WVERSION");
$usage->verify();

$nv=uniqid();
\Anakeen\Core\ContextManager::setParameterValue("WVERSION", $nv);
print "WVERSION: [$nv]\n";

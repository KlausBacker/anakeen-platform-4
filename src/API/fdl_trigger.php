<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: fdl_trigger.php,v 1.8 2007/05/22 16:06:29 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
// refreah for a classname
// use this only if you have changed title attributes

$usage = new \Anakeen\Script\ApiUsage();

$usage->setDefinitionText("Return sql code to inject trigget in base");
$docid = $usage->addOptionalParameter("docid", "special docid", null, 0);
$trigger = $usage->addOptionalParameter("trigger", "trigger", null, "-");
$trig = ($trigger != "-");
$drop = ($trigger == "N");

$usage->verify();



if ($docid != - 1) {
    $query = new \Anakeen\Core\Internal\QueryDb("", \Anakeen\Core\Internal\SmartElement::class);
    $query->AddQuery("doctype='C'");
    
    if ($docid > 0) {
        $query->AddQuery("id=$docid");
    }
    
    $table1 = $query->Query(0, 0, "TABLE");
    
    if ($query->nb > 0) {
        $pubdir = DEFAULT_PUBDIR;
        
        foreach ($table1 as $k => $v) {
            $doc = createDoc("", $v["id"]);
            
            if ($trig) {
                print $doc->sqltrigger($drop) . "\n";
            } else {
                $triggers = $doc->sqltrigger(false, true);
                
                if (is_array($triggers)) {
                    print implode(";\n", $triggers);
                } else {
                    print $triggers . "\n";
                }
            }
            print implode(' ', $doc->getSqlIndex()) . "\n";
        }
    }
}

if (($docid == - 1) || ($docid == 0)) {
    $doc = new \Anakeen\Core\SmartStructure();
    
    $doc->doctype = 'C';
    $doc->fromid = 'fam';
    if ($trig) {
        print $doc->sqltrigger($drop) . "\n";
    } elseif (!empty($doc->sqltcreate)) {
        if (is_array($doc->sqltcreate)) {
            print implode(";\n", $doc->sqltcreate);
        } else {
            print $doc->sqltcreate . "\n";
        }
    }
}

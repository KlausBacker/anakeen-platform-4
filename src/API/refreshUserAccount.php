<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @subpackage
 */
/**
 */
// refreah for a classname
// use this only if you have changed title attributes


$usage = new \Anakeen\Script\ApiUsage();

$usage->setDefinitionText("Update usercard");
$whatid = $usage->addOptionalParameter("whatid", "document"); // document
$fbar = $usage->addOptionalParameter("bar", "for progress bar"); // for progress bar
$onlygroup = ($usage->addOptionalParameter("onlygroup", "for progress bar") != ""); // for progress bar
$usage->verify();

$query = new \Anakeen\Core\Internal\QueryDb("", \Anakeen\Core\Account::class);

if ($whatid > 0) {
    $query->AddQuery("id=$whatid");
} else {
    $query->order_by = "accounttype desc,id";
}

if ($onlygroup) {
    $query->AddQuery("accounttype='G'");
}

$table1 = $query->Query(0, 0, "TABLE");

if ($query->nb > 0) {
    printf("\n%d user to update\n", count($table1));
    $card = count($table1);
    $doc = new \Anakeen\Core\Internal\SmartElement();
    $reste = $card;
    foreach ($table1 as $k => $v) {
        $fid = 0;

        $reste--;
        // search already created card
        $title = strtolower($v["lastname"] . " " . $v["firstname"]);
        $mail = getMailAddr($v["id"]);
        // first in IUSER
        unset($tdoc);
        $udoc = false;
        $foundoc = false;
        $fid = $v["fid"];
        if ($fid > 0) {
            $udoc = \Anakeen\Core\SEManager::getDocument($fid);
            $foundoc = $udoc && $udoc->isAlive();
        }

        if (!$foundoc) {
            // search same doc with us_what id
            if ($v["accounttype"] === "G") {
                $filter = array(
                    "us_whatid = '" . $v["id"] . "'"
                );
                $tdoc = \Anakeen\SmartStructures\Dir\DirLib::internalGetDocCollection("", 0, 0, "ALL", $filter, 1, "TABLE", "IGROUP");
            } else {
                $filter = array(
                    "us_whatid = '" . $v["id"] . "'"
                );
                $tdoc = \Anakeen\SmartStructures\Dir\DirLib::internalGetDocCollection("", 0, 0, "ALL", $filter, 1, "TABLE", "IUSER");
            }

            if (count($tdoc) > 0) {
                $fid = $tdoc["id"];
                $udoc = \Anakeen\Core\SEManager::getDocument($fid);
                $foundoc = $udoc && $udoc->isAlive();
            }
        }
        if ($foundoc) {
            /**
             * @var \SmartStructure\IUSER|\SmartStructure\IGROUP $udoc
             */
            if (method_exists($udoc, "RefreshGroup")) {
                $udoc->RefreshGroup();
            } elseif (method_exists($udoc, "RefreshDocUser")) {
                $udoc->RefreshDocUser();
            }
            //if (method_exists($tdoc[0],"SetGroupMail")) $tdoc[0]->SetGroupMail();
            //$tdoc[0]->refresh();
            //$tdoc[0]->postModify();
            $err = $udoc->modify();
            if ($err != "") {
                print "$err\n";
            } else {
                print "$reste)";
                printf(_("%s updated\n"), $udoc->title);
                $fid = $udoc->id;
            }
        } else {
            // search in all usercard same title
            if ($mail != "") {
                $filter = array(
                    "us_mail = '" . pg_escape_string($mail) . "'"
                );
            } else {
                $filter = array(
                    "lower(title) = '" . pg_escape_string($title) . "'"
                );
            }
            $tdoc = \Anakeen\SmartStructures\Dir\DirLib::internalGetDocCollection("", 0, 0, "ALL", $filter, 1, "LIST", \Anakeen\Core\SEManager::getFamilyIdFromName("IUSER"));
            if (count($tdoc) > 0) {
                if (count($tdoc) > 1) {
                    printf(_("find %s more than one, created aborded\n"), $title);
                } else {
                    $udoc = \Anakeen\Core\SEManager::getDocument($tdoc[0]->id);
                    /**
                     * @var \SmartStructure\IUSER $udoc
                     */
                    $udoc->setValue("US_WHATID", $v["id"]);
                    $udoc->refresh();
                    $udoc->RefreshDocUser();
                    $udoc->modify();
                    $fid = $udoc->id;
                    print "$reste)";
                    printf(_("%s updated\n"), $title);
                    unset($udoc);
                }
            } else {
                // create new card
                if ($v["accounttype"] === "G") {
                    $iuser = \Anakeen\Core\SEManager::createDocument("IGROUP");
                    $iuser->setValue("US_WHATID", $v["id"]);
                    $iuser->store();
                    print "$reste)";
                    printf(_("%s igroup created\n"), $title);
                } else {
                    $iuser = \Anakeen\Core\SEManager::createDocument("IUSER");
                    $iuser->setValue("US_WHATID", $v["id"]);
                    $err = $iuser->add();
                    if ($err == "") {
                        //$iuser->refresh();"
                        //$iuser->RefreshDocUser();
                        //$iuser->modify();
                        print "$reste)";
                        printf(_("%s iuser created\n"), $title);
                    } else {
                        print "$reste)$err";
                        printf(_("%s iuser aborded\n"), $title);
                    }
                }
                $fid = $iuser->id;
                unset($iuser);
            }
        }

        if (($v["fid"] == 0) && ($fid > 0)) {
            $u = new \Anakeen\Core\Account("", $v["id"]);
            $u->fid = $fid;
            $u->modify();
            unset($u);
        }
    }

    $doc->query("update doc127 set name='GADMIN'     where us_whatid='4'");
    $doc->query("update doc127 set name='GDEFAULT'   where us_whatid='2'");
    $doc->query("update doc128 set name='USER_ADMIN' where us_whatid='1'");
    $doc->query("update doc128 set name='USER_GUEST' where us_whatid='3'");
    $doc->query("update doc128 set cvid=(select id from family.cvdoc where name='CV_IUSER_ACCOUNT')          where us_whatid='1'");
    $doc->query("update doc128 set cvid=(select id from family.cvdoc where name='CV_IUSER_ACCOUNT')          where us_whatid='3'");
}

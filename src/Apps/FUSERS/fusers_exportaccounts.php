<?php
/*
 * @author Anakeen
 * @package FDL
*/

function fusers_exportaccounts(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Export accounts definition");
    
    $exportSchema = $usage->addOptionalParameter("xmlschema", "export xml schema also", array(
        "true",
        "false"
    ) , "false") === "true";
    
    $exportPassword = $usage->addOptionalParameter("cryptPassword", "add crypt password", array(
        "true",
        "false"
    ) , "false") === "true";
    
    $exportRole = $usage->addOptionalParameter("relativeRoles", "export associated roles", array(
        "true",
        "false"
    ) , "false") === "true";
    
    $exportGroup = $usage->addOptionalParameter("relativeGroups", "export parent groups", array(
        "true",
        "false"
    ) , "false") === "true";
    $exportDocument = $usage->addOptionalParameter("documentInfo", "export specific document information", array(
        "true",
        "false"
    ) , "false") === "true";
    $type = $usage->addOptionalParameter("accountType", "restricted to account type", array(
        "user",
        "role",
        "group"
    ));
    
    $statusKey = $usage->addOptionalParameter("statusKey", "key for see progress");
    $group = $usage->addOptionalParameter("selectedGroup", "Sub group restriction");
    $deepGroup = $usage->addOptionalParameter("deepGroup", "Export sub groups", array(
        "true",
        "false"
    ) , "true") === "true";
    
    $filter = $usage->addOptionalParameter("filters", "additionnal filters");
    $usage->verify();
    // Memo user preferences
    $uprefs = json_decode($action->getParam("FUSERS_PORTPREF", "{}") , true);
    $uprefs[$type] = array(
        "deepGroup" => $deepGroup,
        "exportRole" => $exportRole,
        "exportGroup" => $exportGroup,
        "exportPassword" => $exportPassword,
        "exportSchema" => $exportSchema,
        "exportDocument" => $exportDocument
    );
    $action->setParamU("FUSERS_PORTPREF", json_encode($uprefs));
    
    setMaxExecutionTimeTo(-1); // May be long
    $export = new \Dcp\Core\ExportAccounts();
    
    if ($statusKey) {
        $export->setSessionKey("EA::" . $statusKey);
    }
    $search = new SearchAccount();
    
    $basename = "accounts";
    if ($type) {
        if (!is_array($type)) {
            
            $basename = $type . "s";
            $type = array(
                $type
            );
        }
        $accountType = 0;
        foreach ($type as $singleType) {
            switch ($singleType) {
                case "user":
                    $accountType|= \SearchAccount::userType;
                    break;

                case "group":
                    $accountType|= \SearchAccount::groupType;
                    break;

                case "role":
                    $accountType|= \SearchAccount::roleType;
                    break;
            }
        }
        $search->setTypeFilter($accountType);
    }
    
    if ($group) {
        $groupAccount = new_Doc("", $group);
        if ($groupAccount->isAffected()) {
            $basename = $groupAccount->getTitle();
            if (!$deepGroup) {
                $search->addFilter("users.id in (select iduser from groups where idgroup=%d)", $groupAccount->getRawValue(\Dcp\AttributeIdentifiers\Igroup::us_whatid));
            }
            $search->addGroupFilter($groupAccount->getRawValue("us_login"));
        } else {
            $action->exitError('Incorrect group id "' . $group . '"');
        }
    }
    
    if ($filter) {
        $filters = json_decode($filter);
        $matchingKeys = array(
            "us_login" => "login",
            "us_lname" => "lastname",
            "us_fname" => "firstname",
            "us_mail" => "mail",
            "grp_name" => "lastname",
            "family" => "...",
            "title" => "(coalesce(firstname,'') || coalesce(lastname,''))"
        );
        
        foreach ($filters as $key => $value) {
            if (isset($matchingKeys[$key])) {
                //print $matchingKeys[$key].", $value\n<br/>";
                if ($key === "family") {
                    $search->filterFamily($value);
                    $basename.= "(" . getDocTitle($value) . ")";
                } else {
                    $search->addFilter(sprintf("%s ~* %s", $matchingKeys[$key], pg_escape_literal($value)));
                    $basename.= "(" . $value . ")";
                }
            }
        }
    }
    $search->setOrder("id");
    $schemaDirectory = '';
    if ($exportSchema) {
        $schemaDirectory = sprintf("%s/%s", getTmpDir() , uniqid("accountExport"));
        mkdir($schemaDirectory);
        $export->setExportSchemaDirectory($schemaDirectory);
    }
    $export->setExportGroupParent($exportGroup);
    $export->setExportRoleParent($exportRole);
    $export->setExportCryptedPassword($exportPassword);
    $export->setExportDocument($exportDocument);
    $export->setSearchAccount($search);
    
    $xmlFileSource = $export->export();
    if ($export->isAborted()) {
        $basename.= " (ABORTED)";
    }
    /* Sanitize $basename */
    $basename = str_replace('/', '_', $basename);
    if (!$exportSchema) {
        Http_Download($xmlFileSource, "xml", $basename, true, "text/xml");
    } else {
        file_put_contents(sprintf("%s/$basename.xml", $schemaDirectory) , $xmlFileSource);
        $zipFile = sprintf("%s/$basename.zip", $schemaDirectory);
        $zip = new ZipArchive();
        $zip->open($zipFile, ZipArchive::CREATE);
        chdir($schemaDirectory);
        $handle = opendir($schemaDirectory);
        if ($handle) {
            while (false !== $f = readdir($handle)) {
                if (preg_match("/.(xml|xsd)$/", $f)) {
                    $zip->addFile($f);
                }
            }
            closedir($handle);
        }
        
        $handle = opendir($schemaDirectory . "/" . $export::XSDDIR);
        if ($handle) {
            while (false !== $f = readdir($handle)) {
                if (preg_match("/.(xml|xsd)$/", $f)) {
                    $zip->addFile($export::XSDDIR . "/" . $f);
                }
            }
            closedir($handle);
        }
        $zip->close();
        
        Http_DownloadFile($zipFile, "$basename.zip", "application/x-zip", false, true, true);
    }
    $action->lay->template = "";
    $action->lay->noparse = true;
}

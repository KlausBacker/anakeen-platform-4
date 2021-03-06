<?php /** @noinspection PhpUnusedParameterInspection */

/**
 * importation of Smart Elements
 *
 */


use Anakeen\Core\ContextManager;

$usage = new \Anakeen\Script\ApiUsage();
$usage->setDefinitionText("Import documents from description file");
$filename = $usage->addRequiredParameter("file", "the description file path");
$analyze = $usage->addOptionalParameter("analyze", "analyze only", array(
    "yes",
    "no"
), "no");
$archive = $usage->addOptionalParameter("archive", "description file is an standard archive (not xml)", array(
    "yes",
    "no"
), "no");
$logfile = $usage->addOptionalParameter("log", "log file output");
$policy = $usage->addOptionalParameter(
    "policy",
    "policy import - \n\t\t[update] to auto update same document (the default), \n\t\t" .
    "[add] to always create new document, \n\t\t[keep] to do nothing if same document already present",
    array(
        "update",
        "add",
        "keep"
    )
);
$htmlmode = $usage->addOptionalParameter("htmlmode", "analyze report mode in html", array(
    "yes",
    "no"
), "yes");

$reset = $usage->addOptionalParameter("reset", "reset options", function ($values, $argName, \Anakeen\Script\ApiUsage $apiusage) {
    $opt = array(
        "default",
        "parameters",
        "attributes",
        "structure",
        "properties",
        "enums"
    );
    if ($values === \Anakeen\Script\ApiUsage::GET_USAGE) {
        return sprintf(" [%s] ", implode('|', $opt));
    }

    $error = $apiusage->matchValues($values, $opt);
    if ($error) {
        $apiusage->exitError(sprintf("Error: wrong value for argument 'reset' : %s", $error));
    }
    return '';
});
$to = $usage->addOptionalParameter("to", "email address to send report");
$dirid = $usage->addOptionalParameter("dir", "folder where imported documents are put");

$strict = $usage->addOptionalParameter("strict", "don't import if one error detected", array(
    "yes",
    "no"
), "yes");

$csvSeparator = $usage->addOptionalParameter("csv-separator", "character to delimiter fields - generaly a comma", function ($values, $argName, \Anakeen\Script\ApiUsage $apiusage) {
    if ($values === \Anakeen\Script\ApiUsage::GET_USAGE) {
        return sprintf(' use single character or "auto"');
    }
    if (!is_string($values)) {
        return sprintf("must be a character [%s] ", print_r($values, true));
    }
    if ($values != "auto") {
        if (mb_strlen($values) > 1) {
            return sprintf("must be a only one character [%s] ", $values);
        }
    }
    return '';
}, ";");

$csvEnclosure = $usage->addOptionalParameter(
    "csv-enclosure",
    "character to enclose fields - generaly double-quote",
    function ($values, $argName, \Anakeen\Script\ApiUsage $apiusage) {
        if ($values === \Anakeen\Script\ApiUsage::GET_USAGE) {
            return sprintf(' use single character or "auto"');
        }
        if (!is_string($values)) {
            return sprintf("must be a character [%s] ", print_r($values, true));
        }
        if ($values != "auto") {
            if (mb_strlen($values) > 1) {
                return sprintf("must be a only one character [%s] ", $values);
            }
        }
        return '';
    },
    '"'
);

$csvLinebreak = $usage->addOptionalParameter("csv-linebreak", "character sequence to be import like CRLF", null, '\n');

$usage->verify();


if (!file_exists($filename)) {
    ContextManager::exitError(sprintf(___("import file %s not found", "sde"), $filename));
}
if (!is_file($filename)) {
    ContextManager::exitError(sprintf(___("import file '%s' is not a valid file", "sde"), $filename));
}
if ($logfile) {
    if (file_exists($logfile) && (!is_writable($logfile))) {
        ContextManager::exitError(sprintf(___("log file %s not writable", "sde"), $logfile));
    }
    if (!file_exists($logfile)) {
        $f = @fopen($logfile, 'a');
        if ($f === false) {
            ContextManager::exitError(sprintf(___("log file %s not writable", "sde"), $logfile));
        }
        fclose($f);
    }
}
$archive = ($archive == "yes");

if ($dirid) {
    $dir = Anakeen\Core\SEManager::getDocument($dirid);
    if (!$dir || !$dir->isAlive()) {
        ContextManager::exitError(sprintf("folder %s not found (dir option)", $dirid));
    }
    $dirid = $dir->id;
}
$oImport = new Anakeen\Exchange\ImportDocument();
if ($strict == 'no') {
    $oImport->setStrict(false);
}
$oImport->setCsvOptions($csvSeparator, $csvEnclosure, $csvLinebreak);
$oImport->setPolicy($policy);
$oImport->setReset($reset);
$oImport->setVerifyAttributeAccess(false);
if ($dirid) {
    $oImport->setTargetDirectory($dirid);
}
$cr = $oImport->importDocuments($filename, $analyze != "no", $archive == "yes");

$filetmp = false;
if ((!$logfile) && $to) {
    $logfile = tempnam(\Anakeen\Core\ContextManager::getTmpDir(), 'logimport');
    $filetmp = true;
}
if ($logfile) {
    if ($htmlmode == "yes") {
        $oImport->writeHTMLImportLog($logfile);
    } else {
        $oImport->writeImportLog($logfile);
    }
}
// mode HTML
if ($to) {
    $message = new \Anakeen\Mail\Message();

    $body = new \Anakeen\Mail\Body(file_get_contents($logfile), (($htmlmode == 'yes') ? 'text/html' : 'text/plain'));
    $message->setBody($body);

    $from = ContextManager::getCurrentUser()->getMail();
    if ($from == "") {
        $from = ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, 'SMTP_FROM');
    }
    if ($from == "") {
        $from = ContextManager::getCurrentUser()->login . '@' . php_uname('n');
    }

    $subject = sprintf(_("result of import  %s"), basename($filename));
    $message->setSubject($subject);
    $message->setFrom($from);
    $message->addTo($to);
    $err = $message->send();
    if ($err) {
        error_log("import sending mail: Error:$err");
    }
    if ($filetmp) {
        unlink($logfile);
    }
}
$err = $oImport->getErrorMessage();
if ($err) {
    ContextManager::exitError($err);
}

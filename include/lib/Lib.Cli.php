<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * CLI Library
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

require_once ('class/Class.WIFF.php');

global $wiff_lock;
function printerr($msg) {
    file_put_contents('php://stderr', $msg);
    $wiff = WIFF::getInstance();
    $wiff->log(LOG_ERR, $msg);
}
/**
 * wiff help
 * @param $argv
 * @return int
 */
function wiff_help(&$argv)
{
    echo "\n";
    echo "Usage\n";
    echo "-----\n";
    echo "\n";
    echo "  wiff help\n";
    echo "\n";
    echo "  wiff param help\n";
    echo "\n";
    echo "  wiff list help\n";
    echo "\n";
    echo "  wiff context <context-name> help\n";
    echo "\n";
    echo "  wiff archive <archive-id> help\n";
    echo "\n";
    echo "  wiff whattext <context-name>\n";
    echo "  wiff wstop <context-name>\n";
    echo "  wiff wstart <context-name>\n";
    echo "\n";
    echo "  wiff delete context <context-name>\n";
    echo "\n";
    echo "  wiff crontab help\n";
    echo "\n";
    echo "  wiff send_configuration\n";
    echo "\n";
    echo "  wiff repository help\n";
    echo "\n";
    echo "  wiff register\n";
    echo "  wiff register <eec-username> <eec-password>\n";
    echo "\n";
    return 0;
}

/**
 * wiff (un)lock
 * @return mixed $lock
 */
function wiff_lock()
{
    $wiff = WIFF::getInstance();
    if ($wiff->lock(false, $lockerPid) === false) {
        printerr(sprintf("Error locking Dynacase-Control: %s\n", $wiff->errorMessage));
        exit(100);
    }
}

function wiff_unlock()
{
    $wiff = WIFF::getInstance();
    $ret = $wiff->unlock();
    if ($ret === false) {
        printerr(sprintf("Warning: could not unlock Dynacase-Control!\n"));
    }
    return $ret;
}

function wiff_param(&$argv)
{
    $op = array_shift($argv);
    
    switch ($op) {
        case 'show':
            $ret = wiff_param_show($argv);
            return $ret;
            break;

        case 'set':
            wiff_lock();
            $ret = wiff_param_set($argv);
            wiff_unlock();
            return $ret;
            break;

        case 'get':
            $ret = wiff_param_get($argv);
            return $ret;
            break;

        case 'help':
            return wiff_param_help();
        default:
            printerr(sprintf("Unknown operation '%s'!\n", $op));
            return wiff_param_help();
    }
}

function wiff_param_show(&$argv) {
    $wiff = WIFF::getInstance();
    $paramList = $wiff->getParamList(true);
    if ($paramList === false) {
        printerr(sprintf("Error: could not get list of params: %s\n", $wiff->errorMessage));
        return 1;
    }
    $visibleParamList = $wiff->getParamList();
    if ($visibleParamList === false) {
        printerr(sprintf("Error: could not get list of visible params: %s\n", $wiff->errorMessage));
        return 1;
    }
    $visibleParamNameList = array_keys($visibleParamList);
    foreach ($paramList as $paramName => $paramValue) {
        $visible = in_array($paramName, $visibleParamNameList);
        printf("%s = %s (%s)\n", $paramName, $paramValue, ($visible?'visible':'hidden'));
    }
    return 0;
}

function wiff_param_get(&$argv)
{
    $wiff = WIFF::getInstance();
    
    $paramKey = array_shift($argv);
    if ($paramKey === null) {
        printerr(sprintf("Error: missing param-name.\n"));
        return 1;
    }
    $ret = $wiff->getParam($paramKey, true, true);
    if (!$ret) {
        printerr(sprintf("%s\n", $wiff->errorMessage));
        return 1;
    }
    echo sprintf("%s = %s\n", $paramKey, $ret);
    return 0;
}
function wiff_param_set(&$argv)
{
    $wiff = WIFF::getInstance();
    
    $paramKey = array_shift($argv);
    if ($paramKey === null) {
        printerr(sprintf("Error: missing param-name.\n"));
        return 1;
    }
    $paramValue = array_shift($argv);
    if ($paramValue === null) {
        printerr(sprintf("Error: missing param-value.\n"));
        return 1;
    }
    $paramMode = array_shift($argv);
    if ($paramMode === null) {
        $paramMode = "hidden";
    }
    
    $ret = $wiff->setParam($paramKey, $paramValue, true, $paramMode);
    if (!$ret) {
        printerr(sprintf("%s\n", $wiff->errorMessage));
        return 1;
    }
    return 0;
}

function wiff_param_help()
{
    echo "\n";
    echo "Usage\n";
    echo "-----\n";
    echo "\n";
    echo "  wiff param show\n";
    echo "\n";
    echo "  wiff param set <param-name> <param-value> ['hidden']\n";
    echo "\n";
    echo "  wiff param get <param-name>\n";
    echo "\n";
    return 0;
}
/**
 * wiff list
 * @param $argv
 * @return int
 */
function wiff_list(&$argv)
{
    $op = array_shift($argv);
    
    switch ($op) {
        case 'help':
            return wiff_list_help($argv);
            break;

        case 'context':
            $ret = wiff_list_context($argv);
            return $ret;
            break;

        case 'archive':
            $ret = wiff_list_archive($argv);
            return $ret;
            break;

        default:
            printerr(sprintf("Unknown operation '%s'!\n", $op));
            return wiff_list_help($argv);
    }
}
/**
 * wiff list context
 * @param $argv
 * @return int
 */
function wiff_list_context(&$argv)
{
    $options = parse_argv_options($argv);
    
    $wiff = WIFF::getInstance();
    
    $ctxList = $wiff->getContextList();
    if ($ctxList === false) {
        printerr(sprintf("Error: could not get contexts list: %s\n", $wiff->errorMessage));
        return 1;
    }
    
    if (boolopt('pretty', $options)) {
        echo sprintf("%-16s   %-64s\n", "Name", "Description");
        echo sprintf("%-16s---%-64s\n", str_repeat("-", 16) , str_repeat("-", 64));
    }
    foreach ($ctxList as $ctx) {
        if (boolopt('pretty', $options)) {
            echo sprintf("%-16s   %-64s\n", $ctx->name, $ctx->description);
        } else {
            echo sprintf("%s\n", $ctx->name);
        }
    }
    
    return 0;
}

function wiff_list_help(&$argv)
{
    echo "\n";
    echo "Usage\n";
    echo "-----\n";
    echo "\n";
    echo "  wiff list context\n";
    echo "  wiff list archive\n";
    echo "\n";
    return 0;
}
/**
 * wiff context
 * @param $argv
 * @return int
 */
function wiff_context(&$argv)
{
    if (!is_array($argv)) return 0;
    $ctx_name = array_shift($argv);
    if ($ctx_name == "") {
        wiff_help($argv);
        return 0;
    }
    
    $wiff = WIFF::getInstance();
    $context = $wiff->getContext($ctx_name);
    if ($context === false) {
        printerr(sprintf("Error: could not get context '%s': %s\n", $ctx_name, $wiff->errorMessage));
        return 1;
    }

    if (count($argv) <= 0) {
        return wiff_context_exportenv($context, $argv);
    }
    
    $op = array_shift($argv);
    switch ($op) {
        case 'exec':
        case 'shell':
            return wiff_context_shell($context, $argv);
            break;

        case 'exportenv':
            $ret = wiff_context_exportenv($context, $argv);
            return $ret;
            break;

        case 'module':
            $ret = wiff_context_module($context, $argv);
            return $ret;
            break;

        case 'param':
            $ret = wiff_context_param($context, $argv);
            return $ret;
            break;

        case 'property':
            $ret = wiff_context_property($context, $argv);
            return $ret;
            break;

        case 'repository':
            $ret = wiff_context_repository($context, $argv);
            return $ret;

        case 'register':
            wiff_lock();
            $ret = wiff_context_register($context, $argv);
            wiff_unlock();
            return $ret;
            break;

        case 'archive':
            wiff_lock();
            $ret = wiff_context_archive($context, $argv);
            wiff_unlock();
            return $ret;
            break;

        case 'download-configuration':
            wiff_lock();
            $ret = wiff_context_download_configuration($context, $argv);
            wiff_unlock();
            return $ret;
            break;

        case 'help':
            return wiff_context_help($context, $argv);
            break;

        default:
            printerr(sprintf("Unknown operation '%s'!\n", $op));
            return wiff_context_help($context, $argv);
    }
}
/**
 * wiff context help
 * @param Context $context
 * @param $argv
 * @return int
 */
function wiff_context_help(&$context, &$argv)
{
    echo "\n";
    echo "Usage\n";
    echo "-----\n";
    echo "\n";
    echo "  wiff context <context-name>\n";
    echo "\n";
    echo "  wiff context <context-name> exportenv\n";
    echo "  wiff context <context-name> shell\n";
    echo "  wiff context <context-name> exec /bin/bash --login\n";
    echo "  wiff context <context-name> register\n";
    echo "  wiff context <context-name> archive <archive-name> [--without-vault] [--description=<description>]\n";
    echo "  wiff context <context-name> download-configuration [--file=<configuration-file.zip>]\n";
    echo "\n";
    echo "Sub-commands help\n";
    echo "-----------------\n";
    echo "\n";
    echo "  wiff context <context-name> param help\n";
    echo "  wiff context <context-name> module help\n";
    echo "  wiff context <context-name> property help\n";
    echo "  wiff context <context-name> repository help\n";
    echo "\n";
    return 0;
}
/**
 * wiff context <ctxName> exportenv
 * @param Context $context
 * @param $argv
 * @return int
 */
function wiff_context_exportenv(&$context, &$argv)
{
    echo "export wpub=" . $context->root . ";\n";
    echo "export pgservice_core=" . $context->getParamByName("core_db") . ";\n";
    echo "export pgservice_freedom=" . $context->getParamByName("core_db") . ";\n";
    echo "export httpuser=" . $context->getParamByName("apacheuser") . ";\n";
    echo "export freedom_context=default\n";
    return 0;
}
/**
 * wiff context <ctxName> shell
 * @param Context $context
 * @param $argv
 * @return int
 */
function wiff_context_shell(&$context, &$argv)
{
    if (!function_exists("posix_setuid")) {
        printerr(sprintf("Error: required POSIX PHP functions not available!\n"));
        return 1;
    }
    if (!function_exists("pcntl_exec")) {
        printerr(sprintf("Error: required PCNTL PHP functions not available!\n"));
        return 1;
    }
    
    $uid = posix_getuid();
    
    $httpuser = $context->getParamByName("apacheuser");
    if ($httpuser === false) {
        printerr(sprintf("%s\n", $context->errorMessage));
        return 1;
    }
    if ($httpuser == '') {
        $httpuser = $uid;
    }
    
    $envs = array();
    $envs['wpub'] = $context->root;
    $envs['pgservice_core'] = $context->getParamByName("core_db");
    $envs['pgservice_freedom'] = $envs['pgservice_core'];
    $envs['freedom_context'] = "default";
    $envs['PS1'] = sprintf("Dynacase-Control(%s)\\w\\$ ", $context->name);
    $envs['USER'] = $httpuser;
    if (getenv('PATH') !== false) {
        $envs['PATH'] = getenv('PATH');
    }
    if (getenv('TERM') !== false) {
        $envs['TERM'] = getenv('TERM');
    }
    
    if ($envs['pgservice_core'] === false || $envs['pgservice_core'] == '') {
        printerr(sprintf("Error: empty core_db parameter!\n"));
        return 1;
    }
    
    if (is_numeric($httpuser)) {
        $http_pw = posix_getpwuid($httpuser);
    } else {
        $http_pw = posix_getpwnam($httpuser);
    }
    if ($http_pw === false) {
        printerr(sprintf("Error: could not get information for httpuser '%s'\n", $httpuser));
        return 1;
    }
    
    $http_uid = $http_pw['uid'];
    $http_gid = $http_pw['gid'];
    
    $shell = array_shift($argv);
    if ($shell === null) {
        $shell = $http_pw['shell'];
    }
    
    $envs['HOME'] = $context->root;
    
    $ret = chdir($context->root);
    if ($ret === false) {
        printerr(sprintf("Error: could not chdir to '%s'\n", $context->root));
        return 1;
    }
    
    if ($uid != $http_uid) {
        $ret = posix_setgid($http_gid);
        if ($ret === false) {
            printerr(sprintf("Error: could not setgid to gid '%s'\n", $http_gid));
            return 1;
        }
        $ret = posix_setuid($http_uid);
        if ($ret === false) {
            printerr(sprintf("Error: could not setuid to uid '%s'\n", $http_uid));
            return 1;
        }
    }
    /** @noinspection PhpVoidFunctionResultUsedInspection Because it return false on error and void on success */
    $ret = pcntl_exec($shell, $argv, $envs);
    if ($ret === false) {
        printerr(sprintf("Error: exec error for '%s'\n", join(" ", array(
            $shell,
            join(" ", $argv)
        ))));
        exit(1);
    }
    return 0;
}
/**
 * wiff context <ctxName> module
 * @param Context $context
 * @param $argv
 * @return int
 */
function wiff_context_module(&$context, &$argv)
{
    $op = array_shift($argv);
    
    switch ($op) {
        case 'install':
            wiff_lock();
            $ret = wiff_context_module_install($context, $argv);
            wiff_unlock();
            return $ret;
            break;

        case 'upgrade':
            wiff_lock();
            $ret = wiff_context_module_upgrade($context, $argv);
            wiff_unlock();
            return $ret;
            break;

        case 'extract':
            $ret = wiff_context_module_extract($context, $argv);
            return $ret;
            break;

        case 'list':
            return wiff_context_module_list($context, $argv);
            break;

        default:
            wiff_context_module_help($context, $argv);
            break;
    }
    
    return 0;
}

function wiff_context_module_help(&$context, &$argv)
{
    echo "\n";
    echo "Usage\n";
    echo "-----\n";
    echo "\n";
    echo "  wiff context <context-name> module install [install-options] <localModulePkgPath|modName>\n";
    echo "  wiff context <context-name> module upgrade [upgrade-options] <localModulePkgPath|modName>\n";
    echo "  wiff context <context-name> module list installed|available|updates\n";
    echo "\n";
    echo "install-options\n";
    echo "---------------\n";
    echo "\n";
    echo "  --nopre    Do not execute pre-install processes.\n";
    echo "  --nopost   Do not execute post-install processes.\n";
    echo "  --nothing  Do not execute pre-install and post-install processes.\n";
    echo "  --force    Force installation.\n";
    echo "\n";
    echo "upgrade-options\n";
    echo "---------------\n";
    echo "\n";
    echo "  --nopre    Do not execute pre-upgrade processes.\n";
    echo "  --nopost   Do not execute post-upgrade processes.\n";
    echo "  --nothing  Do not execute pre-upgrade and post-upgrade processes.\n";
    echo "  --force    Force upgrade.\n";
    echo "\n";
    return 0;
}

function wiff_context_module_install(&$context, &$argv)
{
    $options = parse_argv_options($argv);
    
    $modName = array_shift($argv);
    
    if (is_file($modName)) {
        return wiff_context_module_install_local($context, $options, $modName, $argv);
    } else {
        return wiff_context_module_install_remote($context, $options, $modName, $argv);
    }
}

function wiff_context_module_install_local(Context & $context, &$options, &$pkgName, &$argv)
{
    require_once ('lib/Lib.System.php');
    require_once ('class/Class.Module.php');
    
    $tmpfile = WiffLibSystem::tempnam(null, basename($pkgName));
    if ($tmpfile === false) {
        printerr(sprintf("Error: could not create temp file!\n"));
        return 1;
    }
    
    $ret = copy($pkgName, $tmpfile);
    if ($ret === false) {
        printerr(sprintf("Error: could not copy '%s' to '%s'!\n", $pkgName, $tmpfile));
        return 1;
    }
    
    $tmpMod = $context->loadModuleFromPackage($tmpfile);
    if ($tmpMod === false) {
        printerr(sprintf("Error: could not load module '%s': %s\n", $tmpfile, $context->errorMessage));
        return 1;
    }
    
    $existingModule = $context->getModuleInstalled($tmpMod->name);
    if ($existingModule !== false) {
        echo sprintf("A module '%s' with version '%s-%s' already exists.\n", $existingModule->name, $existingModule->version, $existingModule->release);
        if (!boolopt('force', $options)) {
            return 0;
        }
    }
    unset($existingModule);
    
    $tmpMod = $context->importArchive($tmpfile, 'downloaded');
    if ($tmpMod === false) {
        printerr(sprintf("Error: could not import module '%s': %s\n", $tmpfile, $context->errorMessage));
        return 1;
    }
    if (!empty($context->warningMessage)) {
        echo sprintf("\nWarning: %s%s%s\n", fg_yellow(), $context->warningMessage, color_reset());
        $ret = param_ask($options, "Proceed with installation", "Y/n", "Y");
        if (!preg_match('/^(y|yes|)$/i', $ret)) {
            $wiff = WIFF::getInstance();
            $wiff->cleanup($context->name);
            return 0;
        }
    }

    $depList = $context->getLocalModuleDependencies($tmpfile);
    if ($depList === false) {
        printerr(sprintf("Error: could not get dependencies for '%s': %s\n", $tmpfile, $context->errorMessage));
        return 1;
    }
    
    if (count($depList) > 1) {
        echo sprintf("Will (i)nstall, (u)pgrade or (r)eplace the following modules:\n");
        foreach ($depList as $module) {
            /**
             * @var Module $module
             */
            if ($module->needphase == '') {
                $module->needphase = 'install';
            }
            $op = '(i)';
            if ($module->needphase == 'upgrade') {
                $op = '(u)';
            } else if ($module->needphase == 'replaced') {
                $op = '(r) (replaced by ' . (($module->replacedBy) ? $module->replacedBy : 'unknown') . ')';
            }
            $error = "";
            if ($module->errorMessage) {
                $error = "(" . fg_red() . $module->errorMessage . color_reset() . ")";
            }
            $warning = "";
            if ($module->warningMessage) {
                $warning = "(" . fg_yellow() . $module->warningMessage . color_reset() . ")";
            }
            echo sprintf("- %s-%s-%s %s%s%s\n", $module->name, $module->version, $module->release, $op, $error, $warning);
        }
        $ret = param_ask($options, "Proceed with installation", "Y/n", "Y");
        if (!preg_match('/^(y|yes|)$/i', $ret)) {
            return 0;
        }
    }
    
    return wiff_context_module_install_deplist($context, $options, $argv, $depList);
}

function wiff_context_module_install_remote(Context & $context, &$options, &$modName, &$argv)
{
    require_once ('lib/Lib.System.php');
    
    $existingModule = $context->getModuleInstalled($modName);
    if ($existingModule !== false) {
        echo sprintf("A module '%s' with version '%s-%s' already exists.\n", $existingModule->name, $existingModule->version, $existingModule->release);
        if (!boolopt('force', $options)) {
            return 0;
        }
    }
    unset($existingModule);
    
    $depList = $context->getModuleDependencies(array(
        $modName
    ));
    if ($depList === false) {
        printerr(sprintf("Error: %s\n", $context->errorMessage));
        return 1;
    }
    
    if (count($depList) > 1) {
        echo sprintf("Will (i)nstall, (u)pgrade, or (r)eplace the following modules:\n");
        foreach ($depList as $module) {
            if ($module->needphase == '') {
                $module->needphase = 'install';
            }
            $op = '(i)';
            if ($module->needphase == 'upgrade') {
                $op = '(u)';
            } else if ($module->needphase == 'replaced') {
                $op = '(r) (replaced by ' . (($module->replacedBy) ? $module->replacedBy : 'unknown') . ')';
            }
            $error = "";
            if ($module->errorMessage) {
                $error = "(" . fg_red() . $module->errorMessage . color_reset() . ")";
            }
            $warning = "";
            if ($module->warningMessage) {
                $warning = "(" . fg_yellow() . $module->warningMessage . color_reset() . ")";
            }
            echo sprintf("- %s-%s-%s %s%s%s\n", $module->name, $module->version, $module->release, $op, $error, $warning);
        }
        $ret = param_ask($options, "Proceed with installation", "Y/n", "Y");
        if (!preg_match('/^(y|yes|)$/i', $ret)) {
            return 0;
        }
    }
    
    return wiff_context_module_install_deplist($context, $options, $argv, $depList);
}

function wiff_context_module_install_deplist(Context & $context, &$options, &$argv, &$depList, $type = 'install')
{
    $downloaded = array();
    foreach ($depList as $module) {
        /**
         * @var Module $module
         */
        if ($module->needphase != '') {
            if ($module->needphase == 'replaced') {
                $type = 'unregister';
            } else {
                $type = $module->needphase;
            }
        }
        
        echo sprintf("\nProcessing module '%s' (%s-%s) for %s.\n", $module->name, $module->version, $module->release, $type);
        
        if ($module->needphase == 'replaced') {
            /**
             * Unregister module
             */
            $mod = $context->getModuleInstalled($module->name);
            if ($mod === false) {
                continue;
            }
            echo sprintf("Unregistering module '%s'.\n", $module->name);
            $ret = $context->removeModule($module->name);
            if ($ret === false) {
                printerr(sprintf("Error: could not unregister module '%s' from context: %s\n", $module->name, $context->errorMessage));
                return 1;
            }
            $ret = $context->deleteFilesFromModule($module->name);
            if ($ret === false) {
                printerr(sprintf("Error: could not delete files for module '%s': %s\n", $module->name, $context->errorMessage));
                return 1;
            }
            $ret = $context->deleteManifestForModule($module->name);
            if ($ret === false) {
                printerr(sprintf("Error: could not delete manifest file for module '%s': %s\n", $module->name, $context->errorMessage));
                return 1;
            }
            
            continue;
        }
        
        if ($module->status == 'downloaded' && is_file($module->tmpfile)) {
            echo sprintf("Module '%s-%s-%s' is already downloaded in '%s'.\n", $module->name, $module->version, $module->release, $module->tmpfile);
        } else {
            echo sprintf("Downloading module '%s-%s-%s'... ", $module->name, $module->version, $module->release);
            /**
             * download module
             */
            $ret = $module->download('downloaded');
            if ($ret === false) {
                printerr(sprintf("Error: could not download module '%s': %s\n", $module->name, $module->errorMessage));
                return 1;
            }
            if (!empty($module->warningMessage)) {
                echo sprintf("\nWarning: %s%s%s\n", fg_yellow(), $module->warningMessage, color_reset());
                $ret = param_ask($options, "Proceed with installation", "Y/n", "Y");
                if (!preg_match('/^(y|yes|)$/i', $ret)) {
                    $wiff = WIFF::getInstance();
                    $wiff->cleanup($context->name);
                    return 0;
                }
            }

            echo sprintf("[%sOK%s]\n", fg_green() , color_reset());
        }
        /**
         * switch to the module object from the context XML database
         */
        $modName = $module->name;
        $module = $context->getModuleDownloaded($modName);
        if ($module === false) {
            printerr(sprintf("Error: could not get module '%s' from context: %s\n", $modName, $context->errorMessage));
            return 1;
        }
        $wiff = WIFF::getInstance();
        /**
         * ask license
         */
        if ($module->license != '' && $wiff->getParam("check-license", false, true) != "no") {
            $license = $module->getLicenseText();
            if ($license === false) {
                printerr(sprintf("Error: could not get license '%s' for module '%s': %s\n", $module->license, $module->name, $module->errorMessage));
                return 1;
            }
            
            $licenseAgreement = $module->getLicenseAgreement();
            if ($license != '' && $licenseAgreement != 'yes') {
                $agree = license_ask($module->name, $module->license, $license, $options);
                if ($agree == 'yes') {
                    $module->storeLicenseAgreement($agree);
                } else {
                    printerr(sprintf("Notice: you did not agreed to '%s' for module '%s'.\n", $module->license, $module->name));
                    return 1;
                }
            }
        }
        /**
         * wstop
         */
        $context->wstop();
        /**
         * ask module parameters
         */
        $paramList = $module->getParameterList();
        if ($paramList !== false && count($paramList) > 0) {
            
            $title = sprintf("Parameters for module '%s'", $module->name);
            if (!boolopt('yes', $options)) {
                echo sprintf("\n%s\n%s\n\n", $title, str_repeat('-', strlen($title)));
            }
            
            foreach ($paramList as $param) {
                /**
                 * @var Parameter $param
                 */
                $visibility = $param->getVisibility($type);
                if ($visibility != 'W') {
                    continue;
                }
                
                $pvalue = $param->value == "" ? $param->default : $param->value;
                
                if (boolopt('yes', $options)) {
                    $value = $pvalue;
                } else {
                    $value = param_ask($options, $param->name, $pvalue, $pvalue);
                }
                if ($value === false) {
                    printerr(sprintf("Error: could not read answer!\n"));
                    return 1;
                }
                $param->value = $value;
                
                $ret = $module->storeParameter($param);
                if ($ret === false) {
                    printerr(sprintf("Error: could not store parameter '%s'!\n", $param->name));
                    return 1;
                }
                
                if (!boolopt('yes', $options)) {
                    echo "\n";
                }
            }
        }
        /**
         * Execute phase/process list
         */
        $phaseList = $module->getPhaseList($type);
        if (boolopt('nothing', $options)) {
            $phaseList = array_filter($phaseList, create_function('$v', 'return !preg_match("/^(pre|post)-/",$v);'));
        }
        if (boolopt('nopre', $options)) {
            $phaseList = array_filter($phaseList, create_function('$v', 'return !preg_match("/^pre-/",$v);'));
        }
        if (boolopt('nopost', $options)) {
            $phaseList = array_filter($phaseList, create_function('$v', 'return !preg_match("/^post-/",$v);'));
        }
        
        foreach ($phaseList as $phaseName) {
            echo sprintf("Doing '%s' of module '%s'.\n", $phaseName, $module->name);
            
            switch ($phaseName) {
                case 'clean-unpack':
                    echo sprintf("Removing old files from module '%s'.\n", $module->name);
                    $ret = $context->deleteFilesFromModule($module->name);
                    if ($ret === false) {
                        printerr(sprintf("Error: could not delete old files for module '%s' in '%s': %s\n", $module->name, $context->root, $context->errorMessage));
                        return 1;
                    }
                    echo sprintf("[%sOK%s]\n", fg_green() , color_reset());
                    // Chain with 'unpack'
                    
                case 'unpack':
                    echo sprintf("Unpacking module '%s'... ", $module->name);
                    $ret = $module->unpack($context->root);
                    if ($ret === false) {
                        printerr(sprintf("Error: could not unpack module '%s' in '%s': %s\n", $module->name, $context->root, $module->errorMessage));
                        return 1;
                    }
                    echo sprintf("[%sOK%s]\n", fg_green() , color_reset());
                    break;

                case 'unregister-module':
                    echo sprintf("Unregistering module '%s'... ", $module->name);
                    $ret = $context->removeModule($module->name);
                    if ($ret === false) {
                        printerr(sprintf("Error: could not remove module '%s' in '%s': %s\n", $module->name, $context->root, $context->errorMessage));
                        return 1;
                    }
                    $ret = $context->deleteFilesFromModule($module->name);
                    if ($ret === false) {
                        printerr(sprintf("Error: could not delete files for module '%s' in '%s': %s\n", $module->name, $context->root, $context->errorMessage));
                        return 1;
                    }
                    $ret = $context->deleteManifestForModule($module->name);
                    if ($ret === false) {
                        printerr(sprintf("Error: could not delete manifest for module '%s' in '%s': %s\n", $module->name, $context->root, $context->errorMessage));
                        return 1;
                    }
                    echo sprintf("[%sOK%s]\n", fg_green() , color_reset());
                    break;

                case 'purge-unreferenced-parameters-value':
                    echo sprintf("Purging unreferenced parameters value...");
                    $ret = $context->purgeUnreferencedParametersValue();
                    if ($ret === false) {
                        printerr(sprintf("Error: could not purge unreferenced parameters value in '%s': %s\n", $context->root, $context->errorMessage));
                        return 1;
                    }
                    echo sprintf("[%sOK%s]\n", fg_green() , color_reset());
                    break;

                default:
                    if (($ret = executeModulePhase($module, $phaseName, $options)) != 0) {
                        return $ret;
                    }
                    break;
            }
        }
        /**
         * set status to 'installed'
         */
        if ($type == 'upgrade') {
            $ret = $context->removeModuleInstalled($module->name);
            if ($ret === false) {
                printerr(sprintf("Error: Could not remove old installed module '%s': %s\n", $module->name, $context->errorMessage));
                return 1;
            }
        }
        $ret = $module->setStatus('installed');
        if ($ret === false) {
            printerr(sprintf("Error: Could not set installed status on module '%s': %s\n", $module->name, $module->errorMessage));
            return 1;
        }
        $module->cleanupDownload();
        /**
         * send context registration statistyics
         */
        $wiff = WIFF::getInstance();
        $registrationInfo = $wiff->checkInitRegistration();
        if ($registrationInfo['status'] == 'registered' && $context->register == 'registered') {
            echo sprintf("Sending context configuration... ");
            $ret = $wiff->sendContextConfiguration($context->name);
            if ($ret === false) {
                $err = sprintf("Error: Could not send context configuration: %s", $wiff->errorMessage);
                echo sprintf("[%sSKIPPED%s] (%s)\n", fg_blue() , color_reset() , $err);
            } else {
                echo sprintf("[%sOK%s]\n", fg_green() , color_reset());
            }
        }
        /**
         * wstart
         */
        $ret = $context->wstart();
        
        array_push($downloaded, $module);
    }
    
    echo sprintf("\nDone.\n\n");
    
    return 0;
}
/**
 * wiff context <ctxName> module upgrade
 * @param Context $context
 * @param $argv
 * @return int
 */
function wiff_context_module_upgrade(&$context, &$argv)
{
    $options = parse_argv_options($argv);
    
    $modName = array_shift($argv);
    
    if (is_file($modName)) {
        return wiff_context_module_upgrade_local($context, $options, $modName, $argv);
    } else {
        return wiff_context_module_upgrade_remote($context, $options, $modName, $argv);
    }
}

function wiff_context_module_upgrade_local(Context & $context, &$options, &$pkgName, &$argv)
{
    require_once ('lib/Lib.System.php');
    
    $tmpfile = WiffLibSystem::tempnam(null, basename($pkgName));
    if ($tmpfile === false) {
        printerr(sprintf("Error: could not create temp file!\n"));
        return 1;
    }
    
    $ret = copy($pkgName, $tmpfile);
    if ($ret === false) {
        printerr(sprintf("Error: could not copy '%s' to '%s'!\n", $pkgName, $tmpfile));
        return 1;
    }
    
    $tmpMod = $context->loadModuleFromPackage($tmpfile);
    if ($tmpMod === false) {
        printerr(sprintf("Error: could not load module '%s': %s\n", $tmpfile, $context->errorMessage));
        return 1;
    }
    
    $existingModule = $context->getModuleInstalled($tmpMod->name);
    if ($existingModule !== false) {
        $cmp = $context->cmpVersionReleaseAsc($tmpMod->version, $tmpMod->release, $existingModule->version, $existingModule->release);
        if ($cmp <= 0) {
            echo sprintf("A module '%s' with version '%s-%s' already exists.\n", $existingModule->name, $existingModule->version, $existingModule->release);
            if (!boolopt('force', $options)) {
                return 0;
            }
        }
    }
    unset($existingModule);
    
    $tmpMod = $context->importArchive($tmpfile, 'downloaded');
    if ($tmpMod === false) {
        printerr(sprintf("Error: could not import module '%s': %s\n", $tmpfile, $context->errorMessage));
        return 1;
    }
    if (!empty($context->warningMessage)) {
        echo sprintf("\nWarning: %s%s%s\n", fg_yellow(), $context->warningMessage, color_reset());
        $ret = param_ask($options, "Proceed with installation", "Y/n", "Y");
        if (!preg_match('/^(y|yes|)$/i', $ret)) {
            $wiff = WIFF::getInstance();
            $wiff->cleanup($context->name);
            return 0;
        }
    }

    $depList = $context->getLocalModuleDependencies($tmpfile);
    if ($depList === false) {
        printerr(sprintf("Error: could not get dependencies for '%s': %s\n", $tmpfile, $context->errorMessage));
        return 1;
    }
    
    if (count($depList) > 1) {
        echo sprintf("Will (i)nstall, (u)pgrade or (r)eplace the following modules:\n");
        foreach ($depList as $module) {
            if ($module->needphase == '') {
                $module->needphase = 'upgrade';
            }
            $op = '(i)';
            if ($module->needphase == 'upgrade') {
                $op = '(u)';
            } else if ($module->needphase == 'replaced') {
                $op = '(r) (replaced by ' . (($module->replacedBy) ? $module->replacedBy : 'unknown') . ')';
            }
            $error = "";
            if ($module->errorMessage) {
                $error = "(" . fg_red() . $module->errorMessage . color_reset() . ")";
            }
            $warning = "";
            if ($module->warningMessage) {
                $warning = "(" . fg_yellow() . $module->warningMessage . color_reset() . ")";
            }
            echo sprintf("- %s-%s-%s %s%s%s\n", $module->name, $module->version, $module->release, $op, $error, $warning);
        }
        $ret = param_ask($options, "Proceed with upgrade", "Y/n", "Y");
        if (!preg_match('/^(y|yes|)$/i', $ret)) {
            return 0;
        }
    }
    
    return wiff_context_module_install_deplist($context, $options, $argv, $depList, 'upgrade');
}

function wiff_context_module_upgrade_remote(Context & $context, &$options, &$modName, &$argv)
{
    require_once ('lib/Lib.System.php');
    
    $tmpMod = $context->getModuleAvail($modName);
    if ($tmpMod === false) {
        printerr(sprintf("Error: could not find a module named '%s'!\n", $modName));
        return 1;
    }
    
    $existingModule = $context->getModuleInstalled($modName);
    if ($existingModule !== false) {
        $cmp = $context->cmpVersionReleaseAsc($tmpMod->version, $tmpMod->release, $existingModule->version, $existingModule->release);
        if ($cmp <= 0) {
            echo sprintf("A module '%s' with version '%s-%s' already exists.\n", $existingModule->name, $existingModule->version, $existingModule->release);
            if (!boolopt('force', $options)) {
                return 0;
            }
        }
    }
    unset($existingModule);
    unset($tmpMod);
    
    $depList = $context->getModuleDependencies(array(
        $modName
    ));
    if ($depList === false) {
        printerr(sprintf("Error: %s\n", $context->errorMessage));
        return 1;
    }
    
    if (count($depList) > 1) {
        echo sprintf("Will (i)nstall, (u)pgrade or (r)eplace the following modules:\n");
        foreach ($depList as $module) {
            if ($module->needphase == '') {
                $module->needphase = 'upgrade';
            }
            $op = '(i)';
            if ($module->needphase == 'upgrade') {
                $op = '(u)';
            } else if ($module->needphase == 'replaced') {
                $op = '(r) (replaced by ' . (($module->replacedBy) ? $module->replacedBy : 'unknown') . ')';
            }
            $error = "";
            if ($module->errorMessage) {
                $error = "(" . fg_red() . $module->errorMessage . color_reset() . ")";
            }
            $warning = "";
            if ($module->warningMessage) {
                $warning = "(" . fg_yellow() . $module->warningMessage . color_reset() . ")";
            }
            echo sprintf("- %s-%s-%s %s%s%s\n", $module->name, $module->version, $module->release, $op, $error, $warning);
        }
        $ret = param_ask($options, "Proceed with upgrade", "Y/n", "Y");
        if (!preg_match('/^(y|yes|)$/i', $ret)) {
            return 0;
        }
    }
    
    return wiff_context_module_install_deplist($context, $options, $argv, $depList, 'upgrade');
}
/**
 * wiff context <ctxName> module extract
 * @param Context $context
 * @param $argv
 * @return int
 */
function wiff_context_module_extract(&$context, &$argv)
{
    return 0;
}

function wiff_context_module_list(&$context, &$argv)
{
    $op = array_shift($argv);
    
    switch ($op) {
        case 'help':
            return wiff_context_module_list_help($context, $argv);
            break;

        case 'installed':
            return wiff_context_module_list_installed($context, $argv);
            break;

        case 'upgrade':
            return wiff_context_module_list_upgrade($context, $argv);
            break;

        case 'available':
            return wiff_context_module_list_available($context, $argv);
            break;

        default:
            return wiff_context_module_list_help($context, $argv);
    }
}

function wiff_context_module_list_help(&$context, &$argv)
{
    echo "\n";
    echo "Usage\n";
    echo "-----\n";
    echo "\n";
    echo "  wiff context <context-name> module list installed|available|upgrade\n";
    echo "\n";
    return 0;
}

function wiff_context_module_list_upgrade(Context & $context, &$argv)
{
    $options = parse_argv_options($argv);
    
    $installedList = $context->getInstalledModuleListWithUpgrade(true);
    if ($installedList === false) {
        printerr(sprintf("Error: could not get list of installed modules: %s\n", $context->errorMessage));
        return 1;
    }
    
    if (count($installedList) <= 0) {
        echo sprintf("Found no modules...\n");
        return 0;
    }
    
    if (boolopt('pretty', $options)) {
        echo sprintf("%-32s   %-16s   %-16s\n", "Name", "Current", "Latest");
        echo sprintf("%-32s---%-16s---%-16s\n", str_repeat('-', 32) , str_repeat('-', 16) , str_repeat('-', 16));
    }
    foreach ($installedList as $module) {
        if (!$module->canUpdate) {
            continue;
        }
        $updateName = (isset($module->updateName) && $module->updateName != '') ? $module->updateName : $module->name;
        if (boolopt('pretty', $options)) {
            echo sprintf("%-32s   %-16s   %-16s\n", $updateName, sprintf("%s-%s", $module->version, $module->release) , $module->availableversionrelease);
        } else {
            echo sprintf("%s (%s)\n", $updateName, $module->availableversionrelease);
        }
    }
    
    return 0;
}

function wiff_context_module_list_installed(Context & $context, &$argv)
{
    $options = parse_argv_options($argv);
    
    $installedList = $context->getInstalledModuleList();
    if ($installedList === false) {
        printerr(sprintf("Error: could not get list of installed modules: %s\n", $context->errorMessage));
        return 1;
    }
    
    if (count($installedList) <= 0) {
        echo sprintf("Found no modules...\n");
        return 0;
    }
    
    if (boolopt('pretty', $options)) {
        echo sprintf("%-32s   %-16s\n", "Name", "Version");
        echo sprintf("%-32s---%-16s\n", str_repeat('-', 32) , str_repeat('-', 16));
    }
    foreach ($installedList as $module) {
        if (boolopt('pretty', $options)) {
            echo sprintf("%-32s   %-16s\n", $module->name, sprintf("%s-%s", $module->version, $module->release));
        } else {
            echo sprintf("%s (%s-%s)\n", $module->name, $module->version, $module->release);
        }
    }
    
    return 0;
}

function wiff_context_module_list_available(Context & $context, &$argv)
{
    $options = parse_argv_options($argv);
    
    $availList = $context->getAvailableModuleList();
    if ($availList === false) {
        printerr(sprintf("Error: could not get list of available modules: %s\n", $context->errorMessage));
        return 1;
    }
    
    if (count($availList) <= 0) {
        echo sprintf("Found no modules...\n");
        return 0;
    }
    
    if (boolopt('pretty', $options)) {
        echo sprintf("%-32s   %-16s\n", "Name", "Version");
        echo sprintf("%-32s---%-16s\n", str_repeat('-', 32) , str_repeat('-', 16));
    }
    foreach ($availList as $module) {
        if (boolopt('pretty', $options)) {
            echo sprintf("%-32s   %-16s\n", $module->name, sprintf("%s-%s", $module->version, $module->release));
        } else {
            echo sprintf("%s (%s-%s)\n", $module->name, $module->version, $module->release);
        }
    }
    
    return 0;
}
/**
 * wiff context <ctxName> param
 * @param Context $context
 * @param $argv
 * @return int
 */
function wiff_context_param(&$context, &$argv)
{
    $op = array_shift($argv);
    
    switch ($op) {
        case 'show':
            return wiff_context_param_show($context, $argv);
            break;

        case 'get':
            return wiff_context_param_get($context, $argv);
            break;

        case 'set':
            wiff_lock();
            $ret = wiff_context_param_set($context, $argv);
            wiff_unlock();
            return $ret;
            break;

        case 'help':
            return wiff_context_param_help($context, $argv);
            break;

        default:
            return wiff_context_param_help($context, $argv);
            break;
    }
}

function wiff_context_param_help(&$context, &$argv)
{
    echo "\n";
    echo "Usage\n";
    echo "-----\n";
    echo "\n";
    echo "  wiff context <context-name> param show [<module-name>]\n";
    echo "  wiff context <context-name> param get <module-name>:<param-name>\n";
    echo "  wiff context <context-name> param set <module-name>:<param-name> <param-value>\n";
    echo "\n";
    return 0;
}

function wiff_context_param_show(Context & $context, &$argv)
{
    $showList = array();
    
    while ($modName = array_shift($argv)) {
        $module = $context->getModuleInstalled($modName);
        if ($module === false) {
            continue;
        }
        array_push($showList, $module);
    }
    
    if (count($showList) <= 0) {
        $showList = $context->getInstalledModuleList();
    }
    
    foreach ($showList as $module) {
        /**
         * @var Module $module
         */
        $paramList = $module->getParameterList();
        if ($paramList === false) {
            continue;
        }
        foreach ($paramList as $param) {
            echo sprintf("%s:%s = %s\n", $module->name, $param->name, $param->value);
        }
    }
    
    return 0;
}

function wiff_context_param_get(Context & $context, &$argv)
{
    $modParam = array_shift($argv);
    if ($modParam === null) {
        printerr(sprintf("Error: missing module-name:param-name.\n"));
        return 1;
    }
    
    $m = array();
    if (!preg_match('/^([a-zA-Z0-9_-]+):([a-zA-Z0-9_-]+)$/', $modParam, $m)) {
        printerr(sprintf("Error: malformed module-name:param-name specifier '%s'.\n", $modParam));
        return 1;
    }
    
    $modName = $m[1];
    $paramName = $m[2];
    
    $module = $context->getModuleInstalled($modName);
    if ($module === false) {
        printerr(sprintf("Error: could not get module '%s': %s\n", $modName, $context->errorMessage));
        return 1;
    }
    
    $parameter = $module->getParameter($paramName);
    if ($parameter === false) {
        printerr(sprintf("Error: could not get parameter '%s' for module '%s': %s\n", $paramName, $modName, $module->errorMessage));
        return 1;
    }
    
    echo sprintf("%s:%s = %s\n", $modName, $paramName, $parameter->value);
    
    return 0;
}

function wiff_context_param_set(Context & $context, &$argv)
{
    $modParam = array_shift($argv);
    if ($modParam === null) {
        printerr(sprintf("Error: missing module-name:param-name.\n"));
        return 1;
    }
    
    $paramValue = array_shift($argv);
    if ($paramValue === null) {
        printerr(sprintf("Error: missing param-value.\n"));
        return 1;
    }
    
    $m = array();
    if (!preg_match('/^([a-zA-Z0-9_-]+):([a-zA-Z0-9_-]+)$/', $modParam, $m)) {
        printerr(sprintf("Error: malformed module-name:param-name specifier '%s'.\n", $modParam));
        return 1;
    }
    
    $modName = $m[1];
    $paramName = $m[2];
    
    $module = $context->getModuleInstalled($modName);
    if ($module === false) {
        printerr(sprintf("Error: could not get module '%s'.\n", $modName));
        return 1;
    }
    
    $parameter = $module->getParameter($paramName);
    if ($parameter === false) {
        printerr(sprintf("Error: could not get parameter '%s' for module '%s': %s\n", $paramName, $modName, $module->errorMessage));
        return 1;
    }
    
    $parameter->value = $paramValue;
    $ret = $module->storeParameter($parameter);
    if ($ret === false) {
        printerr(sprintf("Error: could not set paremter '%s' for module '%s': %s\n", $paramName, $modName, $module->errorMessage));
        return 1;
    }
    
    $parameter = $module->getParameter($paramName);
    if ($parameter === false) {
        printerr(sprintf("Error: could not get back parameter '%s' for module '%s': %s\n", $paramName, $modName, $module->errorMessage));
        return 1;
    }
    
    echo sprintf("%s:%s = %s\n", $modName, $paramName, $parameter->value);
    
    return 0;
}

function wiff_whattext(&$argv)
{
    $ctx_name = array_shift($argv);
    
    $wiff = WIFF::getInstance();
    
    $context = $wiff->getContext($ctx_name);
    if ($context === false) {
        printerr(sprintf("Error: could not get context '%s'!\n", $ctx_name));
        return 1;
    }
    
    $whattext = sprintf("%s/whattext", $context->root);
    if (!is_executable($whattext)) {
        printerr(sprintf("Error: whattext '%s' not found or not executable.\n", $whattext));
        return 1;
    }
    
    $cmd = sprintf("%s", escapeshellarg($whattext));
    system($cmd, $ret);
    
    return $ret;
}

function wiff_wstop(&$argv)
{
    $ctx_name = array_shift($argv);
    
    $wiff = WIFF::getInstance();
    
    $context = $wiff->getContext($ctx_name);
    if ($context === false) {
        printerr(sprintf("Error: could not get context '%s'!\n", $ctx_name));
        return 1;
    }
    
    $wstart = sprintf("%s/wstop", $context->root);
    if (!is_executable($wstart)) {
        printerr(sprintf("Error: wstop '%s' not found or not executable.\n", $wstart));
        return 1;
    }
    
    $cmd = sprintf("%s", escapeshellarg($wstart));
    system($cmd, $ret);
    
    return $ret;
}

function wiff_wstart(&$argv)
{
    $ctx_name = array_shift($argv);
    
    $wiff = WIFF::getInstance();
    
    $context = $wiff->getContext($ctx_name);
    if ($context === false) {
        printerr(sprintf("Error: could not get context '%s'!\n", $ctx_name));
        return 1;
    }
    
    $wstart = sprintf("%s/wstart", $context->root);
    if (!is_executable($wstart)) {
        printerr(sprintf("Error: wstart '%s' not found or not executable.\n", $wstart));
        return 1;
    }
    
    $cmd = sprintf("%s", escapeshellarg($wstart));
    system($cmd, $ret);
    
    return $ret;
}

function wiff_default(&$argv)
{
    if (stripos($argv[0], '--getValue=') !== false) {
        return wiff_default_getValue($argv);
    }
    return wiff_help($argv);
}

function wiff_default_getValue(&$argv)
{
    $paramName = substr($argv[0], 11);
    
    $value = wiff_getParamValue($paramName);
    if ($value === false) {
        return 1;
    }
    echo $value . "\n";
    return 0;
}

function wiff_getParamValue($paramName)
{
    $wiffContextName = getenv('WIFF_CONTEXT_NAME');
    if ($wiffContextName === false || preg_match('/^\s*$/', $wiffContextName)) {
        printerr(sprintf("Error: WIFF_CONTEXT_NAME is not defined or empty.\n"));
        return false;
    }
    
    $wiff = WIFF::getInstance();
    $context = $wiff->getContext($wiffContextName);
    if ($context === false) {
        printerr(sprintf("Error: could not get context '%s': %s\n", $wiffContextName, $wiff->errorMessage));
        return false;
    }
    
    return $context->getParamByName($paramName);
}

function parse_argv_options(&$argv)
{
    $options = array();
    $m = array();
    while (count($argv) > 0 && preg_match('/^--/', $argv[0])) {
        if (preg_match('/^--([a-zA-Z0-9_-]+)=(.*)$/', $argv[0], $m)) {
            $options[$m[1]] = $m[2];
        } elseif (preg_match('/--([a-zA-Z0-9_-]+)$/', $argv[0], $m)) {
            $options[$m[1]] = true;
        } elseif (preg_match('/^--$/', $argv[0])) {
            array_shift($argv);
            return $options;
        }
        array_shift($argv);
    }
    
    return $options;
}

function boolopt($opt, &$options)
{
    if (array_key_exists($opt, $options) && $options[$opt]) {
        return true;
    }
    return false;
}

function param_ask($options, $prompt, $choice, $default, $unattendedDefault = false)
{
    echo sprintf("%s ? [%s] ", $prompt, $choice);
    if (boolopt('unattended', $options)) {
        $ans = ($unattendedDefault !== false) ? $unattendedDefault : $default;
        echo $ans . "\n";
        return $ans;
    }
    $fh = fopen('php://stdin', 'r');
    if ($fh === false) {
        return false;
    }
    $ans = fgets($fh);
    if ($ans === false) {
        return $ans;
    }
    $ans = rtrim($ans);
    if ($ans == "") {
        return $default;
    }
    return $ans;
}

function license_ask($moduleName, $licenseName, $license, &$options)
{
    $licStart = sprintf("=== License agreement for module '%s' ===\n", $moduleName);
    $licSub = sprintf("License: %s\n", $licenseName);
    $licSep = sprintf("%s", str_repeat("-", 72));
    
    $licenseLines = preg_split("/\r?\n/", $license);
    
    echo $licStart;
    echo $licSub;
    echo $licSep . "\n";
    
    $max_lines = getenv('LINES');
    if (!is_numeric($max_lines) || $max_lines <= 6) {
        $max_lines = 20;
    }
    $max_lines-= 5;
    
    while (true) {
        $lines = array_splice($licenseLines, 0, $max_lines);
        echo join("\n", $lines);
        if (count($lines) < $max_lines) {
            break;
        }
        echo "\n";
        $ans = param_ask($options, "--- View next page ---", "press enter to view next page", "", "q");
        if (preg_match("/^(q|quit|end)$/i", $ans)) {
            break;
        }
    }
    echo "\n" . $licSep . "\n";
    
    while (true) {
        $ans = param_ask($options, "Do you agree", "y/n", "", "y");
        if (preg_match("/^(y|yes|oui)/i", $ans)) {
            return 'yes';
        }
        if (preg_match("/^(n|no|non)/i", $ans)) {
            return 'no';
        }
    }
    
    return 'no';
}
/**
 * ANSI colors
 * @return string
 */
function fg_black()
{
    return (!posix_isatty(STDOUT)) ? '' : chr(0x1b) . '[30m';
}

function bg_black()
{
    return (!posix_isatty(STDOUT)) ? '' : chr(0x1b) . '[40m';
}

function fg_white()
{
    return (!posix_isatty(STDOUT)) ? '' : chr(0x1b) . '[37m';
}

function bg_white()
{
    return (!posix_isatty(STDOUT)) ? '' : chr(0x1b) . '[47m';
}

function fg_red()
{
    return (!posix_isatty(STDOUT)) ? '' : chr(0x1b) . '[31m';
}

function bg_red()
{
    return (!posix_isatty(STDOUT)) ? '' : chr(0x1b) . '[41m';
}

function fg_green()
{
    return (!posix_isatty(STDOUT)) ? '' : chr(0x1b) . '[32m';
}

function bg_green()
{
    return (!posix_isatty(STDOUT)) ? '' : chr(0x1b) . '[42m';
}

function fg_blue()
{
    return (!posix_isatty(STDOUT)) ? '' : chr(0x1b) . '[34m';
}

function bg_blue()
{
    return (!posix_isatty(STDOUT)) ? '' : chr(0x1b) . '[44m';
}

function fg_yellow()
{
    return (!posix_isatty(STDOUT)) ? '' : chr(0x1b) . '[33m';
}

function bg_yellow()
{
    return (!posix_isatty(STDOUT)) ? '' : chr(0x1b) . '[43m';
}

function color_reset()
{
    return (!posix_isatty(STDOUT)) ? '' : chr(0x1b) . '[0m';
}

function ascii_underline($msg) {
    return $msg . "\n" . str_repeat('-', strlen($msg)) . "\n";
}

/**
 * change UID to the owner of the wiff script
 * @param $path
 * @return bool
 */
function setuid_wiff($path)
{
    $stat = stat($path);
    if ($stat === false) {
        printerr(sprintf("Error: could not stat '%s'!\n", $path));
        return false;
    }
    
    $uid = posix_getuid();
    
    $wiff_uid = $stat['uid'];
    $wiff_gid = $stat['gid'];
    
    if ($uid != $wiff_uid) {
        $ret = posix_setgid($wiff_gid);
        if ($ret === false) {
            printerr(sprintf("Error: could not setgid to gid '%s'.\n", $wiff_gid));
            return false;
        }
        $ret = posix_setuid($wiff_uid);
        if ($ret === false) {
            printerr(sprintf("Error: could not setuid to uid '%s'.\n", $wiff_uid));
            return false;
        }
    }
    
    return true;
}
/**
 * Delete context
 * @param $argv
 * @return int
 */
function wiff_delete(&$argv)
{
    $op = array_shift($argv);
    
    switch ($op) {
        case 'context':
            wiff_lock();
            $ret = wiff_delete_context($argv);
            wiff_unlock();
            return $ret;
            break;

        case 'archive':
            wiff_lock();
            $ret = wiff_delete_archive($argv);
            wiff_unlock();
            return $ret;
            break;

        default:
            printerr(sprintf("Unknown operation '%s'!\n", $op));
            return wiff_delete_help($argv);
    }
}

function wiff_delete_help(&$argv)
{
    echo "\n";
    echo "Usage\n";
    echo "-----\n";
    echo "\n";
    echo "  wiff delete context [delete-options] <context-name>\n";
    echo "  wiff delete archive <archive-id>\n";
    echo "\n";
    echo "delete-options\n";
    echo "---------------\n";
    echo "\n";
    echo "  --nopre    Do not execute pre-install processes.\n";
    echo "  --force    Force deletion of context (do not stop/ask on pre-delete processes errors).\n";
    echo "\n";
    return 0;
}

function wiff_delete_context(&$argv)
{
    $options = parse_argv_options($argv);

    $ctx_name = array_shift($argv);
    if ($ctx_name == "") {
        return wiff_delete_help($argv);
    }
    
    $wiff = WIFF::getInstance();

    /*
     * Execute modules' pre-delete processes
     */

    $context = $wiff->getContext($ctx_name);
    if ($context === false) {
        printerr(sprintf("Error: could not get context '%s': %s\n", $ctx_name, $wiff->errorMessage));
        return 1;
    }

    $context->wstop();

    $moduleList = $context->getInstalledModuleList();
    if (count($moduleList) > 0) {
        $moduleNameList = array();
        foreach ($moduleList as & $module) {
            $moduleNameList[] = $module->name;
        }
        unset($module);
        $moduleList = $context->getModuleDependencies($moduleNameList, false, true);
        if ($moduleList === false) {
            printerr(sprintf("Error: could not get dependencies order: %s\n", $context->errorMessage));
            return 1;
        }
        $moduleList = array_reverse($moduleList);
        /**
         * @var Module $module
         */
        foreach ($moduleList as & $module) {
            printf("\nProcessing module '%s' (%s-%s) for %s.\n", $module->name, $module->version, $module->release, 'delete');
            $phaseList = $module->getPhaseList('delete');
            if (boolopt('nopre', $options)) {
                $phaseList = array_filter($phaseList, create_function('$v', 'return !preg_match("/^pre-/",$v);'));
            }
            foreach ($phaseList as & $phaseName) {
                printf("Doing '%s' of module '%s'.\n", $phaseName, $module->name);
                if (($ret = executeModulePhase($module, $phaseName, $options)) != 0) {
                    return $ret;
                }
                unset($process);
            }
            unset($phaseName);
        }
        unset($module);
    }

    /*
     * Delete context
     */

    printf("Deleting context '%s'... ", $context->name);
    $res = 0;
    $wiff->deleteContext($ctx_name, $res);
    if ($res === false) {
        printerr(sprintf("Error: could not delete context '%s': %s\n", $ctx_name, $wiff->errorMessage));
        return 1;
    }
    echo sprintf("[%sOK%s]\n", fg_green() , color_reset());

    return 0;
}

function wiff_crontab_help()
{
    echo "\n";
    echo "Usage\n";
    echo "-----\n";
    echo "\n";
    echo "  wiff crontab cmd <register|unregister> file <path/to/cronfile> [user=<uid>]\n";
    echo "  wiff crontab cmd list [user=<uid>]\n";
    echo "\n";
    return 0;
}

function wiff_crontab(&$argv)
{
    include_once "class/Class.Crontab.php";
    
    $cmd = "";
    $file = "";
    $user = null;
    while ($op = array_shift($argv)) {
        switch ($op) {
            case 'cmd':
                $cmd = array_shift($argv);
                break;

            case 'file':
                $file = array_shift($argv);
                break;

            case 'user':
                $user = array_shift($argv);
                break;

            case 'help':
                return wiff_crontab_help();
                break;

            default:
                printerr(sprintf("Unknown operation '%s'!\n", $op));
                return wiff_crontab_help();
        }
    }
    switch ($cmd) {
        case 'list':
            $crontab = new Crontab($user);
            $ret = $crontab->listAll();
            return !$ret;
            break;

        case 'register':
            if (!$file) {
                printerr("Error: missing file argument\n");
                wiff_crontab_help();
                return 1;
            }
            $crontab = new Crontab($user);
            $ret = $crontab->registerFile($file);
            return !$ret;
            break;

        case 'unregister':
            if ($file === NULL) {
                printerr("Error: missing file argument\n");
                wiff_crontab_help();
                return 1;
            }
            $crontab = new Crontab($user);
            $ret = $crontab->unregisterFile($file);
            return !$ret;
            break;

        default:
            return wiff_crontab_help();
    }
}

function wiff_send_configuration()
{
    $wiff = WIFF::getInstance();
    $diff = $wiff->getParam("auto-configuration-sender-interval");
    if (!$diff) return 0;
    $date = getdate();
    if (($date["yday"] % $diff) != 0) {
        return 0;
    }
    $contextList = $wiff->getContextList();
    foreach ($contextList as $context) {
        /**
         * @var Context $context
         */
        $context->sendConfiguration();
    }
    return 0;
}

/**
 * @param $argv
 * @return int
 */
function wiff_create(&$argv) {
    $subject = array_shift($argv);
    switch ($subject) {
        case 'context':
            wiff_lock();
            $ret = wiff_create_context($argv);
            wiff_unlock();
            return $ret;
            break;
        default:
            wiff_create_help($argv);
    }
    return 0;
}

/**
 * @param $argv
 * @return int
 */
function wiff_create_help(&$argv) {
    echo "\n";
    echo "Usage\n";
    echo "-----\n";
    echo "\n";
    echo "  wiff create context <context-name> <context-root>\n";
    echo "\n";
    return 0;
}

/**
 * @param $argv
 * @return int
 */
function wiff_create_context(&$argv) {
    $contextName = array_shift($argv);
    if ($contextName === null) {
        printerr(sprintf("Error: missing context-name.\n"));
        return 1;
    }
    $contextRoot = array_shift($argv);
    if ($contextRoot === null) {
        printerr(sprintf("Error: missing context-root.\n"));
        return 1;
    }


    $wiff = WIFF::getInstance();

    $ret = $wiff->createContext($contextName, $contextRoot, '', '');
    if ($ret === false) {
        printerr(sprintf("Error: could not create context '%s' in '%s': %s\n", $contextName, $contextRoot));
        return 1;
    }

    return 0;
}

/**
 * @param Context $context
 * @param $argv
 * @return int
 */
function wiff_context_property(&$context, &$argv) {
    $op = array_shift($argv);
    switch ($op) {
        case 'help':
            return wiff_context_property_help($argv);
        case 'show':
            $ret = wiff_context_property_show($context, $argv);
            return $ret;
        case 'get':
            $ret = wiff_context_property_get($context, $argv);
            return $ret;
            break;
        case 'set':
            wiff_lock();
            $ret = wiff_context_property_set($context, $argv);
            wiff_unlock();
            return $ret;
            break;
        default:
            wiff_context_property_help($argv);
    }
    return 0;
}

/**
 * @param $argv
 * @return int
 */
function wiff_context_property_help(&$argv) {
    echo "\n";
    echo "Usage\n";
    echo "-----\n";
    echo "\n";
    echo "  wiff create context <context-name> property show\n";
    echo "  wiff create context <context-name> property get <property-name>\n";
    echo "  wiff create context <context-name> property set <property-name> <property-value>\n";
    echo "\n";
    return 0;
}

/**
 * @param Context $context
 * @param $argv
 * @return int
 */
function wiff_context_property_show(&$context, &$argv) {
    $pList = $context->getAllProperties();
    foreach ($pList as $pName => $pValue) {
        if ($pValue === null) {
            $pValue = '';
        }
        printf("%s = %s\n", $pName, $pValue);
    }
    return 0;
}

/**
 * @param Context $context
 * @param $argv
 * @return int
 */
function wiff_context_property_get(&$context, &$argv) {
    $propName = array_shift($argv);
    if ($propName === null) {
        printerr(sprintf("Error: missing prop-name.\n"));
        return 1;
    }
    $pValue = $context->getProperty($propName);
    if ($pValue === null) {
        $pValue = '';
    }
    printf("%s = %s\n", $propName, $pValue);
    return 0;
}

/**
 * @param Context $context
 * @param $argv
 * @return int
 */
function wiff_context_property_set(&$context, &$argv) {
    $propName = array_shift($argv);
    if ($propName === null) {
        printerr(sprintf("Error: missing prop-name.\n"));
        return 1;
    }
    $propValue = array_shift($argv);
    if ($propValue === null) {
        printerr(sprintf("Error: missing prop-value.\n"));
        return 1;
    }
    $ret = $context->setProperty($propName, $propValue);
    if ($ret === false) {
        printerr(sprintf("Error: could not set property value"));
        return 1;
    }
    return 0;
}

/**
 * @param $argv
 * @return int
 */
function wiff_repository(&$argv) {
    $cmd = array_shift($argv);
    switch ($cmd) {
        case 'list':
            return wiff_repository_list($argv);
            break;
        case 'add':
            wiff_lock();
            $ret = wiff_repository_add($argv);
            wiff_unlock();
            return $ret;
            break;
        case 'delete':
            wiff_lock();
            $ret = wiff_repository_delete($argv);
            wiff_unlock();
            return $ret;
            break;
        default:
            wiff_repository_help($argv);
    }
    return 0;
}

/**
 * @param $argv
 * @return int
 */
function wiff_repository_help(&$argv) {
    echo "\n";
    echo "Usage\n";
    echo "-----\n";
    echo "\n";
    echo "  wiff repository list [--show-password]\n";
    echo "  wiff repository add <repo-name> <repo-url> <repo-auth-user> <repo-auth-password>\n";
    echo "  wiff repository delete <repo-name>\n";
    echo "  wiff repository delete --all\n";
    echo "\n";
    return 0;
}

/**
 * @param $argv
 * @return int
 */
function wiff_repository_list(&$argv) {
    $options = parse_argv_options($argv);
    $wiff = WIFF::getInstance();
    $repoList = $wiff->getRepoList(false);
    if ($repoList === false) {
        printerr(sprintf("Error: %s", $wiff->errorMessage));
        return 1;
    }
    foreach ($repoList as & $repo) {
        $name = $repo->name;
        $url = $repo->getUrl();
        if (!isset($options['show-password']) || $options['show-password'] !== true) {
            $url = $repo->displayUrl;
        }
        printf("%s\t%s\n", $name, $url);
    }
    unset($repo);
    return 0;
}

/**
 * @param $argv
 * @return int
 */
function wiff_repository_add(&$argv) {
    $repoName = array_shift($argv);
    if ($repoName === null) {
        printerr(sprintf("Error: missing repo-name.\n"));
        return 1;
    }
    if (!preg_match(/* ExtJS's vtype:alphanum */ '/^[a-zA-Z0-9_]+$/', $repoName)) {
        printerr(sprintf("Error: invalid repo-name '%s': repo-name must contain only [a-zA-Z0-9_] chars.\n", $repoName));
        return 1;
    }
    $repoUrl = array_shift($argv);
    if ($repoUrl === null) {
        printerr(sprintf("Error: missing repo-url.\n"));
        return 1;
    }
    $repoAuthUser = array_shift($argv);
    $repoAuthPassword = array_shift($argv);

    $wiff = WIFF::getInstance();
    $ret = $wiff->createRepoUrl($repoName, $repoUrl, $repoAuthUser, $repoAuthPassword);
    if ($ret === false) {
        printerr(sprintf("Error: could not create repository '%s': %s\n", $repoName, $wiff->errorMessage));
        return 1;
    }
    return 0;
}

/**
 * @param $argv
 * @return int
 */
function wiff_repository_delete(&$argv) {
    $options = parse_argv_options($argv);
    $wiff = WIFF::getInstance();
    if (isset($options['all']) && $options['all'] === true) {
        if (count($argv) > 0) {
            printerr(sprintf("Error: use either '--all' or a repo-name but not both.\n"));
            return 1;
        }
        $repoList = $wiff->getRepoList(false);
        foreach ($repoList as & $repo) {
            $wiff->deleteRepo($repo->name);
        }
        unset($repo);
    } else {
        $repoName = array_shift($argv);
        if ($repoName === null) {
            printerr(sprintf("Error: missing repo-name.\n"));
            return 1;
        }
        $ret = $wiff->deleteRepo($repoName);
        if ($ret === false) {
            printerr(sprintf("Error: could not delete repository '%s': %s\n", $repoName, $wiff->errorMessage));
            return 1;
        }
    }
    return 0;
}

/**
 * @param Context $context
 * @param $argv
 */
function wiff_context_repository(&$context, &$argv) {
    $cmd = array_shift($argv);
    switch ($cmd) {
        case 'list':
            $ret = wiff_context_repository_list($context, $argv);
            return $ret;
            break;
        case 'enable':
            wiff_lock();
            $ret = wiff_context_repository_enable($context, $argv);
            wiff_unlock();
            return $ret;
            break;
        case 'disable':
            wiff_lock();
            $ret = wiff_context_repository_disable($context, $argv);
            wiff_unlock();
            return $ret;
        default:
            wiff_context_repository_help($argv);
    }
    return 0;
}

/**
 * @param $argv
 * @return int
 */
function wiff_context_repository_help(&$argv) {
    echo "\n";
    echo "Usage\n";
    echo "-----\n";
    echo "\n";
    echo "  wiff context <context-name> repository list\n";
    echo "  wiff context <context-name> repository enable <repo-name>\n";
    echo "  wiff context <context-name> repository disable <repo-name>\n";
    echo "  wiff context <context-name> repository disable --all\n";
    echo "\n";
    return 0;
}

/**
 * @param Context $context
 * @param $argv
 * @return int
 */
function wiff_context_repository_list(&$context, &$argv) {
    foreach ($context->repo as $repo) {
        printf("%s\n", $repo->name);
    }
    return 0;
}

/**
 * @param Context $context
 * @param $argv
 * @return int
 */
function wiff_context_repository_enable(&$context, &$argv) {
    $repoName = array_shift($argv);
    if ($repoName === null) {
        printerr(sprintf("Error: missing repo-name.\n"));;
        return 1;
    }
    $ret = $context->activateRepo($repoName);
    if ($ret === false) {
        printerr(sprintf("Error: could not activate repository '%s' on context '%s': %s\n", $repoName, $context->name, $context->errorMessage));
        return 1;
    }
    return 0;
}

/**
 * @param Context $context
 * @param $argv
 * @return int
 */
function wiff_context_repository_disable(&$context, &$argv) {
    $options = parse_argv_options($argv);
    if (isset($options['all']) && $options['all'] === true) {
        if (count($argv) > 0) {
            printerr(sprintf("Error: use either '--all' or a repo-name but not both.\n"));
            return 1;
        }
        $ret = $context->deactivateAllRepo();
        if ($ret === false) {
            printerr(sprintf("Error: could not disable all repository on context '%s': %s\n", $context->name, $context->errorMessage));
        }
    } else {
        $repoName = array_shift($argv);
        if ($repoName === null) {
            printerr(sprintf("Error: missing repo-name.\n"));
            return 1;
        }
        $ret = $context->deactivateRepo($repoName);
        if ($ret === false) {
            printerr(sprintf("Error: could not disable repository '%s' on context '%s': %s\n", $repoName, $context->name, $context->errorMessage));
        }
    }
    return ($ret?0:1);
}

/**
 * @param Context $context
 * @param $argv
 */
function wiff_context_register(&$context, &$argv) {
    $wiff = WIFF::getInstance();
    $regInfo = $wiff->getRegistrationInfo();
    if (!isset($regInfo['status']) || $regInfo['status'] != 'registered') {
        printerr(sprintf("Error: cannot register context because dynacase-control is not registered itself.\n"));
        printerr(sprintf("Register dynacase-control itself with `wiff register <eec-username> <eec-password>`,\n"));
        printerr(sprintf("then rerun context registration.\n"));
        return 1;
    }
    $ret = $context->setRegister(true);
    if ($ret === false) {
        printerr(sprintf("Error: could not register context: %s\n", $context->errorMessage));
        return 1;
    }
    return 0;
}

/**
 * @param Context $context
 * @param $argv
 */
function wiff_context_download_configuration(&$context, &$argv) {
    $options = parse_argv_options($argv);
    /*
     * Compose absolute path to output file
     */
    if (!isset($options['file'])) {
        $options['file'] = sprintf("dynacase-context-%s-%s.zip", $context->name, date('c'));
    }
    $fileName = basename($options['file']);
    $outputDir = dirname($options['file']);
    $realDir = realpath($outputDir);
    if ($realDir === false) {
        printerr(sprintf("Error: could not get directory from '%s'.\n", $outputDir));
        return 1;
    }
    $options['file'] = $realDir . DIRECTORY_SEPARATOR . $fileName;
    /*
     * Generate configuration zip
     */
    $zipFile = $context->zipEECConfiguration();
    if ($zipFile === false) {
        printerr(sprintf("Error: could not generate configuration ZIP: %s\n", $context->errorMessage));
        return 1;
    }
    if (rename($zipFile, $options['file']) === false) {
        printerr(sprintf("Error: could not save configuration ZIP to '%s'.\n", $options['file']));
        unlink($zipFile);
        return 1;
    }
    printf("Configuration ZIP saved to: %s\n", $options['file']);
    return 0;
}

/**
 * @param $argv
 */
function wiff_register(&$argv) {
    $wiff = WIFF::getInstance();
    $regInfo = $wiff->checkInitRegistration();
    if ($regInfo === false) {
        printerr(sprintf("Error: could not get registration informations: %s\n", $wiff->errorMessage));
        return 1;
    }
    if (count($argv) <= 0) {
        if (isset($regInfo['status']) && $regInfo['status'] == 'registered') {
            printf("Registered with EEC username '%s'.\n", $regInfo['login']);
        } else {
            printf("Not registered.\n");
        }
    } else {
        $eecUser = array_shift($argv);
        if ($eecUser === null) {
            printerr(sprintf("Error: missing eec-username.\n"));
            return 1;
        }
        $eecPass = array_shift($argv);
        if ($eecPass === null) {
            printerr(sprintf("Error: missing eec-password.\n"));
            return 1;
        }
        $response = $wiff->tryRegister($regInfo['mid'], $regInfo['ctrlid'], $eecUser, $eecPass);
        if ($response === false) {
            printerr(sprintf("Error: could not register dynacase-control: %s\n", $wiff->errorMessage));
            return 1;
        }
        printf("Successfully registered dynacase-control.\n");
    }
    return 0;
}

/**
 * @param $argv
 * @return int
 */
function wiff_context_archive_help(&$argv) {
    echo "\n";
    echo "Usage\n";
    echo "-----\n";
    echo "\n";
    echo "  wiff context <context-name> archive <archive-name> [--without-vault] [--description=<description>]\n";
    echo "\n";
    return 0;
}

/**
 * @param Context $context
 * @param array $argv
 */
function wiff_context_archive(&$context, $argv) {
    $context->wstop();

    $archiveName = array_shift($argv);
    if ($archiveName === null) {
        printerr(sprintf("Error: missing archive name.\n"));
        wiff_context_archive_help($argv);
        return 1;
    }
    $options = parse_argv_options($argv);
    $excludeVault = (isset($options['without-vault']) && $options['without-vault'] === true);
    $archiveDesc = sprintf("%s@%s", $context->name, strftime("%Y-%m-%dT%H:%M:%S"));
    if (isset($options['description'])) {
        $archiveDesc = $options['description'];
    }
    $modules = $context->getInstalledModuleList();
    /* pre-archive */
    foreach ($modules as & $module) {
        printf("Doing '%s' of module '%s'.\n", 'pre-archive', $module->name);
        if (($ret = executeModulePhase($module, 'pre-archive', $options)) != 0) {
            return $ret;
        }
    }
    unset($module);
    /* archive */
    $archiveId = $context->archiveContext($archiveName, $archiveDesc, $excludeVault);
    if ($archiveId === false) {
        printerr(sprintf("Error: could not archive context: %s\n", $context->errorMessage));
        return 1;
    }
    /* post-archive */
    foreach ($modules as & $module) {
        printf("Doing '%s' of module '%s'.\n", 'post-archive', $module->name);
        if (($ret = executeModulePhase($module, 'post-archive', $options)) != 0) {
            return $ret;
        }
    }
    unset($module);

    $context->wstart();

    printf("%s\n", $archiveId);
    return 0;
}

/**
 * @param $argv
 * @return int
 */
function wiff_archive_help(&$argv) {
    echo "\n";
    echo "Usage\n";
    echo "-----\n";
    echo "\n";
    echo "  wiff archive <archive-id> info\n";
    echo "  wiff archive <archive-id> restore <context-name> <context-root> <pg-service-name> <vault-root>\n";
    echo "                                    [--remove-profiles --user-login=<login> --user-password=<password>]\n";
    echo "                                    [--clean-tmp-directory]\n";
    echo "\n";
    return 0;
}

/**
 * @param $argv
 */
function wiff_archive(&$argv) {
    $archiveId = array_shift($argv);
    if ($archiveId === null) {
        printerr("Error: missing archive id.\n");
        wiff_archive_help($argv);
        return 1;
    }
    $cmd = array_shift($argv);
    switch ($cmd) {
        case 'info':
            $ret = wiff_archive_info($archiveId, $argv);
            return $ret;
            break;
        case 'restore':
            wiff_lock();
            $ret = wiff_archive_restore($archiveId, $argv);
            wiff_unlock();
            return $ret;
            break;
    }
    wiff_archive_help($argv);
    return 1;
}

/**
 * @param $archiveId
 * @param $argv
 */
function wiff_archive_restore($archiveId, &$argv) {
    $wiff = WIFF::getInstance();
    $archive = $wiff->getArchivedContextById($archiveId);
    if ($archive === false) {
        printerr(sprintf("Error: could not find archive with id '%s'.\n", $archiveId));
        return 1;
    }
    $contextName = array_shift($argv);
    if ($contextName === null) {
        printerr(sprintf("Error: missing context name.\n"));
        return 1;
    }
    $contextRoot = array_shift($argv);
    if ($contextRoot === null) {
        printerr(sprintf("Error: missing context root directory.\n"));
        return 1;
    }
    $pgService = array_shift($argv);
    if ($pgService === null) {
        printerr(sprintf("Error: missing postgresql's service name.\n"));
        return 1;
    }
    $vaultRoot = array_shift($argv);
    if ($vaultRoot === null) {
        printerr(sprintf("Error: missing vault root directory.\n"));
        return 1;
    }
    $options = parse_argv_options($argv);
    $removeProfiles = false;
    $userLogin = '';
    $userPassword = '';
    if (isset($options['remove-profiles']) && $options['remove-profiles'] === true) {
        $removeProfiles = true;
        if (!isset($options['user_login'])) {
            printerr(sprintf("Error: missing --user-login=<login>.\n"));
            return 1;
        }
        $userLogin = $options['user_login'];
        if (!isset($options['user_password'])) {
            printerr(sprintf("Error: missing --user-password=<password>.\n"));
            return 1;
        }
        $userPassword = $options['user_password'];
    }
    $cleanTmpDirectory = true;
    if (isset($options['clean-tmp-directory'])) {
        switch ($options['clean-tmp-directory']) {
            case 'yes':
                $cleanTmpDirectory = true;
                break;
            case 'no':
                $cleanTmpDirectory = false;
                break;
            default:
                printerr(sprintf("Error: invalid value '%s' for option --clean-tmp-directory.", $options['clean-tmp-directory']));
                return 1;
        }
    }
    $ret = $wiff->createContextFromArchive($archiveId, $contextName, $contextRoot, '', '', $vaultRoot, $pgService, $removeProfiles, $userLogin, $userPassword, $cleanTmpDirectory);
    if ($ret === false) {
        printerr(sprintf("Error: could not restore archive '%s' to new context '%s': %s", $archiveId, $contextName, $wiff->errorMessage));
        return 1;
    }
    $context = $wiff->getContext($contextName);
    if ($context === false) {
        printerr(sprintf("Error: could not get context '%s': %s\n", $contextName, $wiff->errorMessage));
        return 1;
    }
    $modules = $context->getInstalledModuleList();
    foreach ($modules as & $module) {
        printf("Doing '%s' of module '%s'.\n", 'post-restore', $module->name);
        if (($ret = executeModulePhase($module, 'post-restore', $options)) != 0) {
            return $ret;
        }
    }
    unset($module);

    $context->wstart();

    printf("Context '%s' successfully created from archive '%s'.\n", $contextName, $archiveId);
    return 0;
}

/**
 * @param $archiveId
 * @param $argv
 */
function wiff_archive_info($archiveId, &$argv) {
    $wiff = WIFF::getInstance();
    $archive = $wiff->getArchivedContextById($archiveId);
    if ($archive === false) {
        printerr(sprintf("Error: could not find archived context with id '%s'.", $archiveId));
        return 1;
    }
    print ascii_underline(sprintf("Archive '%s'", $archiveId));
    printf("\n");
    printf("id          = %s\n", $archive['id']);
    printf("date        = %s\n", $archive['datetime']);
    printf("name        = %s\n", $archive['name']);
    printf("size        = %s\n", $archive['size']);
    printf("vault       = %s\n", $archive['vault']);
    printf("description = %s\n", $archive['description']);
    printf("\n");
    printf("Installed modules:\n");
    /**
     * @var Module $module
     */
    foreach ($archive['moduleList'] as $module) {
        printf("- %s (%s-%s)\n", $module->name, $module->version, $module->release);
    }
    printf("\n");
    return 0;
}
/**
 * @param $argv
 * @return int
 */
function wiff_list_archive(&$argv) {
    $wiff = WIFF::getInstance();
    $archiveList = $wiff->getArchivedContextList();
    foreach ($archiveList as $archive) {
        printf("%s\n", $archive['id']);
    }
    return 0;
}

function wiff_delete_archive(&$argv) {
    $archiveId = array_shift($argv);
    if ($archiveId === null) {
        printerr(sprintf("Error: missing archive id.\n"));
        return 1;
    }
    $wiff = WIFF::getInstance();
    if ($wiff->deleteArchive($archiveId) === false) {
        printerr(sprintf("Error: could not delete archive with id '%s'.\n", $archiveId));
        return 1;
    }
    return 0;
}

/**
 * @param Process[] $processList
 * @param array $options
 * @return int
 */
function executeProcessList($processList, $options = array()) {
    foreach ($processList as & $process) {
        while (true) {
            printf("Running '%s'... ", $process->label);
            echo fg_yellow();
            $exec = $process->execute();
            echo color_reset();
            if ($exec['ret'] === false) {
                echo sprintf("\nError: process '%s' returned with error: %s%s%s\n", $process->label, fg_red(), $exec['output'], color_reset());
                if (boolopt('force', $options)) {
                    echo sprintf("[%sSKIPPED%s] (%s)\n", fg_blue(), color_reset(), $exec['output']);
                    break;
                }
                $ret = param_ask($options, "(R)etry, (c)continue or (a)bort", "R/c/a", "R", "a");
                if (preg_match('/^a.*$/i', $ret)) {
                    echo sprintf("[%sABORTED%s] (%s)\n", fg_red(), color_reset(), $exec['output']);
                    return 1;
                }
                if (preg_match('/^(c.*)$/i', $ret)) {
                    echo sprintf("[%sSKIPPED%s] (%s)\n", fg_blue(), color_reset(), $exec['output']);
                    break;
                }
            } else {
                echo sprintf("[%sOK%s]\n", fg_green(), color_reset());
                break;
            }
        }
    }
    return 0;
}

/**
 * @param Module $module
 * @param string $phaseName
 * @param array $options
 * @return int
 */
function executeModulePhase(&$module, $phaseName, $options = array()) {
    $phase = $module->getPhase($phaseName);
    $processList = $phase->getProcessList();
    return executeProcessList($processList, $options);
}
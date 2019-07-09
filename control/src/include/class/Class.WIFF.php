<?php
/*
 * Web Installer for Freedom Class
 * @author Anakeen
*/

function curPageURL()
{
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {
        $pageURL.= "s";
    }
    $pageURL.= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL.= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL.= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

require_once __DIR__.'/Class.WiffCommon.php';

class WIFF extends WiffCommon
{
    const logIdent = 'anakeen-control';
    
    const contexts_filepath = 'conf/contexts.xml';
    const params_filepath = 'conf/params.xml';
    const archived_contexts_dir = 'archived-contexts/';
    const archived_tmp_dir = 'archived-tmp/';
    const xsd_catalog_xml = 'xsd/catalog.xml';
    const log_filepath = 'log/wiff.log';
    
    public $update_host;
    public $update_url;
    public $update_file;
    public $update_login;
    public $update_password;
    
    public $contexts_filepath = '';
    public $params_filepath = '';
    public $archived_contexts_dir = '';
    public $archived_tmp_dir = '';
    public $xsd_catalog_xml = '';
    public $log_filepath = '';
    
    public $errorMessage = null;
    
    public $authInfo = array();
    
    private static $instance;
    
    private static $lock = null;
    
    public $errorStatus = "";
    /**
     * @var Logger
     */
    private static $logger;
    /**
     * @var string
     */
    public $root;

    private function __construct()
    {
        $wiff_root = getenv('WIFF_ROOT');
        if ($wiff_root !== false) {
            $wiff_root = $wiff_root . DIRECTORY_SEPARATOR;
        }
        $this->root=$wiff_root;
        $this->contexts_filepath = $wiff_root . WIFF::contexts_filepath;
        $this->params_filepath = $wiff_root . WIFF::params_filepath;
        $this->archived_contexts_dir = $wiff_root . WIFF::archived_contexts_dir;
        $this->archived_tmp_dir = $wiff_root . WIFF::archived_tmp_dir;
        $this->xsd_catalog_xml = $wiff_root . WIFF::xsd_catalog_xml;
        $this->log_filepath = $wiff_root . WIFF::log_filepath;

        if (file_exists( $this->params_filepath)) {
            $this->update_host = $this->getParam('ac-update-host');
            $this->update_url = $this->getParam('ac-update-path');
            $this->update_file = $this->getParam('ac-update-file');
            $this->update_login = $this->getParam('ac-update-login');
            $this->update_password = $this->getParam('ac-update-password');
        }
    }
    
    public function __destruct()
    {
        $this->unlock();
    }
    
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new WIFF();
        }
        if (!self::$instance->isWritable()) {
            self::$instance->errorMessage = 'Cannot write configuration files';
        }
        if (!isset(self::$logger)) {
            self::$instance->initLogger();
        }
        
        return self::$instance;
    }
    /**
     * @TODO: Create php error message management class
     * Get php upload error message
     * @param $code
     * @return string
     */
    public static function getUploadErrorMsg($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;

            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;

            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;

            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;

            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;

            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;

            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $constants = get_defined_constants(true);
                foreach ($constants["Core"] as $name => $value) {
                    if (!strncmp($name, "UPLOAD_ERR_", 11) && $value === $code) {
                        $code = $name;
                        break;
                    }
                }
                $message = "Unknown upload error: " . $code;
                break;
        }
        return $message;
    }
    /**
     * Get WIFF version
     * @return string
     */
    public static function getVersion()
    {
        $wiff_root = getenv('WIFF_ROOT');
        if ($wiff_root !== false) {
            $wiff_root = $wiff_root . DIRECTORY_SEPARATOR;
        }

        $version = json_decode(file_get_contents($wiff_root . 'version.json'), true);
        
        return $version["version"];
    }


    /**
     * Compose and get the update URL
     * @return string
     */
    public function getUpdateBaseURL()
    {
        $url = $this->update_host . $this->update_url;
        $pUrl = parse_url($url);
        if ($pUrl === false) {
            $this->errorMessage = sprintf("Error parsing URL '%s'", $url);
            return false;
        }
        if ($this->update_login != '') {
            $pUrl['user'] = $this->update_login;
            $pUrl['pass'] = $this->update_password;
        }
        $url = $pUrl['scheme'] . "://";
        if (isset($pUrl['user']) && $pUrl['user'] != '') {
            $url.= urlencode($pUrl['user']) . ":" . urlencode($pUrl['pass']) . "@";
        }
        if (isset($pUrl['host']) && $pUrl['host'] != '') {
            $url.= $pUrl['host'];
        }
        if (isset($pUrl['port']) && $pUrl['port'] != '') {
            $url.= ":" . $pUrl['port'];
        }
        if (isset($pUrl['path']) && $pUrl['path'] != '') {
            $url.= $pUrl['path'];
        }
        return $url . "/";
    }
    /**
     * Get current available WIFF version
     * @return string
     */
    public function getAvailVersion()
    {
        $tmpfile = $this->downloadUrl($this->getUpdateBaseURL() . 'content.xml');

        if ($tmpfile === false) {
            $this->errorMessage = $this->errorMessage ? ('Error when retrieving repository for wiff update :: ' . $this->errorMessage) : 'Error when retrieving repository for wiff update.';
            return false;
        }
        
        $xml = new DOMDocument();
        $ret = $xml->load($tmpfile);
        if ($ret === false) {
            unlink($tmpfile);
            $this->errorMessage = sprintf("Error loading XML file '%s'.", $tmpfile);
            return false;
        }
        
        $xpath = new DOMXPath($xml);
        
        $modules = $xpath->query("/repo/modules/module");
        
        $return = false;
        
        foreach ($modules as $module) {
            /**
             * @var DOMElement $module
             */
            $name = $module->getAttribute('name');
            if ($name == 'anakeen-control') {
                $version = $module->getAttribute('version');
                $release = $module->getAttribute('release');
                $return = $version . '-' . $release;
            }
        }
        
        unlink($tmpfile);
        
        return $return;
    }


    
    public function createPasswordFile($login, $password)
    {
        $this->createHtpasswdFile($login, $password);
        $this->createHtaccessFile();
        
        return true;
    }
    
    public function createHtaccessFile()
    {
        $template = <<<'EOF'
AuthUserFile "{WIFF_ROOT}/.htpasswd"
AuthName 'Veuillez vous identifier'
AuthType Basic

Require valid-user

EOF;
        $escapedWiffRoot = str_replace(array(
            "\\",
            "\""
        ) , array(
            "\\\\",
            "\\\""
        ) , rtrim(self::getWiffRoot(), '/'));
        $content = str_replace('{WIFF_ROOT}', $escapedWiffRoot, $template) . "\n";
        
        @$accessFile = fopen(sprintf('%s/.htaccess', self::getWiffRoot()) , 'w');
        fwrite($accessFile, $content);
        fclose($accessFile);
    }
    
    public function createHtpasswdFile($login, $password)
    {
        @$passwordFile = fopen(sprintf('%s/.htpasswd', self::getWiffRoot()) , 'w');
        fwrite($passwordFile, sprintf("%s:{SHA}%s", $login, base64_encode(sha1($password, true))));
        fclose($passwordFile);
    }
    /**
     * Compare WIFF versions using PHP's version_compare()
     * @return int|string
     * @param string $v1
     * @param string $v2
     */
    private function compareVersion($v1, $v2)
    {
        $v1 = $this->explodeVersion($v1);
        $v2 = $this->explodeVersion($v2);
        return version_compare($v1, $v2);
    }
    
    public static function explodeVersion($v)
    {
        $array = explode(".", $v);
        $lastIndex = count($array) - 1;
        $lastPart = $array[$lastIndex];
        if (pow(2, 32) < $lastPart) {
            $dateTime = str_split($lastPart, 8);
            $array[$lastIndex] = $dateTime[0];
            $array[] = $dateTime[1];
            return implode(".", $array);
        }
        return $v;
    }
    /**
     * Download latest WIFF file archive
     * @return bool|string
     */
    private function downloadUpdateFile()
    {
        return $this->downloadUrl($this->getUpdateBaseURL() . $this->update_file);
    }
    /**
     * Unpack archive in specified destination directory
     * @return string containing the given destination dir pr false in case of error
     */
    private function unpack($archiveFile, $destDir = null)
    {

        
        if (!is_file($archiveFile) || !is_readable($archiveFile)) {
            $this->errorMessage = sprintf("Archive file '%s' does not exists or is not readable.", $archiveFile);
            return false;
        }
        
        if ($destDir === null) {
            $destDir = $this->getWiffRoot();
        }
        if (!is_dir($destDir) || !is_writable($destDir)) {
            $this->errorMessage = sprintf("Unpack directory '%s' does not exists or is not writable.", $destDir);
            return false;
        }
        
        $cmd = sprintf('tar -C %s -zxf %s --strip-components 1 2>&1', escapeshellarg($destDir) , escapeshellarg($archiveFile));
        
        $ret = null;
        exec($cmd, $output, $ret);
        if ($ret != 0) {
            $this->errorMessage = sprintf("Error executing command [%s]: %s", $cmd, join("\n", $output));
            return false;
        }
        
        return true;
    }
    private function checkPreUpdate($archiveFile)
    {
        require_once (__DIR__.'/Class.String.php');
        
        $tempDir = Control\Internal\LibSystem::tempnam(null, 'WIFF_checkPreUpdate');
        if ($tempDir === false) {
            $this->errorMessage = sprintf(__METHOD__ . " " . "Error creating temporary file.");
            return false;
        }
        
        unlink($tempDir);
        if (mkdir($tempDir, 0700) === false) {
            $this->errorMessage = sprintf(__METHOD__ . " " . "Error creating temporary directory.");
            return false;
        }
        
        $this->log(LOG_INFO, sprintf("Unpacking update file '%s' into temporary directory '%s'.", $archiveFile, $tempDir));
        if ($this->unpack($archiveFile, $tempDir) === false) {
            $this->errorMessage = sprintf(__METHOD__ . " " . "Error unpacking update into temporary directory '%s': %s", $tempDir, $this->errorMessage);
            $this->rm_Rf($tempDir);
            return false;
        }
        
        $preUpdateFile = $tempDir . DIRECTORY_SEPARATOR . 'pre-update';
        $this->log(LOG_INFO, sprintf("Checking for pre-update script '%s'.", $preUpdateFile));
        if (!is_file($preUpdateFile)) {
            $this->rm_Rf($tempDir);
            return true;
        }
        
        $newVersion = '';
        if (($lines = file($tempDir . DIRECTORY_SEPARATOR . 'VERSION')) !== false) {
            $newVersion = trim($lines[0]);
        }
        if (($lines = file($tempDir . DIRECTORY_SEPARATOR . 'RELEASE')) !== false) {
            $newVersion.= "-" . trim($lines[0]);
        }
        
        $cmd = sprintf("%s %s %s 2>&1", escapeshellarg($preUpdateFile) , escapeshellarg($this->getWiffRoot()) , escapeshellarg($tempDir));
        $this->log(LOG_INFO, sprintf("Executing pre-update script with command: %s", $cmd));
        exec($cmd, $output, $ret);
        if ($ret !== 0) {
            $message = sprintf('<p style="font-weight: bold">Pre-update verification for anakeen-control %s failed with:</p></p><pre style="font-weight: bold; color: red; white-space: pre-wrap;">%s</pre>', $newVersion, join("<br/>", array_map(function ($s)
            {
                return htmlspecialchars($s, ENT_QUOTES);
            }
            , $output)));
            $this->errorMessage = (string)new \String\HTML($message);
            $this->log(LOG_ERR, sprintf("pre-update script '%s' returned with error: %s", $preUpdateFile, $this->errorMessage));
            $this->rm_Rf($tempDir);
            return false;
        }
        $this->rm_Rf($tempDir);
        return true;
    }

    /**
     * Get global repository list
     * @return Repository[] array of object Repository
     */
    public function getRepoList($checkValidity = true)
    {
        require_once (__DIR__.'/Class.Repository.php');
        
        $repoList = array();
        
        $xml = $this->loadParamsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading XML file '%s'.", $this->params_filepath);
            return false;
        }
        
        $repositories = $xml->getElementsByTagName('access');
        
        if ($repositories->length > 0) {
            
            foreach ($repositories as $repository) {
                $repoList[] = new Repository($repository, null, array(
                    'checkValidity' => ($checkValidity === true)
                ));
            }
        }
        
        return $repoList;
    }
    /**
     * Get repository from global repo list
     */
    public function getRepo($name)
    {
        require_once (__DIR__.'/Class.Repository.php');
        
        if ($name == '') {
            $this->errorMessage = "A name must be provided.";
            return false;
        }
        
        $xml = $this->loadParamsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading XML file '%s'.", $this->params_filepath);
            return false;
        }
        
        $xPath = new DOMXPath($xml);
        // Get repository with this name from WIFF repositories
        $wiffRepoList = $xPath->query("/wiff/repositories/access[@name='" . $name . "']");
        if ($wiffRepoList->length == 0) {
            // If there is already a repository with same name
            $this->errorMessage = "Repository does not exist.";
            return false;
        }
        /**
         * @var DOMElement $repository
         */
        $repository = $wiffRepoList->item(0);
        
        $repositoryObject = new Repository($repository);
        
        return $repositoryObject;
    }
    /**
     * Add repository to global repo list
     * @param $name
     * @param $description
     * @param $protocol
     * @param $host
     * @param $path
     * @param $default
     * @param $authenticated
     * @param $login
     * @param $password
     * @param $returnRepoValidation
     * @return boolean
     */
    public function createRepo($name, $description, $protocol, $host, $path, $default, $authenticated, $login, $password, $returnRepoValidation = true)
    {
        require_once (__DIR__.'/Class.Repository.php');
        
        if ($name == '') {
            $this->errorMessage = "A name must be provided.";
            return false;
        }
        
        $xml = $this->loadParamsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading XML file '%s'.", $this->params_filepath);
            return false;
        }
        
        $xPath = new DOMXPath($xml);
        // Get repository with this name from WIFF repositories
        $wiffRepoList = $xPath->query("/wiff/repositories/access[@name='" . $name . "']");
        if ($wiffRepoList->length != 0) {
            // If there is already a repository with same name
            $this->errorMessage = "Repository with same name already exists.";
            return false;
        }
        // Add repository to this context
        $node = $xml->createElement('access');
        /**
         * @var DOMElement $repository
         */
        $repository = $xml->getElementsByTagName('repositories')->item(0)->appendChild($node);
        
        $repository->setAttribute('name', $name);
        $repository->setAttribute('description', $description);
        $repository->setAttribute('protocol', $protocol);
        $repository->setAttribute('host', $host);
        $repository->setAttribute('path', $path);
        $repository->setAttribute('default', $default);
        $repository->setAttribute('authenticated', $authenticated);
        $repository->setAttribute('login', $login);
        $repository->setAttribute('password', $password);
        
        $repositoryObject = new Repository($repository);
        
        $isValid = $repositoryObject->isValid();
        if (!$isValid) {
            $url=sprintf("%s://%s/%s", $protocol, $host, $path);
            $this->errorMessage = sprintf("Repository has no valid content.xml '%s'", $url);
            return false;
        }

        $repository->setAttribute('label', $repositoryObject->label);
        
        $ret = $this->commitDOMDocument($xml);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error writing file '%s': %s", $this->params_filepath, $this->errorMessage);
            return false;
        }
        return ($returnRepoValidation ? $isValid : true);
    }
    public function createRepoUrl($name, $url, $authUser = null, $authPassword = null, $default = false)
    {
        $pURL = parse_url($url);
        
        $useUrlEmbeddedUserPass = false;
        $authenticated = 'no';
        if ($authUser !== null) {
            $authenticated = 'yes';
            if ($authPassword === null) {
                $authPassword = ($useUrlEmbeddedUserPass && isset($pUrl['pass']) ? urldecode($pURL['pass']) : '');
            }
        } else {
            if ($useUrlEmbeddedUserPass && (isset($pURL['user']) || isset($pURL['pass']))) {
                $authenticated = 'yes';
                $authUser = (isset($pURL['user']) ? urldecode($pURL['user']) : '');
                $authPassword = (isset($pURL['pass']) ? urldecode($pURL['pass']) : '');
            } else {
                $authUser = '';
                $authPassword = '';
            }
        }
        
        $protocol = '';
        if (isset($pURL['scheme'])) {
            $protocol = $pURL['scheme'];
        }
        $host = '';
        if (isset($pURL['host'])) {
            $host = $pURL['host'];
            if (isset($pURL['port'])) {
                $host.= ':' . $pURL['port'];
            }
        }
        $path = '';
        if (isset($pURL['path'])) {
            $path = $pURL['path'];
            if (isset($pURL['query'])) {
                $path.= '?' . $pURL['query'];
            }
            if (isset($pURL['fragment'])) {
                $path.= '#' . $pURL['fragment'];
            }
        }
        $description = sprintf("%s://%s/%s", $protocol, $host, $path);
        
        $ret = $this->createRepo($name, $description, $protocol, $host, $path, /* default */
        ($default ? 'yes' : 'no') , $authenticated, $authUser, $authPassword, false);
        if ($ret === false) {
            return false;
        }
        return true;
    }
    /**
     * Change all parameters in one go
     * @param array $request
     * @return boolean
     */
    public function changeAllParams($request)
    {
        if (count($request) <= 1) {
            $this->errorMessage = "No params to change";
            return false;
        }
        $paramList = $this->getParamList();
        if ($paramList === false) {
            return false;
        }
        foreach ($paramList as $name => $value) {
            $i = 0;
            foreach ($request as $r_name => $r_value) {
                if ($r_name !== 'changeAllParams') {
                    if ($r_name == $name) {
                        $err = $this->changeParams($r_name, $r_value);
                        if ($err === false) {
                            return false;
                        }
                        $i++;
                        break;
                    }
                }
            }
            if ($i === 0) {
                $err = $this->changeParams($name, false);
                if ($err === false) {
                    return false;
                }
            }
        }
        return $paramList;
    }
    /**
     * Change Dynacase-control parameters
     * @param string $name : Name of the parameters to change
     * @param string $value : New value one want to set to the parameter
     * @return boolean
     */
    public function changeParams($name, $value)
    {
        if ($name == '') {
            $this->errorMessage = "A name must be provided";
            return false;
        }
        
        $xml = $this->loadParamsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading XML file '%s'.", $this->params_filepath);
            return false;
        }
        $paramList = $xml->getElementsByTagName('param');
        $found = false;
        if ($paramList->length > 0) {
            foreach ($paramList as $param) {
                /**
                 * @var DOMElement $param
                 */
                if ($param->getAttribute('name') === $name) {
                    $found = true;
                    $valueTest = $param->getAttribute('value');
                    $param->removeAttribute('value');
                    if ($valueTest == 'yes' || $valueTest == 'no') {
                        if ($value === true || $value === 'on' || $value === 'true') {
                            $param->setAttribute('value', 'yes');
                        } else {
                            $param->setAttribute('value', 'no');
                        }
                    } else {
                        $param->setAttribute('value', $value);
                    }
                    break;
                }
            }
        }
        if (!$found) {
            /*
             * Add new parameter
            */
            $param = $xml->createElement('param');
            $param->setAttribute('name', $name);
            if ($value === true || $value === 'on' || $value === 'true') {
                $param->setAttribute('value', 'yes');
            } else {
                $param->setAttribute('value', $value);
            }
            $parameters = $xml->getElementsByTagName('parameters');
            if ($parameters->length > 0) {
                $parameters->item(0)->appendChild($param);
            }
        }
        $ret = $this->commitDOMDocument($xml);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error writing file '%s': %s", $this->params_filepath, $this->errorMessage);
            return false;
        }
        return true;
    }
    /**
     * Add repository to global repo list
     * @param string $name
     * @param string $description
     * @param string $protocol
     * @param string $host
     * @param string $path
     * @param string $default
     * @param string $authenticated
     * @param string $login
     * @param string $password
     * @return boolean
     */
    public function modifyRepo($name, $description, $protocol, $host, $path, $default, $authenticated, $login, $password)
    {
        require_once (__DIR__.'/Class.Repository.php');
        
        if ($name == '') {
            $this->errorMessage = "A name must be provided.";
            return false;
        }
        
        $xml = $this->loadParamsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading XML file '%s'.", $this->params_filepath);
            return false;
        }
        
        $xPath = new DOMXPath($xml);
        // Get repository with this name from WIFF repositories
        $wiffRepoList = $xPath->query("/wiff/repositories/access[@name='" . $name . "']");
        if ($wiffRepoList->length == 0) {
            // If there is already a repository with same name
            $this->errorMessage = "Repository does not exist.";
            return false;
        }
        // Add repository to this context
        //        $node = $xml->createElement('access');
        //        $repository = $xml->getElementsByTagName('repositories')->item(0)->appendChild($node);
        
        /**
         * @var DOMElement $repository
         */
        $repository = $wiffRepoList->item(0);
        
        $repository->setAttribute('name', $name);
        $repository->setAttribute('description', $description);
        $repository->setAttribute('protocol', $protocol);
        $repository->setAttribute('host', $host);
        $repository->setAttribute('path', $path);
        $repository->setAttribute('default', $default);
        $repository->setAttribute('authenticated', $authenticated);
        $repository->setAttribute('login', $login);
        $repository->setAttribute('password', $password);
        
        $repositoryObject = new Repository($repository);
        $ret = $this->commitDOMDocument($xml);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error writing file '%s': %s", $this->params_filepath, $this->errorMessage);
            return false;
        }
        return $repositoryObject->isValid();
    }
    /**
     * Delete repository from global repo list
     * @param string $name
     * @return boolean
     */
    public function deleteRepo($name)
    {
        require_once (__DIR__.'/Class.Repository.php');
        
        $xml = $this->loadParamsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading XML file '%s'.", $this->params_filepath);
            return false;
        }
        
        $xPath = new DOMXPath($xml);
        // Get repository with this name from WIFF repositories
        $wiffRepoList = $xPath->query("/wiff/repositories/access[@name='" . $name . "']");
        if ($wiffRepoList->length == 0) {
            // If there is not at least one repository with such name enlisted
            $this->errorMessage = "Repository not found.";
            return false;
        }
        // Delete repository from this context
        $xml->getElementsByTagName('repositories')->item(0)->removeChild($wiffRepoList->item(0));
        
        $ret = $this->commitDOMDocument($xml);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error writing file '%s': %s", $this->params_filepath, $this->errorMessage);
            return false;
        }
        
        return true;
    }
    
    public function setAuthInfo($request)
    {
        //echo 'REQUEST'.print_r($request[0]->name,true);
        //echo 'SET AuthInfo Size'.count($request);
        $this->authInfo = $request;
    }
    /**
     * @param $repoName
     * @return bool|StdClass
     */
    public function getAuthInfo($repoName)
    {
        //echo ('GET AuthInfo'.$repoName.count($this->authInfo));
        for ($i = 0; $i < count($this->authInfo); $i++) {
            //echo ('Looking through authinfo');
            if ($this->authInfo[$i]->name == $repoName) {
                return $this->authInfo[$i];
            }
        }
        return false;
    }
    /**
     * Get Context list with contexts being restored
     * @return Context[]
     */
    /**
     * Get Context list
     * @param bool $withInProgress Include contexts being restored as ContextProperties objects instead of
     * full-blown Context objects (default: bool(false))
     * @return Context[] array of object Context or bool(false) on error
     */
    public function getContextList($withInProgress = false)
    {
        require_once (__DIR__.'/Class.Repository.php');
        require_once (__DIR__.'/Class.Context.php');
        
        $contextList = array();
        
        $xml = $this->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml' [001]: %s", $this->errorMessage);
            return false;
        }
        
        $xpath = new DOMXpath($xml);
        $contexts = $xpath->query("/contexts/context");
        
        if ($contexts->length > 0) {
            
            foreach ($contexts as $context) {
                /**
                 * @var DOMElement $context
                 */
                $repoList = array();
                
                $repositories = $context->getElementsByTagName('access');
                
                foreach ($repositories as $repository) {
                    $repoList[] = new Repository($repository);
                }
                
                $contextClass = new Context($context->getAttribute('name') , $context->getElementsByTagName('description')->item(0)->nodeValue, $context->getAttribute('root') , $repoList, $context->getAttribute('url') , $context->getAttribute('register'));
                $contextClass->isValid();
                
                if (!$contextClass->isWritable()) {
                    $this->errorMessage = sprintf("Apache user does not have write rights for context '%s'.", $contextClass->name);
                    return false;
                }
                
                $contextList[] = $contextClass;
            }
        }
        
        $collator = new Collator(Locale::getDefault());
        usort($contextList, function ($context1, $context2) use ($collator)
        {
            /** @var Collator $collator */
            return $collator->compare($context1->name, $context2->name);
        });
        
        if (is_dir($this->archived_tmp_dir)) {
            if (!is_writable($this->archived_tmp_dir)) {
                $this->errorMessage = sprintf("Directory '%s' is not writable.", $this->archived_tmp_dir);
                return false;
            }
        } else {
            if (@mkdir($this->archived_tmp_dir) === false) {
                $this->errorMessage = sprintf("Error creating directory '%s'.", $this->archived_tmp_dir);
                return false;
            }
        }
        
        if ($withInProgress) {
            if ($handle = opendir($this->archived_tmp_dir)) {
                while (false !== ($file = readdir($handle))) {
                    if (!preg_match('/^.+\.ctx$/', $file)) {
                        continue;
                    }
                    $absFile = $this->archived_tmp_dir . DIRECTORY_SEPARATOR . $file;
                    if (($name = self::readFirstLine($absFile)) !== false) {
                        $contextClass = new ContextProperties();
                        $contextClass->name = rtrim($name, "\n");
                        $contextClass->inProgress = true;
                        $contextClass->description = sprintf("Restoration in progress (started on %s)", date("Y-m-d H:i:s", filectime($absFile)));
                        $contextList[] = $contextClass;
                    }
                }
            }
        }
        
        return $contextList;
    }

    public function verifyGzipIntegrity($file, &$err = '')
    {
        $cmd = sprintf("unzip -t %s 2>&1", escapeshellarg($file));
        $output = array();
        exec($cmd, $output, $retval);
        $err = join("\n", $output);
        if ($retval != 0) {
            return false;
        }
        return true;
    }

    /**
     * Get Context by name
     * @return Context Context or boolean false
     * @param string $name context name
     * @param bool $opt (default false)
     */
    public function getContext($name, $opt = false)
    {
        require_once (__DIR__.'/Class.Repository.php');
        require_once (__DIR__.'/Class.Context.php');
        
        $xml = $this->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading '%s': %s", $this->contexts_filepath, $this->errorMessage);
            return false;
        }
        
        $xpath = new DOMXPath($xml);
        
        $query = "/contexts/context[@name = '" . $name . "']";
        $context = $xpath->query($query);
        
        if ($context->length >= 1) {
            
            $repoList = array();
            /**
             * @var DOMElement $contextNode
             */
            $contextNode = $context->item(0);
            $repositories = $contextNode->getElementsByTagName('access');
            
            foreach ($repositories as $repository) {
                $repoList[] = new Repository($repository);
            }
            
            $this->errorMessage = null;
            $context = new Context($contextNode->getAttribute('name') , $contextNode->getElementsByTagName('description')->item(0)->nodeValue, $contextNode->getAttribute('root') , $repoList, $contextNode->getAttribute('url') , $contextNode->getAttribute('register'));
            
            if (!$context->isWritable() && $opt == false) {
                $this->errorMessage = sprintf("Context '%s' configuration is not writable.", $context->name);
                return false;
            }
            
            return $context;
        }
        
        $this->errorMessage = sprintf("Context '%s' not found.", $name);
        return false;
    }
    
    public function isWritable()
    {
        if (!is_writable($this->contexts_filepath) || !is_writable($this->params_filepath)) {
            return false;
        }
        return true;
    }
    /**
     * Create Context
     * @return object Context or boolean false
     * @param string $name context name
     * @param string $root context root folder
     * @param string $desc context description
     * @param string $url context url
     */
    public function createContext($name, $root, $desc, $url)
    {
        // Check for invalid chars in context root path
        if (!$this->checkValidContextDirChars($root)) {
            $this->errorMessage = sprintf("Invalid context root directory '%s': %s", $root, $this->errorMessage);
            return false;
        }
        // If Context already exists, method fails.
        if ($this->getContext($name) !== false) {
            $this->errorMessage = sprintf("Context '%s' already exists.", $name);
            return false;
        } else {
            $this->errorMessage = null;
        }
        // Create or reuse directory
        if (is_dir($root)) {
            if (!is_writable($root)) {
                $this->errorMessage = sprintf("Directory '%s' is not writable.", $root);
                return false;
            }
            $dirListing = @scandir($root);
            if ($dirListing === false) {
                $this->errorMessage = sprintf("Error scanning directory '%s'.", $root);
                return false;
            }
            $dirListingCount = count($dirListing);
            if ($dirListingCount > 2) {
                $this->errorMessage = sprintf("Directory '%s' is not empty.", $root);
                return false;
            }
        } else {
            if (@mkdir($root) === false) {
                $this->errorMessage = sprintf("Error creating directory '%s'.", $root);
                return false;
            }
        }
        // Get absolute pathname if directory is not already in absolute form
        if (!preg_match('|^/|', $root)) {
            $abs_root = realpath($root);
            if ($abs_root === false) {
                $this->errorMessage = sprintf("Error getting absolute pathname for '%s'.", $root);
                return false;
            }
            $root = $abs_root;
        }
        // Write contexts XML
        $xml = $this->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading '%s' [003]: %s", $this->contexts_filepath, $this->errorMessage);
            return false;
        }
        $xml->formatOutput = true;
        
        $node = $xml->createElement('context');
        /**
         * @var DOMElement $context
         */
        $context = $xml->getElementsByTagName('contexts')->item(0)->appendChild($node);
        
        $context->setAttribute('name', $name);
        
        $context->setAttribute('root', $root);
        
        $context->setAttribute('url', $url);
        
        $descriptionNode = $xml->createElement('description', $desc);
        
        $context->appendChild($descriptionNode);
        
        $moduleNode = $xml->createElement('modules');
        $context->appendChild($moduleNode);
        // Save XML to file
        $ret = $this->commitDOMDocument($xml);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error saving 'contexts.xml': %s", $this->errorMessage);
            return false;
        }
        
        return $this->getContext($name);
    }
    /**
     * Save Context
     * @param string $name
     * @param string $root
     * @param string $desc
     * @param string $url
     * @return object Context or boolean false
     */
    public function saveContext($name, $root, $desc, $url)
    {
        // Write contexts XML
        $xml = $this->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error saving '%s' : %s", $this->contexts_filepath, $this->errorMessage);
            return false;
        }
        $xml->formatOutput = true;
        
        $xpath = new DOMXPath($xml);
        
        $query = "/contexts/context[@root = " . self::xpathLiteral($root) . "]";
        /**
         * @var DOMElement $context
         */
        $res = $xpath->query($query);
        if ($res === false) {
            $this->errorMessage = sprintf("Invalid or malformed XPath expression [%s].", $query);
            return false;
        }
        $context = $res->item(0);
        if ($context === null) {
            $this->errorMessage = sprintf("Could not find context with root = '%s'.", $root);
            return false;
        }
        
        $context->setAttribute('name', $name);
        $context->setAttribute('url', $url);
        
        $query = "/contexts/context[@root = " . self::xpathLiteral($root) . "]/description";
        $res = $xpath->query($query);
        if ($res === false) {
            $this->errorMessage = sprintf("Invalid or malformed XPath expression [%s].", $query);
            return false;
        }
        $description = $res->item(0);
        if ($description == null) {
            $this->errorMessage = sprintf("Could not find description for context with root = '%s'.", $root);
            return false;
        }
        
        $description->nodeValue = $desc;
        // Save XML to file
        $ret = $this->commitDOMDocument($xml);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error saving 'contexts.xml': %s", $this->errorMessage);
            return false;
        }
        
        return $this->getContext($name);
    }
    /**
     * Get parameters list
     * @param bool $withHidden true to get hidden parameters too
     * @return array containing 'key' => 'value' pairs
     */
    public function getParamList($withHidden = false)
    {
        /*
         * Default params' values
        */
        $plist = array();
        
        $xml = $this->loadParamsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading XML file '%s'.", $this->params_filepath);
            return false;
        }
        
        $xpath = new DOMXpath($xml);
        $params = $xpath->query("/wiff/parameters/param");
        if ($params === null) {
            $this->errorMessage = sprintf("Error executing XPath query '%s' on file '%s'.", "/wiff/parameters/param", $this->params_filepath);
            return false;
        }
        foreach ($params as $param) {
            /**
             * @var DOMElement $param
             */
            if (!$withHidden && $param->getAttribute("mode") === "hidden") {
                continue;
            }
            $paramName = $param->getAttribute('name');
            $paramValue = $param->getAttribute('value');
            $plist[$paramName] = $paramValue;
        }
        
        return $plist;
    }
    /**
     * Get a specific parameter value
     * @return string the value of the parameter or false in case of errors
     * @param string $paramName the parameter name
     * @param boolean $strict if not found, should method report an error
     * @param bool $withHidden true to get hidden parameters
     */
    public function getParam($paramName, $strict = false, $withHidden = false)
    {

        if (!file_exists( $this->params_filepath)) {
            return false;
        }
        $plist = $this->getParamList($withHidden);
        
        if (array_key_exists($paramName, $plist)) {
            return $plist[$paramName];
        }
        
        if ($strict) {
            $this->errorMessage = sprintf("Parameter '%s' not found in contexts parameters.", $paramName);
        }
        return false;
    }
    /**
     * Set a specific parameter value
     * @return string return the value or false in case of errors
     * @param string $paramName the name of the parameter to set
     * @param string $paramValue the value of the parameter to set
     * @param bool $create
     * @param string $mode Mode of param (hidden or visible)
     */
    public function setParam($paramName, $paramValue, $create = true, $mode = "visible")
    {
        $xml = $this->loadParamsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading XML file '%s'.", $this->params_filepath);
            return false;
        }
        
        $xpath = new DOMXpath($xml);
        $params = $xpath->query("/wiff/parameters/param[@name='$paramName']");
        if ($params === null) {
            $this->errorMessage = sprintf("Error executing XPath query '%s' on file '%s'.", "/wiff/parameters/param[@name='$paramName']", $this->params_filepath);
            return false;
        }
        
        $found = false;
        
        foreach ($params as $param) {
            /**
             * @var DOMElement $param
             */
            $found = true;
            $param->setAttribute('value', $paramValue);
        }
        
        if (!$found && $create) {
            $param = $xml->createElement('param');
            $param = $xml->getElementsByTagName('parameters')->item(0)->appendChild($param);
            $param->setAttribute('name', $paramName);
            $param->setAttribute('value', $paramValue);
            $param->setAttribute('mode', $mode);
        }
        
        $ret = $this->commitDOMDocument($xml);
        if ($ret === false) {
            $this->errorStatus = false;
            $this->errorMessage = sprintf("Error writing file '%s': %s", $this->params_filepath, $this->errorMessage);
            return false;
        }
        
        return $paramValue;
    }
    /**
     * download the file pointed by the URL to a temporary file
     * @param string $url the URL of the file to retrieve
     * @param array $opts
     * @return bool|string the name of a temporary file holding the
     *         retrieved data or false in case of error
     */
    public function downloadUrl($url, $opts = array())
    {
        require_once __DIR__.'/Class.WWWUserAgent.php';
        
        if ($url == '') {
            $this->errorMessage = 'Download URL must not be empty';
            return false;
        }
        $ua = new WWW\UserAgent(array(
            'use-cache' => true
        ));
        $file = $ua->downloadUrl($url, $opts);
        if ($file === false) {
            $this->errorMessage = $ua->errorMessage;
            return false;
        }
        return $file;
    }
    
    public function expandParamValue($paramName)
    {
        $paramName = preg_replace('/@(\w+?)/', '\1', $paramName);
        
        $contextName = getenv("WIFF_CONTEXT_NAME");
        if ($contextName === false) {
            $this->errorMessage = sprintf(__METHOD__ . " " . "WIFF_CONTEXT_NAME env var not defined!");
            return false;
        }
        $context = $this->getContext($contextName);
        if ($context === false) {
            $this->errorMessage = sprintf(__METHOD__ . " " . "Could not get context with name '%s'.", $contextName);
            return false;
        }
        $paramValue = $context->getParamByName($paramName);
        if ($paramValue === false) {
            $this->errorMessage = sprintf(__METHOD__ . " " . "Could not get value for param with name '%s'.", $paramName);
            return false;
        }
        
        return $paramValue;
    }
    
    public function lock($blocking = true, &$lockerPid = null)
    {
        $this->errorMessage = '';
        if (self::$lock !== null) {
            $this->errorMessage = sprintf("Already locked.");
            return false;
        }
        $fh = fopen(sprintf("%s.lock", $this->contexts_filepath) , "a+");
        if ($fh === false) {
            $this->errorMessage = sprintf("Could not open '%s' for lock.", sprintf("%s.lock", $this->contexts_filepath));
            return false;
        }
        $op = LOCK_EX;
        if (!$blocking) {
            $op|= LOCK_NB;
        }
        $ret = flock($fh, $op);
        if ($ret === false) {
            rewind($fh);
            $pid = trim(fgets($fh));
            if ($pid != '') {
                $lockerPid = $pid;
                $this->errorMessage = sprintf("Already locked by process with pid '%s'.", $lockerPid);
            } else {
                $this->errorMessage = sprintf("Could not get lock on '%s'.", sprintf("%s.lock", $this->contexts_filepath));
            }
            fclose($fh);
            return false;
        }
        ftruncate($fh, 0);
        rewind($fh);
        fputs($fh, getmypid());
        fflush($fh);
        self::$lock = $fh;
        return true;
    }
    
    public function unlock()
    {
        $this->errorMessage = '';
        if (!is_resource(self::$lock)) {
            $this->errorMessage = sprintf("Already unlocked?");
            return false;
        }
        rewind(self::$lock);
        ftruncate(self::$lock, 0);
        fflush(self::$lock);
        $ret = flock(self::$lock, LOCK_UN);
        if ($ret == false) {
            $this->errorMessage = sprintf("Could not release lock on '%s'.", sprintf("%s.lock", $this->contexts_filepath));
            return false;
        }
        fclose(self::$lock);
        self::$lock = null;
        return true;
    }




    /**
     * Get WIFF root path
     * @return string
     */
    public function getWiffRoot()
    {
        $wiff_root = getenv('WIFF_ROOT');
        if ($wiff_root !== false) {
            $wiff_root = $wiff_root . DIRECTORY_SEPARATOR;
        }
        return $wiff_root;
    }
    


    


    
    static function anonymizeUrl($url)
    {
        require_once __DIR__.'/Class.WWWUserAgent.php';
        return WWW\UserAgent::anonymizeUrl($url);
    }

    /**
     * Convert a string to an XPath literal
     *
     * If the string contains an apostrophe, then a concat() is used
     * to construct the string literal expression.
     *
     * If no apostrophe is found, then quote the string with apostrophes.
     *
     * @param $str
     * @return string
     */
    static function xpathLiteral($str)
    {
        if (strpos($str, "'") === false) {
            return "'" . $str . "'";
        } else {
            return "concat(" . str_replace(array(
                "'',",
                ",''"
            ) , "", "'" . implode("',\"'\",'", explode("'", $str)) . "'") . ")";
        }
    }
    /**
     * Check for invalid/unsupported chars in context directory
     *
     * @param string $path the context root dir
     * @return bool true if valid, false if invalid
     */
    public function checkValidContextDirChars($path)
    {
        /* Preprend CWD to relative paths in order to also
         * check the validity of CWD
        */
        if (substr($path, 0, strlen(DIRECTORY_SEPARATOR)) !== DIRECTORY_SEPARATOR) {
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }
        $sep = preg_quote(DIRECTORY_SEPARATOR, '/');
        if (!preg_match(sprintf('/^[%sa-zA-Z0-9._-]*$/', $sep) , $path)) {
            $this->errorMessage = sprintf("path name should contain only [%sa-zA-Z0-9._-] characters.", DIRECTORY_SEPARATOR);
            return false;
        }
        return true;
    }

    
    public function validateDOMDocument(DOMDocument $dom, $urn)
    {
        require_once (__DIR__.'/Class.XMLSchemaCatalogValidator.php');
        try {
            $validator = new \XMLSchemaCatalogValidator\Validator($this->xsd_catalog_xml);
            $validator->loadDOMDocument($dom);
            $validator->validate($urn);
        }
        catch(\XMLSchemaCatalogValidator\Exception $e) {
            return $e->getMessage();
        }
        return '';
    }
    /**
     * Cleanup all contexts or a specific context by it's name
     */
    public function cleanup($contextName = '')
    {
        $contextList = array();
        if ($contextName == '') {
            $contextList = $this->getContextList();
        } else {
            $context = $this->getContext($contextName);
            if ($context === false) {
                return false;
            }
            $contextList = array(
                $context
            );
        }
        foreach ($contextList as $context) {
            if ($context->cleanup() === false) {
                $this->errorMessage = $context->errorMessage;
                return false;
            }
        }
        return true;
    }
    
    public function loadContextsDOMDocument($options = 0)
    {
        require_once __DIR__.'/Class.DOMDocumentCacheFactory.php';
        try {
            $dom = DOMDocumentCacheFactory::load($this->contexts_filepath, $options);
        }
        catch(Exception $e) {
            $this->errorMessage = $e->getMessage();
            return false;
        }
        return $dom;
    }
    
    public function loadParamsDOMDocument($options = 0)
    {
        require_once __DIR__.'/Class.DOMDocumentCacheFactory.php';
        try {
            $dom = DOMDocumentCacheFactory::load($this->params_filepath, $options);
        }
        catch(Exception $e) {
            $this->errorMessage = $e->getMessage();
            return false;
        }
        return $dom;
    }
    
    public function commitDOMDocument(DOMDocumentCache & $dom)
    {
        try {
            $ret = $dom->commit();
        }
        catch(Exception $e) {
            $this->errorMessage = $e->getMessage();
            return false;
        }
        return $ret;
    }
    
    private function initLogger()
    {
        require_once __DIR__.'/Class.Logger.php';
        self::$logger = new Logger(self::logIdent);
        if ($this->getParam('local-log', false, true) == 'yes') {
            self::$logger->setLogFile($this->log_filepath);
        }
        if (($facility = $this->getParam('syslog-facility', false, true)) !== '') {
            self::$logger->setSyslogFacility($facility);
        }
    }
    
    public function log($pri, $msg)
    {
        if (isset(self::$logger)) {
            self::$logger->log($pri, $msg);
        }
    }
    
    public function clearLog()
    {
        if (!file_exists($this->log_filepath)) {
            return false;
        }
        return file_put_contents($this->log_filepath, '');
    }
    
    public function streamLog()
    {
        $fh = fopen($this->log_filepath, 'r');
        if ($fh === false) {
            return false;
        }
        $ret = fpassthru($fh);
        fclose($fh);
        return $ret;
    }
    
    public function rm_Rf($path, &$err_list = array())
    {
        if (!is_array($err_list)) {
            $err = sprintf(__METHOD__ . " " . "err_list is not an array.");
            $this->errorMessage.= $err;
            $this->log(LOG_ERR, $err);
            return false;
        }
        
        $filetype = filetype($path);
        if ($filetype === false) {
            $this->errorMessage.= sprintf(__METHOD__ . " " . "Could not get type for file '%s'.\n", $path);
            $err = sprintf("Could not get type for file '%s'.", $path);
            array_push($err_list, $err);
            $this->log(LOG_ERR, $this->errorMessage);
            return false;
        }
        
        if ($filetype == 'dir') {
            $recursive_ret = true;
            foreach (scandir($path) as $file) {
                if ($file == "." || $file == "..") {
                    continue;
                };
                $recursive_ret = ($recursive_ret && $this->rm_Rf(sprintf("%s%s%s", $path, DIRECTORY_SEPARATOR, $file) , $err_list));
            }
            
            $s = stat($path);
            if ($s === false) {
                $this->errorMessage.= sprintf(__METHOD__ . " " . "Could not stat dir '%s'.\n", $path);
                $err = sprintf("Could not stat dir '%s'.", $path);
                array_push($err_list, $err);
                $this->log(LOG_ERR, $this->errorMessage);
                return false;
            }
            
            if ($s['nlink'] > 2) {
                $this->errorMessage = sprintf(__METHOD__ . " " . "Won't remove dir '%s' as it contains %s files.\n", $path, $s['nlink'] - 2);
                $err = sprintf("Won't remove dir '%s' as it contains %s files.", $path, $s['nlink'] - 2);
                array_push($err_list, $err);
                $this->log(LOG_ERR, $this->errorMessage);
                return false;
            }
            
            $ret = @rmdir($path);
            if ($ret === false) {
                $this->errorMessage = sprintf(__METHOD__ . " " . "Error removing dir '%s'.\n", $path);
                $err = sprintf("Error removing dir '%s'.", $path);
                array_push($err_list, $err);
                $this->log(LOG_ERR, $this->errorMessage);
                return false;
            }
            
            return ($ret && $recursive_ret);
        }
        
        $ret = unlink($path);
        if ($ret === false) {
            $this->errorMessage = sprintf(__METHOD__ . " " . "Error removing file '%s' (filetype=%s).\n", $path, $filetype);
            $err = sprintf("Error removing file '%s' (filetype=%s).", $path, $filetype);
            array_push($err_list, $err);
            $this->log(LOG_ERR, $this->errorMessage);
            return false;
        }
        
        return $ret;
    }
    /**
     * Return the first line of a file and remove trailing CR/LF.
     *
     * @param $file
     * @return string|bool(false) The first line without trailing CR/LF or bool(false) on failure
     */
    static function readFirstLine($file)
    {
        if (($fh = fopen($file, 'r')) === false) {
            return false;
        }
        if (($line = fgets($fh)) === false) {
            fclose($fh);
            return false;
        }
        fclose($fh);
        return rtrim($line, "\r\n");
    }
}

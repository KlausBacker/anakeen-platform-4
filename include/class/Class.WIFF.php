<?php
/*
 * Web Installer for Freedom Class
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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

require_once 'class/Class.WiffCommon.php';

class WIFF extends WiffCommon
{
    const logIdent = 'dynacase-control';
    
    const contexts_filepath = 'conf/contexts.xml';
    const params_filepath = 'conf/params.xml';
    const archive_filepath = 'archived-contexts/';
    const xsd_catalog_xml = 'xsd/catalog.xml';
    const log_filepath = 'log/wiff.log';
    
    public $update_host;
    public $update_url;
    public $update_file;
    public $update_login;
    public $update_password;
    
    public $contexts_filepath = '';
    public $params_filepath = '';
    public $archive_filepath = '';
    public $xsd_catalog_xml = '';
    public $log_filepath = '';
    
    public $errorMessage = null;
    
    public $archiveFile;
    
    public $authInfo = array();
    
    private static $instance;
    
    private static $lock = null;
    
    public $errorStatus = "";
    /**
     * @var Logger
     */
    private static $logger;
    
    private function __construct()
    {
        $wiff_root = getenv('WIFF_ROOT');
        if ($wiff_root !== false) {
            $wiff_root = $wiff_root . DIRECTORY_SEPARATOR;
        }
        
        $this->contexts_filepath = $wiff_root . WIFF::contexts_filepath;
        $this->params_filepath = $wiff_root . WIFF::params_filepath;
        $this->archive_filepath = $wiff_root . WIFF::archive_filepath;
        $this->xsd_catalog_xml = $wiff_root . WIFF::xsd_catalog_xml;
        $this->log_filepath = $wiff_root . WIFF::log_filepath;
        
        $this->update_host = $this->getParam('wiff-update-host');
        $this->update_url = $this->getParam('wiff-update-path');
        $this->update_file = $this->getParam('wiff-update-file');
        $this->update_login = $this->getParam('wiff-update-login');
        $this->update_password = $this->getParam('wiff-update-password');
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
    public function getVersion()
    {
        $wiff_root = getenv('WIFF_ROOT');
        if ($wiff_root !== false) {
            $wiff_root = $wiff_root . DIRECTORY_SEPARATOR;
        }
        
        if (!$fversion = fopen($wiff_root . 'VERSION', 'r')) {
            $this->errorMessage = sprintf("Error when opening VERSION file.");
            return false;
        }
        
        if (!$frelease = fopen($wiff_root . 'RELEASE', 'r')) {
            $this->errorMessage = sprintf("Error when opening RELEASE file.");
            return false;
        }
        
        $version = trim(fgets($fversion));
        $release = trim(fgets($frelease));
        
        fclose($fversion);
        fclose($frelease);
        
        return $version . '-' . $release;
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
            if ($name == 'dynacase-control') {
                $version = $module->getAttribute('version');
                $release = $module->getAttribute('release');
                $return = $version . '-' . $release;
            }
        }
        
        unlink($tmpfile);
        
        return $return;
    }
    
    public function getLogin()
    {
        if (!$this->hasPasswordFile()) {
            return false;
        } else {
            @$passwordFile = fopen('.htpasswd', 'r');
            $explode = explode(':', fgets($passwordFile, 100));
            $login = $explode[0];
            return $login;
        }
    }
    
    public function hasPasswordFile()
    {
        
        @$accessFile = fopen('.htaccess', 'r');
        @$passwordFile = fopen('.htpasswd', 'r');
        
        if (!$accessFile || !$passwordFile) {
            return false;
        } else {
            return true;
        }
    }
    
    public function createPasswordFile($login, $password)
    {
        $this->createHtpasswdFile($login, $password);
        $this->createHtaccessFile();
        
        return true;
    }
    
    public function createHtaccessFile()
    {
        @$accessFile = fopen('.htaccess', 'w');
        fwrite($accessFile, "AuthUserFile " . getenv('WIFF_ROOT') . "/.htpasswd\n" . "AuthName 'Veuillez vous identifier'\n" . "AuthType Basic\n" . "\n" . "Require valid-user\n");
        fclose($accessFile);
    }
    
    public function createHtpasswdFile($login, $password)
    {
        @$passwordFile = fopen('.htpasswd', 'w');
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
     * Check if WIFF has available update
     * @return boolean
     */
    public function needUpdate()
    {
        $vr = $this->getVersion();
        $avr = $this->getAvailVersion();
        return $this->compareVersion($avr, $vr) > 0 ? true : false;
    }
    /**
     * Download latest WIFF file archive
     * @return bool|string
     */
    private function download()
    {
        $this->archiveFile = $this->downloadUrl($this->getUpdateBaseURL() . $this->update_file);
        return $this->archiveFile;
    }
    /**
     * Unpack archive in specified destination directory
     * @return string containing the given destination dir pr false in case of error
     */
    private function unpack()
    {
        include_once ('lib/Lib.System.php');
        
        if (!is_file($this->archiveFile)) {
            $this->errorMessage = sprintf("Archive file has not been downloaded.");
            return false;
        }
        
        $cmd = 'tar xf ' . escapeshellarg($this->archiveFile) . ' --strip-components 1 2>&1';
        
        $ret = null;
        exec($cmd, $output, $ret);
        if ($ret != 0) {
            $this->errorMessage = sprintf("Error executing command [%s]: %s", $cmd, join("\n", $output));
            return false;
        }
        
        return true;
    }
    /**
     * Update WIFF
     * @return boolean
     */
    public function update()
    {
        $v1 = $this->getVersion();
        
        $ret = $this->download();
        if ($ret === false) {
            return $ret;
        }
        
        $ret = $this->unpack();
        if ($ret === false) {
            return $ret;
        }
        
        $v2 = $this->getVersion();
        
        $ret = $this->postUpgrade($v1, $v2);
        if ($ret === false) {
            return $ret;
        }
        
        return true;
    }
    /**
     * Get global repository list
     * @return Repository[] array of object Repository
     */
    public function getRepoList($checkValidity = true)
    {
        require_once ('class/Class.Repository.php');
        
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
        require_once ('class/Class.Repository.php');
        
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
        require_once ('class/Class.Repository.php');
        
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
        
        $repository->setAttribute('label', $repositoryObject->label);
        
        $ret = $this->commitDOMDocument($xml);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error writing file '%s': %s", $this->params_filepath, $this->errorMessage);
            return false;
        }
        return ($returnRepoValidation ? $isValid : true);
    }
    public function createRepoUrl($name, $url, $authUser = null, $authPassword = null)
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
        'no', $authenticated, $authUser, $authPassword, false);
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
        require_once ('class/Class.Repository.php');
        
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
        require_once ('class/Class.Repository.php');
        
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
    public function getContextListWithInProgress() {
        return $this->getContextList(true);
    }

    /**
     * Get Context list
     * @param bool $withInProgress Include contexts being restored as ContextProperties objects instead of
     * full-blown Context objects (default: bool(false))
     * @return Context[] array of object Context or bool(false) on error
     */
    public function getContextList($withInProgress = false)
    {
        require_once ('class/Class.Repository.php');
        require_once ('class/Class.Context.php');
        
        $contextList = array();
        
        $xml = $this->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $this->errorMessage);
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
        
        $archived_root = $this->archive_filepath;
        
        if (is_dir($archived_root)) {
            if (!is_writable($archived_root)) {
                $this->errorMessage = sprintf("Directory '%s' is not writable.", $archived_root);
                return false;
            }
        } else {
            if (@mkdir($archived_root) === false) {
                $this->errorMessage = sprintf("Error creating directory '%s'.", $archived_root);
                return false;
            }
        }
        
        if ($withInProgress) {
            if ($handle = opendir($archived_root)) {
                while (false !== ($file = readdir($handle))) {
                    if (!preg_match('/^.+\.ctx$/', $file)) {
                        continue;
                    }
                    $absFile = $archived_root . DIRECTORY_SEPARATOR . $file;
                    if (($name = file_get_contents($absFile)) !== false) {
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
    
    private function addErrorToArchiveInfo($errorMsg, array $archiveContext, array & $archivedContextList)
    {
        $this->errorMessage.= $this->errorMessage ? "\n" : "" . $errorMsg;
        $archiveContext['error'] = $errorMsg;
        $archivedContextList[] = $archiveContext;
    }
    
    public function getArchivedContextList()
    {
        $archivedContextList = array();
        
        $wiff_root = getenv('WIFF_ROOT');
        if ($wiff_root !== false) {
            $wiff_root = $wiff_root . DIRECTORY_SEPARATOR;
        }
        
        $archived_root = $wiff_root . WIFF::archive_filepath;
        
        if (is_dir($archived_root)) {
            if (!is_writable($archived_root)) {
                $this->errorMessage = sprintf("Directory '%s' is not writable.", $archived_root);
                return false;
            }
        } else {
            if (@mkdir($archived_root) === false) {
                $this->errorMessage = sprintf("Error creating directory '%s'.", $archived_root);
                return false;
            }
        }
        
        if ($handle = opendir($archived_root)) {
            
            while (false !== ($file = readdir($handle))) {
                
                if (preg_match('/^(?P<basename>.+)\.fcz$/', $file, $fmatch)) {
                    
                    $zipfile = $archived_root . DIRECTORY_SEPARATOR . $file;
                    $size = $this->filesize_stat($zipfile);
                    $archiveContext = array(
                        "urlfile" => WIFF::archive_filepath . DIRECTORY_SEPARATOR . $file,
                        "moduleList" => array() ,
                        "id" => $fmatch["basename"],
                        "size" => $size / (1024 * 1024) >= 1024 ? sprintf("%.3f Go", $size / (1024.0 * 1024.0 * 1024.0)) : sprintf("%.3f Mo", $size / (1024.0 * 1024.0)) ,
                        "datetime" => "-",
                        "description" => "-",
                        "vault" => "-",
                        "name" => "invalid archive"
                    );
                    
                    if (file_exists($archived_root . DIRECTORY_SEPARATOR . $fmatch["basename"] . ".error")) {
                        $file = $fmatch["basename"] . ".error";
                        $error_handle = fopen($archived_root . DIRECTORY_SEPARATOR . $file, 'r');
                        $this->addErrorToArchiveInfo("Error with archive " . $zipfile . " : " . fread($error_handle, filesize($archived_root . DIRECTORY_SEPARATOR . $file)) , $archiveContext, $archivedContextList);
                        continue;
                    }
                    
                    $zip = new ZipArchiveCmd();
                    $ret = $zip->open($zipfile);
                    if ($ret === false) {
                        $this->addErrorToArchiveInfo("Error when opening archive.", $archiveContext, $archivedContextList);
                        continue;
                    }
                    
                    $zipIndex = $zip->getIndex();
                    if ($zipIndex === false) {
                        $this->addErrorToArchiveInfo(sprintf("Error getting index from Zip file '%s': %s", $zipfile, $zip->getStatusString()) , $archiveContext, $archivedContextList);
                        continue;
                    }
                    
                    $foundInfoXML = false;
                    foreach ($zipIndex as $info) {
                        if ($info['name'] == 'info.xml') {
                            $foundInfoXML = true;
                            break;
                        }
                    }
                    if (!$foundInfoXML) {
                        $this->addErrorToArchiveInfo(sprintf("Could not find 'info.xml' in content index of Zip file '%s'.", $zipfile) , $archiveContext, $archivedContextList);
                        continue;
                    }
                    
                    $info_content = $zip->getFileContentFromName('info.xml');
                    if ($info_content === false) {
                        $this->addErrorToArchiveInfo(sprintf("Error extracting 'info.xml' from '%s': %s", $zipfile, $zip->getStatusString()) , $archiveContext, $archivedContextList);
                        continue;
                    }
                    
                    $xml = new DOMDocument();
                    $ret = $xml->loadXML($info_content);
                    if ($ret === false) {
                        $this->addErrorToArchiveInfo(sprintf("Error loading XML file '%s'.", $info_content) , $archiveContext, $archivedContextList);
                        continue;
                    }
                    
                    $xpath = new DOMXpath($xml);
                    
                    $contexts = $xpath->query("/info/context");
                    $contextName = "";
                    if ($contexts->length > 0) {
                        foreach ($contexts as $context) { // Should be only one context
                            
                            /**
                             * @var DOMElement $context
                             */
                            $contextName = $context->getAttribute('name');
                        }
                    }
                    
                    $archived_contexts = $xpath->query("/info/archive");
                    
                    if ($archived_contexts->length > 0) {
                        foreach ($archived_contexts as $context) { // Should be only one context
                            $archiveContext = array();
                            $archiveContext['name'] = $context->getAttribute('name');
                            $archiveContext['size'] = $size / (1024 * 1024) >= 1024 ? sprintf("%.3f Go", $size / (1024.0 * 1024.0 * 1024.0)) : sprintf("%.3f Mo", $size / (1024.0 * 1024.0));
                            $archiveContext['description'] = $context->getAttribute('description');
                            $archiveContext['id'] = $fmatch['basename'];
                            $archiveContext['datetime'] = $context->getAttribute('datetime');
                            $archiveContext['vault'] = $context->getAttribute('vault');
                            $archiveContext['urlfile'] = self::archive_filepath . DIRECTORY_SEPARATOR . $file;
                            
                            $moduleList = array();
                            
                            $moduleDom = $xpath->query("/info/context[@name='" . $contextName . "']/modules/module");
                            
                            foreach ($moduleDom as $module) {
                                $mod = new Module($this, null, $module, true);
                                if ($mod->status == 'installed') {
                                    $moduleList[] = $mod;
                                }
                            }
                            
                            $archiveContext['moduleList'] = $moduleList;
                            
                            $archivedContextList[] = $archiveContext;
                        }
                    }
                }
                
                if (preg_match('/^.+\.sts$/', $file)) {
                    
                    $this->log(LOG_INFO, 'STATUS FILE --- ' . $file);
                    
                    $status_handle = fopen($archived_root . DIRECTORY_SEPARATOR . $file, 'r');
                    $archiveContext = array();
                    $archiveContext['name'] = fread($status_handle, filesize($archived_root . DIRECTORY_SEPARATOR . $file));
                    
                    $archiveContext['inProgress'] = true;
                    $archivedContextList[] = $archiveContext;
                }
            }
        }
        
        return $archivedContextList;
    }
    /**
     * @param $archiveId
     * @return bool
     */
    function getArchivedContextById($archiveId)
    {
        $archiveList = $this->getArchivedContextList();
        foreach ($archiveList as $archive) {
            if ($archive['id'] === $archiveId) {
                return $archive;
            }
        }
        return false;
    }
    /**
     * Compute file size based on the number of blocks
     * (of 512 bytes) returned by stat().
     *
     * @param string $file filename
     * @return float size or boolean false on error
     */
    public function filesize_stat($file)
    {
        $stat = stat($file);
        if ($stat === false) {
            return false;
        }
        return (float)($stat['blocks'] * 512);
    }
    
    public function verifyGzipIntegrity($file, &$err = '')
    {
        $cmd = sprintf("gzip -t %s 2>&1", escapeshellarg($file));
        $output = array();
        exec($cmd, $output, $retval);
        $err = join("\n", $output);
        if ($retval != 0) {
            return false;
        }
        return true;
    }
    
    public function verifyArchiveIntegrity($pathToArchive)
    {
        if (($handle = opendir($pathToArchive)) === false) {
            $this->errorMessage = sprintf("Can't open archive directory '%s'", $pathToArchive);
            return false;
        }
        while (($file = readdir($handle)) !== false) {
            if (substr($file, -3) !== ".gz") {
                continue;
            }
            $file = $pathToArchive . DIRECTORY_SEPARATOR . $file;
            if ($this->verifyGzipIntegrity($file, $err) === false) {
                $this->errorMessage = sprintf("Archive '%s' is not correct: %s", $file, $err);
                return false;
            }
        }
        return true;
    }
    
    public function createContextFromArchive($archiveId, $name, $root, $desc, $url, $vault_root, $pgservice, $remove_profiles, $user_login, $user_password, $clean_tmp_directory = false)
    {
        $wiff_root = getenv('WIFF_ROOT');
        if ($wiff_root !== false) {
            $wiff_root = $wiff_root . DIRECTORY_SEPARATOR;
        }
        if (!$this->checkValidContextDirChars($root)) {
            $this->errorMessage = sprintf("Invalid context root directory '%s': %s", $root, $this->errorMessage);
            return false;
        }
        $archived_root = $wiff_root . self::archive_filepath;
        // --- Create status file for context --- //
        $status_file = $archived_root . DIRECTORY_SEPARATOR . $archiveId . '.ctx';
        file_put_contents($status_file, $name);
        // --- Connect to database --- //
        $dbconnect = pg_connect("service=$pgservice");
        if ($dbconnect === false) {
            $this->errorMessage = "Error connecting to database 'service=$pgservice'";
            $this->log(LOG_ERR, $this->errorMessage);
            unlink($status_file);
            return false;
        }
        // --- Create or reuse directory --- //
        if (is_dir($root)) {
            if (!is_writable($root)) {
                $this->errorMessage = sprintf("Directory '%s' is not writable.", $root);
                // --- Delete status file --- //
                unlink($status_file);
                return false;
            }
            $dirListing = @scandir($root);
            if ($dirListing === false) {
                $this->errorMessage = sprintf("Error scanning directory '%s'.", $root);
                // --- Delete status file --- //
                unlink($status_file);
                return false;
            }
            $dirListingCount = count($dirListing);
            if ($dirListingCount > 2) {
                $this->errorMessage = sprintf("Directory '%s' is not empty.", $root);
                // --- Delete status file --- //
                unlink($status_file);
                return false;
            }
        } else {
            if (@mkdir($root) === false) {
                $this->errorMessage = sprintf("Error creating directory '%s'.", $root);
                // --- Delete status file --- //
                unlink($status_file);
                return false;
            }
        }
        
        if (is_dir($vault_root)) {
            if (!is_writable($vault_root)) {
                $this->errorMessage = sprintf("Directory '%s' is not writable.", $vault_root);
                // --- Delete status file --- //
                unlink($status_file);
                return false;
            }
            $dirListing = @scandir($vault_root);
            if ($dirListing === false) {
                $this->errorMessage = sprintf("Error scanning directory '%s'.", $vault_root);
                // --- Delete status file --- //
                unlink($status_file);
                return false;
            }
            $dirListingCount = count($dirListing);
            if ($dirListingCount > 2) {
                $this->errorMessage = sprintf("Directory '%s' is not empty.", $vault_root);
                // --- Delete status file --- //
                unlink($status_file);
                return false;
            }
        } else {
            if (@mkdir($vault_root, 0777, true) === false) {
                $this->errorMessage = sprintf("Error creating directory '%s'.", $vault_root);
                // --- Delete status file --- //
                unlink($status_file);
                return false;
            }
        }
        // If Context already exists, method fails.
        if ($this->getContext($name) !== false) {
            $this->errorMessage = sprintf("Context '%s' already exists.", $name);
            // --- Delete status file --- //
            unlink($status_file);
            return false;
        }
        // Get absolute pathname if directory is not already in absolute form
        if (!preg_match('|^/|', $root)) {
            $abs_root = realpath($root);
            if ($abs_root === false) {
                $this->errorMessage = sprintf("Error getting absolute pathname for '%s'.", $root);
                // --- Delete status file --- //
                unlink($status_file);
                return false;
            }
            $root = $abs_root;
        }
        
        if (!preg_match('|^/|', $vault_root)) {
            $abs_vault_root = realpath($vault_root);
            if ($abs_vault_root === false) {
                $this->errorMessage = sprintf("Error getting absolute pathname for '%s'.", $vault_root);
                // --- Delete status file --- //
                unlink($status_file);
                return false;
            }
            $vault_root = $abs_vault_root;
        }
        
        $wiff_root = getenv('WIFF_ROOT');
        if ($wiff_root !== false) {
            $wiff_root = $wiff_root . DIRECTORY_SEPARATOR;
        }
        
        $archived_root = $wiff_root . WIFF::archive_filepath;
        
        $temporary_extract_root = $archived_root . 'archived-tmp';
        if (!is_dir($temporary_extract_root)) {
            $ret = mkdir($temporary_extract_root);
            if ($ret === false) {
                $this->errorMessage = sprintf("Error creating temporary extract root directory '%s'.", $temporary_extract_root);
                unlink($status_file);
                return false;
            }
        }
        if (!$this->deleteDirContent($temporary_extract_root, true)) {
            $this->errorMessage = sprintf("Error cleaning up extract-tmp dir: %s", $this->errorMessage);
            unlink($status_file);
            return false;
        }
        $vaultfound = false;
        $context_tar = $temporary_extract_root . DIRECTORY_SEPARATOR . "context.tar.gz";
        $dump = $temporary_extract_root . DIRECTORY_SEPARATOR . "core_db.pg_dump.gz";
        
        if ($handle = opendir($archived_root)) {
            
            while (false !== ($file = readdir($handle))) {
                
                if ($file == $archiveId . '.fcz') {
                    
                    $zip = new ZipArchiveCmd();
                    $zipfile = $archived_root . DIRECTORY_SEPARATOR . $file;
                    $ret = $zip->open($zipfile);
                    if ($ret === false) {
                        $this->errorMessage = sprintf("Error when opening archive '%s': %s", $zipfile, $zip->getStatusString());
                        // --- Delete status file --- //
                        unlink($status_file);
                        return false;
                    }
                    $ret = $zip->extractTo($temporary_extract_root);
                    if ($ret === false) {
                        $this->errorMessage = sprintf("Error extracting '%s' into '%s': %s", $zipfile, $temporary_extract_root, $zip->getStatusString());
                        unlink($status_file);
                        $zip->close();
                        return false;
                    }
                    
                    $ret = $this->verifyArchiveIntegrity($temporary_extract_root);
                    if ($ret === false) {
                        unlink($status_file);
                        $zip->close();
                        return false;
                    }
                    // --- Extract context tar gz --- //
                    $script = sprintf("tar -zxf %s -C %s", escapeshellarg($context_tar) , escapeshellarg($root));
                    
                    exec($script, $output, $retval);
                    
                    if ($retval != 0) {
                        $this->errorMessage = "Error when extracting context.tar.gz to $root";
                        // --- Delete status file --- //
                        unlink($status_file);
                        return false;
                    }
                    
                    $this->log(LOG_INFO, 'Context tar gz extracted');
                    // --- Restore database --- //
                    // Setting datestyle
                    // Get current database name
                    $result = pg_query($dbconnect, sprintf("SELECT current_database()"));
                    if ($result === false) {
                        $this->errorMessage = sprintf("Error getting current database name: %s", pg_last_error($dbconnect));
                        $this->log(LOG_ERR, $this->errorMessage);
                        unlink($status_file);
                        return false;
                    }
                    $row = pg_fetch_assoc($result);
                    if ($row === false) {
                        $this->errorMessage = sprintf("Error fetching first row for current database name: %s", pg_last_error($dbconnect));
                        $this->log(LOG_ERR, $this->errorMessage);
                        unlink($status_file);
                        return false;
                    }
                    if (!isset($row['current_database'])) {
                        $this->errorMessage = sprintf("Error getting 'current_database' field in row: %s", pg_last_error($dbconnect));
                        $this->log(LOG_ERR, $this->errorMessage);
                        unlink($status_file);
                        return false;
                    }
                    $current_database = $row['current_database'];
                    if ($current_database == '') {
                        $this->errorMessage = sprintf("Got an empty current database name!?!");
                        $this->log(LOG_ERR, $this->errorMessage);
                        unlink($status_file);
                        return false;
                    }
                    // Alter current database datestyle
                    $result = pg_query($dbconnect, sprintf("ALTER DATABASE \"%s\" SET datestyle = 'SQL, DMY';", str_replace("\"", "\"\"", $current_database)));
                    if ($result === false) {
                        $this->errorMessage = "Error when trying to set database datestyle :: " . pg_last_error($dbconnect);
                        $this->log(LOG_ERR, "Error when trying to set database datestyle :: " . pg_last_error($dbconnect));
                        unlink($status_file);
                        return false;
                    }
                    pg_close($dbconnect);
                    
                    $script = sprintf("gzip -dc %s | PGSERVICE=%s psql", escapeshellarg($dump) , escapeshellarg($pgservice));
                    exec($script, $output, $retval);
                    
                    if ($retval != 0) {
                        $this->errorMessage = "Error when restoring core_db.pg_dump.gz";
                        // --- Delete status file --- //
                        unlink($status_file);
                        return false;
                    }
                    
                    $this->log(LOG_INFO, 'Database restored');
                    // --- Extract vault tar gz --- //
                    if ($handle = opendir($temporary_extract_root)) {
                        
                        while (false !== ($file = readdir($handle))) {
                            
                            if (substr($file, 0, 5) == 'vault') {
                                $id_fs = substr($file, 6, -7);
                                $vaultfound = true;
                                $vault_tar = $temporary_extract_root . DIRECTORY_SEPARATOR . $file;
                                $vault_subdir = $vault_root . DIRECTORY_SEPARATOR . $id_fs . DIRECTORY_SEPARATOR;
                                
                                if (@mkdir($vault_subdir, 0777, true) === false) {
                                    $this->errorMessage = sprintf("Error creating directory '%s'.", $vault_subdir);
                                    $this->log(LOG_ERR, sprintf("Error creating directory '%s'.", $vault_subdir));
                                    // --- Delete status file --- //
                                    unlink($status_file);
                                    return false;
                                }
                                
                                $script = sprintf("tar -zxf %s -C %s", escapeshellarg($vault_tar) , escapeshellarg($vault_subdir));
                                
                                exec($script, $output, $retval);
                                
                                if ($retval != 0) {
                                    $this->errorMessage = "Error when extracting vault to $vault_root";
                                    // --- Delete status file --- //
                                    unlink($status_file);
                                    return false;
                                }
                                
                                if ($clean_tmp_directory) {
                                    // --- Delete tmp tar file --- //
                                    unlink($vault_tar);
                                }
                            }
                        }
                    }
                    
                    $this->log(LOG_INFO, 'Vault tar gz extracted');
                }
            }
        }
        // Write contexts XML
        $xml = $this->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $this->errorMessage);
            return false;
        }
        $xml->formatOutput = true;
        
        $infoFile = $temporary_extract_root . DIRECTORY_SEPARATOR . "info.xml";
        
        $archiveXml = new DOMDocument();
        $ret = $archiveXml->load($infoFile);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error loading XML file '%s'.", $infoFile);
            return false;
        }
        $xmlXPath = new DOMXPath($xml);
        $contextList = $xmlXPath->query("/contexts/context[@name='" . $name . "']");
        if ($contextList->length != 0) {
            // If more than one context with name
            $this->errorMessage = "Context with same name already exists.";
            // --- Delete status file --- //
            unlink($status_file);
            return false;
        }
        
        $contextList = $xmlXPath->query("/contexts");
        
        $archiveXPath = new DOMXPath($archiveXml);
        // Get this context
        $archiveList = $archiveXPath->query("/info/context");
        if ($archiveList->length != 1) {
            // If more than one context found
            $this->errorMessage = "More than one context in archive";
            // --- Delete status file --- //
            unlink($status_file);
            return false;
        }
        /**
         * @var DOMElement $context
         */
        $context = $xml->importNode($archiveList->item(0) , true); // Node must be imported from archive document.
        $context->setAttribute('name', $name);
        $context->setAttribute('root', $root);
        $context->setAttribute('url', $url);
        $contextList->item(0)->appendChild($context);
        // Modify core_db in xml
        $paramList = $xmlXPath->query("/contexts/context[@name='" . $name . "']/parameters-value/param[@name='core_db']");
        if ($paramList->length != 1) {
            $this->errorMessage = "Parameter core_db does not exist.";
            // --- Delete status file --- //
            unlink($status_file);
            return false;
        }
        /**
         * @var DOMElement $paramNode
         */
        $paramNode = $paramList->item(0);
        $paramNode->setAttribute('value', $pgservice);
        // Modify client_name in xml by context name
        $paramList = $xmlXPath->query("/contexts/context[@name='" . $name . "']/parameters-value/param[@name='client_name']");
        if ($paramList->length != 1) {
            $this->errorMessage = "Parameter client_name does not exist.";
            // --- Delete status file --- //
            unlink($status_file);
            return false;
        }
        $paramNode = $paramList->item(0);
        $paramNode->setAttribute('value', $name);
        // Modify or add vault_root in xml
        $paramList = $xmlXPath->query("/contexts/context[@name='" . $name . "']/parameters-value/param[@name='vault_root']");
        $paramValueList = $xmlXPath->query("/contexts/context[@name='" . $name . "']/parameters-value");
        $paramVaultRoot = $xml->createElement('param');
        $paramVaultRoot->setAttribute('name', 'vault_root');
        $paramVaultRoot->setAttribute('value', $vault_root);
        if ($vaultfound === false) {
            $vault_save_value = 'no';
        } else {
            $vault_save_value = 'yes';
        }
        if ($paramList->length != 1) {
            $paramVaultRoot = $paramValueList->item(0)->appendChild($paramVaultRoot);
        } else {
            $paramNode = $paramList->item(0);
            $paramVaultRoot = $paramValueList->item(0)->replaceChild($paramVaultRoot, $paramNode);
        }
        
        $vault_save = $xml->createElement('param');
        $vault_save->setAttribute('name', 'vault_save');
        $vault_save->setAttribute('value', $vault_save_value);
        $paramValueList->item(0)->appendChild($vault_save);
        
        if (isset($remove_profiles) && $remove_profiles == true) {
            // Modify or add remove_profiles in xml
            $paramList = $xmlXPath->query("/contexts/context[@name='" . $name . "']/parameters-value/param[@name='remove_profiles']");
            if ($paramList->length != 1) {
                
                $paramValueList = $xmlXPath->query("/contexts/context[@name='" . $name . "']/parameters-value");
                
                $paramRemoveProfiles = $xml->createElement('param');
                $paramRemoveProfiles->setAttribute('name', 'remove_profiles');
                $paramRemoveProfiles->setAttribute('value', true);
                $paramValueList->item(0)->appendChild($paramVaultRoot);
                
                $paramUserLogin = $xml->createElement('param');
                $paramUserLogin->setAttribute('name', 'user_login');
                $paramUserLogin->setAttribute('value', $user_login);
                $paramValueList->item(0)->appendChild($paramUserLogin);
                
                $paramUserPassword = $xml->createElement('param');
                $paramUserPassword->setAttribute('name', 'user_password');
                $paramUserPassword->setAttribute('value', $user_password);
                $paramValueList->item(0)->appendChild($paramUserPassword);
            }
        }
        // Save XML to file
        $ret = $this->commitDOMDocument($xml);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error saving 'contexts.xml': %s", $this->errorMessage);
            // --- Delete status file --- //
            unlink($status_file);
            return false;
        }
        // Run reconfigure phase
        $this->reconfigure($name);
        
        if ($clean_tmp_directory) {
            // --- Delete Tmp tar file --- //
            unlink($context_tar);
            unlink($dump);
            unlink($infoFile);
        }
        // --- Delete status file --- //
        unlink($status_file);
        
        return true;
    }
    
    public function reconfigure($name)
    {
        
        $this->log(LOG_INFO, 'Call to reconfigure');
        
        $context = $this->getContext($name);
        
        $installedModuleList = $context->getInstalledModuleList();
        foreach ($installedModuleList as $module) {
            /**
             * @var Module $module
             */
            $phase = $module->getPhase('reconfigure');
            $processList = $phase->getProcessList();
            foreach ($processList as $process) {
                /**
                 * @var Process $process
                 */
                $process->execute();
            }
        }
    }
    /**
     * Delete an archived context.
     * @return boolean method success
     * @param integer $archiveId
     */
    public function deleteArchive($archiveId)
    {
        
        $wiff_root = getenv('WIFF_ROOT');
        if ($wiff_root !== false) {
            $wiff_root = $wiff_root . DIRECTORY_SEPARATOR;
        }
        
        $archived_root = $wiff_root . WIFF::archive_filepath;
        
        if (file_exists($archived_root . $archiveId . '.error')) {
            unlink($archived_root . $archiveId . '.error');
        }
        if (file_exists($archived_root . $archiveId . '.sts')) {
            unlink($archived_root . $archiveId . '.sts');
        }
        
        if (unlink($archived_root . $archiveId . '.fcz')) {
            return true;
        }
        
        return false;
    }
    /**
     * Get an url to download an archived context.
     * @return string Archive url
     * @param string $archiveId Archive Id
     */
    public function downloadArchive($archiveId)
    {
        
        $archived_url = curPageURL() . self::archive_filepath;
        
        return $archived_url . DIRECTORY_SEPARATOR . $archiveId . 'fcz';
    }
    /**
     * Get Context by name
     * @return Context Context or boolean false
     * @param string $name context name
     * @param bool $opt (default false)
     */
    public function getContext($name, $opt = false)
    {
        require_once ('class/Class.Repository.php');
        require_once ('class/Class.Context.php');
        
        $xml = $this->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $this->errorMessage);
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
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $this->errorMessage);
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
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $this->errorMessage);
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
        require_once 'class/Class.WWWUserAgent.php';
        
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
    
    public function postUpgrade($fromVersion, $toVersion)
    {
        include_once ('lib/Lib.System.php');
        
        $wiff_root = getenv('WIFF_ROOT');
        if ($wiff_root !== false) {
            $wiff_root = $wiff_root . DIRECTORY_SEPARATOR;
        }
        
        $dir = @opendir(sprintf('%s/%s', $wiff_root, 'migr'));
        if ($dir === false) {
            $this->errorMessage = sprintf("Failed to open 'migr' directory.");
            return false;
        }
        
        $migrList = array();
        while ($migr = readdir($dir)) {
            array_push($migrList, $migr);
        }
        
        usort($migrList, array(
            $this,
            'postUpgradeCompareVersion'
        ));
        
        foreach ($migrList as $migr) {
            if ($this->compareVersion($migr, $fromVersion) <= 0) {
                continue;
            }
            
            $this->log(LOG_INFO, __METHOD__ . " " . sprintf("Executing migr script '%s'.", $migr));
            $temp = tempnam(null, sprintf("wiff_migr_%s", $migr));
            if ($temp === false) {
                $this->errorMessage = "Could not create temp file.";
                return false;
            }
            
            $migrScript = sprintf("%s/%s/%s", $wiff_root, 'migr', $migr);
            $cmd = sprintf("%s > %s 2>&1", escapeshellarg($migrScript) , escapeshellarg($temp));
            exec($cmd, $output, $ret);
            $output = file_get_contents($temp);
            if ($ret !== 0) {
                $err = sprintf("Migr script '%s' returned with error status %s (output=[[[%s]]])", $migr, $ret, $output);
                $this->log(LOG_ERR, __METHOD__ . " " . sprintf("%s", $err));
                $this->errorMessage = $err;
                return false;
            }
            $this->log(LOG_INFO, __METHOD__ . " " . sprintf("Migr script '%s': Ok.", $migr));
            @unlink($temp);
        }
        
        $this->errorMessage = '';
        return true;
    }
    
    function postUpgradeCompareVersion($a, $b)
    {
        return version_compare($a, $b);
    }
    
    function getLicenseAgreement($ctxName, $moduleName, $licenseName)
    {
        $xml = $this->loadContextsDOMDocument();
        if ($xml === false) {
            $err = sprintf(__METHOD__ . " " . "Could not load 'contexts.xml': %s", $this->errorMessage);
            $this->log(LOG_ERR, $err);
            $this->errorMessage = $err;
            return false;
        }
        
        $xpath = new DOMXpath($xml);
        $query = sprintf("/contexts/context[@name='%s']/licenses/license[@module='%s' and @license='%s']", $ctxName, $moduleName, $licenseName);
        $licensesList = $xpath->query($query);
        
        if ($licensesList->length <= 0) {
            $err = sprintf(__METHOD__ . " " . "Could not find a license for module '%s' in context '%s'.", $moduleName, $ctxName);
            $this->errorMessage = $err;
            return 'no';
        }
        
        if ($licensesList->length > 1) {
            $warn = sprintf(__METHOD__ . " " . "Warning: found more than one license for module '%s' in context '%s'", $moduleName, $ctxName);
            $this->log(LOG_WARNING, $warn);
        }
        /**
         * @var DOMElement  $licenseNode
         */
        $licenseNode = $licensesList->item(0);
        
        $agree = ($licenseNode->getAttribute('agree') != 'yes') ? 'no' : 'yes';
        
        return $agree;
    }
    
    function storeLicenseAgreement($ctxName, $moduleName, $licenseName, $agree)
    {
        $xml = $this->loadContextsDOMDocument();
        if ($xml === false) {
            $err = sprintf(__METHOD__ . " " . "Could not load 'contexts.xml': %s", $this->errorMessage);
            $this->log(LOG_ERR, $err);
            $this->errorMessage = $err;
            return false;
        }
        
        $xpath = new DOMXpath($xml);
        
        $query = sprintf("/contexts/context[@name='%s']", $ctxName);
        $contextNodeList = $xpath->query($query);
        if ($contextNodeList->length <= 0) {
            $err = sprintf(__METHOD__ . " " . "Could not find context '%s' in '%s'.", $ctxName, $this->contexts_filepath);
            $this->errorMessage = $err;
            return false;
        }
        
        $licensesNode = null;
        $query = sprintf("/contexts/context[@name='%s']/licenses", $ctxName);
        $licensesNodeList = $xpath->query($query);
        if ($licensesNodeList->length <= 0) {
            // Create licenses node
            $licensesNode = $xml->createElement('licenses');
            $contextNodeList->item(0)->appendChild($licensesNode);
        } else {
            $licensesNode = $licensesNodeList->item(0);
        }
        
        $query = sprintf("/contexts/context[@name='%s']/licenses/license[@module='%s' and @license='%s']", $ctxName, $moduleName, $licenseName);
        $licenseNodeList = $xpath->query($query);
        
        if ($licenseNodeList->length > 1) {
            // That should not happen...
            // Cannot store/update license if multiple licenses exists.
            $err = sprintf(__METHOD__ . " " . "Warning: found more than one license for module '%s' in context '%s'", $moduleName, $ctxName);
            $this->log(LOG_ERR, $err);
            $this->errorMessage = $err;
            return false;
        }
        
        if ($licenseNodeList->length <= 0) {
            // Add a new license node.
            $licenseNode = $xml->createElement('license');
            $licenseNode->setAttribute('module', $moduleName);
            $licenseNode->setAttribute('license', $licenseName);
            $licenseNode->setAttribute('agree', $agree);
            
            $ret = $licensesNode->appendChild($licenseNode);
            if (!is_object($ret)) {
                $err = sprintf(__METHOD__ . " " . "Could not append license '%s' for module '%s' in context '%s'.", $moduleName, $licenseName, $ctxName);
                $this->log(LOG_ERR, $err);
                $this->errorMessage = $err;
                return false;
            }
        } else {
            // Update the existing license.
            
            /**
             * @var DOMElement $licenseNode
             */
            $licenseNode = $licenseNodeList->item(0);
            $licenseNode->setAttribute('agree', $agree);
        }
        
        $ret = $this->commitDOMDocument($xml);
        if ($ret === false) {
            $err = sprintf(__METHOD__ . " " . "Error saving 'contexts.xml': %s", $this->errorMessage);
            $this->log(LOG_ERR, $err);
            $this->errorMessage = $err;
            return false;
        }
        
        return $agree;
    }
    /**
     * Check repo validity
     * @param string $name
     * @return array|bool
     */
    public function checkRepoValidity($name)
    {
        $repo = $this->getRepo($name);
        if ($repo === false) {
            return false;
        }
        
        if ($repo->isValid() === false) {
            return false;
        }
        
        return array(
            'valid' => true,
            'label' => $repo->label
        );
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
    /**
     * Delete a context
     * @param string $contextName
     * @param boolean $result the result of the operation (boolean false|true)
     * @param string|boolean $opt (default false)
     * @return null|string
     */
    public function deleteContext($contextName, &$result, $opt = false)
    {
        $result = true;
        if ($opt === 'unregister') {
            $context = $this->getContext($contextName, true);
        } else {
            $context = $this->getContext($contextName);
        }
        if ($context === false) {
            $result = false;
            $this->log(LOG_ERR, "ContextName == $contextName ::: opt === $opt ::: error === $this->errorMessage");
            $this->errorMessage = sprintf("Error: could not get context '%s'.", $contextName);
            return $this->errorMessage;
        }
        
        $res = false;
        $err = $context->delete($res, $opt);
        if ($res === false) {
            $result = false;
            $this->log(LOG_ERR, "ContextName == $contextName ::: opt === $opt ::: error === $this->errorMessage");
            $this->errorMessage = sprintf("Error: could not delete context '%s': %s", $contextName, implode("\n", $err));
            return $this->errorMessage;
        }
        if (!empty($err)) {
            $this->log(LOG_ERR, __METHOD__ . " " . sprintf("The following errors occured : '%s'", $context->errorMessage));
            $this->errorMessage = sprintf("The following errors occured : '%s'", $context->errorMessage);
            return $err;
        }
        return null;
    }
    
    public function fmtSystemMsg($m)
    {
        return ($m != "" ? '<div style="margin-top:10px;font-color:#333;font-size:85%">' . $m . '</div>' : "");
    }
    /**
     * Generate a UUID suitable for registration process
     *
     * @return string $uuid the UUID in RFC 4122 form
     *
     */
    function genControlID()
    {
        return sprintf("%04x%04x-%04x-%04x-%04x-%04x%04x%04x", rand(0, 0xffff) , rand(0, 0xffff) , rand(0, 0xffff) , rand(0, 0xffff) , rand(0, 0xffff) , rand(0, 0xffff) , rand(0, 0xffff) , rand(0, 0xffff));
    }
    /**
     * Generate a {mid, ctrlid} and store it in params <registration/> node
     *
     * @param boolean $force to force regeneration of a new UUID
     *
     * @return array() on success or boolean false on error
     *
     */
    function checkInitRegistration($force = false)
    {
        $mid = $this->getMachineId();
        if ($mid === false) {
            return false;
        }
        
        $info = $this->getRegistrationInfo();
        if ($info === false) {
            return false;
        }
        
        $rewriteInfo = false;
        if ($info['mid'] != $mid) {
            $info['mid'] = $mid;
            $info['status'] = '';
            $rewriteInfo = true;
        }
        if ($force || $info['mid'] == '') {
            $info['mid'] = $this->getMachineId();
            $rewriteInfo = true;
        }
        if ($force || $info['ctrlid'] == '') {
            $info['ctrlid'] = $this->genControlID();
            $rewriteInfo = true;
        }
        
        if ($rewriteInfo) {
            $ret = $this->setRegistrationInfo($info);
            if ($ret === false) {
                return false;
            }
        }
        
        return $info;
    }
    
    function getMachineId()
    {
        include_once ('class/Class.StatCollector.php');
        
        $sc = new StatCollector();
        
        $mid = $sc->getMachineId();
        
        if ($mid === false) {
            $this->errorMessage = sprintf("Could not get machine id: %s", $sc->last_error);
            return false;
        }
        
        return $mid;
    }
    /**
     * Retrieve registration information.
     *
     * @return array|boolean false on error or array() $info on success
     *
     *   array(
     *     'mid' => $mid || '',
     *     'ctrlid' => $ctrlid || '',
     *     'login' => $login || '',
     *     'status' => 'registered' || 'unregistered' || ''
     *   )
     *
     */
    function getRegistrationInfo()
    {
        $xml = $this->loadParamsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading XML file '%s'.", $this->params_filepath);
            return false;
        }
        
        $xPath = new DOMXPath($xml);
        
        $info = array(
            'mid' => '',
            'ctrlid' => '',
            'login' => '',
            'status' => ''
        );
        
        $registrationNodeList = $xPath->query('/wiff/registration');
        if ($registrationNodeList->length > 0) {
            /**
             * @var DOMElement $registrationNode
             */
            $registrationNode = $registrationNodeList->item(0);
            foreach (array_keys($info) as $key) {
                $info[$key] = $registrationNode->getAttribute($key);
            }
        }
        
        return $info;
    }
    /**
     * Set/store registration info
     *
     * @param array $info containing the registration information
     *
     *   array(
     *     'uuid' => $uuid || '',
     *     'login' => $login || '',
     *     'status' => 'registered' || 'unregistered' || ''
     *   )
     *
     * @return boolean false on error or true on success.
     *
     */
    function setRegistrationInfo($info)
    {
        $xml = $this->loadParamsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading XML file '%s'.", $this->params_filepath);
            return false;
        }
        
        $xPath = new DOMXpath($xml);
        
        $registrationNode = null;
        $registrationNodeList = $xPath->query('/wiff/registration');
        if ($registrationNodeList->length <= 0) {
            $registrationNode = $xml->createElement('registration');
            $xml->documentElement->appendChild($registrationNode);
        } else {
            $registrationNode = $registrationNodeList->item(0);
        }
        
        foreach ($info as $key => $value) {
            $registrationNode->setAttribute($key, $value);
        }
        
        $ret = $this->commitDOMDocument($xml);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error writing file '%s': %s", $this->params_filepath, $this->errorMessage);
            return false;
        }
        
        return $info;
    }
    
    function getRegistrationClient()
    {
        include_once ('class/Class.RegistrationClient.php');
        
        $rc = new RegistrationClient();
        
        if ($this->getParam('use-proxy') === 'yes') {
            $proxy_host = $this->getParam('proxy-host');
            $proxy_port = $this->getParam('proxy-port');
            $proxy_user = $this->getParam('proxy-username');
            $proxy_pass = $this->getParam('proxy-password');
            
            if ($proxy_host != '') {
                if ($proxy_user != '') {
                    $rc->setProxy($proxy_host, $proxy_port, $proxy_user, $proxy_pass);
                } else {
                    $rc->setProxy($proxy_host, $proxy_port);
                }
            }
        }
        
        return $rc;
    }
    
    function tryRegister($mid, $ctrlid, $login, $password)
    {
        $rc = $this->getRegistrationClient();
        
        $response = $rc->register($mid, $ctrlid, $login, $password);
        if ($response === false) {
            $this->errorMessage = sprintf("Error posting register request: '%s'", $rc->last_error);
            return false;
        }
        
        if ($response['code'] >= 200 && $response['code'] < 300) {
            $info['login'] = $login;
            $info['status'] = 'registered';
            $ret = $this->setRegistrationInfo($info);
            if ($ret === false) {
                $this->errorMessage = sprintf("Error storing registration information to local XML file.");
                return false;
            }
        }
        
        return $response;
    }
    
    function sendContextConfiguration($contextName)
    {
        $regInfo = $this->getRegistrationInfo();
        if ($regInfo === false) {
            return false;
        }
        
        if ($regInfo['status'] != 'registered') {
            $this->errorMessage = sprintf("Installation '%s/%s' is not registered!", $regInfo['mid'], $regInfo['ctrlid']);
            return false;
        }
        
        $context = $this->getContext($contextName);
        if ($context === false) {
            return false;
        }
        
        $ret = $context->sendConfiguration();
        if ($ret === false) {
            $this->errorMessage = sprintf("Could not send context configuration for context '%s': %s", $contextName, $context->errorMessage);
            return false;
        }
        
        return true;
    }
    
    static function anonymizeUrl($url)
    {
        require_once 'class/Class.WWWUserAgent.php';
        return WWW\UserAgent::anonymizeUrl($url);
    }
    
    static function strAnonymizeUrl($url, $str)
    {
        return str_replace($url, self::anonymizeUrl($url) , $str);
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
    /**
     * Delete directory content
     *
     * @param string $dir The directory to clean
     * @param bool $recursive true to also delete files/dirs in sub-dirs,
     *                        or false (default) to only clean files from
     *                        the main directory.
     * @return bool true on success, false on failure
     */
    private function deleteDirContent($dir, $recursive = false)
    {
        if (($dh = opendir($dir)) === false) {
            $this->errorMessage = sprintf("Error opening directory '%s'.", $dir);
            return false;
        }
        while (($entry = readdir($dh)) !== false) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            $entry = $dir . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($entry) && $recursive) {
                if ($this->deleteDirContent($entry, $recursive) === false) {
                    closedir($dh);
                    return false;
                }
                if (rmdir($entry) === false) {
                    $this->errorMessage = sprintf("Error deleting directory '%s'.", $entry);
                    closedir($dh);
                    return false;
                }
            } else {
                if (unlink($entry) === false) {
                    $this->errorMessage = sprintf("Error deleting file '%s'.", $entry);
                    closedir($dh);
                    return false;
                }
            }
        }
        closedir($dh);
        return true;
    }
    
    public function validateDOMDocument(DOMDocument $dom, $urn)
    {
        require_once ('class/Class.XMLSchemaCatalogValidator.php');
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
        require_once 'class/Class.DOMDocumentCacheFactory.php';
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
        require_once 'class/Class.DOMDocumentCacheFactory.php';
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
        require_once 'class/Class.Logger.php';
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
}

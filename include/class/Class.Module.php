<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Module Class
 * @author Anakeen
 */

class Module
{
    const SCHEMA_NAMESPACE = 'https://platform.anakeen.com/4/schemas/app/1.0';
    /**
     * xml attributes
     */
    public $name;
    public $updateName;
    public $vendor;
    public $version;
    public $release;
    public $versionrelease;
    public $license;
    public $basecomponent;
    public $src;
    
    public $changelog = "";
    public $availablechangelog = "";
    
    public $availableversion;
    public $availableversionrelease;
    
    public $description;
    
    public $infopath = false;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var Repository
     */
    public $repository;
    
    public $status;
    public $errorstatus;
    
    public $tmpfile;
    
    public $requires;
    
    public $replaces;
    
    public $xmlNode;
    
    public $needphase = '';
    /**
     * @var boolean true if module is installed, false if module is available
     */
    private $isInstalled;
    /**
     * @var boolean true if module is installed and has available update
     */
    public $canUpdate = false;
    /**
     * @var boolean true if module has parameters
     */
    public $hasParameter = false;
    /**
     * @var boolean true if module has displayable parameters (e.g. not hidden)
     */
    public $hasDisplayableParameter = false;
    /**
     * @var string Name of module wich replace this one
     */
    public $replacedBy = "";
    /**
     * @var string last error message
     */
    public $errorMessage = '';
    /**
     * @var string last warning message
     */
    public $warningMessage = "";
    
    public function __construct($context, $repository = null, $xmlNode = null, $isInstalled = false)
    {
        
        $this->context = $context;
        $this->repository = $repository;
        
        if ($xmlNode) {
            $this->parseXmlNode($xmlNode);
        }
        
        $this->isInstalled = $isInstalled;
        
        $parameterList = $this->getParameterList();
        
        $this->hasParameter = is_array($parameterList) && (count($parameterList) != 0);
        
        $this->hasDisplayableParameter = $this->fGetDisplayableParameterList();
    }
    
    public function __set($property, $value)
    {
        $this->$property = $value;
    }
    
    public function __get($property)
    {
        return isset($this->$property) ? $this->$property : null;
    }
    
    public function &getContext()
    {
        return $this->context;
    }
    /**
     * @param DOMElement $xmlNode
     * @return DOMElement
     */
    public function parseXmlNode($xmlNode)
    {
        $this->xmlNode = $xmlNode;
        // Load xmlNode attributes="value"
        foreach (array(
            'name',
            'vendor',
            'version',
            'release',
            'author',
            'license',
            'basecomponent',
            'infopath',
            'src',
            'tmpfile',
            'status',
            'errorstatus',
            'changelog'
        ) as $attrName) {
            $this->$attrName = $xmlNode->getAttribute($attrName);
        }

        if ($this->release) {
            $this->version  .= '-' . $this->release;
            $this->release='';
        }
        $this->versionrelease = $this->version ;
        // Load xmlNode <description> elements
        $descriptionNodeList = $xmlNode->getElementsByTagName('description');
        if ($descriptionNodeList->length > 0) {
            $this->description = $descriptionNodeList->item(0)->nodeValue;
        }
        // Load xmlNode <requires> elements
        $this->requires = array();
        $requiresNodeList = $xmlNode->getElementsByTagName('requires');
        if ($requiresNodeList->length > 0) {
            /**
             * @var DOMElement $requiresNode
             */
            $requiresNode = $requiresNodeList->item(0);
            $installerNodeList = $requiresNode->getElementsByTagName('installer');
            if ($installerNodeList->length > 0) {
                /**
                 * @var DOMElement $installerNode
                 */
                $installerNode = $installerNodeList->item(0);
                $this->requires['installer'] = array(
                    'version' => $installerNode->getAttribute('version') ,
                    'comp' => $installerNode->getAttribute('comp')
                );
            }
            $moduleNodeList = $requiresNode->getElementsByTagName('module');
            foreach ($moduleNodeList as $moduleNode) {
                /**
                 * @var DOMElement $moduleNode
                 */
                $this->requires['modules'][] = array(
                    'name' => $moduleNode->getAttribute('name') ,
                    'version' => $moduleNode->getAttribute('version') ,
                    'comp' => $moduleNode->getAttribute('comp')
                );
            }
        }
        // Load xmlNode <replaces> elements
        $this->replaces = array();
        $replacesNodeList = $xmlNode->getElementsByTagName('replaces');
        if ($replacesNodeList->length > 0) {
            /**
             * @var DOMElement $replacesNode
             */
            $replacesNode = $replacesNodeList->item(0);
            $moduleNodeList = $replacesNode->getElementsByTagName('module');
            foreach ($moduleNodeList as $moduleNode) {
                array_push($this->replaces, array(
                    'name' => $moduleNode->getAttribute('name')
                ));
            }
        }
        return $xmlNode;
    }
    /**
     * @param DOMElement $node
     * @return bool|string
     */
    private function xt_innerXML(&$node)
    {
        if (!$node) {
            return false;
        }
        $document = $node->ownerDocument;
        $nodeAsString = $document->saveXML($node);
        preg_match('!\<.*?\>(.*)\</.*?\>!s', $nodeAsString, $match);
        return isset($match[1]) ? $match[1] : '';
    }
    /**
     * Check dependency with other Modules in repositories
     * Use getErrorMessage() to retrieve error
     * @return array of object Module or false if dependency can not be satisfied
     */
    public function checkDependency()
    {
    }
    /**
     * Set error status
     * @param string $newErrorStatus new error status of module
     * @return boolean method success
     */
    public function setErrorStatus($newErrorStatus)
    {
        require_once ('class/Class.WIFF.php');
        
        $wiff = WIFF::getInstance();
        
        $xml = $wiff->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }
        $xpath = new DOMXPath($xml);
        
        $modules = $xpath->query("/contexts/context[@name = '" . $this->context->name . "']/modules/module[@name = '" . $this->name . "']");
        /**
         * @var DOMElement $moduleNode
         */
        $moduleNode = $modules->item(0);
        $moduleNode->setAttribute('errorstatus', $newErrorStatus);
        $ret = $wiff->commitDOMDocument($xml);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error saving 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }
        
        return true;
    }
    /**
     * Download archive in temporary folder
     * @param string $status
     * @return string|bool temp filename of downloaded file, or false in case of error
     */
    public function download($status = '')
    {
        require_once ('class/Class.WIFF.php');
        
        $wiff = WIFF::getInstance();
        
        if ($this->repository === null) {
            $this->errorMessage = sprintf("Can't call '%s' method with null '%s'.", __FUNCTION__, 'repository');
            return false;
        }
        
        $modUrl = $this->repository->getUrl() . '/' . $this->src;
        $this->tmpfile = $wiff->downloadUrl($modUrl);
        if ($this->tmpfile === false) {
            $this->errorMessage = sprintf("Could not download '%s': %s", WIFF::anonymizeUrl($modUrl) , $wiff->errorMessage);
            return false;
        }
        // Register downloaded module in context xml
        $infoXML = $this->getValidDOMDocumentInfoXml();
        if ($infoXML === false) {
            return false;
        }
        
        $module = $infoXML->documentElement;

        $contextsXML = $wiff->loadContextsDOMDocument();
        if ($contextsXML === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }
        $contextsXPath = new DOMXPath($contextsXML);
        /**
         * @var DOMElement $module
         */
        $module = $contextsXML->importNode($module, true); // Import module to contexts xml document
        $module->setAttribute('status', 'downloaded');
        $module->setAttribute('tmpfile', $this->tmpfile);
        // Get <modules> node
        $modulesNodeList = $contextsXPath->query("/contexts/context[@name = '" . $this->context->name . "']/modules");
        if ($modulesNodeList->length <= 0) {
            $this->errorMessage = sprintf("Found no <modules> for context '%s' in '%s'.", $this->context->name, $wiff->contexts_filepath);
            return false;
        }
        $modulesNode = $modulesNodeList->item(0);
        // Look for an existing <module> node
        if ($status == 'downloaded') {
            $query = sprintf("/contexts/context[@name='%s']/modules/module[@name='%s' and @status='downloaded']", $this->context->name, $this->name);
        } else {
            $query = sprintf("/contexts/context[@name='%s']/modules/module[@name='%s']", $this->context->name, $this->name);
        }
        $existingModuleNodeList = $contextsXPath->query($query);
        if ($existingModuleNodeList->length <= 0) {
            // No corresponding module was found, so just append the current module
            # error_log("Creating a new <module> node.");
            $modulesNode->appendChild($module);
        } else {
            // A corresponding module was found, so replace it
            # error_log("Replacing existing <module> node.");
            if ($existingModuleNodeList->length > 1) {
                $this->errorMessage = sprintf("Found more than one <module> with name='%s' in '%s'.", $this->name, $wiff->contexts_filepath);
                return false;
            }
            $existingModuleNode = $existingModuleNodeList->item(0);
            $modulesNode->replaceChild($module, $existingModuleNode);
        }
        
        $ret = $wiff->commitDOMDocument($contextsXML);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error saving 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }
        
        return $module;
    }
    /**
     * Get manifest from temporary downloaded module archive
     * @return string of the index of the content of the module content
     */
    public function getManifest()
    {
        if (!is_file($this->tmpfile)) {
            $this->errorMessage = sprintf("Temporary file of downloaded module does not exists.");
            return false;
        }
        
        $cmd = 'tar zxOf ' . escapeshellarg($this->tmpfile) . ' content.tar.gz | tar ztvf -';
        
        $manifest = shell_exec($cmd);
        
        return $manifest;
    }
    /**
     * Get the content of the `info.xml' file from temporary downloaded
     * module archive
     * @return string of the content of `info.xml'
     */
    public function getInfoXml()
    {
        if (!is_file($this->tmpfile)) {
            $this->errorMessage = sprintf("Temporary file of downloaded module does not exists.");
            return false;
        }
        
        $cmd = 'tar zxOf ' . escapeshellarg($this->tmpfile) . ' info.xml';
        
        $infoxml = shell_exec($cmd);
        if ($infoxml === null) {
            $this->errorMessage = sprintf("Empty or missing info.xml.");
            return false;
        }

        return $infoxml;
    }

    public function getValidDOMDocumentInfoXml()
    {
        require_once('class/Class.WIFF.php');
        require_once('class/Class.String.php');
        require_once('class/Class.XMLUtils.php');

        $infoxml = $this->getInfoXml();
        if ($infoxml === false) {
            return false;
        }

        $dom = new DOMDocument();
        if ($dom->loadXML($infoxml, LIBXML_NSCLEAN) === false) {
            $this->errorMessage = sprintf("Error loading info.xml: %s", XMLUtils::getLastXMLError());
            return false;
        }

        $wiff = WIFF::getInstance();
        $moduleName = $dom->documentElement->getAttribute('name');
        if (!XMLUtils::DOMDocumentHaveNamespace($dom, self::SCHEMA_NAMESPACE)) {
            if (!XMLUtils::isBasicModuleDOMDocument($dom, $err)) {
                /**
                 * Return a hard error if the XML lacks a basic <module name="xxx"/> root node
                 */
                $this->errorMessage = sprintf("Module %s : %s",$moduleName, $err);
                return false;
            }
            $wiff->log(LOG_INFO, (string)new \String\HTML(new \String\sprintf("<p style=\"margin: 0.5em 0 0.5em 0\">Module '%s' uses a legacy 'info.xml' without namespace declaration.</p><p style=\"margin: 0.5em 0 0.5em 0\">Support for legacy 'info.xml' format is deprecated and will be removed in future version of anakeen-control.</p><p style=\"margin: 0.5em 0 0.5em 0\">You should update this 'info.xml' definition with the correct XML Schema Definition as soon as possible.</p></div>", new \String\HTML($moduleName), new \String\HTML(self::SCHEMA_NAMESPACE))));
            return $dom;
        } else {
            /*
             * This looks like a new info.xml with the webinst module XMLNS,
             * so we can validate it and return a hard error on validation failure.
             */
            $validationError = $wiff->validateDOMDocument($dom, self::SCHEMA_NAMESPACE);
            if ($validationError != '') {
                print_r($dom->saveXML());
                $this->errorMessage = sprintf("Module '%s' did not passed XML validation!", $moduleName);
                return false;
            }
        }
        /*
         * As the contexts.xml is not fully namespaced, we must remove the namespace from the info.xml
         * otherwise the XPath queries won't find it (as they are namespaceless).
         *
         * This is actually a workaround to not break the current contexts.xml
         * which happens to be namespaceless.
         *
         * If we ever expand the schema validation and namespace use to every XML files, we'll have to :
         * - Migrate contexts.xml's content with xmlns="xxx"
         * - Adapt all the XPaths' queries to use the namespace
         * - etc.
         */
        try {
            $dom = XMLUtils::removeNamespaceFromDOMDocument($dom, self::SCHEMA_NAMESPACE);
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
            $dom = false;
        }
        if ($dom === false) {
            $this->errorMessage = sprintf("Error removing namespace from info.xml: %s", $this->errorMessage);
            return false;
        }
        return $dom;
    }
    /**
     * Get the content of the `LICENSE' file from temporary downloaded
     * module archive.
     * @return string of the content of `LICENSE'
     */
    public function getLicenseText()
    {
        if (!is_file($this->tmpfile)) {
            $this->errorMessage = sprintf("Temporary file of downloaded module does not exists.");
            return false;
        }
        
        $cmd = sprintf('tar zxOf %s LICENSE', escapeshellarg($this->tmpfile));
        
        $license = shell_exec($cmd);
        
        return $license;
    }
    
    public function loadInfoXml()
    {
        $xml = $this->getValidDOMDocumentInfoXml();
        if ($xml === false) {
            return false;
        }
        
        $xmlNode = $xml->documentElement;
        if ($xmlNode === null) {
            $this->errorMessage = "documentElement is null.";
            return false;
        }

        return $this->parseXmlNode($xmlNode);
    }
    /**
     * Remove temp file used by download/unpack/install process
     * @return bool false in case of error
     */
    public function cleanupDownload()
    {
        if (is_file($this->tmpfile)) {
            unlink($this->tmpfile);
            return $this->tmpfile;
        }
        return false;
    }
    
    public function getTmpManifestEntriesForModule()
    {
        $manifest = $this->getManifest();
        $manifestLines = preg_split("/\n/", $manifest);
        $manifestEntries = array();
        
        foreach ($manifestLines as $line) {
            $minfo = array();
            if (!preg_match("|^(?P<type>.)(?P<mode>.........)\s+(?P<uid>.*?)/(?P<gid>.*?)\s+(?P<size>\d+)\s+(?P<date>\d\d\d\d-\d\d-\d\d\s+\d\d:\d\d(?::\d\d)?)\s+(?P<name>.*?)(?P<link>\s+->\s+.*?)?$|", $line, $minfo)) {
                continue;
            }
            array_push($manifestEntries, $minfo);
        }
        
        return $manifestEntries;
    }
    
    public function checkManifestFiles()
    {
        $moduleManifest = $this->getTmpManifestEntriesForModule();
        if (!empty($moduleManifest)) {
            $moduleInstalledList = $this->context->getInstalledModuleList();
            $modulesManifestFiles = array();
            foreach ($moduleManifest as $manifest) {
                $modulesManifestFiles[] = $manifest["name"];
            }
            foreach ($moduleInstalledList as $module) {
                if ($module->name != $this->name) {
                    /**
                     * @var Module $module
                     */
                    $installedManifest = $this->context->getManifestEntriesForModule($module->name);
                    foreach ($installedManifest as $manifest) {
                        if ($manifest["type"] != "d" && in_array($manifest["name"], $modulesManifestFiles)) {
                            $this->errorMessage = sprintf("File '%s' is already given by module '%s'. If you skip this, this file will be replaced by the new one.", $manifest["name"], $module->name);
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }
    /**
     * Unpack archive in specified destination directory
     * @param string $destDir directory path to unpack the archive in (e.g. context root dir)
     * @return string containing the given destination dir pr false in case of error
     */
    public function unpack($destDir = '')
    {
        include_once ('lib/Lib.System.php');
        
        if (!is_file($this->tmpfile)) {
            $this->errorMessage = sprintf("Temporary file of downloaded module does not exists.");
            return false;
        }
        // Store BOM/manifest
        $ret = $this->context->storeManifestForModule($this);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error getting manifest for '%s': %s", $this->name, $this->context->errorMessage);
            return false;
        }
        // Unpack archive
        $cmd = '(tar -zxOf ' . escapeshellarg($this->tmpfile) . ' content.tar.gz | tar ' . (($destDir != '') ? '-C ' . escapeshellarg($destDir) : '') . ' -zxf -) 2>&1';
        
        $ret = null;
        exec($cmd, $output, $ret);
        if ($ret != 0) {
            $this->errorMessage = sprintf("Error executing command [%s]: %s", $cmd, join("\n", $output));
            return false;
        }
        
        return $destDir;
    }
    /**
     * Delete module folder
     * @return boolean success
     */
    public function uninstall()
    {
        $this->errorMessage = sprintf("Method not yet implemented.");
        return false;
    }
    /**
     * Get Module parameter list
     * @return array of object Parameter or false in case of error
     */
    public function getParameterList()
    {
        require_once ('class/Class.WIFF.php');
        require_once ('class/Class.Parameter.php');
        
        $plist = array();
        
        if (!isset($this->context->name) || $this->context->name == null) {
            $this->errorMessage = sprintf("Can't call '%s' method with null '%s'.", __FUNCTION__, 'context');
            return false;
        }
        
        $wiff = WIFF::getInstance();
        
        $xml = $wiff->loadContextsDOMDocument();

        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }
        
        $contextsXpath = new DOMXPath($xml);
        $params = $contextsXpath->query("/contexts/context[@name='" . $this->context->name . "']/modules/module[@name='" . $this->name . "' and @status='" . $this->status . "']/parameters/param");
        if ($params->length <= 0) {
            return array();
        }
        
        $pSeen = array();
        foreach ($params as $param) {
            /**
             * @var DOMElement $param
             */
            $paramName = $param->getAttribute('name');
            if (array_key_exists($paramName, $pSeen)) {
                continue;
            }
            $pSeen[$paramName] = isset($pSeen[$paramName]) ? $pSeen[$paramName] + 1 : 1;
            
            $p = new Parameter();
            foreach (array(
                'name',
                'label',
                'default',
                'type',
                'needed',
                'values',
                'volatile',
                'oninstall',
                'onedit',
                'onupgrade'
            ) as $attr) {
                $p->$attr = $param->getAttribute($attr);
                // Replace keywords
                // @CONTEXT_NAME
                $p->$attr = $this->context->expandParamsValues($p->$attr);
            }

            $storedParamValue = $contextsXpath->query("/contexts/context[@name='" . $this->context->name . "']/parameters-value/param[@name='" . $p->name . "' and @modulename='" . $this->name . "']");
            if ($storedParamValue->length <= 0) {
                $p->value = $this->getParameterValueFromReplacedModules($contextsXpath, $p->name);
            } else {
                /**
                 * @var DOMElement $storedParamValueNode
                 */
                $storedParamValueNode = $storedParamValue->item(0);
                if (($p->volatile == 'yes' || $p->volatile == 'Y') && ($storedParamValueNode->getAttribute('volatile') != 'yes' && $storedParamValueNode->getAttribute('volatile') != 'Y')) {
                    /*
                     * Handle the case where a parameter is defined as volatile in the module and the stored value
                     * is not declared as volatile: the definition from the module supersede the stored one.
                     * So, we take the default value from the modules parameter definition instead of the stored one.
                    */
                    $p->value = $p->default;
                } else {
                    $p->value = $storedParamValueNode->getAttribute('value');
                }
            }
            
            $plist[] = $p;
        }
        
        return $plist;
    }
    
    function fGetDisplayableParameterList()
    {
        $plist = $this->getParameterList();
        if ($plist === false) {
            return false;
        }
        foreach ($plist as $p) {
            if ($p->onedit == '' || $p->onedit == 'W' || $p->onedit == 'R') return true;
        }
        return false;
    }
    /**
     * Try to get parameter value from replaced modules
     * @param DOMXPath $contextsXpath DOMXPath object
     * @param string $paramName
     * @return string the parameters value
     */
    public function getParameterValueFromReplacedModules(&$contextsXpath, $paramName)
    {
        $value = '';
        foreach ($this->replaces as $replaced) {
            $storedParamValue = $contextsXpath->query("/contexts/context[@name='" . $this->context->name . "']/parameters-value/param[@name='" . $paramName . "' and @modulename='" . $replaced['name'] . "']");
            if ($storedParamValue->length <= 0) {
                continue;
            }
            /**
             * @var DOMElement $storedParamValueNode
             */
            $storedParamValueNode = $storedParamValue->item(0);
            $value = $storedParamValueNode->getAttribute('value');
            break;
        }
        
        return $value;
    }
    /**
     * Get Module parameter by name
     * @return object Parameter or false if parameter not found
     * @param string $name
     */
    public function getParameter($name)
    {
        $plist = $this->getParameterList();
        foreach ($plist as $p) {
            if ($p->name == $name) {
                return $p;
            }
        }
        $this->errorMessage = sprintf("Parameter '%s' not found.", $name);
        return false;
    }
    /**
     * Store Module parameter
     * @return Parameter|bool the given object Parameter or false in case of error
     * @param Parameter $parameter
     */
    public function storeParameter(Parameter $parameter)
    {
        require_once ('class/Class.WIFF.php');
        
        if ($this->context->name == null) {
            $this->errorMessage = sprintf("Can't call '%s' method with null '%s'.", __FUNCTION__, 'context');
            return false;
        }
        
        $wiff = WIFF::getInstance();
        
        $xml = $wiff->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }
        
        $contextsXpath = new DOMXPath($xml);
        $contextNodeList = $contextsXpath->query(sprintf("/contexts/context[@name='%s']", $this->context->name));
        if ($contextNodeList->length <= 0) {
            $this->errorMessage = sprintf("Could not find the module node.");
            return false;
        }
        $contextNode = $contextNodeList->item(0);
        
        $parametersValueList = $contextsXpath->query(sprintf("/contexts/context[@name='%s']/parameters-value", $this->context->name));
        if ($parametersValueList->length <= 0) {
            $parametersValueNode = $xml->createElement('parameters-value');
            if ($parametersValueNode === false) {
                $this->errorMessage = sprintf("Could not create parameters-value element.");
                return false;
            }
            $contextNode->appendChild($parametersValueNode);
        } else {
            $parametersValueNode = $parametersValueList->item(0);
        }
        
        $paramList = $contextsXpath->query(sprintf("/contexts/context[@name='%s']/parameters-value/param[@modulename='%s' and @name='%s']", $this->context->name, $this->name, $parameter->name));
        if ($paramList->length <= 0) {
            $param = $xml->createElement('param');
            if ($param === false) {
                $this->errorMessage = sprintf("Could not create param element.");
                return false;
            }
            $parametersValueNode->appendChild($param);
        } else {
            $param = $paramList->item(0);
        }
        
        $param->setAttribute('name', $parameter->name);
        $param->setAttribute('modulename', $this->name);
        $param->setAttribute('value', $parameter->value);
        if ($parameter->volatile == 'yes' || $parameter->volatile == 'Y') {
            $param->setAttribute('volatile', $parameter->volatile);
        }
        
        $ret = $wiff->commitDOMDocument($xml);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error saving XML to '%s': %s", $wiff->contexts_filepath, $wiff->errorMessage);
            return false;
        }
        
        return $parameter;
    }
    /**
     * Get Phase list
     * @return array of object Phase
     * @param string $operation operation string code 'install|upgrade|uninstall|parameter'
     */
    public function getPhaseList($operation)
    {
        switch ($operation) {
            case 'install':
                return array(
                    'pre-install',
                    'check-files',
                    'unpack',
                    'post-install',
                    'purge-unreferenced-parameters-value'
                );
                break;

            case 'upgrade':
                return array(
                    'pre-upgrade',
                    'check-files',
                    'clean-unpack',
                    'post-upgrade',
                    'purge-unreferenced-parameters-value'
                );
                break;

            case 'uninstall':
                return array(
                    'pre-remove',
                    'check-files',
                    'remove',
                    'post-remove'
                );
                break;

            case 'parameter':
                return array(
                    'param',
                    'post-param'
                );
                break;

            case 'replaced':
                return array(
                    'unregister-module'
                );
                break;

            case 'archive':
                return array(
                    'pre-archive',
                    'post-archive'
                );
                break;

            case 'restore':
                return array(
                    'post-restore'
                );
                break;

            case 'delete':
                return array(
                    'pre-delete'
                );
                break;

            default:
        }
        return array();
}
/**
 * Get phase by name
 * @return Phase
 * @param string $name Phase name and XML tag
 */
public function getPhase($name)
{
    require_once ('class/Class.Phase.php');
    
    return new Phase($name, $this->xmlNode, $this);
}
/**
 * Get last error message
 * @return string error message
 */
public function getErrorMessage()
{
    return $this->errorMessage;
}
/**
 * Return required installer version for this module
 * @return array( 'version' => $version, 'comp' => $comp ), or false
 *         in case of error
 */
public function getRequiredInstaller()
{
    if (!array_key_exists('installer', $this->requires)) {
        return false;
    }
    $installer = $this->requires['installer'];
    return $installer;
}
/**
 * Return required modules name/version/etc. for this module
 * @return array of array( 'name' => $name, 'version' => $version, [...] )
 *         or false in case of error
 */
public function getRequiredModules()
{
    if (!array_key_exists('modules', $this->requires)) {
        return array();
    }
    $modules = $this->requires['modules'];
    return $modules;
}
/**
 * Return modules replaced by this module
 * @return array( array('name' => $name), [...] )
 */
public function getReplacesModules()
{
    return $this->replaces;
}
/**
 * Set the status of a module
 * @param string $status the modules status
 * @param string $errorstatus the modules error status (default null)
 * @return bool
 */
public function setStatus($status, $errorstatus = null)
{
    require_once ('class/Class.WIFF.php');
    
    $wiff = WIFF::getInstance();
    
    $xml = $wiff->loadContextsDOMDocument();
    if ($xml === false) {
        $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
        return false;
    }
    
    $xpath = new DOMXpath($xml);
    $moduleNodeList = $xpath->query(sprintf("/contexts/context[@name='%s']/modules/module[@name='%s']", $this->context->name, $this->name));
    if ($moduleNodeList->length <= 0) {
        $this->errorMessage = sprintf("Could not find module '%s' in context '%s'!", $this->name, $this->context->name);
        return false;
    }
    /**
     * @var DOMElement $moduleNode
     */
    $moduleNode = $moduleNodeList->item(0);
    
    $this->status = $status;
    $moduleNode->setAttribute('status', $status);
    if ($errorstatus !== null) {
        $this->errorstatus = $errorstatus;
        $moduleNode->setAttribute('errorstatus', $errorstatus);
    }
    
    $ret = $wiff->commitDOMDocument($xml);
    if ($ret === false) {
        $this->errorMessage = sprintf("Error saving XML to '%s': %s", $wiff->contexts_filepath, $wiff->errorMessage);
        return false;
    }
    
    return $status;
}
/**
 * Delete the tmpfile associated with a module
 * @return bool false on error or string containing the temporary file pathname
 */
public function deleteTmpFile()
{
    require_once ('class/Class.WIFF.php');
    
    $wiff = WIFF::getInstance();
    
    $xml = $wiff->loadContextsDOMDocument();
    if ($xml === false) {
        $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
        return false;
    }
    
    $xpath = new DOMXpath($xml);
    $moduleNodeList = $xpath->query(sprintf("/contexts/context[@name='%s']/modules/module[@name='%s']", $this->context->name, $this->name));
    if ($moduleNodeList->length <= 0) {
        $this->errorMessage = sprintf("Could not find module '%s' in context '%s'!", $this->name, $this->context->name);
        return false;
    }
    /**
     * @var DOMElement $moduleNode
     */
    $moduleNode = $moduleNodeList->item(0);
    
    $tmpfile = $moduleNode->getAttribute('tmpfile');
    unlink($tmpfile);
    $moduleNode->removeAttribute('tmpfile');
    $this->tmpfile = false;
    
    $ret = $wiff->commitDOMDocument($xml);
    if ($ret === false) {
        $this->errorMessage = sprintf("Error saving XML to '%s': %s", $wiff->contexts_filepath, $wiff->errorMessage);
        return false;
    }
    
    return $tmpfile;
}

public function getLicenseAgreement()
{
    require_once ('class/Class.WIFF.php');
    
    $wiff = WIFF::getInstance();
    
    return $wiff->getLicenseAgreement($this->context->name, $this->name, $this->license);
}

public function storeLicenseAgreement($agree)
{
    require_once ('class/Class.WIFF.php');
    
    $wiff = WIFF::getInstance();
    
    return $wiff->storeLicenseAgreement($this->context->name, $this->name, $this->license, $agree);
}
}

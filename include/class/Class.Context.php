<?php
/*
 * Context Class
 * @author Anakeen
*/

require_once(__DIR__ . "/../../vendor/autoload.php");
/**
 * Util functions using php DOMDocument
 *
 * @param DOMElement $node
 */
function deleteNode(DOMElement $node)
{
    deleteChildren($node);
    /**
     * @var DOMElement $parent
     */
    $parent = $node->parentNode;
    $parent->removeChild($node);
}

/**
 * Util functions using php DOMDocument
 *
 * @param DOMElement $node
 */
function deleteChildren($node)
{
    while (isset($node->firstChild)) {
        deleteChildren($node->firstChild);
        $node->removeChild($node->firstChild);
    }
}


class ContextProperties extends WiffCommon
{
    /**
     * @var string Context's name
     */
    public $name;
    /**
     * @var string Context's description
     */
    public $description;
    /**
     * @var string Context's install path
     */
    public $root;
    /**
     * @var string Context's url
     */
    public $url;
    /**
     * @var Context[] array Context's repository
     */
    public $repo;
    /**
     * @var string Is context registered
     */
    public $register;
    /**
     * @var string Context's error message
     */
    public $errorMessage = null;
    /**
     * @var string Context's warning message
     */
    public $warningMessage = null;
    /**
     * @var array
     */
    protected $props
        = array(
            'url',
            'description'
        );
    /**
     * @var bool Is context being restored?
     */
    public $inProgress = false;
}

class Context extends ContextProperties
{
    public function __construct($name, $desc, $root, array $repo, $url, $register)
    {
        $this->name = $name;
        $this->description = $desc;
        $this->root = $root;
        $this->url = $url;
        $this->repo = $repo;
        $this->register = $register;
        foreach ($this->repo as $repository) {
            /**
             * @var Repository $repository
             */
            $repository->setContext($this);
        }
    }

    /**
     * Check if context repositories are valid.
     * Populate repositories object with appropriate attributes.
     *
     * @return void
     */
    public function isValid()
    {
        foreach ($this->repo as $repository) {
            /**
             * @var Repository $repository
             */
            $repository->needAuth();
        }
    }

    /**
     * Check if context's root is wirtable
     *
     * @return bool
     */
    public function isWritable()
    {
        if (!is_writable($this->root)) {
            return false;
        }
        return true;
    }

    /**
     * Import archive in Context
     *
     * @param string $archive the archive pathname
     * @param string $status  the status to which the imported archive will be set to (default = 'downloaded')
     *
     * @return bool|string boolean false on error or the archive pathname
     *
     */
    public function importArchive($archive, $status = 'downloaded')
    {
        require_once('class/Class.WIFF.php');
        require_once('class/Class.Module.php');

        $wiff = WIFF::getInstance();
        if ($wiff === false) {
            $this->errorMessage = sprintf("Could not get context.");
            return false;
        }

        $module = new Module($this);
        // Set package file to tmpfile archive
        $module->tmpfile = $archive;
        if ($module->tmpfile === false) {
            $this->errorMessage = sprintf("No archive provided.");
            return false;
        }
        // Load module attributes from info.xml
        $moduleXML = $module->loadInfoXml();
        if ($moduleXML === false) {
            $this->errorMessage = sprintf("Could not load info xml: '%s'.", $module->errorMessage);
            return false;
        }

        $contextsXML = $wiff->loadContextsDOMDocument();
        if ($contextsXML === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }

        $importedXML = $contextsXML->importNode($moduleXML, true); // Import module to contexts xml document
        if ($importedXML === false) {
            $this->errorMessage = sprintf("Could not import module node.");
            return false;
        }
        /**
         * @var DOMElement $moduleXML
         */
        $moduleXML = $importedXML;

        $moduleXML->setAttribute('tmpfile', $archive);
        if ($status == '') {
            $moduleXML->setAttribute('status', 'downloaded');
        } else {
            $moduleXML->setAttribute('status', $status);
        }
        // Get <modules> node
        $contextsXPath = new DOMXPath($contextsXML);
        $modulesNodeList = $contextsXPath->query("/contexts/context[@name = '" . $this->name . "']/modules");
        if ($modulesNodeList->length <= 0) {
            $this->errorMessage = sprintf("Found no modules node for context '%s'.", $this->name);
            return false;
        }
        $modulesNode = $modulesNodeList->item(0);
        // Look for an existing <module> node
        if ($status == 'downloaded') {
            $query = sprintf("/contexts/context[@name='%s']/modules/module[@name='%s' and @status='downloaded']", $this->name, $module->name);
        } else {
            if ($status == 'installed') {
                $query = sprintf("/contexts/context[@name='%s']/modules/module[@name='%s' and @status='installed']", $this->name, $module->name);
            } else {
                $query = sprintf("/contexts/context[@name='%s']/modules/module[@name='%s']", $this->name, $module->name);
            }
        }
        # $existingModuleNodeList = $contextsXPath->query("/contexts/context[@name='".$this->name."']/modules/module[@name='".$module->name."']");
        $existingModuleNodeList = $contextsXPath->query($query);
        if ($existingModuleNodeList->length <= 0) {
            // No corresponding module was found, so just append the current module
            # error_log("Creating a new <module> node.");
            $modulesNode->appendChild($moduleXML);
        } else {
            // A corresponding module was found, so replace it
            # error_log("Replacing existing <module> node.");
            if ($existingModuleNodeList->length > 1) {
                $this->errorMessage = sprintf("Found more than one <module> with name='%s' in '%s'.", $module->name, $wiff->contexts_filepath);
                return false;
            }
            $existingModuleNode = $existingModuleNodeList->item(0);
            $modulesNode->replaceChild($moduleXML, $existingModuleNode);
        }

        $ret = $wiff->commitDOMDocument($contextsXML);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error saving contexts.xml '%s': %s", $wiff->contexts_filepath, $wiff->errorMessage);
            return false;
        }

        if (!empty($module->warningMessage)) {
            $this->warningMessage = $module->warningMessage;
        }
        return $module->tmpfile;
    }

    /**
     * Activate repository for Context
     *
     * @param string $name repository name
     *
     * @return boolean success
     *
     * @internal param string $url repository url
     */
    public function activateRepo($name)
    {
        require_once('class/Class.WIFF.php');
        require_once('class/Class.Repository.php');

        $wiff = WIFF::getInstance();

        $paramsXml = $wiff->loadParamsDOMDocument();
        if ($paramsXml === false) {
            $this->errorMessage = sprintf("Error loading 'params.xml'.");
            return false;
        }

        $paramsXPath = new DOMXPath($paramsXml);

        $contextsXml = $wiff->loadContextsDOMDocument();
        if ($contextsXml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }

        $contextsXPath = new DOMXPath($contextsXml);
        // Get this context
        $contextList = $contextsXPath->query("/contexts/context[@name='" . $this->name . "']");
        if ($contextList->length != 1) {
            // If more than one context with name
            $this->errorMessage = "Duplicate contexts with same name";
            return false;
        }
        /**
         * @var DOMElement $contextNode
         */
        $contextNode = $contextList->item(0);
        // Add a repositories list if context does not have one
        $contextRepo = $contextsXPath->query("/contexts/context[@name='" . $this->name . "']/repositories");
        if ($contextRepo->length != 1) {
            // if repositories node does not exist, create one
            $contextNode->appendChild($contextsXml->createElement('repositories'));
        }
        // Check this repository is not already in context
        //$contextRepoList = $contextsXPath->query("/contexts/context[@name='".$this->name."']/repositories/access[@name='".$name."']");
        $contextRepoList = $contextsXPath->query("/contexts/context[@name='" . $this->name . "']/repositories/access[@use='" . $name . "']");
        if ($contextRepoList->length > 0) {
            // If more than zero repository with name
            $this->errorMessage = "Repository already activated.";
            return false;
        }
        // Get repository with this name from WIFF repositories
        $wiffRepoList = $paramsXPath->query("/wiff/repositories/access[@name='" . $name . "']");
        if ($wiffRepoList->length == 0) {
            $this->errorMessage = "No repository with name " . $name . ".";
            return false;
        } else {
            if ($wiffRepoList->length > 1) {
                $this->errorMessage = "Duplicate repository with same name";
                return false;
            }
        }
        // Add repository to this context
        $node = $contextsXml->createElement('access');
        /**
         * @var DOMElement $repository
         */
        $repository = $contextNode->getElementsByTagName('repositories')->item(0)->appendChild($node);

        $repository->setAttribute('use', $name);
        $ret = $wiff->commitDOMDocument($contextsXml);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error writing file '%s': %s", $wiff->contexts_filepath, $wiff->errorMessage);
            return false;
        }
        //Update Context object accordingly
        $this->repo[] = new Repository($repository, $this);

        return true;
    }

    /**
     * Activate repositories declared as 'default'
     *
     * @return boolean success
     */
    public function activateDefaultRepo()
    {
        require_once('class/Class.WIFF.php');

        $wiff = WIFF::getInstance();
        if (($repoList = $wiff->getRepoList(false)) === false) {
            $this->errorMessage = $wiff->errorMessage;
            return false;
        }
        foreach ($repoList as $repo) {
            if ($repo->default !== 'yes') {
                continue;
            }
            if ($this->activateRepo($repo->name) === false) {
                $this->errorMessage = sprintf("Error activating default repository '%s': %s", $repo->name, $this->errorMessage);
                return false;
            }
        }
        return true;
    }

    /**
     * Deactivate repository for Context
     *
     * @param string $name repository name
     *
     * @return boolean success
     *
     */
    public function deactivateRepo($name)
    {
        require_once('class/Class.WIFF.php');

        $wiff = WIFF::getInstance();

        $xml = $wiff->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }

        $xpath = new DOMXPath($xml);
        // Check this repository exists
        $contextRepoList = $xpath->query("/contexts/context[@name='" . $this->name . "']/repositories/access[@use='" . $name . "']");
        if ($contextRepoList->length == 1) {
            $xpath->query("/contexts/context[@name='" . $this->name . "']/repositories")->item(0)->removeChild($contextRepoList->item(0));
            $ret = $wiff->commitDOMDocument($xml);
            if ($ret === false) {
                $this->errorMessage = sprintf("Error writing file '%s': %s", $wiff->errorMessage);
                return false;
            }

            foreach ($this->repo as $repo) {
                if ($repo->name == $name) {
                    unset($this->repo[array_search($repo, $this->repo)]);
                }
            }

            return true;
        } else {
            $this->errorMessage = sprintf("Could not find active repository '%s' in context '%s'.", $name, $this->name);
            return false;
        }
    }

    /**
     * Deactivate all repositories
     *
     * @return bool success
     */
    public function deactivateAllRepo()
    {

        require_once('class/Class.WIFF.php');

        $wiff = WIFF::getInstance();

        $xml = $wiff->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }

        $xpath = new DOMXPath($xml);

        $contextRepo = $xpath->query("/contexts/context[@name='" . $this->name . "']/repositories")->item(0);
        if ($contextRepo && $contextRepo->hasChildNodes()) {
            while ($contextRepo->childNodes->length) {
                $contextRepo->removeChild($contextRepo->firstChild);
            }
        }
        $ret = $wiff->commitDOMDocument($xml);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error writing file '%s': %s", $wiff->contexts_filepath, $wiff->errorMessage);
            return false;
        }

        $this->repo = array();

        return true;
    }

    /**
     * Get Module list
     *
     * @return array of object Module or boolean false
     */
    public function getModuleList()
    {

        $availableModuleList = $this->getAvailableModuleList();
        if ($availableModuleList === false) {
            $this->errorMessage = sprintf("Could not get available module list.");
            return false;
        }

        $installedModuleList = $this->getInstalledModuleList();
        if ($installedModuleList === false) {
            $this->errorMessage = sprintf("Could not get installed module list.");
            return false;
        }

        $moduleList = array_merge($availableModuleList, $installedModuleList); // TODO appropriate merge
        return $moduleList;
    }

    /**
     * Get installed Module list
     *
     * @param boolean $withAvailableVersion returned objects will have last available version from Repository attribute populated
     *
     * @return Module[]
     */
    public function getInstalledModuleList($withAvailableVersion = false)
    {
        require_once('class/Class.WIFF.php');
        require_once('class/Class.Module.php');

        $wiff = WIFF::getInstance();

        $xml = $wiff->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return array();
        }

        $xpath = new DOMXPath($xml);

        $moduleList = array();

        $moduleDom = $xpath->query("/contexts/context[@name='" . $this->name . "']/modules/module");

        foreach ($moduleDom as $module) {
            $mod = new Module($this, null, $module, true);
            if ($mod->status == 'installed') {
                $moduleList[] = $mod;
            }
        }
        //Process for with available version option
        if ($withAvailableVersion) {
            $availableModuleList = $this->getAvailableModuleList();

            foreach ($availableModuleList as $availableModule) {
                foreach ($moduleList as $module) {
                    /**
                     * @var Module $module
                     */
                    if ($availableModule->name == $module->name) {
                        $module->availableversion = $availableModule->version;
                        $module->availableversionrelease = $availableModule->version;
                        $module->availablechangelog = $availableModule->changelog;
                        $cmp = $this->cmpModuleByVersionAsc($module, $availableModule);
                        if ($cmp < 0) {
                            $module->canUpdate = true;
                        }
                    }
                }
            }
        }

        return $moduleList;
    }

    public function getInstalledModuleListWithUpgrade($withAvailableVersion = false)
    {
        require_once('class/Class.WIFF.php');
        require_once('class/Class.Module.php');

        $wiff = WIFF::getInstance();

        $xml = $wiff->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return array();
        }

        $xpath = new DOMXPath($xml);
        $installedModuleList = array();
        $moduleDom = $xpath->query("/contexts/context[@name='" . $this->name . "']/modules/module");
        foreach ($moduleDom as $module) {
            $mod = new Module($this, null, $module, true);
            if ($mod->status == 'installed') {
                $installedModuleList[] = $mod;
            }
        }
        //Process for with available version option
        if ($withAvailableVersion) {
            $availableModuleList = $this->getAvailableModuleList();
            /**
             * @var Module $availableModule
             */
            foreach ($availableModuleList as $availableModule) {
                /**
                 * @var Module $module
                 */
                foreach ($installedModuleList as $module) {
                    if ($availableModule->name == $module->name) {
                        $module->availableversion = $availableModule->version;
                        $module->availableversionrelease = $availableModule->version;
                        $module->availablechangelog = $availableModule->changelog;
                        $cmp = $this->cmpModuleByVersionAsc($module, $availableModule);
                        if ($cmp < 0) {
                            $module->canUpdate = true;
                            $module->updateName = $availableModule->name;
                        }
                    } else {
                        /* Search for available modules that replaces the installed module */
                        $replaceList = $availableModule->getReplacesModules();
                        $replacement = '';
                        foreach ($replaceList as $replace) {
                            if ($module->name == $replace['name']) {
                                $replacement = $replace['name'];
                            }
                        }
                        if ($replacement != '') {
                            $module->canUpdate = true;
                            $module->updateName = $availableModule->name;
                            $module->availableversion = $availableModule->version;
                            $module->availableversionrelease = $availableModule->version . '-' . $availableModule->release;
                            $module->availablechangelog = $availableModule->changelog;
                        }
                    }
                }
            }
        }

        return $installedModuleList;
    }

    /**
     * Get the list of available module Objects in the repositories of the context
     *
     * @param boolean $onlyNotInstalled only return available and not installed modules
     *
     * @return array of module Objects
     */
    public function getAvailableModuleList($onlyNotInstalled = false)
    {
        $moduleList = array();
        foreach ($this->repo as $repository) {
            /**
             * @var Repository $repository
             */
            $repoModuleList = $repository->getModuleList($this);
            if ($repoModuleList === false) {
                $this->errorMessage = sprintf("Error fetching index for repository '%s'.", $repository->name);
                continue;
            }
            $moduleList = $this->mergeModuleList($moduleList, $repoModuleList);
            if ($moduleList === false) {
                $this->errorMessage = sprintf("Error merging module list.");
                return false;
            }
        }
        // Process for only not installed option
        if ($onlyNotInstalled) {
            $installedModuleList = $this->getInstalledModuleListWithUpgrade(true);

            foreach ($installedModuleList as $installedModule) {
                /**
                 * @var Module $installedModule
                 */
                $deleted = 0;
                $replaceList = $installedModule->getReplacesModules();
                $replaceListName = array();
                foreach ($replaceList as $replace) {
                    $replaceListName[] = $replace["name"];
                }
                foreach ($moduleList as $moduleKey => $module) {
                    /**
                     * @var Module $module
                     */
                    if ($installedModule->name == $module->name) {
                        unset($moduleList[$moduleKey - $deleted]);
                        $moduleList = array_values($moduleList);
                        $deleted++;
                    } elseif ($installedModule->updateName != '' && $installedModule->updateName == $module->name) {
                        unset($moduleList[$moduleKey - $deleted]);
                        $moduleList = array_values($moduleList);
                        $deleted++;
                    } elseif (!empty($replaceListName) && in_array($module->name, $replaceListName)) {
                        unset($moduleList[$moduleKey - $deleted]);
                        $moduleList = array_values($moduleList);
                        $deleted++;
                    }
                }
            }
        }

        return $moduleList;
    }

    /**
     * Merge two module lists, sort and keep modules with highest version-release
     *   (kinda sort|uniq).
     *
     * @param first array of module Objects
     * @param second array of module Objects
     *
     * @return array containing unique module Objects
     *
     */
    public function mergeModuleList(&$list1, &$list2)
    {
        $tmp = array_merge($list1, $list2);
        $ret = usort($tmp, array(
            $this,
            'cmpModuleByVersionDesc'
        ));
        if ($ret === false) {
            $this->errorMessage = sprintf("Error sorting module list.");
            return false;
        }

        $seen = array();
        $list = array();
        foreach ($tmp as $module) {
            /**
             * @var Module $module
             */
            if (array_key_exists($module->name, $seen)) {
                continue;
            }
            array_push($list, $module);
            $seen[$module->name] = isset($seen[$module->name]) ? $seen[$module->name] + 1 : 1;
        }

        return $list;
    }


    /**
     * @param string $v1 version
     * @param string $v2 version
     *
     * @return int return 1 if $v1 > $v2, -1 if $v1 < $v2, 0 if $v1 == $v2
     */
    public static function cmpVersionGt($v1, $v2)
    {
        if ($v1 === $v2) {
            return 0;
        }
        return \vierbergenlars\SemVer\version::gt($v1, $v2, true) ? 1 : -1;
    }

    public static function satisfyVersion($semVerExpression, $version)
    {
        $semver = new \vierbergenlars\SemVer\version($version, true);
        return $semver->satisfies(new \vierbergenlars\SemVer\expression($semVerExpression));
    }

    /**
     * Compare two module Objects by ascending version
     *
     * @param Module $module1
     * @param Module $module2
     *
     * @return int < 0 if mod1 is less than mod2, > 0 if mod1 is greater than mod2,
     *
     */
    public static function cmpModuleByVersionAsc(&$module1, &$module2)
    {
        return Context::cmpVersionGt($module1->version, $module2->version);
    }

    /**
     * Compare two module Objects by descending version
     *
     * @param Module $module1
     * @param Module $module2
     *
     * @return int > 0 if mod1 is less than mod2, < 0 if mod1 is greater than mod2,
     *
     */
    public function cmpModuleByVersionDesc(&$module1, &$module2)
    {
        $ret = $this->cmpModuleByVersionAsc($module1, $module2);
        if ($ret > 0) {
            return -1;
        } else {
            if ($ret < 0) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * Get Module by name
     *
     * @param string $name Module name
     * @param bool   $status
     *
     * @return Module or boolean false
     *
     */
    public function getModule($name, $status = false)
    {
        require_once('class/Class.WIFF.php');
        require_once('class/Class.Module.php');

        $wiff = WIFF::getInstance();

        $xml = $wiff->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }

        $xpath = new DOMXPath($xml);
        $query = null;
        if ($status == 'installed') {
            $query = sprintf("/contexts/context[@name='%s']/modules/module[@name='%s' and @status='installed']", $this->name, $name);
        } else {
            if ($status == 'downloaded') {
                $query = sprintf("/contexts/context[@name='%s']/modules/module[@name='%s' and @status='downloaded']", $this->name, $name);
            } else {
                $query = sprintf("/contexts/context[@name='%s']/modules/module[@name='%s']", $this->name, $name);
            }
        }
        $moduleDom = $xpath->query($query);

        if ($moduleDom->length <= 0) {
            $this->errorMessage = sprintf("Could not find a module named '%s' in context '%s'.", $name, $this->name);
            return false;
        }

        return new Module($this, null, $moduleDom->item(0), true);
    }

    /**
     * Get module wich replace $name module
     *
     * @param string $name
     * @param bool   $status
     *
     * @return bool|Module
     */
    public function getModuleReplaced($name, $status = false)
    {
        require_once('class/Class.WIFF.php');
        require_once('class/Class.Module.php');

        $wiff = WIFF::getInstance();

        $xml = $wiff->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }

        $xpath = new DOMXPath($xml);

        $query = null;
        if ($status == 'installed') {
            $query = sprintf("/contexts/context[@name='%s']/modules/module[@status='installed']/replaces/module[@name='%s']/../..", $this->name, $name);
        } else {
            if ($status == 'downloaded') {
                $query = sprintf("/contexts/context[@name='%s']/modules/module[@status='downloaded']/replaces/module[@name='%s']/../..", $this->name, $name);
            } else {
                $query = sprintf("/contexts/context[@name='%s']/modules/module/replaces/module[@name='%s']/../..", $this->name, $name);
            }
        }
        $moduleDom = $xpath->query($query);

        if ($moduleDom->length <= 0) {
            $this->errorMessage = sprintf("Could not find a module providing '%s' in context '%s'.", $name, $this->name);
            return false;
        }

        return new Module($this, null, $moduleDom->item(0), true);
    }

    /**
     * Get module with downloaded status
     *
     * @param string $name
     *
     * @return Module
     */
    public function getModuleDownloaded($name)
    {
        return $this->getModule($name, 'downloaded');
    }

    /**
     * Get module with installed status
     *
     * @param string $name
     *
     * @return Module
     */
    public function getModuleInstalled($name)
    {
        return $this->getModule($name, 'installed');
    }

    /**
     * Get module wich replaces $name module with installed status
     *
     * @param string $name
     *
     * @return bool|Module
     */
    public function getModuleInstalledReplaced($name)
    {
        return $this->getModuleReplaced($name, 'installed');
    }

    /**
     * Get module if it's in available module list
     *
     * @param string $name
     *
     * @return Module
     */
    public function getModuleAvail($name)
    {
        $modAvails = $this->getAvailableModuleList();
        if ($modAvails === false) {
            return false;
        }

        foreach ($modAvails as $mod) {
            if ($mod->name == "$name") {
                $mod->context = $this;

                return $mod;
            }
        }

        $this->errorMessage = sprintf("Could not find module '%s' in context '%s'.", $name, $this->name);
        return false;
    }

    /**
     * Get true comparaison symbol
     *
     * @param string $comp
     *
     * @return string
     */
    public function compSymbol($comp)
    {
        $symbol = array(
            'gt' => '>',
            'ge' => '>=',
            'lt' => '<',
            'le' => '<=',
            'eq' => '==',
            'ne' => '!='
        );
        return (isset($symbol[$comp])) ? $symbol[$comp] : "?";
    }

    /**
     * Get available module statisfying $comp $version with $name
     *
     * @param string $name
     * @param string $version
     *
     * @return bool|Module
     */
    public function getModuleAvailSatisfying($name, $version)
    {
        $moduleList = array();
        foreach ($this->repo as $repository) {
            /**
             * @var Repository $repository
             */
            $repoModuleList = $repository->getModuleList($this);
            if ($repoModuleList === false) {
                $this->errorMessage = sprintf("Error fetching index for repository '%s'.", $repository->name);
                continue;
            }
            foreach ($repoModuleList as $module) {
                if ($module->name == $name && $this->moduleMeetsRequiredVersion($module, $version)) {
                    array_push($moduleList, $module);
                }
            }
        }
        usort($moduleList, array(
            $this,
            "cmpModuleByVersionDesc"
        ));
        if (isset($moduleList[0])) {
            $mod = $moduleList[0];
            $mod->context = $this;
            return $mod;
        }
        return false;
    }

    /**
     * Get module avalaible wich replace $name module
     *
     * @param string $name
     *
     * @return bool|Module
     */
    public function getModuleAvailReplaced($name)
    {
        $modAvails = $this->getAvailableModuleList();
        if ($modAvails === false) {
            return false;
        }
        foreach ($modAvails as $mod) {
            foreach ($mod->replaces as $replace) {
                if ($replace['name'] == $name) {
                    $mod->context = $this;
                    return $mod;
                }
            }
        }

        $this->errorMessage = sprintf("Could not find a module providing '%s' in context '%s'.", $name, $this->name);
        return false;
    }

    /**
     * Get module dependencies from repositories indexes
     *
     * @param array $namelist the module name list
     * @param bool  $local
     * @param bool  $installed
     *
     * @return array containing a list of Module objects ordered by their
     *         install order, or false in case of error
     *
     */
    public function getModuleDependencies(array $namelist, $local = false, $installed = false)
    {
        $depsList = array();
        foreach ($namelist as $name) {
            if ($installed) {
                $module = $this->getModuleInstalled($name);
                if ($module === false) {
                    $this->errorMessage = sprintf("Local module '%s' not found in contexts.xml.", $name);
                    return false;
                }
            } else {
                if ($local == false) {
                    $module = $this->getModuleAvail($name);
                    if ($module === false) {
                        $this->errorMessage = sprintf("Module '%s' could not be found in repositories.", $name);
                        return false;
                    }
                } else {
                    $module = $this->getModuleDownloaded($name);
                    if ($module === false) {
                        $this->errorMessage = sprintf("Local module '%s' not found in contexts.xml.", $name);
                        return false;
                    }
                }
            }
            array_push($depsList, $module);
        }

        $i = 0;
        while ($i < count($depsList)) {
            /**
             * @var Module $mod
             */
            $mod = $depsList[$i];

            if (!$this->installerMeetsModuleRequiredVersion($mod)) {
                $this->errorMessage = sprintf("Module '%s' (%s) requires installer %s", $mod->name, $mod->version, $this->errorMessage);
                return false;
            }

            $reqList = $mod->getRequiredModules();

            foreach ($reqList as $req) {
                $reqModName = $req['name'];
                $reqModVersion = $req['version'];

                $reqMod = $this->getModuleInstalled($reqModName);
                if ($reqMod !== false) {
                    // Found an installed module
                    if ($this->moduleMeetsRequiredVersion($reqMod, $reqModVersion)) {
                        // The installed module satisfy the required version
                        // Keep it
                        continue;
                    } else {
                        // Installed module does not satisfy required version
                        // so try looking for a matching module in repositories
                        $currentInstalledMod = $reqMod;
                        $satisfyingMod = $this->getModuleAvailSatisfying($reqModName, $reqModVersion);
                        if ($satisfyingMod !== false) {
                            if ($this->cmpModuleByVersionAsc($satisfyingMod, $currentInstalledMod) > 0) {
                                $satisfyingMod->needphase = 'upgrade';
                            } else {
                                $this->errorMessage = sprintf("Module \"%s\" (%s) required by \"%s\" is not compatible with current set of installed and available modules.",
                                    $reqModName, $reqModVersion, $mod->name);
                                return false;
                            }
                            // Keep the satisfying module as the required module for install/upgrade
                            $reqMod = $satisfyingMod;
                        } else {
                            // No satisfying module has been found
                            $reqMod = false;
                        }
                    }
                } else {
                    // Module is not already installed
                    // so lookup in repositories for a matching module
                    $reqMod = $this->getModuleAvailSatisfying($reqModName, $reqModVersion);
                    if ($reqMod !== false) {
                        $reqMod->needphase = 'install';
                    }
                }

                if ($reqMod === false) {
                    // Search the required module in replaced modules
                    $reqMod = $this->getModuleInstalledReplaced($reqModName);
                    if ($reqMod !== false) {
                        continue;
                    } else {
                        // Look for an available module online that replaces the required module
                        $reqMod = $this->getModuleAvailReplaced($reqModName);
                        if ($reqMod !== false) {
                            $reqMod->needphase = 'install';
                        }
                    }
                }

                if ($reqMod === false) {
                    if ($installed) {
                        /*
                         * Do not warn/err if an installed module has a broken
                         * dependency when archiving or restoring a context.
                        */
                        $this->log(LOG_INFO, sprintf("Module '%s' (%s) required by '%s' could not be found in repositories.", $reqModName,
                            $reqModVersion, $mod->name));
                        continue;
                    }
                    $this->errorMessage = sprintf("Module '%s' (%s) required by '%s' could not be found in repositories.", $reqModName,
                        $reqModVersion, $mod->name);
                    return false;
                }

                $pos = $this->depsListContains($depsList, $reqMod->name);
                if ($pos < 0) {
                    // Add the module to the dependencies list
                    array_push($depsList, $reqMod);
                }
            }
            $i++;
        }

        $orderList = array();

        $ret = $this->recursiveOrdering($depsList, $orderList);

        if ($ret === false) {
            return false;
        }
        // Put toolbox always at the beginning of the list
        foreach ($orderList as $key => $value) {
            if ($value->name == 'smart-data-engine') {
                unset($orderList[$key]);
                array_unshift($orderList, $value);
            }
        }
        // Check for and add replaced modules to the dep list
        // and mark them with needPhase='replaced'
        $removeList = array();
        foreach ($orderList as & $mod) {
            foreach ($mod->replaces as $replace) {
                $replacedModule = $this->getModuleInstalled($replace['name']);
                if ($replacedModule !== false) {
                    // This module is installed, so mark it for removal
                    $replacedModule->replacedBy = $mod->name;
                    array_push($removeList, $replacedModule);
                    // and mark the main module for 'upgrade'
                    $mod->needphase = 'upgrade';
                    $mod->warningMessage = "Warning: replace " . $replacedModule->name . ", versions are not checked";
                }
            }
        }
        unset($mod);

        foreach ($removeList as & $mod) {
            if (!$this->listContains($orderList, $mod->name)) {
                $mod->needphase = 'replaced';
                /*
                 * Try to insert the replaced module just before the
                 * newly installed module.
                */
                if (isset($mod->replacedBy)) {
                    /*
                     * Lookup insertion position from the end of the list.
                     *
                     * If no insertion position is found, then the
                     * module will be inserted at the front of the list
                     * (position = 0).
                    */
                    $pos = count($orderList) - 1;
                    while ($pos > 0) {
                        if ($mod->replacedBy == $orderList[$pos]->name) {
                            break;
                        }
                        $pos--;
                    }
                    /*
                     * Insert the module at found position.
                    */
                    array_splice($orderList, $pos, 0, array(
                        $mod
                    ));
                }
            }
        }
        unset($mod);

        if (($err = $this->checkBrokenDepsInInstalledModules($orderList)) !== '') {
            $this->errorMessage = $err;
            return false;
        }

        return $orderList;
    }

    /**
     * Check installed modules against the new set of module to see if a module
     * in the new set would break the requirements/dependencies of the
     * installed modules.
     *
     * Note:
     *
     * @param $newSet
     *
     * @return string
     */
    public function checkBrokenDepsInInstalledModules($newSet)
    {
        $installedList = $this->getInstalledModuleList();
        $brokenDeps = array();
        /*
         * Iterate over all installed modules
        */
        foreach ($installedList as $module) {
            /*
             * Skip modules which are in the new set of modules
            */
            $skipModule = false;
            foreach ($newSet as $newModule) {
                if ($newModule->name == $module->name) {
                    $skipModule = true;
                }
            }
            if ($skipModule) {
                continue;
            }
            /*
             * Check the requirements of the remaining installed modules to
             * see if the new set would break these modules requirements.
            */
            $requires = $module->getRequiredModules();
            foreach ($requires as $req) {
                foreach ($newSet as $newModule) {
                    if ($req['name'] == $newModule->name) {
                        if (!$this->moduleMeetsRequiredVersion($newModule, $req['version'])) {
                            $brokenDeps[] = array(
                                'brokenModule' => $module,
                                'brokenBy' => $newModule,
                                'brokenRequirement' => $req
                            );
                        }
                    }
                }
            }
        }

        if (count($brokenDeps) > 0) {
            $errList = array();
            foreach ($brokenDeps as $elmt) {
                $errList[] = sprintf("Module '%s' (version %s) would break installed module '%s' (version %s) which requires '%s' (version %s %s)", $elmt['brokenBy']->name,
                    $elmt['brokenBy']->version, $elmt['brokenModule']->name, $elmt['brokenModule']->version, $elmt['brokenBy']->name, $elmt['brokenRequirement']['comp'],
                    $elmt['brokenRequirement']['version']);
            }
            return join("\n", $errList);
        }
        return '';
    }

    /**
     * Check if $list contains module $name
     *
     * @param array  $list
     * @param string $name
     *
     * @return bool
     */
    function listContains(array $list, $name)
    {
        foreach ($list as $module) {
            if ($module->name == $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * Order $orderList by required module
     *
     * @param array $list
     * @param array $orderList
     *
     * @return bool
     */
    function recursiveOrdering(array & $list, array & $orderList)
    {
        $count = count($list);
        foreach ($list as $key => $mod) {
            /**
             * @var Module $mod
             */
            $reqList = $mod->getRequiredModules();

            $pushable = true;

            foreach ($reqList as $req) {
                // If ordered list does not contain one dependency and dependency list does contain it, module must not be added to ordered list at that time
                if (!$this->listContains($orderList, $req['name']) && $this->listContains($list, $req['name'])) {
                    $pushable = false;
                }
            }

            if ($pushable) {
                array_push($orderList, $mod);
                unset($list[$key]);
            }
        }

        if ($count === count($list)) {
            $modulesList = "";
            foreach ($list as $mod) {
                $modulesList .= ($modulesList ? ",\n" : "") . $mod->name;
            }
            $this->errorMessage = sprintf("These modules requirement are in conflict: \n" . $modulesList);
            return false;
        }
        if (count($list) != 0) {
            $ret = $this->recursiveOrdering($list, $orderList);
        } else {
            return true;
        }
        return $ret;
    }

    /**
     * Check if a Module object with this name already exists a a list of
     * Module objects
     *
     * @param        $depsList array( Module object 1, [...], Module object N )
     * @param string $name
     *
     * @return integer Index where module was found, -1 if not found
     *
     */
    private function depsListContains(array & $depsList, $name)
    {
        $i = 0;
        while ($i < count($depsList)) {
            if ($depsList[$i]->name == $name) {
                return $i;
            }
            $i++;
        }
        return -1;
    }

    /**
     * Move a module at position $pos after position $pivot
     *
     * @param array $depsList array of Modules
     * @param int   $pos      actual module to move
     * @param int   $pivot    position which the module should be moved to
     *
     * @return void (nothing)
     *
     */
    private function moveDepToRight(array & $depsList, $pos, $pivot)
    {
        $extractedModule = array_splice($depsList, $pos, 1);
        array_splice($depsList, $pivot, 0, $extractedModule);
    }

    /**
     * Check if a module is installed
     *
     * @param Module $module the Module object
     *
     * @return bool|\Module|object
     */
    private function moduleIsInstalled(Module & $module)
    {
        $installedModule = $this->getModuleInstalled($module->name);
        if ($installedModule === false) {
            return false;
        }
        return $installedModule;
    }


    /**
     * Check if installer meet required version by module
     *
     * @param Module $module
     *
     * @return bool
     */
    public function installerMeetsModuleRequiredVersion(Module & $module)
    {
        if (!isset($module->requires['installer'])) {
            return true;
        }

        $wiff = WIFF::getInstance();
        $wiffVersion = $wiff->getVersion();
        if ($wiffVersion === false) {
            $this->errorMessage = $wiff->errorMessage;
            return false;
        }
        $wiffVersion = preg_split('/\-/', $wiffVersion, 2);
        if (!empty($module->requires['installer']["version"])) {
            $cmp = Context::satisfyVersion($module->requires['installer']['version'], $wiffVersion[0]);
            if ($cmp === false) {
                $this->errorMessage = sprintf("%s", $module->requires['installer']['version']);
                return false;
            }
        }

        return true;
    }

    /**
     * Check if module meets required version
     *
     * @param Module $module
     * @param string $version
     *
     * @return bool
     */
    private function moduleMeetsRequiredVersion(Module & $module, $version = '')
    {
        return Context::satisfyVersion($version, $module->version);
    }

    /**
     * Get param by name
     *
     * @param string $paramName
     *
     * @return bool|string
     */
    public function getParamByName($paramName)
    {
        require_once(__DIR__ . '/Class.WIFF.php');

        $wiff = WIFF::getInstance();

        $xml = $wiff->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }

        $xpath = new DOMXPath($xml);
        /**
         * @var DOMElement $parameterNode
         */
        $parameterNode = $xpath->query(sprintf("/contexts/context[@name='%s']/parameters-value/param[@name='%s']", $this->name, $paramName))->item(0);
        if ($parameterNode) {
            $value = $parameterNode->getAttribute('value');
            $this->errorMessage = '';
            return $value;
        }
        $this->errorMessage = sprintf("Parameter with name '%s' not found in context '%s'.", $paramName, $this->name);
        return '';
    }

    /**
     * Get all parameters in contexts.xml
     *
     * @param string $paramName
     *
     * @return array
     */
    public function getParameters()
    {
        require_once(__DIR__ . '/Class.WIFF.php');

        $wiff = WIFF::getInstance();

        $xml = $wiff->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }

        $xpath = new DOMXPath($xml);
        /**
         * @var DOMElement $parameterNode
         */
        $parameterNodes = $xpath->query(sprintf("/contexts/context[@name='%s']/parameters-value/param", $this->name));
        $parameters=[];
        foreach ($parameterNodes as $parameterNode) {
            $parameters[$parameterNode->getAttribute("name")]=$parameterNode->getAttribute("value");
        }

        return $parameters;
    }
    /**
     * Set parameter value
     *
     * @param string $paramName  The parameter's name
     * @param string $paramValue The parameter's value
     *
     * @return bool bool(false) on error or bool(true) on success
     */
    public function setParamByName($paramName, $paramValue)
    {
        require_once __DIR__ . '/Class.WIFF.php';
        $wiff = WIFF::getInstance();

        $xml = $wiff->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }

        $xpath = new DOMXpath($xml);
        $parametersRoot = $xpath->query(sprintf("/contexts/context[@name='%s']/parameters-value", $this->name))->item(0);
        if (!$parametersRoot) {
            $this->errorMessage = sprintf("Could not find 'parameters-value' node for context with name '%s'.", $this->name);
            return false;
        }
        /**
         * @var DOMElement $paramNode
         */
        $paramList = $xpath->query(sprintf("/contexts/context[@name='%s']/parameters-value/param[@name='%s']", $this->name, $paramName));
        if ($paramList->length >= 1) {
            /* Update first parameter */
            $paramNode = $paramList->item(0);
            $paramNode->setAttribute('value', $paramValue);
        } else {
            /* Store new parameter */
            $paramNode = $xml->createElement('param');
            $paramNode->setAttribute('name', $paramName);
            $paramNode->setAttribute('value', $paramValue);
            $parametersRoot->appendChild($paramNode);
        }

        if ($wiff->commitDOMDocument($xml) === false) {
            $this->errorMessage = sprintf("Error saving contexts.xml '%s': %s", $wiff->contexts_filepath, $wiff->errorMessage);
            return false;
        }

        return true;
    }

    public function wstop(&$output = array())
    {
        $wstop = sprintf("%s/wstop", $this->root);
        $ret = 0;
        if (!is_executable($wstop)) {
            $this->log(LOG_WARNING, sprintf("Unexpected missing or non-executable script '%s/wstop' in context '%s'.", $this->root, $this->name));
            return 0;
        }
        $cmd = sprintf("%s", escapeshellarg($wstop));
        $this->log(LOG_INFO, sprintf("Running '%s' in context '%s'.", $cmd, $this->name));
        exec($cmd, $output, $ret);
        if ($ret !== 0) {
            $this->log(LOG_ERR, sprintf("Execution of '%s' in context '%s' failed with exit code '%d': %s", $cmd, $this->name, $ret, join("\n", $output)));
        }

        return $ret;
    }

    public function wstart(&$output = array(), $args = array())
    {
        if (!is_array($args)) {
            $args = array();
        }
        $wstart_args = array();
        foreach ($args as $arg) {
            $wstart_args[] = escapeshellarg($arg);
        }

        $wstart = sprintf("%s/wstart", $this->root);
        if (!is_executable($wstart)) {
            $this->log(LOG_WARNING, sprintf("Unexpected missing or non-executable script '%s/wstart' in context '%s'.", $this->root, $this->name));
            return 0;
        }

        $ret = 0;
        $cmd = escapeshellarg($wstart);
        if ($wstart_args != '') {
            $cmd = $cmd . " " . join(' ', $wstart_args);
        }
        $this->log(LOG_INFO, sprintf("Running '%s' in context '%s'.", $cmd, $this->name));
        exec($cmd, $output, $ret);
        if ($ret !== 0) {
            $this->log(LOG_ERR, sprintf("Execution of '%s' in context '%s' failed with exit code '%d': %s", $cmd, $this->name, $ret, join("\n", $output)));
        }
        return $ret;
    }

    /**
     * Upload module wich are in $_FILES
     *
     * @return bool|string
     */
    public function uploadModule()
    {

        $tmpfile = Control\Internal\LibSystem::tempnam(null, 'WIFF_downloadLocalFile');
        if ($tmpfile === false) {
            $this->errorMessage = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "Error creating temporary file.");
            return false;
        }
        if (empty($_FILES)) {
            $this->errorMessage = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("File size (" . $_SERVER['CONTENT_LENGTH'] . " bytes) is bigger than php post_max_size ("
                    . ini_get('post_max_size') . ")"));
            unlink($tmpfile);
            return false;
        }
        if (!array_key_exists('module', $_FILES)) {
            $this->errorMessage = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Missing 'module' in uploaded files."));
            unlink($tmpfile);
            return false;
        }
        if ($_FILES['module']['error'] !== UPLOAD_ERR_OK) {
            $this->errorMessage = WIFF::getUploadErrorMsg($_FILES['module']['error']);
            unlink($tmpfile);
            return false;
        }
        $ret = move_uploaded_file($_FILES['module']['tmp_name'], $tmpfile);
        if ($ret === false) {
            $this->errorMessage = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Could not move uploaded file to temporary file '%s'.", $tmpfile));
            unlink($tmpfile);
            return false;
        }

        $ret = $this->importArchive($tmpfile);
        if ($ret === false) {
            $this->errorMessage = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Failed to import archive: '%s'.", $this->errorMessage));
            return false;
        }

        return $tmpfile;
    }

    /**
     * Get module name from tmp file
     *
     * @param string $moduleFilePath
     *
     * @return bool
     */
    public function getModuleNameFromTmpFile($moduleFilePath)
    {
        $wiff = WIFF::getInstance();
        if ($wiff === false) {
            $this->errorMessage = sprintf("Could not get context.");
            return false;
        }

        $xml = $wiff->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }

        $xpath = new DOMXPath($xml);

        $res = $xpath->query(sprintf("/contexts/context[@name='%s']/modules/module[@tmpfile='%s']", $this->name, $moduleFilePath));
        if ($res->length <= 0) {
            $this->errorMessage = sprintf("Could not find module with tmpfile '%s'", $moduleFilePath);
            return false;
        }
        if ($res->length > 1) {
            $this->errorMessage = sprintf("Found more than one module with tmpfile '%s'", $moduleFilePath);
            return false;
        }
        /**
         * @var DOMElement $module
         */
        $module = $res->item(0);

        return $module->getAttribute('name');
    }

    /**
     * Get local module dependencies
     *
     * @param string $moduleFilePath
     *
     * @return array|bool array of Modules are false if error
     */
    public function getLocalModuleDependencies($moduleFilePath)
    {
        $moduleName = $this->getModuleNameFromTmpFile($moduleFilePath);
        if ($moduleName === false) {
            $this->errorMessage = sprintf("Could not get module name from filepath '%s' in contexts.xml: %s", $moduleFilePath, $this->errorMessage);
            return false;
        }

        $module = $this->getModuleDownloaded($moduleName);
        if ($module === false) {
            $this->errorMessage = sprintf("Could not get module with name '%s' in contexts.xml: %s", $moduleName, $this->errorMessage);
            return false;
        }
        # error_log(sprintf(">>> moduleName = %s", $moduleName));
        $deps = $this->getModuleDependencies(array(
            $moduleName
        ), true);

        return $deps;
    }

    /**
     * Load module from $filename package
     *
     * @param string $filename
     *
     * @return bool|Module
     */
    public function loadModuleFromPackage($filename)
    {
        require_once(__DIR__ . '/Class.Module.php');

        $module = new Module($this);
        $module->tmpfile = $filename;

        $xml = $module->loadInfoXml();
        if ($xml === false) {
            $this->errorMessage = sprintf("Could not load info xml: '%s'.", $module->errorMessage);
            return false;
        }

        return $module;
    }

    /**
     * Remove module $moduleName with status $status
     *
     * @param string $moduleName
     * @param string $status
     *
     * @return bool
     */
    public function removeModule($moduleName, $status = '')
    {
        require_once('class/Class.WIFF.php');
        require_once('class/Class.Module.php');

        $wiff = WIFF::getInstance();
        if ($wiff === false) {
            $this->errorMessage = sprintf("Could not get context.");
            return false;
        }

        $xml = $wiff->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }

        $xpath = new DOMXpath($xml);

        $query = null;
        if ($status == 'installed') {
            $query = sprintf("/contexts/context[@name='%s']/modules/module[@name='%s' and @status='installed']", $this->name, $moduleName);
        } else {
            if ($status == 'downloaded') {
                $query = sprintf("/contexts/context[@name='%s']/modules/module[@name='%s' and @status='downloaded']", $this->name, $moduleName);
            } else {
                $query = sprintf("/contexts/context[@name='%s']/modules/module[@name='%s']", $this->name, $moduleName);
            }
        }
        $moduleDom = $xpath->query($query);

        if ($moduleDom->length <= 0) {
            return true;
        }

        for ($i = 0; $i < $moduleDom->length; $i++) {
            $module = $moduleDom->item($i);
            $module->parentNode->removeChild($module);
        }

        $ret = $wiff->commitDOMDocument($xml);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error saving contexts.xml '%s': %s", $wiff->contexts_filepath, $wiff->errorMessage);
            return false;
        }

        return true;
    }

    /**
     * Remove module $moduleName with installed status
     *
     * @param string $moduleName
     *
     * @return bool
     */
    public function removeModuleInstalled($moduleName)
    {
        $ret = $this->removeModule($moduleName, 'installed');
        if ($ret === false) {
            return false;
        }

        return true;
    }

    /**
     * Remove module $moduleName with downloaded status
     *
     * @param string $moduleName
     *
     * @return bool
     */
    public function removeModuleDownloaded($moduleName)
    {
        return $this->removeModule($moduleName, 'downloaded');
    }

    /**
     * Create or write in error file for archive error
     *
     * @param string $archiveId
     * @param string $dir
     */
    private function writeArchiveError($archiveId, $dir)
    {
        $error_file = $dir . DIRECTORY_SEPARATOR . $archiveId . '.error';
        file_put_contents($error_file, $this->errorMessage);
    }

    /**
     * Archive context
     *
     * @param string $archiveName
     * @param string $archiveDesc
     * @param bool   $vaultExclude
     *
     * @return bool|string false or archiveId
     */
    public function archiveContext($archiveName, $archiveDesc = '', $vaultExclude = false)
    {
        $unlink = array();
        $archiveId = $this->__archiveContext($unlink, $archiveName, $archiveDesc, $vaultExclude);
        foreach ($unlink as $file => $meta) {
            if (!file_exists($file)) {
                continue;
            }
            unlink($file);
        }
        return $archiveId;
    }

    private function __archiveContext(&$unlink, $archiveName, $archiveDesc = '', $vaultExclude = false)
    {
        $wiff = WIFF::getInstance();
        $wiff_root = $wiff->getWiffRoot();
        if ($wiff_root === false) {
            $this->errorMessage = sprintf("Could not get wiff root directory.");
            return false;
        }

        $archived_tmp_dir = $wiff->archived_tmp_dir;
        // --- Create or reuse directory --- //
        if (is_dir($archived_tmp_dir)) {
            if (!is_writable($archived_tmp_dir)) {
                $this->errorMessage = sprintf("Directory '%s' is not writable.", $archived_tmp_dir);
                return false;
            }
        } else {
            if (@mkdir($archived_tmp_dir) === false) {
                $this->errorMessage = sprintf("Error creating directory '%s'.", $archived_tmp_dir);
                return false;
            }
        }

        $zip = new ZipArchiveCmd();

        $archived_contexts_dir = $wiff->archived_contexts_dir;
        // --- Generate archive id --- //
        $datetime = new DateTime();
        $archiveId = sprintf("%s-%s", preg_replace('/\//', '_', $archiveName), sha1($this->name . $datetime->format('Y-m-d H:i:s')));
        // --- Create status file for archive --- //
        $status_file = $archived_tmp_dir . DIRECTORY_SEPARATOR . $archiveId . '.sts';
        file_put_contents($status_file, $archiveName);
        $unlink[$status_file] = true;

        $zipfile = $archived_contexts_dir . "/$archiveId.fcz";
        if ($zip->open($zipfile, ZipArchiveCmd::CREATE) === false) {
            $this->errorMessage = sprintf("Cannot create Zip archive '%s': %s", $zipfile, $zip->getStatusString());
            // --- Delete status file --- //
            $this->writeArchiveError($archiveId, $archived_tmp_dir);
            return false;
        }
        // --- Generate info.xml --- //
        $doc = new DOMDocument();
        $doc->formatOutput = true;
        $doc->preserveWhiteSpace = false;

        $root = $doc->createElement('info');
        $root = $doc->appendChild($root);
        // --- Copy context information --- //
        $contextsXml = $wiff->loadContextsDOMDocument();
        if ($contextsXml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            $zip->close();
            $this->writeArchiveError($archiveId, $archived_tmp_dir);
            return false;
        }

        $contextsXPath = new DOMXPath($contextsXml);
        // Get this context
        $contextList = $contextsXPath->query("/contexts/context[@name='" . $this->name . "']");
        if ($contextList->length != 1) {
            // If more than one context with name
            $this->errorMessage = "Duplicate contexts with same name";
            $zip->close();
            $this->writeArchiveError($archiveId, $archived_tmp_dir);
            return false;
        }
        /**
         * @var DOMElement $context
         */
        $context = $doc->importNode($contextList->item(0), true); // Node must be imported from contexts document.
        if ($context->hasAttribute('register')) {
            // Remove register status on archived contexts
            $context->removeAttribute('register');
        }
        $context = $root->appendChild($context);
        /**
         * @var DOMElement $repositories
         */
        $repositories = $context->getElementsByTagName('repositories')->item(0);
        if ($repositories) {
            deleteNode($repositories);
        }
        // Identify and exclude vaults located below the context directory
        $vaultList = $this->getVaultList();
        if ($vaultList === false) {
            $this->errorMessage = sprintf("Error getting vault list for context '%s'", $this->root);
            $zip->close();
            $this->writeArchiveError($archiveId, $archived_tmp_dir);
            return false;
        }
        $realContextRootPath = realpath($this->root);
        if ($realContextRootPath === false) {
            $this->errorMessage = sprintf("Error getting real path for '%s'", $this->root);
            $zip->close();
            $this->writeArchiveError($archiveId, $archived_tmp_dir);
            return false;
        }

        $tarExcludeOpts = '';
        $tarExcludeList = array(
            sprintf("--exclude %s", escapeshellarg('.' . DIRECTORY_SEPARATOR . 'var/tmp')),
            sprintf("--exclude %s", escapeshellarg('.' . DIRECTORY_SEPARATOR . 'var/session')),
            sprintf("--exclude %s", escapeshellarg('.' . DIRECTORY_SEPARATOR . 'var/cache'))
        );
        foreach ($vaultList as $vault) {
            $r_path = $vault['r_path'];
            if ($r_path[0] != '/') {
                $r_path = $this->root . DIRECTORY_SEPARATOR . $r_path;
            }
            $real_r_path = realpath($r_path);
            if ($real_r_path === false) {
                continue;
            }
            if (strpos($real_r_path, $realContextRootPath) === 0) {
                $relative_r_path = "." . substr($real_r_path, strlen($realContextRootPath));
                $tarExcludeList[] = sprintf("--exclude %s", escapeshellarg($relative_r_path));
            }
        }
        if (count($tarExcludeList) > 0) {
            $tarExcludeOpts = join(' ', $tarExcludeList);
        }
        //error_log(__METHOD__ . " " . sprintf("tarExcludeOpts = [%s]", $tarExcludeOpts));
        // --- Generate context tar.gz --- //
        $script = sprintf("tar -C %s -czf %s/context.tar.gz %s . 2>&1", escapeshellarg($this->root), escapeshellarg($archived_tmp_dir), $tarExcludeOpts);
        exec($script, $output, $retval);
        $unlink["$archived_tmp_dir/context.tar.gz"] = true;
        if ($retval != 0) {
            $this->errorMessage = "Error when making context tar :: " . join("\n", $output);
            $zip->close();
            $this->writeArchiveError($archiveId, $archived_tmp_dir);
            return false;
        }
        if ($wiff->verifyGzipIntegrity("$archived_tmp_dir/context.tar.gz", $err) === false) {
            $this->errorMessage = sprintf("Corrupted gzip archive '%s': %s", "$archived_tmp_dir/context.tar.gz", $err);
            $zip->close();
            $this->writeArchiveError($archiveId, $archived_tmp_dir);
            return false;
        }
        $err = $zip->addFileWithoutPath("$archived_tmp_dir/context.tar.gz");
        if ($err === false) {
            $this->errorMessage = sprintf("Could not add 'context.tar.gz' to archive: %s", $zip->getStatusString());
            $zip->close();
            $this->writeArchiveError($archiveId, $archived_tmp_dir);
            return false;
        }
        $this->log(LOG_INFO, 'Generated context.tar.gz');
        unlink("$archived_tmp_dir/context.tar.gz");
        unset($unlink["$archived_tmp_dir/context.tar.gz"]);
        // --- Generate database dump --- //
        $pgservice_core = $this->getParamByName('core_db');

        $dump = $archived_tmp_dir . DIRECTORY_SEPARATOR . 'core_db.pg_dump.gz';

        $errorFile = Control\Internal\LibSystem::tempnam(null, 'WIFF_error.tmp');
        if ($errorFile === false) {
            $this->log(LOG_ERR, __FUNCTION__ . " " . sprintf("Error creating temporary file."));
            $this->errorMessage = "Error creating temporary file for error.";
            $zip->close();
            $this->writeArchiveError($archiveId, $archived_tmp_dir);
            return false;
        }

        $script = sprintf("PGSERVICE=%s pg_dump --compress=9 --no-owner 1>%s 2>%s", escapeshellarg($pgservice_core), escapeshellarg($dump), escapeshellarg($errorFile));
        exec($script, $output, $retval);
        $unlink[$dump] = true;
        if ($retval != 0) {
            $this->errorMessage = "Error when making database dump :: " . file_get_contents($errorFile);
            if (file_exists("$errorFile")) {
                unlink("$errorFile");
            }
            $zip->close();
            $this->writeArchiveError($archiveId, $archived_tmp_dir);
            return false;
        }
        if ($wiff->verifyGzipIntegrity($dump, $err) === false) {
            $this->errorMessage = sprintf("Corrupted gzip archive '%s': %s", $dump, $err);
            $zip->close();
            $this->writeArchiveError($archiveId, $archived_tmp_dir);
            return false;
        }
        $err = $zip->addFileWithoutPath($dump);
        if ($err === false) {
            $this->errorMessage = sprintf("Could not add 'core_db.pg_dump.gz' to archive: %s", $zip->getStatusString());
            $zip->close();
            $this->writeArchiveError($archiveId, $archived_tmp_dir);
            return false;
        }
        $this->log(LOG_INFO, 'Generated core_db.pg_dump.gz');
        unlink($dump);
        unset($unlink[$dump]);

        if ($vaultExclude != 'on') {
            // --- Generate vaults tar.gz files --- //
            $vaultList = $this->getVaultList();
            if ($vaultList === false) {
                $this->errorMessage = sprintf("Error getting vault list: %s", $this->errorMessage);
                $zip->close();
                $this->writeArchiveError($archiveId, $archived_tmp_dir);
                return false;
            }

            $vaultDirList = array();
            foreach ($vaultList as $vault) {
                $id_fs = $vault['id_fs'];
                $r_path = $vault['r_path'];
                if (is_dir($r_path)) {
                    $vaultDirList[] = array(
                        "id_fs" => $id_fs,
                        "r_path" => $r_path
                    );
                    $vaultExclude = 'Vaultexists';
                    $tmpVault = sprintf("%s/vault_%s.tar.gz", $archived_tmp_dir, $id_fs);
                    $script = sprintf("tar -C %s -czf  %s . 2>&1", escapeshellarg($r_path), escapeshellarg($tmpVault));
                    exec($script, $output, $retval);
                    $unlink[$tmpVault] = true;
                    if ($retval != 0) {
                        $this->errorMessage = sprintf("Error when archiving vault '%s': %s", $r_path, join("\n", $output));
                        $zip->close();
                        $this->writeArchiveError($archiveId, $archived_tmp_dir);
                        return false;
                    }
                    $err = $zip->addFileWithoutPath($tmpVault);
                    if ($err === false) {
                        $this->errorMessage = sprintf("Could not add 'vault_%s.tar.gz' to archive: %s", $id_fs, $zip->getStatusString());
                        $zip->close();
                        $this->writeArchiveError($archiveId, $archived_tmp_dir);
                        return false;
                    }
                    if ($wiff->verifyGzipIntegrity($tmpVault, $err) === false) {
                        $this->errorMessage = sprintf("Corrupted gzip archive '%s': %s", $tmpVault, $err);
                        $zip->close();
                        $this->writeArchiveError($archiveId, $archived_tmp_dir);
                        return false;
                    }
                    unlink($tmpVault);
                    unset($unlink[$tmpVault]);
                } elseif ($vaultExclude != 'Vaultexists') {
                    $vaultExclude = 'on';
                    $this->log(LOG_INFO, "No vault directory found");
                }
            }
            if ($vaultExclude != 'on') {
                $this->log(LOG_INFO, 'Generated vault tar gz');
            }
        }
        // --- Write archive information --- //
        $archive = $doc->createElement('archive');
        $archive->setAttribute('id', $archiveId);
        $archive->setAttribute('name', $archiveName);
        $archive->setAttribute('datetime', $datetime->format('Y-m-d H:i:s'));
        $archive->setAttribute('description', $archiveDesc);

        if ($vaultExclude == 'on') {
            $archive->setAttribute('vault', 'No');
        } else {
            $archive->setAttribute('vault', 'Yes');
        }
        $root->appendChild($archive);

        $xml = $doc->saveXML();

        $err = $zip->addFromString('info.xml', $xml);
        if ($err === false) {
            $zip->close();
            $this->errorMessage = sprintf("Could not add 'info.xml' to archive: %s", $zip->getStatusString());
            $this->writeArchiveError($archiveId, $archived_tmp_dir);
            return false;
        }

        $zip->close();

        printf("\rWrote \"%s\"\n", $zipfile);
        return $archiveId;
    }

    /**
     * Get vault list
     *
     * @return array|bool
     */
    private function getVaultList()
    {
        $pgservice_core = $this->getParamByName('core_db');
        $dbconnect = pg_connect("service=$pgservice_core");
        if ($dbconnect === false) {
            $this->errorMessage = sprintf("Error when trying to connect to database with service '%s'.", $pgservice_core);
            return false;
        }
        $result = pg_query($dbconnect, "SELECT id_fs, r_path FROM vaultdiskfsstorage ;");
        if ($result === false) {
            $this->errorMessage = sprintf("Error executing query: %s", pg_last_error($dbconnect));
            return false;
        }
        $vaultList = pg_fetch_all($result);
        if ($vaultList === false) {
            /* pg_fetch_all() returns false if the result is empty
             but we want an empty array() in this case. */
            $vaultList = array();
        }
        pg_close($dbconnect);
        return $vaultList;
    }

    /**
     * Store the manifest of a downloaded module
     *
     * @param Module $module a Module object
     *
     * @return bool
     */
    public function storeManifestForModule($module)
    {
        if (!is_object($module)) {
            $err = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "not an object");
            $this->errorMessage = $err;
            return false;
        }

        $manifest = $module->getManifest();
        if ($manifest == '') {
            $err = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "empty manifest for '%s'", $module->name);
            $this->errorMessage = $err;
            return $manifest;
        }

        $manifestDir = sprintf("%s/", $this->root);
        $manifestFile = sprintf("%s.manifest", $module->name);

        $tmpfile = tempnam($manifestDir, $manifestFile);
        if ($tmpfile === false) {
            $err = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "Error creating temp file in '%s'", $manifestDir);
            $this->errorMessage = $err;
            return false;
        }

        $fout = fopen($tmpfile, 'w');
        if ($fout === false) {
            $err = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "Error opening output file '%s' for writing.", $tmpfile);
            $this->errorMessage = $err;
            unlink($tmpfile);
            return false;
        }

        $ret = fwrite($fout, $manifest);
        if ($ret === false) {
            $err = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "Error writing manifest to '%s'.", $tmpfile);
            $this->errorMessage = $err;
            unlink($tmpfile);
            return false;
        }

        fclose($fout);

        $ret = rename($tmpfile, sprintf("%s/%s", $manifestDir, $manifestFile));
        if ($ret === false) {
            $err = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "Error moving '%s' to '%s'", $tmpfile, sprintf("%s/%s", $manifestDir, $manifestFile));
            $this->errorMessage = $err;
            unlink($tmpfile);
            return false;
        }

        return $manifest;
    }

    /**
     * get the manifest of a module name
     *
     * @param Module $moduleName a Module object
     *
     * @return bool|string boolean false on error or the manifests content
     */
    public function getManifestForModule($moduleName)
    {
        if (is_object($moduleName)) {
            $moduleName = $moduleName->name;
        }

        $manifestFile = sprintf("%s/%s.manifest", $this->root, $moduleName);
        if (!is_file($manifestFile)) {
            $err = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "Manifest file '%s' does not exists.", $manifestFile);
            $this->errorMessage = $err;
            return false;
        }

        $manifest = file_get_contents($manifestFile);
        if ($manifest === false) {
            $err = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "Error getting content from manifest file '%s'.", $manifestFile);
            $this->errorMessage = $err;
            return false;
        }

        return $manifest;
    }

    /**
     * Delete the manifest file of a module name
     *
     * @param Module $moduleName a Module object
     *
     * @return bool|string boolean false on error or the manifests content
     */
    public function deleteManifestForModule($moduleName)
    {
        if (is_object($moduleName)) {
            $moduleName = $moduleName->name;
        }

        $manifestFile = sprintf("%s/%s.manifest", $this->root, $moduleName);
        if (!file_exists($manifestFile)) {
            return $manifestFile;
        }
        if (!is_file($manifestFile)) {
            $err = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "'%s' is not a file.", $manifestFile);
            $this->errorMessage = $err;
            return false;
        }

        $ret = unlink($manifestFile);
        if ($ret === false) {
            $err = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "Error unlinking manifest file '%s'.", $manifestFile);
            $this->errorMessage = $err;
            return false;
        }

        return $manifestFile;
    }

    /**
     * Delete files from the given module name
     *
     * @param Module $moduleName a Module object
     *
     * @return bool success
     */
    public function deleteFilesFromModule($moduleName)
    {
        if (is_object($moduleName)) {
            $moduleName = $moduleName->name;
        }

        $manifestEntries = $this->getManifestEntriesForModule($moduleName);
        if ($manifestEntries === false) {
            $err = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "Error getting manifest entries for module '%s': %s", $moduleName, $this->errorMessage);
            $this->errorMessage = $err;
            return false;
        }
        // Sort files in reverse order in order to be able to processs
        // removal of directories after their contained files
        usort($manifestEntries, array(
            $this,
            "sortManifestEntriesByNameReverse"
        ));

        foreach ($manifestEntries as $mentry) {
            $fpath = sprintf("%s/%s", $this->root, $mentry['name']);

            if (!file_exists($fpath)) {
                continue;
            }

            $stat = lstat($fpath);
            if ($stat === false) {
                $this->log(LOG_WARNING, __CLASS__ . "::" . __FUNCTION__ . " " . sprintf("stat('%s') from module '%s' returned with error.", $fpath, $moduleName));
                continue;
            }

            if (!is_link($fpath) && is_dir($fpath)) {
                if ($mentry['type'] != 'd') {
                    $this->log(LOG_WARNING,
                        __CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Type mismatch for file '%s' from module '%s': type is 'd' while manifest says '%s'.", $fpath, $moduleName,
                            $mentry['type']));
                    continue;
                }
                if ($stat['nlink'] > 2 || count(scandir($fpath)) > 2) {
                    continue;
                }
                $ret = @rmdir($fpath);
            } else {
                $ret = @unlink($fpath);
            }

            if ($ret === false) {
                $this->log(LOG_ERR, __CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Error removing '%s' (%s) from module '%s'.", $fpath, $mentry['type'], $moduleName));
            }
        }

        return true;
    }

    /**
     * Sort helper function for manifest entries
     *
     * @param array $a a manifest entry array structure
     * @param array $b a manifest entry array structure
     *
     * @return int
     */
    private function sortManifestEntriesByNameReverse($a, $b)
    {
        return strcmp($b['name'], $a['name']);
    }

    public function getManifestEntriesForModule($moduleName)
    {
        $manifest = $this->getManifestForModule($moduleName);
        $manifestLines = preg_split("/\n/", $manifest);
        $manifestEntries = array();

        foreach ($manifestLines as $line) {
            $minfo = array();
            if (!preg_match("|^(?P<type>.)(?P<mode>.........)\s+(?P<uid>.*?)/(?P<gid>.*?)\s+(?P<size>\d+)\s+(?P<date>\d\d\d\d-\d\d-\d\d\s+\d\d:\d\d(?::\d\d)?)\s+(?P<name>.*?)(?P<link>\s+->\s+.*?)?$|",
                $line, $minfo)) {
                continue;
            }
            array_push($manifestEntries, $minfo);
        }

        return $manifestEntries;
    }

    /**
     * Purge/remove parameters value that are associated
     * with a module that is no more present in the context.
     *
     * @return bool success
     */
    public function purgeUnreferencedParametersValue()
    {
        require_once('class/Class.WIFF.php');

        $wiff = WIFF::getInstance();

        $xml = $wiff->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage = sprintf("Error lopading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }

        $xpath = new DOMXPath($xml);

        $parametersValueNodeList = $xpath->query(sprintf("/contexts/context[@name='%s']/parameters-value/param", $this->name));
        if ($parametersValueNodeList->length <= 0) {
            return true;
        }

        $purgeNodeList = array();
        for ($i = 0; $i < $parametersValueNodeList->length; $i++) {
            /**
             * @var DOMElement $pv
             */
            $pv = $parametersValueNodeList->item($i);
            if ($pv->getAttribute('volatile') == 'yes' || $pv->getAttribute('volatile') == 'Y') {
                /* Purge volatile parameters */
                array_push($purgeNodeList, $pv);
            } else {
                $moduleName = $pv->getAttribute('modulename');
                $module = $this->getModule($moduleName);
                if ($module === false) {
                    /* If the parameter's module does not exists, then try to find an
                     * installed module which replaces this missing parameter's module.
                    */
                    $newModule = $this->getModuleReplaced($moduleName);
                    if ($newModule === false) {
                        /* Purge the parameter as it belongs to nobody */
                        array_push($purgeNodeList, $pv);
                    } else {
                        /* Re-affect the parameter to this new module */
                        $pv->setAttribute('modulename', $newModule->name);
                    }
                }
            }
        }

        foreach ($purgeNodeList as $node) {
            /**
             * @var DOMElement $node
             */
            $node->parentNode->removeChild($node);
        }

        $ret = $wiff->commitDOMDocument($xml);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error saving contexts.xml '%s': %s", $wiff->contexts_filepath, $wiff->errorMessage);
            return false;
        }

        return true;
    }

    /**
     * Execute wsh api from context
     *
     * @param string $api_name
     * @param array  $args
     *
     * @return string
     */
    public function wsh($api_name, array $args)
    {
        $cmd = sprintf('%s/wsh.php --api=%s', escapeshellarg($this->root), escapeshellarg($api_name));
        foreach ($args as $name => $value) {
            $cmd .= sprintf(' --%s=%s', $name, escapeshellarg($value));
        }

        exec(sprintf("%s 2>&1", $cmd), $output, $ret);
        if ($ret != 0) {
            $this->errorMessage = sprintf("Wsh command '%s' returned with error: %s", $cmd, join("\n", $output));
            return false;
        }
        return true;
    }

    /**
     * Delete context
     *
     * @param boolean $res the result of the operation: boolean false|true
     * @param boolean $opt
     *
     * @return string the error message
     */
    public function delete(&$res, $opt = false)
    {
        $err_msg = '';
        $res = true;
        if ($opt === 'crontab' || $opt === false) {
            $args = array(
                "cmd" => "unregister",
                'file' => 'FREEDOM/freedom.cron'
            );
            $ret = $this->wsh("crontab", $args);
            if ($ret === false) {
                $err_msg .= 'Error Trying to delete crontab';
                $this->log(LOG_WARNING, __CLASS__ . "::" . __FUNCTION__ . " " . sprintf("deleteContextCrontab returned with error: %s", $this->errorMessage));
            }
            $this->log(LOG_INFO, "crontab deleted");
        }
        if ($opt === 'vault' || $opt === false) {
            $ret = $this->deleteContextVault();
            if ($ret) {
                $err_msg .= $ret;
                $this->log(LOG_WARNING, __CLASS__ . "::" . __FUNCTION__ . " " . sprintf("deleteContextVault returned with error: %s", $this->errorMessage));
            }
            $this->log(LOG_INFO, "vault deleted");
        }
        if ($opt === 'database' || $opt === false) {
            $err = '';
            $ret = $this->deleteContextDatabaseContent($err);
            if ($ret === false) {
                $err_msg .= $this->errorMessage;
                $this->log(LOG_WARNING, __CLASS__ . "::" . __FUNCTION__ . " " . sprintf("deleteContextDatabaseContent returned with error: %s", $this->errorMessage));
            } elseif ($err != '') {
                $err_msg .= $err;
                $this->log(LOG_WARNING, __CLASS__ . "::" . __FUNCTION__ . " " . sprintf("deleteContextDatabaseContent returned with warning: %s", $err));
            }
            $this->log(LOG_INFO, "database deleted");
        }
        if ($opt === 'root' || $opt === false) {
            $ret = $this->deleteContextRoot();
            if ($ret) {
                $err_msg .= $ret;
                $this->log(LOG_WARNING, __CLASS__ . "::" . __FUNCTION__ . " " . sprintf("deleteContextRoot returned with error: %s", $this->errorMessage));
            }
            $this->log(LOG_INFO, "root deleted");
        }
        if ($opt === 'unregister' || $opt === false) {
            if ($this->register == 'registered') {
                $ret = $this->deleteRegistrationConfiguration();
                if ($ret === false) {
                    $err_msg .= $this->errorMessage;
                    $this->log(LOG_WARNING, __CLASS__ . "::" . __FUNCTION__ . " " . sprintf("deleteRegistrationConfiguration returned with error: %s", $this->errorMessage));
                }
            }
            $ret = $this->unregisterContextFromConfig();
            if ($ret) {
                $res = false;
                $err_msg .= $ret;
                $this->log(LOG_WARNING, __CLASS__ . "::" . __FUNCTION__ . " " . sprintf("unregisterContextFromConfig returned with error: %s", $this->errorMessage));
            }
            $this->log(LOG_INFO, "context unregister");
        }
        return $err_msg;
    }

    /**
     * Unregister context from configuration
     *
     * @return string
     */
    public function unregisterContextFromConfig()
    {
        $wiff = WIFF::getInstance();
        if ($wiff === false) {
            $this->errorMessage .= sprintf("Could not get wiff instance.");
            return sprintf("Could not get wiff instance.");
        }

        $xml = $wiff->loadContextsDOMDocument();
        if ($xml === false) {
            $this->errorMessage .= sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return $this->errorMessage;
        }

        $xpath = new DOMXpath($xml);

        $contextNodeList = $xpath->query(sprintf("/contexts/context[@name='%s']", $this->name));
        if ($contextNodeList->length <= 0) {
            $this->errorMessage .= sprintf("Could not find a context with name '%s'!", $this->name);
            return sprintf("Could not find a context with name '%s'!", $this->name);
        }
        if ($contextNodeList->length > 1) {
            $this->errorMessage .= sprintf("There is more than one context with name '%s'!", $this->name);
            return sprintf("There is more than one context with name '%s'!", $this->name);
        }
        $contextNode = $contextNodeList->item(0);

        $xml->documentElement->removeChild($contextNode);

        $ret = $wiff->commitDOMDocument($xml);
        if ($ret === false) {
            $this->errorMessage .= sprintf("Error saving contexts.xml '%s': %s", $wiff->contexts_filepath, $wiff->errorMessage);
            return sprintf("Error saving contexts.xml '%s': %s", $wiff->contexts_filepath, $wiff->errorMessage);
        }

        return "";
    }

    /**
     * get Context's vault path list
     *
     * @param string $err
     *
     * @return array|bool
     */
    public function getContextVaultPathList(&$err)
    {
        $pgservice_core = $this->getParamByName('core_db');
        if ($pgservice_core == "") {
            $err = sprintf("Parameter 'core_db' not found or empty in context '%s'.\n", $this->name);
            $this->errorMessage .= $err;
            return false;
        }

        $conn = pg_connect(sprintf("service=%s", $pgservice_core));
        if ($conn === false) {
            $err = sprintf("Error connecting to 'service=%s'.\n", $pgservice_core);
            $this->errorMessage .= $err;
            return false;
        }

        $res = pg_query($conn, "SELECT r_path FROM vaultdiskfsstorage");
        if ($res === false) {
            $err = sprintf("Error fetching vaultdiskfsstorage.r_path from 'service=%s'.\n", $pgservice_core);
            $this->errorMessage .= $err;
            pg_close($conn);
            return false;
        }

        $pathList = array();
        while ($el = pg_fetch_assoc($res)) {
            array_push($pathList, $el['r_path']);
        }

        pg_close($conn);
        return $pathList;
    }

    private function rm_Rf($path, &$err_list)
    {
        if (!is_array($err_list)) {
            $err = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "err_list is not an array.");
            $this->errorMessage .= $err;
            $this->log(LOG_ERR, $err);
            return false;
        }
        /**
         * We use lstat() because file_exists() could return bool(false) if $path
         * is a symlink pointing to a non-existing file: in this case we do not want
         * to check the existence of the target but really want to check the existence
         * of the symlink itself.
         */
        if (@lstat($path) === false) {
            return true;
        }

        $filetype = filetype($path);
        if ($filetype === false) {
            $this->errorMessage .= sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "Could not get type for file '%s'.\n", $path);
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
                $recursive_ret = ($recursive_ret && $this->rm_Rf(sprintf("%s%s%s", $path, DIRECTORY_SEPARATOR, $file), $err_list));
            }

            $s = stat($path);
            if ($s === false) {
                $this->errorMessage .= sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "Could not stat dir '%s'.\n", $path);
                $err = sprintf("Could not stat dir '%s'.", $path);
                array_push($err_list, $err);
                $this->log(LOG_ERR, $this->errorMessage);
                return false;
            }

            if ($s['nlink'] > 2) {
                $this->errorMessage = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "Won't remove dir '%s' as it contains %s files.\n", $path, $s['nlink'] - 2);
                $err = sprintf("Won't remove dir '%s' as it contains %s files.", $path, $s['nlink'] - 2);
                array_push($err_list, $err);
                $this->log(LOG_ERR, $this->errorMessage);
                return false;
            }

            $ret = @rmdir($path);
            if ($ret === false) {
                $this->errorMessage = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "Error removing dir '%s'.\n", $path);
                $err = sprintf("Error removing dir '%s'.", $path);
                array_push($err_list, $err);
                $this->log(LOG_ERR, $this->errorMessage);
                return false;
            }

            return ($ret && $recursive_ret);
        }

        $ret = unlink($path);
        if ($ret === false) {
            $this->errorMessage = sprintf(__CLASS__ . "::" . __FUNCTION__ . " " . "Error removing file '%s' (filetype=%s).\n", $path, $filetype);
            $err = sprintf("Error removing file '%s' (filetype=%s).", $path, $filetype);
            array_push($err_list, $err);
            $this->log(LOG_ERR, $this->errorMessage);
            return false;
        }

        return $ret;
    }

    /**
     * Delete context's vault
     *
     * @return string
     */
    public function deleteContextVault()
    {
        $vaultList = $this->getContextVaultPathList($err);
        if ($vaultList === false) {
            return $err;
        }

        if (count($vaultList) <= 0) {
            return "";
        }

        $ret = true;
        $err_list = array();
        foreach ($vaultList as $vault) {
            $ret = ($ret && $this->rm_Rf($vault, $err_list));
        }

        if ($ret === false) {
            $this->errorMessage .= sprintf("Some errors occured while removing files from vaults:\n");
            $this->errorMessage .= join("\n", $err_list);
            $err = sprintf("Some errors occured while removing files from vaults:\n");
            $err .= join("\n", $err_list);
            return $err;
        }
        return "";
    }

    /**
     * Delete context's root
     *
     * @return string
     */
    public function deleteContextRoot()
    {
        $err_list = array();
        $ret = $this->rm_Rf($this->root, $err_list);

        if ($ret === false) {
            $this->errorMessage .= sprintf("Some errors occured while removing files from context root.\n");
            $this->errorMessage .= join("\n", $err_list);
            $err = sprintf("Some errors occured while removing files from context root.\n");
            $err .= join("\n", $err_list);
            return $err;
        }
        return "";
    }

    /**
     * Delete context's database content
     *
     * @param string $err
     *
     * @return bool
     */
    public function deleteContextDatabaseContent(&$err)
    {
        $pgservice_core = $this->getParamByName('core_db');

        if ($pgservice_core == "") {
            $this->errorMessage .= sprintf("Parameter 'core_db' not found or empty in context '%s'.\n", $this->name);
            return false;
        }

        $conn = pg_connect(sprintf("service=%s", $pgservice_core));
        if ($conn === false) {
            $this->errorMessage .= sprintf("Error connecting to 'service=%s'.\n", $pgservice_core);
            return false;
        }

        $res = pg_query($conn, sprintf("DROP SCHEMA IF EXISTS public CASCADE"));
        if ($res === false) {
            $this->errorMessage .= sprintf("Error dropping schema public.\n");
            $err .= sprintf("Error dropping schema public.\n");
        }
        $res = pg_query($conn, sprintf("CREATE SCHEMA public"));
        if ($res === false) {
            $this->errorMessage .= sprintf("Error re-creating schema public.\n");
            $err .= sprintf("Error dropping schema public.\n");
        }

        foreach (
            array(
                "family",
                "dav"
            ) as $schema
        ) {
            $res = pg_query($conn, sprintf("DROP SCHEMA IF EXISTS %s CASCADE", pg_escape_string($schema)));
            if ($res === false) {
                $this->errorMessage .= sprintf("Error dropping schema %s.", $schema);
                $err .= sprintf("Error dropping schema %s.", $schema);
            }
        }
        return true;
    }

    /**
     * Set context to register
     *
     * @param bool $register
     *
     * @return bool
     */
    public function setRegister($register)
    {
        require_once('class/Class.WIFF.php');
        require_once('class/Class.Repository.php');

        if (!is_bool($register)) {
            $this->errorMessage = sprintf("Argument of %s::%s should be boolean (%s given).", __CLASS__, __FUNCTION__, gettype($register));
            return false;
        }

        $wiff = WIFF::getInstance();

        $contextsXml = $wiff->loadContextsDOMDocument();
        if ($contextsXml === false) {
            $this->errorMessage = sprintf("Error loading 'contexts.xml': %s", $wiff->errorMessage);
            return false;
        }

        $contextsXPath = new DOMXPath($contextsXml);
        // Get this context
        $contextList = $contextsXPath->query("/contexts/context[@name='" . $this->name . "']");
        if ($contextList->length <= 0) {
            $this->errorMessage = sprintf("Could not get context with name '%s'.", $this->name);
            return false;
        }
        if ($contextList->length > 1) {
            $this->errorMessage = sprintf("Found more than 1 context with name '%s'.", $this->name);
            return false;
        }
        /**
         * @var DOMElement $contextNode
         */
        $contextNode = $contextList->item(0);
        $contextNode->setAttribute('register', ($register === true) ? 'registered' : 'unregistered');

        $ret = $wiff->commitDOMDocument($contextsXml);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error writing file '%s': %s", $wiff->contexts_filepath, $wiff->errorMessage);
            return false;
        }

        return true;
    }

    /**
     * Send context's configuration to anakeen server
     *
     * @return bool
     */
    public function sendConfiguration()
    {
        include_once('class/Class.StatCollector.php');

        if ($this->register != 'registered') {
            $this->errorMessage = sprintf("Context '%s' is not registered.", $this->name);
            $this->log(LOG_WARNING, __CLASS__ . "::" . __FUNCTION__ . " " . $this->errorMessage);
            return true;
        }

        $wiff = WIFF::getInstance();
        $info = $wiff->getRegistrationInfo();
        if ($info === false) {
            $this->errorMessage = sprintf("Could not get WIFF registration info.");
            return false;
        }

        $sc = new StatCollector($wiff, $this);
        $sc->collect();
        $stats = $sc->getXML();

        $rc = $wiff->getRegistrationClient();

        $res = $rc->add_context($info['mid'], $info['ctrlid'], $this->name, $info['login'], $stats);
        if ($res === false) {
            $this->errorMessage = sprintf("Error add_context request: %s", $rc->last_error);
            return false;
        }

        if ($res['code'] >= 200 && $res['code'] < 300) {
            return true;
        }

        $this->errorMessage = sprintf("Unknwon response with code '%s': %s", $res['code'], $res['response']);
        return false;
    }

    /**
     * Zip context's configuration
     *
     * @return bool
     */
    public function zipEECConfiguration()
    {
        require_once('class/Class.StatCollector.php');


        if ($this->register != 'registered') {
            $this->log(LOG_WARNING, __METHOD__ . " " . $this->errorMessage);
        }

        $wiff = WIFF::getInstance();
        $info = $wiff->getRegistrationInfo();
        if ($info === false) {
            $this->errorMessage = sprintf("Could not get WIFF registration info.");
            return false;
        }

        $sc = new StatCollector($wiff, $this);
        $sc->collect();
        $stats = $sc->getXML();

        $rc = $wiff->getRegistrationClient();
        $xml = $rc->_get_context_xml_configuration($info['mid'], $info['ctrlid'], $this->name, $info['login'], $stats);
        if ($xml === false) {
            $this->errorMessage = sprintf("Error _get_context_xml_configuration request: %s", $rc->last_error);
            return false;
        }

        $tmpZIP = Control\Internal\LibSystem::tempnam(null, 'downloadZip');
        if ($tmpZIP === false) {
            $this->errorMessage = sprintf("Error creating temporary file.");
            return false;
        }
        unlink($tmpZIP);
        $tmpZIP = sprintf("%s.zip", $tmpZIP);
        $zip = new ZipArchiveCmd();
        if ($zip->open($tmpZIP, ZipArchiveCmd::CREATE) === false) {
            $this->errorMessage = sprintf("Error opening zip file '%s' for creation: %s", $tmpZIP, $zip->getStatusString());
            unlink($tmpZIP);
            return false;
        }
        if ($zip->addFromString('configuration.xml', $xml) === false) {
            $this->errorMessage = sprintf("Error adding 'configuration.xml' to temporary ZIP file '%s': %s", $tmpZIP, $zip->getStatusString());
            unlink($tmpZIP);
            return false;
        }
        $zip->close();
        return $tmpZIP;
    }

    public function downloadZipEECConfiguration()
    {
        $zipFile = $this->zipEECConfiguration();
        if ($zipFile === false) {
            $this->downloadZipEECConfigurationError(sprintf("Error generating configuration archive: %s", $this->errorMessage));
        }
        $filename = sprintf("anakeen-context-%s-%s.zip", $this->name, date('c'));
        header("Content-Type: application/zip");
        header(sprintf("Content-Disposition: filename=%s", $filename));
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
        header(sprintf("Content-Length: %d", filesize($zipFile)));
        readfile($zipFile);
        unlink($zipFile);
        exit(0);
    }

    private function downloadZipEECConfigurationError($msg)
    {
        $this->log(LOG_ERR, $msg);
        header("HTTP/1.1 500 Error");
        header("Content-Type: text/plain");
        header(sprintf("Content-Length: %d", strlen($msg)));
        print $msg;
        exit(1);
    }

    /**
     * Delete context's registration configuration
     *
     * @return bool
     */
    public function deleteRegistrationConfiguration()
    {
        $wiff = WIFF::getInstance();

        $info = $wiff->getRegistrationInfo();
        if ($info === false) {
            $this->errorMessage = sprintf("Error getting registration info: %s", $wiff->errorMessage);
            return false;
        }

        $rc = $wiff->getRegistrationClient();

        $res = $rc->delete_context($info['mid'], $info['ctrlid'], $this->name);
        if ($res === false) {
            $this->errorMessage = sprintf("Error delete_context request: %s", $rc->last_error);
            return false;
        }

        if ($res['code'] >= 200 && $res['code'] < 300) {
            return true;
        }

        $this->errorMessage = sprintf("Unknown response with code '%s': %s", $res['code'], $res['response']);
        return false;
    }

    /**
     * Expand "@PARAM_NAME" variables in a string.
     *
     * Supported notations:
     * - "@PARAM_NAME" -> value of PARAM_NAME
     * - "@{PARAM_NAME}" -> value of PARAM_NAME
     * - "@@" -> literal "@"
     *
     * @param $str
     *
     * @return string
     */
    public function expandParamsValues($str)
    {
        return self::_expandParamsValues($str, array(
            'escape' => '@',
            'begin' => '{',
            'allow_shorthand' => true,
            'vars' => array(
                $this,
                '_expandParamsValuesHandler'
            )
        ));
    }

    /**
     * Get the value of the given parameters name
     *
     * @param string $varName parameters name to expand
     *
     * @return string the value of the parameter
     */
    private function _expandParamsValuesHandler($varName)
    {
        $staticVars = array(
            'CONTEXT_NAME' => $this->name,
        );
        if (isset($staticVars[$varName])) {
            return $staticVars[$varName];
        }
        $value = $this->getParamByName($varName);
        if ($value === false) {
            return '';
        }
        return $value;
    }

    /**
     * Generic and configurable method to expand variables in a string.
     *
     * Behaviour is configured though the $conf hash argument:
     * - 'escape' => the character that trigger variable expansion (default "@")
     * - 'begin' => the variable beginning delimiter character (default "{")
     * - 'end' => the variable ending delimiter character (default is the corresponding closing brace/paren/braquet of the 'begin' char)
     * - 'allow_shorthand' => allow var expansion without begin/end delimiters (default "false")
     * - 'vars' => an array containing ("VAR_name" => "value") associations, or a callback function
     *             that will perform the expansion
     *
     * @param string $str  the string to expand
     * @param array  $conf the config
     *
     * @return string the resulting string with expanded values
     */
    private function _expandParamsValues($str, $conf = array())
    {
        /* Config check */
        if (!isset($conf['escape'])) {
            $conf['escape'] = '@';
        }
        if (!isset($conf['begin'])) {
            $conf['begin'] = '{';
        }
        if (!isset($conf['end'])) {
            $conf['end'] = $conf['begin'];
            foreach (
                array(
                    '{}',
                    '()',
                    '[]',
                    '<>'
                ) as $t
            ) {
                if ($conf['begin'] == $t[0]) {
                    $conf['end'] = $t[1];
                    break;
                }
            }
        }
        if (!isset($conf['allow_shorthand']) || !is_bool($conf['allow_shorthand'])) {
            $conf['allow_shorthand'] = false;
        }
        if (!isset($conf['vars']) || (!is_array($conf['vars']) && !is_callable($conf['vars']))) {
            $conf['vars'] = array();
        }
        /* Parse the string */
        $tokens = preg_split('/([' . preg_quote($conf['escape'] . $conf['begin'] . $conf['end'], '/') . '])/', $str, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $stack = array();
        $var = null;
        $len = count($tokens);
        for ($i = 0; $i < $len; $i++) {
            if ($var === null) {
                if ($tokens[$i] == $conf['escape'] && $i < ($len - 1)) {
                    if ($tokens[$i + 1] == $conf['escape']) {
                        $stack[] = $conf['escape'];
                        $i++;
                    } else {
                        if ($tokens[$i + 1] == $conf['begin']) {
                            $var = '';
                            $i++;
                        } else {
                            if ($conf['allow_shorthand']) {
                                if (preg_match('/^(?<var>[a-zA-Z_][a-zA-Z0-9_]*)(?<remaining>.*)$/', $tokens[$i + 1], $m)) {
                                    $stack[] = is_callable($conf['vars']) ? call_user_func_array($conf['vars'], array(
                                        $m['var']
                                    )) : ((isset($conf['vars'][$m['var']])) ? $conf['vars'][$m['var']] : '');
                                    $tokens[$i + 1] = $m['remaining'];
                                }
                            } else {
                                $stack[] = $tokens[$i];
                            }
                        }
                    }
                } else {
                    $stack[] = $tokens[$i];
                }
            } else {
                if ($tokens[$i] == $conf['end']) {
                    $stack[] = is_callable($conf['vars']) ? call_user_func_array($conf['vars'], array(
                        $var
                    )) : ((isset($conf['vars'][$var])) ? $conf['vars'][$var] : '');
                    $var = null;
                } else {
                    $var .= $tokens[$i];
                }
            }
        }

        return join('', $stack);
    }

    /**
     * Cleanup:
     * - lingering modules in status="downloaded"
     */
    public function cleanup()
    {
        $wiff = WIFF::getInstance();
        $xml = new DOMDocument();
        $xml->load($wiff->contexts_filepath);
        $xpath = new DOMXPath($xml);
        $query = sprintf("/contexts/context[@name='%s']/modules/module[@status='downloaded']", $this->name);
        $moduleDom = $xpath->query($query);
        if ($moduleDom->length <= 0) {
            return true;
        }
        for ($i = 0; $i < $moduleDom->length; $i++) {
            $elmt = $moduleDom->item($i);
            $module = new Module($this, null, $elmt);
            $module->cleanupDownload();
            $elmt->parentNode->removeChild($elmt);
        }
        $ret = $xml->save($wiff->contexts_filepath);
        if ($ret === false) {
            $this->errorMessage = sprintf("Error saving contexts.xml '%s'.", $wiff->contexts_filepath);
            return false;
        }
        return true;
    }

    public function getAllProperties()
    {
        $res = array();
        foreach ($this->props as $pName) {
            $res[$pName] = isset($this->$pName) ? $this->$pName : null;
        }
        return $res;
    }

    public function getProperty($propName)
    {
        foreach ($this->props as $pName) {
            if ($pName == $propName) {
                return isset($this->$propName) ? $this->$propName : null;
            }
        }
        return null;
    }

    public function setProperty($propName, $propValue)
    {
        if (!in_array($propName, $this->props)) {
            return false;
        }
        $this->$propName = $propValue;
        return $this->saveProperties();
    }

    private function saveProperties()
    {
        $wiff = WIFF::getInstance();
        $xml = $wiff->loadContextsDOMDocument();
        $x = new DOMXPath($xml);
        $result = $x->query("/contexts/context[@name='" . $this->name . "']");
        if ($result->length != 1) {
            $this->errorMessage = "Multiple contexts with same name";
            return false;
        }
        /**
         * @var DOMElement $contextNode
         */
        $contextNode = $result->item(0);
        $contextNode->setAttribute('url', $this->url);

        $result = $x->query("/contexts/context[@name='" . $this->name . "']/description");
        if ($result->length > 0) {
            /**
             * @var DOMElement $description
             */
            $description = $result->item(0);
            $description->nodeValue = $this->description;
        }
        return $wiff->commitDOMDocument($xml);
    }
}

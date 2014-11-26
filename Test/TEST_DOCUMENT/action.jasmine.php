<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

function jasmine(Action & $action)
{

    $usage = new ActionUsage($action);
    $restrict = $usage->addOptionalParameter("restrict", "restrict test to only");

    $render = new \Dcp\Ui\RenderDefault();

    $version = \ApplicationParameterManager::getParameterValue("CORE", "WVERSION");

    $css = array_values($render->getCssReferences());
    $css[] = "lib/jasmine/jasmine.css?ws=" . $version;

    $require = $render->getRequireReference();

    $testWidgetLoader = array();

    if (!$restrict) {
        $testWidgetLoader = rsearch("DOCUMENT/IHM/widgets/");
    } else {
        $restrict = strtolower($restrict);
        $ucfRestrict = ucfirst($restrict);
        $file = "DOCUMENT/IHM/widgets/attributes/$restrict/test$ucfRestrict.js";
        if (is_file($file)) {
            $testWidgetLoader = array($file);
        }
    }

    $testDocumentLoader = array();

    if (!$restrict) {
        $testDocumentLoader = rsearch("DOCUMENT/IHM/test/");
    } else {
        $restrict = strtolower($restrict);
        $ucfRestrict = ucfirst($restrict);
        $file = "DOCUMENT/IHM/test/test$ucfRestrict.js";
        if (is_file($file)) {
            $testDocumentLoader = array($file);
        }
    }

    $js = array(
        $require["src"],
        $require["config"],
        'lib/KendoUI/js/jquery.js?ws=' . $version,
        'lib/jasmine/jasmine.js?ws=' . $version,
        'lib/jasmine/jasmine-html.js?ws=' . $version,
        'lib/jasmine/boot.js?ws=' . $version,
        'lib/jasmine/jasmine-jquery.js?ws=' . $version
    );

    $widgetList = array_merge($testWidgetLoader, $testDocumentLoader);

    $js = array_merge($js, $widgetList);

    $options = array(
        'cache' => DEFAULT_PUBDIR . '/var/cache/mustache',
        'cache_file_mode' => 0600,
        'cache_lambda_templates' => true
    );

    $keys = array(
        "css" => $css,
        "js" => $js,
        "ws" => $version,
        "nbTest" => count($widgetList) ,
        "icon" => "lib/jasmine/jasmine_favicon.png",
    );

    $mustacheRender = new \Mustache_Engine($options);

    $action->lay->template = $mustacheRender->render('{{=[[ ]]=}}' . file_get_contents("TEST_DOCUMENT/Layout/main.mustache") , $keys);
    $action->lay->noparse = true;
}

function rsearch($path)
{
    $directory = new \RecursiveDirectoryIterator($path, \FilesystemIterator::FOLLOW_SYMLINKS);
    $filter = new JasmineRecursiveFilterIterator($directory);
    $iterator = new \RecursiveIteratorIterator($filter);
    $files = array();
    foreach ($iterator as $info) {
        $files[] = $info->getPathname();
    }
    return $files;
}

class JasmineRecursiveFilterIterator extends \RecursiveFilterIterator
{

    public function accept()
    {
        $filename = $this->current()->getFilename();
        // Skip hidden files and directories.
        if ($filename[0] === '.') {
            return FALSE;
        }
        if ($this->isDir()) {
            // Only recurse into intended subdirectories.
            return true;
        } else {
            // Only consume files of interest.
            return preg_match('/test.*js$/', $filename) === 1;
        }
    }
}

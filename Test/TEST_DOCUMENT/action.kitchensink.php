<?php

function kitchensink(\Action &$action)
{

    $render = new \Dcp\Ui\RenderDefault();

    $version = \ApplicationParameterManager::getParameterValue("CORE", "WVERSION");

    $css = $render->getCssReferences();

    $require = $render->getRequireReference();

    $js = array(
        $require["src"],
        $require["config"],
        'TEST_DOCUMENT/Layout/kitchensink.js?wv='.$version
    );

    $loaders = rsearch("DOCUMENT/IHM/widgets/");

    $options = array(
        'cache' => DEFAULT_PUBDIR . '/var/cache/mustache',
        'cache_file_mode' => 0600,
        'cache_lambda_templates' => true
    );

    $keys = array(
        "css" => array_values($css),
        "js" => $js,
        "loaders" => json_encode($loaders),
        "ws" => $version
    );

    $mustacheRender = new \Mustache_Engine($options);

    $action->lay->template = $mustacheRender->render('{{=[[ ]]=}}' . file_get_contents("TEST_DOCUMENT/Layout/kitchensink.mustache"), $keys);
    $action->lay->noparse = true;

}

function rsearch($path)
{
    $directory = new \RecursiveDirectoryIterator($path, \FilesystemIterator::FOLLOW_SYMLINKS);
    $filter = new KitchenSinkRecursiveFilterIterator($directory);
    $iterator = new \RecursiveIteratorIterator($filter);
    $files = array();
    foreach ($iterator as $info) {
        $files[] = $info->getPathname();
    }
    return $files;
}

class KitchenSinkRecursiveFilterIterator extends \RecursiveFilterIterator
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
            return preg_match('/loader.*js$/', $filename) === 1;
        }
    }
}

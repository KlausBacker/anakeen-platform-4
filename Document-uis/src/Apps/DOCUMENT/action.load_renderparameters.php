<?php
/*
 * @author Anakeen
 * @package FDL
*/

function load_renderparameters(Action & $action)
{
    
    $usage = new ActionUsage($action);
    
    $usage->setStrictMode(false);
    $usage->verify(true);
    
    $directory = DEFAULT_PUBDIR."/Apps/DOCUMENT/customRender.d";
    var_dump($directory);
    if (is_dir($directory)) {
        
        $directoryIterator = new DirectoryIterator($directory);
        $jsonIterator = new RegexIterator($directoryIterator, "/.*\.json$/");
        $jsonList = array();
        foreach ($jsonIterator as $currentFile) {
            $jsonList[] = $currentFile->getPathName();
        }
        sort($jsonList);
        $rules = array();
        foreach ($jsonList as $currentPath) {
            $currentRule = file_get_contents($currentPath);
            $currentRule = json_decode($currentRule, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Unable to read and decode " . $currentPath);
            }
            $rules = array_replace_recursive($rules, $currentRule);
        }
        
        ApplicationParameterManager::setParameterValue(ApplicationParameterManager::CURRENT_APPLICATION, "RENDER_PARAMETERS", json_encode($rules));
        $action->lay->template = json_encode($rules, JSON_PRETTY_PRINT);
    } else {
        $action->lay->template = sprintf("No found directory : %s", $directory);
    }
    $action->lay->noparse = true;
}

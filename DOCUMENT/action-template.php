<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

function template(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setText("Get template document");
    
    $renderId = $usage->addOptionalParameter("render", "render identifier", array() , "defaultView");
    $templatePart = $usage->addOptionalParameter("part", "templatePart", function ($values, $argName, ApiUsage $apiusage)
    {
        $opt = array(
            "attributes",
            "custom",
            "body",
            "sections"
        );
        if ($values === ApiUsage::GET_USAGE) return sprintf(" [%s] ", implode('|', $opt));
        
        $error = $apiusage->matchValues($values, $opt);
        if ($error) {
            $apiusage->exitError(sprintf("Error: wrong value for argument 'templatePart' : %s", $error));
        }
        return '';
    });
    
    $usage->verify();
    
    if (!$templatePart) {
        $templatePart = array(
            "attributes",
            "custom",
            "body",
            "sections"
        );
    } elseif (!is_array($templatePart)) {
        $templatePart = array(
            $templatePart
        );
    }
    
    $doc = new \Doc();
    
    $dr = new Dcp\Ui\DocumentTemplateRender();
    
    $config = Dcp\Ui\Utils::getRenderConfigObject($renderId);
    
    $dr->loadConfiguration($config);
    $action->lay->template = $dr->render($doc);
    $action->lay->noparse = true;
    header('Content-Type: application/json');
}

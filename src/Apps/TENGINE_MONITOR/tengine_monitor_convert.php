<?php
/*
 * @author Anakeen
 * @package FDL
*/

function tengine_monitor_convert(Action & $action)
{
    require_once "FDL/editutil.php";
    
    $usage = new ActionUsage($action);
    $op = $usage->addOptionalPArameter('op', 'Operation');
    $engine = $usage->addOptionalParameter('engine', 'Conversion engine name');
    $file = $usage->addOptionalParameter('file', 'File to convert');
    $tid = $usage->addOptionalParameter('tid', 'Task id');
    $usage->verify(true);
    
    editmode($action);
    switch ($op) {
        case '':
            $action->parent->AddCssRef("css/dcp/jquery-ui.css");
            $action->parent->AddJsRef("legacy/jquery-ui/js/jquery-ui.js");
            $action->parent->AddJsRef("TENGINE_MONITOR:tengine_monitor.js", true);
            $action->parent->AddJsRef("TENGINE_MONITOR:tengine_monitor_convert.js", true);
            $action->parent->AddCssRef("TENGINE_CLIENT:tengine_client.css", true);
            $action->parent->AddCssRef("TENGINE_MONITOR:tengine_monitor_convert.min.css", true);
            $action->lay->eSet('HTML_LANG', str_replace('_', '-', getParam('CORE_LANG', 'fr_FR')));
            $action->lay->eSet('ACTIONNAME', strtoupper($action->name));
            break;

        case 'engines':
            $response = _getEngines($action, $file, $engine);
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
            break;

        case 'convert':
            $response = _convert($action, $file, $engine);
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
            break;

        case 'info':
            $response = _info($action, $tid);
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
            break;

        case 'get':
            $response = _get($action, $tid);
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
            break;

        default:
             $err = sprintf(_("TE:Monitor:Unknown op '%s'.") , $op);
   }
}

function _uploadErrorConst($errorCode)
{
    foreach (get_defined_constants() as $const => $code) {
        if (strpos($const, 'UPLOAD_ERR_') === 0) {
            return $const;
        }
    }
    return $errorCode;
}

function _getEngines(Action & $action)
{
    $response = array(
        "success" => false,
        "message" => "Unknow error",
        "info" => null
    );
    $err = \Dcp\TransformationEngine\Manager::checkParameters();
    if ($err != '') {
        $response['success'] = false;
        $response['message'] = $err;
    } else {
        $te = new \Dcp\TransformationEngine\Client($action->getParam("TE_HOST") , $action->getParam("TE_PORT"));
        $err = $te->retrieveEngines($engines);
        if ($err != '') {
            $response['success'] = false;
            $response['message'] = $err;
        } else {
            
            $enginesList = array();
            foreach ($engines as $engine) {
                if (!isset($enginesList[$engine['name']])) {
                    $enginesList[$engine['name']]['mimes'] = array();
                }
                $enginesList[$engine['name']]['mimes'][] = $engine['mime'];
            }
            $response['success'] = true;
            $response['message'] = "";
            $response['info'] = $enginesList;
        }
    }
    return $response;
}

function _convert(Action & $action, $filename, $engineName)
{
    $response = array(
        "success" => true,
        "message" => "",
        "info" => null
    );
    $err = \Dcp\TransformationEngine\Manager::checkParameters();
    if ($err != '') {
        $response['success'] = false;
        $response['message'] = $err;
        return $response;
    }
    global $_FILES;
    if (!isset($_FILES['file'])) {
        $response['success'] = false;
        $response['message'] = sprintf(_("TE:Monitor:Convert:Missing file."));
        return $response;
    }
    if (isset($_FILES['file']['error']) && $_FILES['file']['error'] != UPLOAD_ERR_OK) {
        $response['success'] = false;
        $response['message'] = sprintf(_("TE:Monitor:Convert:Error in file upload: %s") , _uploadErrorConst($_FILES['file']['error']));
        return $response;
    }
    if (($tmpfile = tempnam(getTmpDir() , '_convert_')) === false) {
        $response['success'] = false;
        $response['message'] = sprintf(_("TE:Monitor:Convert:Error creating temporary file."));
        return $response;
    }
    if (move_uploaded_file($_FILES['file']['tmp_name'], $tmpfile) === false) {
        $response['success'] = false;
        $response['message'] = sprintf(_("TE:Monitor:Convert:Error moving uploaded file to '%s'.") , getTmpDir());
        return $response;
    }
    $te = new \Dcp\TransformationEngine\Client($action->getParam("TE_HOST") , $action->getParam("TE_PORT"));
    $taskInfo = array();
    $err = $te->sendTransformation($engineName, basename($tmpfile) , $tmpfile, '', $taskInfo);
    $response['success'] = ($err != "" ? false : true);
    $response['message'] = $err;
    $response['info'] = $taskInfo;
    return $response;
}

function _info(Action & $action, $tid)
{
    $response = array(
        "success" => true,
        "message" => "",
        "info" => null
    );
    $err = \Dcp\TransformationEngine\Manager::checkParameters();
    if ($err != '') {
        $response['success'] = false;
        $response['message'] = $err;
        return $response;
    }
    $te = new \Dcp\TransformationEngine\Client($action->getParam("TE_HOST") , $action->getParam("TE_PORT"));
    $info = array();
    $err = $te->getInfo($tid, $info);
    if ($err != '') {
        $response['success'] = false;
        $response['message'] = sprintf(_("TE:Monitor:Convert:Error fetching task info with id '%s': %s") , $tid, $err);
        return $response;
    }
    $response['info'] = $info;
    return $response;
}

function _get(Action & $action, $tid)
{
    $response = array(
        "success" => true,
        "message" => "",
        "info" => null
    );
    require_once ('WHAT/Lib.FileMime.php');
    $err = \Dcp\TransformationEngine\Manager::checkParameters();
    if ($err != '') {
        $response['success'] = false;
        $response['message'] = $err;
        return $response;
    }
    $te = new \Dcp\TransformationEngine\Client($action->getParam("TE_HOST") , $action->getParam("TE_PORT"));
    $info = array();
    $err = $te->getInfo($tid, $info);
    if ($err != '') {
        $response['success'] = false;
        $response['message'] = sprintf(_("TE:Monitor:Convert:Error fetching task info with id '%s': %s") , $tid, $err);
        return $response;
    }
    if ($info['status'] !== 'D') {
        $response['success'] = false;
        $response['message'] = sprintf(_("TE:Monitor:Convert:Task '%s' is not finished.") , $tid);
        return $response;
    }
    $tmpfile = tempnam(getTmpDir() , '_get_');
    if ($tmpfile === false) {
        $response['success'] = false;
        $response['message'] = sprintf(_("TE:Monitor:Convert:Error creating temporary file."));
        return $response;
    }
    $te->getTransformation($tid, $tmpfile);
    if (filesize($tmpfile) <= 0) {
        unlink($tmpfile);
        $response['success'] = false;
        $response['message'] = sprintf(_("TE:Monitor:Convert:File is empty."));
        return $response;
    }
    
    $mimeType = getSysMimeFile($tmpfile);
    $ext = getExtension($mimeType);
    if ($ext == '') {
        $ext = 'bin';
    }
    Http_DownloadFile($tmpfile, sprintf('convert.%s', $ext) , $mimeType, false, true, true);
    exit;
}


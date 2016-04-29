<?php
/*
 * @author Anakeen
 * @package FDL
*/

require_once "FDL/freedom_util.php";

/**
 * Return a template
 *
 * BEWARE : use of etag for cache (based on package version)
 *
 * @param Action $action
 */
function template(Action & $action)
{
    $etag = generateEtag();
    $useCache = verifiyEtag($etag);
    if ($useCache) {
        generateResponseHeader($etag, $useCache);
        $action->lay->template = '';
        $action->lay->noparse = true;
        return;
    }

    $usage = new ActionUsage($action);
    $usage->setText("Get template document");

    $renderId = $usage->addOptionalParameter("render", "render identifier", array() , '\\Dcp\\ui\\RenderDefault');
    $templatePart = $usage->addOptionalParameter("part", "templatePart");
    $templateSubPart = $usage->addOptionalParameter("subPart", "templateSubPart");
    $content = array(
        "success" => true
    );

    try {
        $usage->setStrictMode(false);
        $usage->verify(true);

        $options = array(
            'cache' => DEFAULT_PUBDIR . '/var/cache/mustache',
            'cache_file_mode' => 0600,
            'cache_lambda_templates' => true
        );

        $renderConfig = new $renderId();
        /* @var \Dcp\ui\RenderDefault $renderConfig */
        if (!is_a($renderConfig, "\\Dcp\\ui\\RenderDefault")) {
            throw new \Exception("Bad type of render $renderId");
        }
        $templates =  $renderConfig->getTemplates();
        if (!empty($templatePart)) {
            if (!isset($templates[$templatePart])) {
                throw new \Exception("Unknown type of template part $templatePart");
            }
            $templates = $templates[$templatePart];
        }
        if (!empty($templateSubPart)) {
            if (!isset($templates[$templateSubPart])) {
                throw new \Exception("Unknown type of template part $templatePart");
            }
            $templates = $templates[$templateSubPart];
        }
        $mustacheRender = new \Mustache_Engine($options);
        $mustacheLoader = new \Dcp\Ui\MustacheLoaderSection($templates);
        $mustacheRender->setPartialsLoader($mustacheLoader);
        $content["content"] = json_decode($mustacheRender->render("{{>templates}}"), true);
        //Translation
        $content["options"] = $renderConfig->getOptions(new_Doc(getDbAccess()));
    } catch(Exception $e) {
        $content["success"] = false;
        $content["error"] = $e->getMessage();
    }

    $action->lay->template = json_encode($content);
    $action->lay->noparse = true;
    generateResponseHeader($etag, $useCache);
}


/**
 * Generate an etag for the static data
 *
 * The etag is computed with application version
 *
 * @return string
 */
function generateEtag()
{
    $etag = ApplicationParameterManager::getParameterValue(ApplicationParameterManager::CURRENT_APPLICATION, "VERSION");
    return sha1($etag);
}

/**
 * Verify the etag validity against the If-None-Match header
 * @param $etag
 * @return bool
 */
function verifiyEtag($etag)
{
    $headers = apache_request_headers();
    if (isset($headers["If-None-Match"])) {
        return $headers["If-None-Match"] === $etag;
    } else {
        return false;
    }
}

/**
 * Generate the header for the static response
 *
 * @param $etag
 * @param $useCache
 */
function generateResponseHeader($etag, $useCache)
{
    ini_set('session.cache_limiter', 'none');
    header('Content-Type: application/json');
    header("Cache-Control: private");
    header("Content-Disposition: inline;");
    header('ETag: ' . $etag);
    if ($useCache) {
        header('HTTP/1.1 304 Not Modified');
        header('Connection: close');
    }
}
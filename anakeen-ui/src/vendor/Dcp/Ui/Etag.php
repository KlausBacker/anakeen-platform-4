<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 03/09/14
 * Time: 10:19
 */

namespace Dcp\Ui;

class Etags
{

    private $content_type = null;

    public function __construct($content_type = "application/json")
    {
        $this->content_type = $content_type;
    }

    /**
     * Generate an etag for the static data
     *
     * The etag is computed with application version and the locale of the current user
     *
     * @param string
     *
     * @return string
     */
    function generateEtag($etag = "")
    {
        if (!$etag) {
            $etag = \Anakeen\Core\Internal\ApplicationParameterManager::getParameterValue(\Anakeen\Core\Internal\ApplicationParameterManager::CURRENT_APPLICATION, "VERSION");
            $etag .= \Anakeen\Core\Internal\ApplicationParameterManager::getParameterValue("CORE", "CORE_LANG");
        }
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
        header('Content-Type: ' . $this->content_type);
        header("Cache-Control: private");
        header("Content-Disposition: inline;");
        header('ETag: ' . $etag);
        if ($useCache) {
            header('HTTP/1.1 304 Not Modified');
            header('Connection: close');
        }
    }
}
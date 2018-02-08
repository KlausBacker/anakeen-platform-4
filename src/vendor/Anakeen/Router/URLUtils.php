<?php


namespace Anakeen\Router;

class URLUtils
{
    public static function getBaseURL()
    {
        $coreURL = \ApplicationParameterManager::getScopedParameterValue("CORE_URLINDEX");
        $components = parse_url($coreURL);

        if ($coreURL) {
            if (isset($components["query"])) {
                unset($components["query"]);
            }
            if (isset($components["fragment"])) {
                unset($components["fragment"]);
            }
            $coreURL = static::unparseURL($components);
        } else {
            $coreURL = self::getUrlPath();
        }

        return $coreURL;
    }

    public static function generateUrl($path)
    {
        return static::stripUrlSlahes(sprintf("%s/%s", static::getBaseURL(), $path));
    }

    /**
     * Delete double slashes in url path
     *
     * @param string $url
     *
     * @return string
     */
    public static function stripUrlSlahes($url)
    {
        $pos = mb_strpos($url, '://');
        return mb_substr($url, 0, $pos + 3) . preg_replace('/\/+/u', '/', mb_substr($url, $pos + 3));
    }

    protected static function getUrlPath()
    {
        $turl = @parse_url($_SERVER["REQUEST_URI"]);
        if ($turl['path']) {
            $scriptDirName = pathinfo($_SERVER["SCRIPT_FILENAME"], PATHINFO_DIRNAME);
            if (strpos($scriptDirName, DEFAULT_PUBDIR) === 0) {
                $relativeBaseFilePath = substr($scriptDirName, strlen(DEFAULT_PUBDIR));
                $script = $_SERVER["SCRIPT_NAME"];
                if ($relativeBaseFilePath) {
                    $pos = strpos($script, $relativeBaseFilePath);
                    $localPath = substr($script, 0, $pos) . '/';
                } else {
                    $localPath = dirname($script) . '/';
                }
            } else {
                if (substr($turl['path'], -1) != '/') {
                    $localPath = dirname($turl['path']) . '/';
                } else {
                    $localPath = $turl['path'];
                }
            }
            $localPath = preg_replace(':/+:', '/', $localPath);

            return $localPath;
        }
        return '/';
    }

    protected static function unparseURL($parsed_url)
    {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}

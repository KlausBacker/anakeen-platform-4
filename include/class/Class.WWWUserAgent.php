<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package CONTROL
*/

namespace WWW;

require_once 'class/Class.WIFF.php';
require_once 'lib/Lib.System.php';

class UserAgent
{
    const UA_NAME = 'dynacase-control';
    protected $ua_string = null;
    /**
     * @var null|Cache
     */
    protected static $cache = null;
    
    public $errorMessage = '';
    
    public function __construct($opts = array())
    {
        $this->setupUAString();
        $this->setupCache($opts);
    }
    
    protected function setupUAString()
    {
        require_once 'class/Class.WIFF.php';
        $wiff = \WIFF::getInstance();
        $this->ua_string = sprintf('%s/%s', self::UA_NAME, $wiff->getVersion());
    }
    
    private function setupCache($opts = array())
    {
        if (!isset(self::$cache)) {
            if (isset($opts['use-cache']) && $opts['use-cache'] === true) {
                self::$cache = new DefaultCache();
            } else {
                self::$cache = new NoCache();
            }
        }
    }
    
    public function downloadUrl($url, $opts = array())
    {
        if (preg_match('/^https?:/i', $url)) {
            return $this->downloadHttpUrl($url, $opts);
        } else if (preg_match('/^ftp:/i', $url)) {
            return $this->downloadFtpUrl($url, $opts);
        } else {
            // treat url as a pathname to a local file
            return $this->downloadLocalFile($url, $opts);
        }
    }
    public function downloadHttpUrl($url, $opts = array())
    {
        return $this->downloadHttpUrlCurl($url, $opts);
    }
    
    public function downloadFtpUrl($url, $opts = array())
    {
        return $this->downloadHttpUrlCurl($url, $opts);
    }
    
    public function downloadLocalFile($url, $opts = array())
    {
        
        $tmpfile = \WiffLibSystem::tempnam(null, 'WIFF_downloadLocalFile');
        if ($tmpfile === false) {
            $this->errorMessage = sprintf("Error creating temporary file.");
            return false;
        }
        
        $ret = copy($url, $tmpfile);
        if ($ret === false) {
            $this->errorMessage = sprintf(__METHOD__ . " " . "Error copying file '%s' to '%s'.", $url, $tmpfile);
            return false;
        }
        
        $this->errorMessage = "";
        return $tmpfile;
    }
    
    public function downloadHttpUrlCurl($url, $opts = array())
    {
        $file = self::$cache->get($url);
        if ($file !== false) {
            return $file;
        }
        
        $wiff = \WIFF::getInstance();
        
        $tmpfile = \WiffLibSystem::tempnam(null, 'WIFF_downloadHttpUrlCurl');
        if ($tmpfile === false) {
            $this->errorMessage = sprintf("Error creating temporary file.");
            return false;
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        if ($this->ua_string !== null) {
            curl_setopt($ch, CURLOPT_USERAGENT, $this->ua_string);
        }
        /* Setup output file */
        $ftmp = fopen($tmpfile, 'w');
        if ($ftmp === false) {
            $this->errorMessage = sprintf("Error opening temporary file '%s' for writing.", $tmpfile);
            return false;
        }
        curl_setopt($ch, CURLOPT_FILE, $ftmp);
        /* Setup proxy */
        if ($wiff->getParam('use-proxy') === 'yes') {
            $http_proxy = '';
            $proxy_host = $wiff->getParam('proxy-host');
            if ($proxy_host !== false && $proxy_host != '') {
                $http_proxy = "http://" . $proxy_host;
                $proxy_port = $wiff->getParam('proxy-port');
                if ($proxy_port !== false && $proxy_port != '') {
                    $http_proxy.= ":" . $proxy_port;
                }
            }
            curl_setopt($ch, CURLOPT_PROXY, $http_proxy);
        }
        /* Setup proxy auth */
        $proxy_username = $wiff->getParam('proxy-username');
        if ($proxy_username !== false && $proxy_username != '') {
            curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            
            $proxy_password = $wiff->getParam('proxy-password');
            if ($proxy_password !== false && $proxy_password != '') {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, sprintf('%s:%s', $proxy_username, $proxy_password));
            } else {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_username);
            }
        }
        /* Setup timeout/retries with same/similar defaults as wget */
        if (isset($opts['timeout'])) {
            /*
             * With wget, each timeout (connect and read) can be set to a
             * specific value, but curl only support a connect timeout and
             * a "general" timeout that includes the time taken by the connect
             * + the time of the read.
            */
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, intval($opts['timeout']));
            curl_setopt($ch, CURLOPT_TIMEOUT, 2 * intval($opts['timeout']));
        }
        $retry = 20;
        $waitretry = 10;
        if (isset($opts['tries']) && $opts['tries'] > 0) {
            $retry = $opts['tries'];
        }
        if (isset($opts['waitretry']) && $opts['waitretry'] > 0) {
            $waitretry = $opts['waitretry'];
        }
        /* Fetch the URL */
        $wait = 0;
        while ($retry >= 0) {
            ftruncate($ftmp, 0);
            rewind($ftmp);
            curl_exec($ch);
            $errno = curl_errno($ch);
            if ($errno) {
                $error = curl_error($ch);
                if ($retry > 0) {
                    $retry--;
                    $wait = ($wait + 1 > $waitretry) ? $wait : $wait + 1;
                    $this->log(LOG_INFO, __METHOD__ . " " . sprintf("Notice: got error (%s) '%s' while fetching '%s'. Retrying %s in %s second(s)...", $errno, $error, $this->anonymizeUrl($url) , $retry, $wait));
                    sleep($wait);
                    continue;
                }
                curl_close($ch);
                fclose($ftmp);
                unlink($tmpfile);
                $this->errorMessage = sprintf("Error fetching '%s': %s", \WIFF::anonymizeUrl($url) , $error);
                return false;
            }
            $code = 0;
            if (!$this->isCurlHttpCodeOk($ch, $code)) {
                if ($code != 404 && $retry > 0) {
                    $retry--;
                    $wait = ($wait + 1 > $waitretry) ? $wait : $wait + 1;
                    $this->log(LOG_INFO, __METHOD__ . " " . sprintf("Notice: got HTTP status code '%s' fetching '%s'. Retrying %s in %s second(s)...", $code, $this->anonymizeUrl($url) , $retry, $wait));
                    sleep($wait);
                    continue;
                }
                curl_close($ch);
                fclose($ftmp);
                $content = file_get_contents($tmpfile);
                if ($content === false) {
                    $content = '<Could not get content>';
                }
                unlink($tmpfile);
                $this->errorMessage = sprintf("HTTP Error fetching '%s': HTTP status = '%s' / Content = '%s'", \WIFF::anonymizeUrl($url) , $code, $content);
                
                return false;
            }
            break;
        }
        
        curl_close($ch);
        fclose($ftmp);
        self::$cache->put($url, $tmpfile);
        return $tmpfile;
    }
    
    private function isCurlHttpCodeOk($ch, &$code)
    {
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $scheme = strtolower($this->getUrlElement($url, 'scheme'));
        /* Treat all FTP's 2xx codes as OK */
        if ($scheme == 'ftp' && intval($code / 100) == 2) {
            return true;
        }
        return ($code == 200);
    }
    
    private function getUrlElement($url, $elmt)
    {
        $tokens = parse_url($url);
        return ($tokens !== false && isset($tokens[$elmt])) ? $tokens[$elmt] : '';
    }
    
    public static function anonymizeUrl($url)
    {
        $u = parse_url($url);
        if ($u === false) {
            return $url;
        }
        $url = '';
        if (isset($u['scheme'])) {
            $url = sprintf('%s://', $u['scheme']);
        }
        if (isset($u['user'])) {
            $url.= sprintf('%s:***@', $u['user']);
        }
        if (isset($u['host'])) {
            $url.= $u['host'];
        }
        if (isset($u['port'])) {
            $url.= sprintf(':%s', $u['port']);
        }
        if (isset($u['path'])) {
            $url.= $u['path'];
        } else {
            $url.= '/';
        }
        if (isset($u['query'])) {
            $url.= sprintf('?%s', $u['query']);
        }
        if (isset($u['fragment'])) {
            $url.= sprintf('#%s', $u['fragment']);
        }
        return $url;
    }

    private function log($pri, $msg) {
        require_once 'class/Class.WIFF.php';
        $wiff = \WIFF::getInstance();
        $wiff->log($pri, $msg);
    }
}

class CacheException extends \Exception
{
}

interface Cache
{
    public function put($url, $file, $expires = - 1);
    public function get($url);
    public function remove($url);
}

class DefaultCache implements Cache
{
    /**
     * @var CacheItem[]
     */
    protected $cache = array();
    
    public function put($url, $file, $expires = - 1)
    {
        try {
            $cachedItem = new CacheItem($url, $file, $expires);
            $this->cache[$url] = $cachedItem;
        }
        catch(CacheException $e) {
            $this->log(LOG_ERR, __METHOD__ . " " . $e->getMessage());
            return false;
        }
        return true;
    }
    
    public function get($url)
    {
        if (!isset($this->cache[$url])) {
            return false;
        }
        $item = $this->cache[$url];
        if ($item->expires >= 0 && $item->expires < time()) {
            $this->remove($url);
            return false;
        }
        if (!file_exists($item->file)) {
            $this->remove($url);
            return false;
        }
        if (!is_readable($item->file)) {
            $this->remove($url);
            return false;
        }
        $tmpfile = \WiffLibSystem::tempnam(null, 'www_get.XXXXXX');
        if ($tmpfile === false) {
            $this->log(LOG_ERR, __METHOD__ . " " . sprintf("Error creating temporary file."));
            return false;
        }
        if (copy($item->file, $tmpfile) === false) {
            unlink($tmpfile);
            $this->log(LOG_ERR, __METHOD__ . " " . sprintf("Error copying cached file '%s' to '%s'.", $item->file, $tmpfile));
            return false;
        }
        return $tmpfile;
    }
    
    public function remove($url)
    {
        if (isset($this->cache[$url])) {
            unset($this->cache[$url]);
        }
        return true;
    }
    
    public function __destruct()
    {
        foreach ($this->cache as $url => $item) {
            if (file_exists($item->file)) {
                unlink($item->file);
            }
        }
    }

    private function log($pri, $msg) {
        require_once 'class/Class.WIFF.php';
        $wiff = \WIFF::getInstance();
        $wiff->log($pri, $msg);
    }
}

class NoCache implements Cache
{
    public function put($url, $file, $expires = - 1)
    {
        return true;
    }
    public function get($url)
    {
        return false;
    }
    public function remove($url)
    {
        return true;
    }
}

class CacheItem
{
    public $url = '';
    public $file = '';
    public $expires = - 1;
    public function __construct($url, $file, $expires)
    {
        $cacheFile = \WiffLibSystem::tempnam(null, 'www_cache.XXXXXX');
        if ($cacheFile === false) {
            throw new CacheException(sprintf("Error creating temporary cache file."));
        }
        if (copy($file, $cacheFile) === false) {
            throw new CacheException(sprintf("Error copying file into cached file."));
        }
        $this->url = $url;
        $this->file = $cacheFile;
        $this->expires = $expires;
    }
}

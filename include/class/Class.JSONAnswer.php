<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * JSONAnswer Class
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

require_once 'class/Class.WIFF.php';

class JSONAnswer
{
    public $error;
    public $data;
    public $success;
    
    public function __construct($data = null, $error = null, $success = true, $warnings = array())
    {
        if (is_scalar($warnings) && $warnings == '') {
            $warnings = array();
        } else if (!is_array($warnings)) {
            $warnings = array($warnings);
        }
        $this->data = $data;
        $this->error = $error;
        $this->success = $success;
        $this->warnings = $warnings;
    }
    
    public function encode()
    {
        $wiff = WIFF::getInstance();
        if ($this->error != '') {
            if ($this->success) {
                $wiff->log(LOG_INFO, $this->error);
            } else {
                $wiff->log(LOG_ERR, $this->error);
            }
        }
        foreach ($this->warnings as $warn) {
            $wiff->log(LOG_WARNING, $warn);
        }
        return json_encode($this, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP);
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 03/10/14
 * Time: 18:12
 */

namespace Dcp\UI\Logger\JS;


class Dcp extends Logger {

    public function __construct() {
        $this->logger = new \Log(false, "CLIENT", "JS");
    }

    public function writeError($message, $context, $stack) {
        $stack = preg_replace('!\s+!', ' ', $stack);
        $logMessage = sprintf("## Message : %s ## Context : %s ## Stack : %s", $message, $context, $stack);
        $this->logger->error($logMessage);
        error_log($logMessage);
    }

} 
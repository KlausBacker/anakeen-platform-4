<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

interface IRenderConfigAccess {
    /**
     * @param string $mode
     * @return IRenderConfig
     */
    public function getRenderConfig($mode, \Doc $document);
}

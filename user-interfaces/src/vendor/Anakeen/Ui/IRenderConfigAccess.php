<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

interface IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return IRenderConfig
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document);
}

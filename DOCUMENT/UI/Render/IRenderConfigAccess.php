<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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

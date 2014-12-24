<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class TabRenderOptions extends CommonRenderOptions
{
    
    const type = "tab";
    const openFirstOption = "openFirst";
    /**
     * Open this tab on render first
     *
     * Can be use only with specific tab attribute
     * @param bool $openIt open it
     * @return $this
     */
    public function setOpenFirst($openIt = true)
    {
        return $this->setOption(self::openFirstOption, (bool)$openIt);
    }
}

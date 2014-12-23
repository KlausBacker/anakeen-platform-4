<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class ArrayRenderOptions extends CommonRenderOptions
{
    
    const type = "array";

    const rowCountThresholdOption = "rowCountThreshold";
    /**
     * Display row count if row number is greater than $since
     * @param int $since : limit to see row numbers (if zero always see count) if (-1) never see count
     * @return $this
     */
    public function setRowCountThreshold($since)
    {
        return $this->setOption(self::rowCountThresholdOption, (int)$since);
    }
}

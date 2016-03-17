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
    const rowAddDisableOption = "rowAddDisable";
    const rowDelDisableOption = "rowDelDisable";
    const rowMoveDisableOption = "rowMoveDisable";
    const rowMinLimitOption = "rowMinLimit";
    const rowMinDefaultOption = "rowMinDefault";
    const rowMaxLimitOption = "rowMaxLimit";
    const arrayBreakPointsOption = "arrayBreakPoints";
    
    const transpositionRule = "@media only screen and (max-width: 768px),(min-device-width : 768px) and (max-device-width : 1024px) and (orientation : portrait)";
    const upRule = "@media (max-width: 1280px)";
    /**
     * Display row count if row number is greater than $since
     * @param int $since : limit to see row numbers (if zero always see count) if (-1) never see count
     * @return $this
     */
    public function setRowCountThreshold($since)
    {
        return $this->setOption(self::rowCountThresholdOption, (int)$since);
    }
    /**
     * Disable or enable the access to add new row on a table
     * It is enable by default
     * @param bool $disable : true disable, false enable
     * @return $this
     */
    public function disableRowAdd($disable)
    {
        return $this->setOption(self::rowAddDisableOption, (bool)$disable);
    }
    /**
     * Disable or enable the access to remove row on a table
     * It is enable by default
     * @param bool $disable : true disable, false enable
     * @return $this
     */
    public function disableRowDel($disable)
    {
        return $this->setOption(self::rowDelDisableOption, (bool)$disable);
    }
    /**
     * Disable or enable the access to move row on a table
     * It is enable by default
     * @param bool $disable : true disable, false enable
     * @return $this
     */
    public function disableRowMove($disable)
    {
        return $this->setOption(self::rowMoveDisableOption, (bool)$disable);
    }
    /**
     * Set min row to the table
     * If array has not the min, empty rows are added since reach limit
     * The remove button is disabled when limit is reach
     * No limit by default
     * @param int $limit : min limit, negative (like -1) means no limits
     * @return $this
     */
    public function setRowMinLimit($limit)
    {
        return $this->setOption(self::rowMinLimitOption, (int)$limit);
    }


    /**
     * Set min row displayed for the array
     * If array has not the min, empty rows are added since reach limit
     * The remove button is NOT disabled when default limit is reach unlike the setRowMinLimit option
     * No min by default
     * @param int $default : min default, 0 (or negative) means no default
     * @see setRowMinLimit
     * @return $this
     */
    public function setRowMinDefault($default)
    {
        return $this->setOption(self::rowMinDefaultOption, (int)$default);
    }

    /**
     * Set max row to the table
     * The add button is disabled when limit is reach
     * No limit by default
     * @param int $limit : min limit, negative (like -1) means no limits
     * @return $this
     */
    public function setRowMaxLimit($limit)
    {
        return $this->setOption(self::rowMaxLimitOption, (int)$limit);
    }
    
    public function setResponsiveBreakpoints($transposition = self::transpositionRule, $labelUp = self::upRule)
    {
        
        return $this->setOption(self::arrayBreakPointsOption, array(
            "transpositionRule" => $transposition,
            "upRule" => $labelUp
        ));
    }
}

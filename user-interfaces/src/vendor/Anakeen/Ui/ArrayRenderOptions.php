<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

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
    const transpositionWidthOption = "transpositionWidth";


    const collapseOption = "collapse";
    const collapseNone = "none";
    const collapseExpanded = "expand";
    const collapseCollapsed = "collapse";

    /**
     * Expand / Collapse frame content
     *
     * @param bool $expand false to collapse, true (default) to expand
     *
     * @return $this
     * @throws Exception
     */
    public function setCollapse($expand)
    {
        $allow = array(
            self::collapseNone,
            self::collapseExpanded,
            self::collapseCollapsed
        );
        if (!in_array($expand, $allow)) {
            throw new Exception("UI0214", $expand, implode(', ', $allow));
        }
        return $this->setOption(self::collapseOption, $expand);
    }

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
     * @return $this
     * @see setRowMinLimit
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

    /**
     * If table width is less than the limit the table is displayed with a transposition
     * @param string $transpositionWidth the max width to not transpose table
     * @return ArrayRenderOptions
     */
    public function setTranspositionWidthLimit(string $transpositionWidth)
    {
        return $this->setOption(self::transpositionWidthOption, $transpositionWidth);
    }
    /**
     * Add an html text near the frame
     *
     * @param string $htmlTitle   Html text short description
     * @param string $position    position : top, bottom, bottomLabel, click
     *
     * @param string $htmlContent Html text long description
     * @param bool   $collapsed   if true the long description is collapsed (need click to see it)
     *
     * @return $this
     * @throws Exception
     */
    public function setDescription($htmlTitle, $position = "top", $htmlContent = "", $collapsed = false)
    {
        $allow = array(
            self::topPosition,
            self::bottomPosition,
            self::bottomLabelPosition,
            self::clickPosition
        );
        if (!in_array($position, $allow)) {
            throw new Exception("UI0218", $position, implode(', ', $allow));
        }

        parent::setDescription($htmlTitle, $position, $htmlContent, $collapsed);
        return $this;
    }
}

<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class DocumentRenderOptions extends BaseRenderOptions
{
    const type = "document";
    const openFirstTabOption = "openFirstTab";
    const tabTooltipLabel = "tooltipLabel";
    const tabTooltipHtml = "tooltipHtml";
    const tabPlacementOption = "tabPlacement";
    const tabLeftPlacement = "left";
    const tabTopProportionalPlacement = "topProportional";
    const tabTopScrollPlacement = "top"; // fix width and horizontal scroll
    const stickyTabsOptions = "stickyTabs";

    protected $scope = "document";
    /**
     * add custom option to be propagated to client
     * @param string $optName option name
     * @param string $optValue option value
     * @return $this
     */
    public function setOption($optName, $optValue)
    {
        if ($this->optionObject) {
            $this->optionObject->setScopeOption($this->scope, $optName, $optValue);
        } else {
            return parent::setOption($optName, $optValue);
        }
        return $this;
    }
    
    public function getOption($optName)
    {
        if ($this->optionObject) {
            return $this->optionObject->getScopeOption($this->scope, $optName);
        }
        return null;
    }
    /**
     * Open a specific tab when render document
     *
     * Always open this tab if visible else first tab is opened
     * @param bool $openIt open it
     * @return $this
     */
    public function setOpenFirstTab($tabId)
    {
        return $this->setOption(self::openFirstTabOption, $tabId);
    }
    /**
     * Placement of tab labels
     *
     * @note The value cannot be apply to a particular tab but for all tabs
     *
     * @param string $tabPlacement top (default) or right
     *
     * @throws Exception UI0107
     * @return $this
     */
    public function setTabPlacement($tabPlacement)
    {
        $allowPlacement = array(
            self::tabLeftPlacement,
            self::tabTopProportionalPlacement,
            self::tabTopScrollPlacement
        );
        if (!in_array($tabPlacement, $allowPlacement)) {
            throw new Exception("UI0107", $tabPlacement, implode(', ', $allowPlacement));
        }
        return $this->setOption(self::tabPlacementOption, $tabPlacement);
    }
    /**
     * Tab labels are sticky when scroll height has reach $height
     * Cannot be used with leftPlacement
     * @param string $height with unit like "100px" or "10rem".
     * if "auto", the barmenu height is used
     *
     * @return DocumentRenderOptions
     */
    public function setStickyTabs($height = "auto")
    {
        return $this->setOption(self::stickyTabsOptions, $height);
    }
}

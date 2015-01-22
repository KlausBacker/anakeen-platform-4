<?php
namespace Dcp\Ui;

trait TFormatRenderOption {

    /**
     * Format use to decorate string
     * @note use only in consultation mode
     * @param string $format
     * @return $this
     */
    public function setFormat($format)
    {
        /**
         * @var CommonRenderOptions $this
         */
        return $this->setOption(CommonRenderOptions::formatOption, $format);
    }
}
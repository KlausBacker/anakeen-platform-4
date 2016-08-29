<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class ImageRenderOptions extends FileRenderOptions
{
    const type = "image";
    const thumbnailSizeOption = "thumbnailSize";
    /**
     * Define image width
     * Width of displayed image
     *
     * @note use only in view mode
     * @deprecated use setThumbnailSize instead
     *
     * @param int $width the width in px of image
     *
     * @return $this
     */
    public function setThumbnailWidth($width)
    {
        return $this->setOption(self::thumbnailSizeOption, (int)$width);
    }
    /**
     * Define image geometry
     * 200 : width 200px
     * x300 : height 300px
     * 200x300 : width 200 height 300 (define the box where image dimension can be included)
     * 200x300c : width 200 height 300 with crop to get exact dimension
     * 200x300s : width 200 height 300 with streched image to get exact dimension
     *
     * @note use only in view mode
     *
     * @param string $size in px (number to define width, xNumber to define Height, or WidthxHeight) : 300, x450, 200x300
     *
     * @return $this
     * @throws Exception
     */
    public function setThumbnailSize($size)
    {
        if (!preg_match("/^x?[0-9]+$/", $size) && !preg_match("/^[0-9]+x[0-9]+[fsc]?$/", $size)) {
            throw new Exception("UI0212", $size);
        }
        return $this->setOption(self::thumbnailSizeOption, $size);
    }
}

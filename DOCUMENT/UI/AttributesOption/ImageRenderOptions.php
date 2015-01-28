<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class ImageRenderOptions extends FileRenderOptions
{
    
    const type = "image";
    const thumbnailWidthOption = "thumbnailWidth";
    /**
     * Define image width
     * Width of displayed image
     * @note use only in view mode
     * @param int $size width in px
     * @return $this
     */
    public function setThumbnailWidth($size)
    {
        return $this->setOption(self::thumbnailWidthOption, (int)$size);
    }
}

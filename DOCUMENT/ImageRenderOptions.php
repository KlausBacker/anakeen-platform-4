<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class ImageRenderOptions extends CommonRenderOptions
{
    
    const type = "image";
    const downloadInlineOption = "downloadInline";
    const thumbnailWidthOption = "thumbnailWidth";
    /**
     * Define if image link show image in browser or propose download
     * @note use only in view mode
     * @param bool $inline true => show in browser, false : download it
     * @return $this
     */
    public function setDownloadInline($inline)
    {
        if (!$inline) {
            $htmlLink = new HtmlLinkOptions();
            $htmlLink->target = "_self";
            $this->setOption(self::htmlLinkOption, $htmlLink);
        }
        
        return $this->setOption(self::downloadInlineOption, (bool)$inline);
    }
    /**
     * Define image width
     * The image link is modified to upload the image in this size
     * @note use only in view mode
     * @param int $size size width in px
     * @return $this
     */
    public function setThumbnailSize($size)
    {
        return $this->setOption(self::thumbnailWidthOption, (int)$size);
    }
}

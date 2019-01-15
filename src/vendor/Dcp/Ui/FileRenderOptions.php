<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class FileRenderOptions extends CommonRenderOptions
{
    
    const type = "file";
    const contentDispositionOption = "contentDisposition";
    const fileInlineDisposition = 'inline';
    const fileAttachmentDisposition = 'attachment';
    const mimeIconSizeOption = 'mimeIconSize';
    /**
     * Define if image link show image in browser or propose download
     * @note use only in view mode
     * @param bool $inline true => show in browser, false : download it
     * @return $this
     */
    public function setContentDispositionInline($inline)
    {
        if (!$inline) {
            $htmlLink = new HtmlLinkOptions();
            $htmlLink->target = "_self";
            $this->setOption(self::htmlLinkOption, $htmlLink);
        }
        
        return $this->setOption(self::contentDispositionOption, $inline ? self::fileInlineDisposition : self::fileAttachmentDisposition);
    }
    /**
     * Text to set into input when is empty
     * @note use only in edition mode
     * @param string $text text to display
     * @return $this
     */
    public function setPlaceHolder($text)
    {
        return $this->setOption(self::placeHolderOption, $text);
    }
    /**
     * Define mime icon geometry of file
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
    public function setMimeIconSize($size)
    {
        if (!preg_match("/^x?[0-9]+$/", $size) && !preg_match("/^[0-9]+x[0-9]+[fsc]?$/", $size)) {
            throw new Exception("UI0212", $size);
        }
        return $this->setOption(self::mimeIconSizeOption, $size);
    }
}

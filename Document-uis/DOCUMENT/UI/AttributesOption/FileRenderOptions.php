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
}

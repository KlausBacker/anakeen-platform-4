<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class FileRenderOptions extends CommonRenderOptions
{
    
    const type = "file";
    const downloadInlineOption = "downloadInline";
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
     * Text to set into input when is empty
     * @note use only in edition mode
     * @param string $text text to display
     * @return $this
     */
    public function setPlaceHolder($text)
    {
        return $this->setLabels(array(
            "placeHolder" => $text
        ));
    }
}

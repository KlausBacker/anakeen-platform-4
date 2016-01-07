<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class PasswordRenderOptions extends CommonRenderOptions
{
    
    const type = "password";
    const hideValueOption = "hideValue";
    /**
     * Text to display instead of real password value
     * @note use only in read mode
     * @param string $text
     * @return $this
     */
    public function hideValue($text)
    {
        return $this->setOption(self::hideValueOption, $text);
    }
}

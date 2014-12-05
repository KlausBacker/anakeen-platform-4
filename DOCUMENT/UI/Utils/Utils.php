<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class Utils
{
    /**
     * @param $renderId
     * @return \Dcp\Ui\IRenderConfig
     */
    public static function getRenderConfigObject($renderId)
    {
        // TODO waiting real family render
        switch ($renderId) {
            case "defaultView":
                return new \Dcp\Ui\DefaultView();
            case "defaultEdit":
                return new \Dcp\Ui\DefaultEdit();
            case "myCustom":
                return new \Dcp\Test\RenderConfigCustom();
            case "myCustomEdit":
                return new \Dcp\Test\RenderConfigCustomEdit();
        }
    }
}

<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class ButtonOptions
{
    public function __construct($url = null)
    {
        if ($url !== null) {
            $this->url = $url;
        }
    }
    /**
     * @var string target of url
     */
    public $target = "_self";
    public $windowWidth = "300px";
    public $windowHeight = "200px";
    /**
     * @var string title of window
     * only for _dialog target
     */
    public $windowTitle = "";
    /**
     * @var string addtionnal css class
     */
    public $class = "";
    /**
     * @var string tooltip of button
     */
    public $title = "";
    /**
     * @var string button content
     * The content must be a valid Html fragment
     */
    public $htmlContent = "";
    /**
     * @var string url to launch
     */
    public $url = "";
}

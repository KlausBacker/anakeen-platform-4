<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;



class HtmlLinkOptions
{
    public function __construct($url = null)
    {
        if ($url !== null) {
            $this->url = $url;
        }
    }
    /**
     * @var string target of window
     */
    public $target = "_self";
    /**
     * @var string width of window
     */
    public $windowWidth = "300px";
    /**
     * @var string height of window
     */
    public $windowHeight = "200px";
    /**
     * @var string title of window
     */
    public $windowTitle = "";
    /**
     * @var string tooltip text
     */
    public $title = "";
    /**
     * @var string url link for single value
     */
    public $url = "";
    /**
     * @var string[] url links for multiple value
     */
    public $urls = array();
}

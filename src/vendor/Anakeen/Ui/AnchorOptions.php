<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 20/11/18
 * Time: 14:33
 */

namespace Anakeen\Ui;

class AnchorOptions
{
    public function __construct($target = "_blank", $width = "300px", $height = "200px", $modal = false)
    {
        $this->$target = $target;
        $this->windowWidth = $width;
        $this->windowHeight = $height;
        $this->$modal = $modal;
    }

    /**
     * @var string target of window
     */
    public $target = "_blank";
    /**
     * @var string width of window
     */
    public $windowWidth = "300px";
    /**
     * @var string height of window
     */
    public $windowHeight = "200px";
    /**
     * @var bool modal window
     */
    public $modal = false;
}

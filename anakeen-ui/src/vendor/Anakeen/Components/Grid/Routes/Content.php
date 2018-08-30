<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 09/08/18
 * Time: 15:21
 */

namespace Anakeen\Components\Grid\Routes;

use Anakeen\Core\Settings;
use Anakeen\Router\URLUtils;

class Content extends DataSource
{
    protected function getData()
    {
        $data = parent::getData();
        $data["uri"] = URLUtils::generateURL(Settings::ApiV2 . sprintf("grid/content/%s", $this->smartElementId));
        return $data;
    }
}
<?php

 /**
  * Application parameters 
  * @name $app_const
  * @global array $GLOBALS['app_const'] 
  */
global $app_const;

$app_const= array(
  "INIT"    => "yes",
  "VERSION" => "3.2.12-2",
  "DOCADMIN_DEVEL" => array(
        "val" => "N",
        "descr" => N_("docadmin:Devel mode activation") ,
        "kind" => "enum(N|Y)",
        "global" => "N"
    )
);


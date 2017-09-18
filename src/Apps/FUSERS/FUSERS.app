<?php
// ---------------------------------------------------------------
// $Id: FUSERS.app,v 1.6 2006/04/06 16:48:02 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/App/Fusers/FUSERS.app,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------
$app_desc = array(
    "name" => "FUSERS", //Name
    "short_name" => N_("Users") , //Short name
    "description" => N_("Users Management") , //long description
    "access_free" => "N", //Access free ? (Y,N)
    "icon" => "fusers.png", //Icon
    "displayable" => "Y", //Should be displayed on an app list (Y,N)
    "with_frame" => "Y", //Use multiframe ? (Y,N)
    "childof" => "", //
    "iorder" => 120, // install order
    "tag" => "ADMIN SYSTEM"
);

$app_acl = array(
    
    array(
        "name" => "FUSERS",
        "description" => N_("To create and modify users and groups") ,
        "group_default" => "N"
    ) ,
    
    array(
        "name" => "FUSERS_MASTER",
        "description" => N_("Access to user refresh function") ,
        "group_default" => "N"
    )
);
$action_desc = array(
    
    array(
        "name" => "FUSERS_ROOT",
        "short_name" => N_("fusers root window") ,
        "acl" => "FUSERS",
        "root" => "Y"
    ) ,
    array(
        "name" => "FUSERS_LIST",
        "short_name" => N_("fusers list window") ,
        "acl" => "FUSERS"
    ) ,
    array(
        "name" => "FUSERS_VIEW",
        "short_name" => N_("fusers view/edit window") ,
        "acl" => "FUSERS"
    ) ,
    array(
        "name" => "FUSERS_EXPORTFORM",
        "short_name" => N_("account export form") ,
        "acl" => "FUSERS_MASTER"
    ) ,
    array(
        "name" => "FUSERS_IMPORTFORM",
        "short_name" => N_("account import form") ,
        "acl" => "FUSERS_MASTER"
    ) ,
    array(
        "name" => "FUSERS_IMPORTSTATUS",
        "short_name" => N_("account import status") ,
        "acl" => "FUSERS_MASTER"
    ) ,
    array(
        "name" => "FUSERS_IMPORTXSD",
        "short_name" => N_("account import xsd") ,
        "acl" => "FUSERS_MASTER"
    ) ,
    array(
        "name" => "FUSERS_EXPORTSTATUS",
        "short_name" => N_("account export status") ,
        "acl" => "FUSERS_MASTER"
    ) ,
    array(
        "name" => "FUSERS_EXPORTACCOUNTS",
        "short_name" => N_("account export") ,
        "acl" => "FUSERS_MASTER"
    ) ,
    array(
        "name" => "FUSERS_IMPORTACCOUNTS",
        "short_name" => N_("account import") ,
        "acl" => "FUSERS_MASTER"
    ) ,
    array(
        "name" => "FUSERS_LDAPINIT",
        "short_name" => N_("refresh ldap entries") ,
        "script" => "fusers_iuser.php",
        "function" => "fusers_ldapinit",
        "acl" => "FUSERS_MASTER"
    ) ,
    array(
        "name" => "FUSERS_IGROUP",
        "short_name" => N_("refresh groups") ,
        "script" => "fusers_iuser.php",
        "function" => "fusers_igroup",
        "acl" => "FUSERS_MASTER"
    ) ,
    array(
        "name" => "FUSERS_MAINCOLS",
        "short_name" => N_("iuser choose main view column") ,
        "acl" => "FUSERS",
        "root" => "N"
    ) ,
    array(
        "name" => "FUSERS_MAIN",
        "short_name" => N_("iuser list main page") ,
        "acl" => "FUSERS",
        "root" => "N"
    ) ,
    array(
        "name" => "FUSERS_DATATABLES_LAYOUT",
        "short_name" => N_("get layout for datatable") ,
        "acl" => "FUSERS",
        "root" => "N"
    ) ,
    array(
        "name" => "FUSERS_GET_DATATABLE_INFO",
        "short_name" => N_("iuser list all") ,
        "acl" => "FUSERS",
        "root" => "N"
    ) ,
    array(
        "name" => "GET_TYPE_IMAGE",
        "short_name" => N_("get image type") ,
        "acl" => "FUSERS",
        "script" => "fusers_datatables_layout.php",
        "function" => "get_type_image",
        "root" => "N"
    )
);
?>

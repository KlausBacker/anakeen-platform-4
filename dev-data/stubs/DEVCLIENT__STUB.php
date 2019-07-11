<?php

namespace SmartStructure {

    class Devclient extends \Anakeen\SmartStructures\Devclient\DevclientBehavior
    {
        const familyName = "DEVCLIENT";
    }
}

namespace SmartStructure\Fields {

    class Devclient
    {
        /**
        * Identification
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const client_fr_ident='client_fr_ident';
        /**
        * Title
        * <ul>
        * <li> <i>is-title</i> true </li>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const client_title='client_title';
        /**
        * Firstname
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const client_firstname='client_firstname';
        /**
        * Lastname
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const client_lastname='client_lastname';
        /**
        * Email address
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const client_email='client_email';
        /**
        * Enterprise
        * <ul>
        * <li> <i>is-title</i> true </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const client_society='client_society';
        /**
        * City
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const client_location='client_location';

    }
}
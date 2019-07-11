<?php

namespace SmartStructure {

    class Devperson extends \Anakeen\SmartStructures\Devperson\DevpersonBehavior
    {
        const familyName = "DEVPERSON";
    }
}

namespace SmartStructure\Fields {

    class Devperson
    {
        /**
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const dev_fr_ident='dev_fr_ident';
        /**
        * Title
        * <ul>
        * <li> <i>is-title</i> true </li>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const dev_title='dev_title';
        /**
        * First name
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const dev_firstname='dev_firstname';
        /**
        * Last name
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const dev_lastname='dev_lastname';
        /**
        * Email adress
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const dev_email='dev_email';
        /**
        * Birthdate
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> date </li>
        * </ul>
        */ 
        const dev_birthdate='dev_birthdate';

    }
}
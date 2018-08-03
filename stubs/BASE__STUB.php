<?php

namespace SmartStructure {

    class Base extends \Anakeen\SmartElement
    {
        const familyName = "BASE";
    }
}

namespace SmartStructure\Fields {

    class Base
    {
        /**
        * Basique
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const fr_basic='fr_basic';
        /**
        * Titre
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const ba_title='ba_title';

    }
}
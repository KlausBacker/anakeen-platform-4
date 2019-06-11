<?php

namespace SmartStructure {

    class Tst_docenum extends \Anakeen\SmartElement
    {
        const familyName = "TST_DOCENUM";
    }
}

namespace SmartStructure\Fields {

    class Tst_docenum
    {
        /**
        * Cadre Html
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const tst_fr_info='tst_fr_info';
        /**
        * Titre
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const tst_title='tst_title';
        /**
        * Enum
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> TST_DOCENUM-0123 </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const tst_enum1='tst_enum1';
        /**
        * Enum
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> TST_DOCENUM-ABCD </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const tst_enuma='tst_enuma';

    }
}
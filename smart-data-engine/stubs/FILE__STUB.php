<?php

namespace SmartStructure {

    class File extends \Anakeen\SmartStructures\File\FileHooks
    {
        const familyName = "FILE";
    }
}

namespace SmartStructure\Fields {

    class File
    {
        /**
        * Description
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const fi_frdesc='fi_frdesc';
        /**
        * titre
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const fi_title='fi_title';
        /**
        * titre
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const fi_titlew='fi_titlew';
        /**
        * principal
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> file </li>
        * </ul>
        */ 
        const fi_file='fi_file';

    }
}
<?php

namespace SmartStructure {

    class Image extends \Anakeen\SmartStructures\Image\ImageHooks
    {
        const familyName = "IMAGE";
    }
}

namespace SmartStructure\Fields {

    class Image
    {
        /**
        * image
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const img_frfile='img_frfile';
        /**
        * titre
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const img_title='img_title';
        /**
        * image
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> image </li>
        * </ul>
        */ 
        const img_file='img_file';

    }
}
<?php

namespace SmartStructure {

    class Tst_render extends \Anakeen\SmartStructures\UiTest\TestRender\TestRenderHooks
    {
        const familyName = "TST_RENDER";
    }
}

namespace SmartStructure\Fields {

    class Tst_render
    {
        /**
        * Titre
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const tst_fr_desc='tst_fr_desc';
        /**
        * Référence
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const tst_ref='tst_ref';
        /**
        * Le titre
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const tst_title='tst_title';
        /**
        * Description
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> htmltext </li>
        * </ul>
        */ 
        const tst_desc='tst_desc';
        /**
        * Configuration
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const tst_fr_config='tst_fr_config';
        /**
        * Nom logique
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const tst_docname='tst_docname';
        /**
        * Identifiant de vue
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const tst_docviewid='tst_docviewid';

    }
}
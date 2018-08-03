<?php

namespace SmartStructure {

    class Report extends \Anakeen\SmartStructures\Report\ReportHooks
    {
        const familyName = "REPORT";
    }
}

namespace SmartStructure\Fields {

    class Report
    {
        /**
        * Présentation
        * <ul>
        * <li> <i>type</i> tab </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const rep_tab_presentation='rep_tab_presentation';
        /**
        * Présentation
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const rep_fr_presentation='rep_fr_presentation';
        /**
        * Description
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> longtext </li>
        * </ul>
        */ 
        const rep_caption='rep_caption';
        /**
        * Tri
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const rep_sort='rep_sort';
        /**
        * Id tri
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const rep_idsort='rep_idsort';
        /**
        * Ordre
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> REPORT-rep_ordersort </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const rep_ordersort='rep_ordersort';
        /**
        * Limite
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const rep_limit='rep_limit';
        /**
        * Colonnes
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const rep_tcols='rep_tcols';
        /**
        * Label
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const rep_lcols='rep_lcols';
        /**
        * Id colonnes
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const rep_idcols='rep_idcols';
        /**
        * Option de présentation
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const rep_displayoption='rep_displayoption';
        /**
        * Couleur
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> color </li>
        * </ul>
        */ 
        const rep_colors='rep_colors';
        /**
        * Pied de tableau
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> report-rep_foots </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const rep_foots='rep_foots';

    }
}
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
        /** [array] Colonnes */
        const rep_tcols='rep_tcols';
        /** [text] Label */
        const rep_lcols='rep_lcols';
        /** [text] Id colonnes */
        const rep_idcols='rep_idcols';
        /** [text] Option de présentation */
        const rep_displayoption='rep_displayoption';
        /** [enum] Pied de tableau */
        const rep_foots='rep_foots';
        /** [text] Tri */
        const rep_sort='rep_sort';
        /** [text] Id tri */
        const rep_idsort='rep_idsort';
        /** [longtext] Description */
        const rep_caption='rep_caption';
        /** [enum] Ordre */
        const rep_ordersort='rep_ordersort';
        /** [int] Limite */
        const rep_limit='rep_limit';

    }
}
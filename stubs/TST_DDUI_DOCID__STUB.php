<?php

namespace SmartStructure {

    class Tst_ddui_docid extends \Anakeen\SmartStructures\UiTest\TstUiDocid\TstUiDocidHooks
    {
        const familyName = "TST_DDUI_DOCID";
    }
}

namespace SmartStructure\Fields {

    class Tst_ddui_docid
    {
        /**
        * Titre
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const test_ddui_docid__f_title='test_ddui_docid__f_title';
        /**
        * Référence
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const test_ddui_docid__titleref='test_ddui_docid__titleref';
        /**
        * Le titre
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>relation</i> TST_DDUI_DOCID </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const test_ddui_docid__title='test_ddui_docid__title';
        /**
        * Les documents
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const test_ddui_docid__fr_docid='test_ddui_docid__fr_docid';
        /**
        * Une première relation
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> TST_DDUI_DOCID </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const test_ddui_docid__single1='test_ddui_docid__single1';
        /**
        * Une deuxième relation
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>link</i> #action/document.history:%TEST_DDUI_DOCID__SINGLE2% </li>
        * <li> <i>relation</i> TST_DDUI_DOCID </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const test_ddui_docid__single2='test_ddui_docid__single2';
        /**
        * Une troisième relation
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>link</i> #action/document.properties:%TEST_DDUI_DOCID__SINGLE3% </li>
        * <li> <i>relation</i> TST_DDUI_DOCID </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const test_ddui_docid__single3='test_ddui_docid__single3';
        /**
        * Des premières relations
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> TST_DDUI_DOCID </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const test_ddui_docid__multiple1='test_ddui_docid__multiple1';
        /**
        * Des secondes relations
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>link</i> #action/document.history:%TEST_DDUI_DOCID__MULTIPLE2% </li>
        * <li> <i>relation</i> TST_DDUI_DOCID </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const test_ddui_docid__multiple2='test_ddui_docid__multiple2';
        /**
        * Des troisièmes relations
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>link</i> #action/document.properties:%TEST_DDUI_DOCID__MULTIPLE3% </li>
        * <li> <i>relation</i> TST_DDUI_DOCID </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const test_ddui_docid__multiple3='test_ddui_docid__multiple3';
        /**
        * Voir l'historique
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>link</i> #action/document.history:%TEST_DDUI_DOCID__SINGLE1% </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const test_ddui_docid__histo1='test_ddui_docid__histo1';
        /**
        * Autres relations
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const test_ddui_docid__t_rels='test_ddui_docid__t_rels';
        /**
        * Une autre relation
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> TST_DDUI_DOCID </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const test_ddui_docid__single_array='test_ddui_docid__single_array';
        /**
        * D'autres relations
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> TST_DDUI_DOCID </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const test_ddui_docid__multiple_array='test_ddui_docid__multiple_array';
        /**
        * Autres relations avec liens
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const test_ddui_docid__t_links='test_ddui_docid__t_links';
        /**
        * Un lien particulier
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>link</i> #action/document.properties:%TEST_DDUI_DOCID__SINGLE_LINK% </li>
        * <li> <i>relation</i> TST_DDUI_DOCID </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const test_ddui_docid__single_link='test_ddui_docid__single_link';
        /**
        * D'autres liaisons spécifique
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>link</i> #action/document.properties:%TEST_DDUI_DOCID__MULTIPLE_LINK%:%TEST_DDUI_DOCID__SINGLE_LINK% </li>
        * <li> <i>relation</i> TST_DDUI_DOCID </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const test_ddui_docid__multiple_link='test_ddui_docid__multiple_link';
        /**
        * Voir l'historique
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>link</i> #action/document.history:%TEST_DDUI_DOCID__SINGLE_LINK% </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const test_ddui_docid__link_histo='test_ddui_docid__link_histo';

    }
}
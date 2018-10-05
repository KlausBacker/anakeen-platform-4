<?php

namespace SmartStructure {

    class Cvdoc extends \Anakeen\SmartStructures\Cvdoc\CVDocHooks
    {
        const familyName = "CVDOC";
    }
}

namespace SmartStructure\Fields {

    class Cvdoc
    {
        /**
        * <ul>
        * <li> <i>extended</i> true </li>
        * <li> <i>type</i> frame </li>
        * </ul>
        */ 
        const fr_basic='fr_basic';
        /**
        * Description
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> longtext </li>
        * </ul>
        */ 
        const ba_desc='ba_desc';
        /**
        * Structure
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> -1 </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const cv_famid='cv_famid';
        /**
        * Vues
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const cv_t_views='cv_t_views';
        /**
        * Identifiant de la vue
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const cv_idview='cv_idview';
        /**
        * Label
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const cv_lview='cv_lview';
        /**
        * Classe de configuration de rendu (HTML5)
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const cv_renderconfigclass='cv_renderconfigclass';
        /**
        * Menu
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const cv_menu='cv_menu';
        /**
        * Type
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> CVDOC-cv_kview </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const cv_kview='cv_kview';
        /**
        * Affichable
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> CVDOC-cv_displayed </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const cv_displayed='cv_displayed';
        /**
        * Masque
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> MASK </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const cv_mskid='cv_mskid';
        /**
        * Ordre de sélection
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> int </li>
        * </ul>
        */ 
        const cv_order='cv_order';
        /**
        * Vues par défauts
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const cv_fr_default='cv_fr_default';
        /**
        * Id création vues par défaut
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const cv_idcview='cv_idcview';
        /**
        * Création vue
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const cv_lcview='cv_lcview';
        /**
        * Classe d'accès au rendu
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const cv_renderaccessclass='cv_renderaccessclass';
        /**
        * Masque primaire
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> MASK </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const cv_primarymask='cv_primarymask';
        /**
        * Profil dynamique
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const dpdoc_fr_dyn='dpdoc_fr_dyn';
        /**
        * Structure pour le profil
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> -1 </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const dpdoc_famid='dpdoc_famid';

    }
}
<?php

namespace SmartStructure {

    class Dir extends \Anakeen\SmartStructures\Dir\DirHooks
    {
        const familyName = "DIR";
    }
}

namespace SmartStructure\Fields {

    class Dir
    {
        /**
        * <ul>
        * <li> <i>extended</i> true </li>
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
        * Couleur intercalaire
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> color </li>
        * </ul>
        */ 
        const gui_color='gui_color';
        /**
        * Restrictions
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> Read </li>
        * </ul>
        */ 
        const fld_fr_rest='fld_fr_rest';
        /**
        * Tout ou rien
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> dir-fld_allbut </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const fld_allbut='fld_allbut';
        /**
        * Smart structure autorisées
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const fld_tfam='fld_tfam';
        /**
        * Smart structure (titre)
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const fld_fam='fld_fam';
        /**
        * Smart structure
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> -1 </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const fld_famids='fld_famids';
        /**
        * Restriction sous Smart structure
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> dir-fld_subfam </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const fld_subfam='fld_subfam';
        /**
        * Profils par défaut
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> Read </li>
        * </ul>
        */ 
        const fld_fr_prof='fld_fr_prof';
        /**
        * Profil par défaut de document
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> PDOC </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const fld_pdocid='fld_pdocid';
        /**
        * Profil par défaut de dossier
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> PDIR </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const fld_pdirid='fld_pdirid';

    }
}
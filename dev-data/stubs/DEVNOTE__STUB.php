<?php

namespace SmartStructure {

    class Devnote extends \Anakeen\SmartStructures\Devnote\DevnoteBehavior
    {
        const familyName = "DEVNOTE";
    }
}

namespace SmartStructure\Fields {

    class Devnote
    {
        /**
        * Identification
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const note_fr_ident='note_fr_ident';
        /**
        * Title
        * <ul>
        * <li> <i>is-title</i> true </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const note_title='note_title';
        /**
        * Author name
        * <ul>
        * <li> <i>is-title</i> true </li>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const note_author_display='note_author_display';
        /**
        * City
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const note_location='note_location';
        /**
        * Author
        * <ul>
        * <li> <i>relation</i> DEVPERSON </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const note_author='note_author';
        /**
        * Redaction date
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> date </li>
        * </ul>
        */ 
        const note_redactdate='note_redactdate';
        /**
        * Co-authors
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const note_co_authors='note_co_authors';
        /**
        * Co-author list
        * <ul>
        * <li> <i>relation</i> DEVPERSON </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const note_coauthor='note_coauthor';
        /**
        * Co-author name
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const note_name='note_name';
        /**
        * Co-author phone
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const note_phone='note_phone';
        /**
        * Note content
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const note_fr_body='note_fr_body';
        /**
        * Note text
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> htmltext </li>
        * </ul>
        */ 
        const note_content='note_content';

    }
}
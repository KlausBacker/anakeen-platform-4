<?php

namespace SmartStructure {

    class Devbill extends \Anakeen\SmartStructures\Devbill\DevbillBehavior
    {
        const familyName = "DEVBILL";
    }
}

namespace SmartStructure\Fields {

    class Devbill
    {
        /**
        * Identification
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const bill_fr_ident='bill_fr_ident';
        /**
        * Title
        * <ul>
        * <li> <i>is-title</i> true </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const bill_title='bill_title';
        /**
        * <ul>
        * <li> <i>is-title</i> true </li>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const bill_author_display='bill_author_display';
        /**
        * City
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const bill_location='bill_location';
        /**
        * Description
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> longtext </li>
        * </ul>
        */ 
        const bill_content='bill_content';
        /**
        * Author
        * <ul>
        * <li> <i>relation</i> DEVPERSON </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const bill_author='bill_author';
        /**
        * Clients
        * <ul>
        * <li> <i>relation</i> DEVCLIENT </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>multiple</i> true </li>
        * <li> <i>type</i> docid </li>
        * </ul>
        */ 
        const bill_clients='bill_clients';
        /**
        * Bill date
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> date </li>
        * </ul>
        */ 
        const bill_billdate='bill_billdate';
        /**
        * Other clients
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const bill_otherclients='bill_otherclients';
        /**
        * Client name
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const bill_clientname='bill_clientname';
        /**
        * Enterprise
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const bill_society='bill_society';
        /**
        * Cost
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> money </li>
        * </ul>
        */ 
        const bill_cost='bill_cost';

    }
}
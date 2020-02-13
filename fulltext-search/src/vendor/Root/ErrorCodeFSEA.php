<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * Errors code used for full search queries
 */
class ErrorCodeFSEA
{
    /**
     * @errorCode the ns name must be https://platform.anakeen.com/4/schemas/search-domain/1.0
     */
    const FSEA0001 = 'Xml file "%s", is not a search domain configuration file.';
    /**
     * @errorCode The search domain is not referenced in database
     */
    const FSEA0002 = 'Search Domain "%s" is not recorded.';
    /**
     * @errorCode The search domain reference a structure name that not exists
     */
    const FSEA0003 = 'Search Domain "%s", structure "%s" not found.';
    /**
     * @errorCode The search domain reference is use to construct database table
     */
    const FSEA0004 = 'Search Domain name "%s" must contains only alphanum characters.';
    /**
     * @errorCode The search domain reference a structure name that not exists
     */
    const FSEA0005 = 'Search Domain "%s", structure "%s", field "%s" not found.';
}

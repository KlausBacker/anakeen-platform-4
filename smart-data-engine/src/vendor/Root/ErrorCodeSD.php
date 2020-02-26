<?php

/**
 * Errors code used by searchDoc class
 *
 * @class ErrorCodeSD
 * @see   ErrorCode
 * @brief List all error code for searchDoc class
 * @see   \Anakeen\Search\Internal\SearchSmartData
 */
class ErrorCodeSD
{
    /**
     * @errorCode the join must be conform to syntax
     *
     */
    const SD0001 = 'join syntax error : %s';
    /**
     * @errorCode only and, or operator allowed
     *
     */
    const SD0002 = 'general filter: Unknown operator %s : %s';
    /**
     * @errorCode all parenthesis must be closes
     *
     */
    const SD0003 = 'general filter: unbalanced parenthesis : %s';
    /**
     * @errorCode error in syntax
     *
     */
    const SD0004 = 'general filter: check syntax : %s';
    /**
     * @errorCode when use DocSearch::setRecursiveSearch()
     *
     */
    const SD0005 = 'recursive search: cannot create temporary search : %s';
    /**
     * @errorCode when use DocSearch::setRecursiveFolderLevel()
     *
     */
    const SD0006 = 'recursive search: level depth must be integer : %s';

    /**
     * Searching on a "Specialized search" and specifying additional filters is not supported
     *
     * @errorCode When setting a "Specialized search" collection with useCollection() and adding filters with addFilter()
     */
    const SD0008 = 'Searching on a "Specialized search" collection and specifying additional filters is not supported';
    /**
     * @errorCode The slice must be a number
     * @see \Anakeen\Search\SearchElements::setSlice()
     */
    const SD0009 = 'Slice "%s" is not correct - use positive number or "ALL"';
     /**
      * @errorCode The start must be a number
      * @see \Anakeen\Search\SearchElements::setStart()
      */
    const SD0010 = 'Start "%s" is not correct - use positive number';
     /**
      * @errorCode The id may be not reference a id collection
      * @see \Anakeen\Search\SearchElements::useCollection()
      */
    const SD0011 = 'Collection id "%s" is not correct';
    /**
     * @errorCode Query cannot be executed
     * @see \Anakeen\Search\SearchElements::onlyCount()
     */
    const SD0012 = 'Only count error :"%s"';
    /**
     * @errorCode Query cannot be executed
     * @see \Anakeen\Search\SearchElements::search()
     */
    const SD0013 = 'Search error :"%s"';
    /**
     * @errorCode when use docrel and usertag table join by exemple
     * @see \Anakeen\Search\SearchElements::join()
     */
    const SD0014 = "The join cannot be used with several filters which use different join ('%s' - '%s')";
    /**
     * @errorCode when use docrel and usertag table join by exemple
     * @see \Anakeen\Search\SearchElements::join()
     */
    const SD0015 = "The join type must be %s, not '%s'";
}

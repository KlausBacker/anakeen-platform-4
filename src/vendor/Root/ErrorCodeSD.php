<?php

/**
 * Errors code used by searchDoc class
 *
 * @class ErrorCodeSD
 * @see   ErrorCode
 * @brief List all error code for searchDoc class
 * @see   SearchDoc
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
     * Only words can be use in fulltext not symbol or punctauation
     *
     * @errorCode when use DocSearch::addGeneralFilter()
     *
     */
    const SD0007 = 'general filter: words not supported : "%s"';
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
}

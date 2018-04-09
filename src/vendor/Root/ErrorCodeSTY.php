<?php


class ErrorCodeSTY
{
    /**
     * @errorCode sty file not reachable
     */
    const STY0001 = 'sty file not reachable: %s';
    /**
     * @errorCode malformed sty file
     */
    const STY0002 = 'malformed sty file: %s';
    /**
     * @errorCode style registration error
     */
    const STY0003 = 'style registration error: %s';
    /**
     * @errorCode source file not readable for sty target
     */
    const STY0004 = 'source file not readable for sty target: %s';
    /**
     * @errorCode file creation error
     */
    const STY0005 = 'file creation error: %s';
    /**
     * @errorCode parser does not implements required interfaces
     */
    const STY0006 = 'parser does not implements required interfaces: %s';
    /**
     * @errorCode unimplemented feature
     */
    const STY0007 = 'unimplemented feature: %s';
    /**
     * @errorCode when try create target directory
     * @see       dcpCssCopyDirectory
     */
    const STY0008 = 'cannot create target directory: %s';
    /**
     * @errorCode when try create view target directory
     * @see       dcpCssCopyDirectory
     */
    const STY0009 = 'origin "%s" is not a directory';
    /**
     * @errorCode copy error when copy directory for css
     * @see       dcpCssCopyDirectory
     */
    const STY0010 = 'cannot copy from origin "%s" to "%s"';
}


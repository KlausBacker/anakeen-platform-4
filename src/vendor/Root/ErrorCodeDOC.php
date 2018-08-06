<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * Errors code used when import document
 * @class ErrorCodeDOC
 * @brief List all error code for DOC
 * @see   ErrorCode
 */
class ErrorCodeDOC
{
    /**
     * @errorCode
     * when import smart element the  smart structure  reference is required
     */
    const DOC0002 = ' smart structure  reference is empty for smart element "%s"';
    /**
     * @errorCode
     * the reference  smart structure  must begin with a letter and must contains only alphanum characters
     */
    const DOC0003 = 'syntax error  smart structure  reference "%s" for smart element "%s"';
    /**
     * @errorCode
     * the document's reference must begin with a letter and must contains only alphanum characters
     */
    const DOC0004 = 'syntax error smart element reference "%s" ';
    /**
     * @errorCode
     * the reference  smart structure  must be exists
     */
    const DOC0005 = ' smart structure  reference "%s" not exists for smart element "%s"';
    /**
     * @errorCode
     * the reference  smart structure  must be a  smart structure  document
     */
    const DOC0006 = ' smart structure  reference "%s" is not a  smart structure  "%s"';
    /**
     * @errorCode
     * must have create privilege to import thid kind of document
     */
    const DOC0007 = 'insufficient privileges to import smart element "%s" of "%s"  smart structure  ';
    /**
     * @errorCode
     * cannot change inherit  smart structure  for a document
     */
    const DOC0008 = 'the smart element "%s" cannot be converted from "%s" to "%s"  smart structure  ';
    /**
     * @errorCode
     * cannot update fixed document, no alive revision is found
     */
    const DOC0009 = 'the smart element "%s" ( smart structure  "%s") is fixed';
    /**
     * @errorCode
     * the smart element cannot be imported because  smart structure  is not completed
     */
    const DOC0010 = ' smart structure  error detected "%s" for the smart element "%s" : %s';

    /**
     * @errorCode  the smart element cannot be imported because no order specified
     */
    const DOC0011 = ' smart structure  "%s" error detected  ORDER is needed';
    /**
     * @errorCode
     * error in setvalue when import document
     */
    const DOC0100 = 'setValue error "%s" for field "%s"';
    /**
     * @errorCode
     * error when inserting file for file fields
     * @note when file is included in array field
     */
    const DOC0101 = 'vault error "%s" to import file "%s" for field "%s" in "%s" document';
    /**
     * @errorCode
     * error when inserting file in vault for file fields
     */
    const DOC0102 = 'vault error "%s" to import file "%s" for field "%s" in "%s" document';
    /**
     * @errorCode
     * error in set value for file fields
     */
    const DOC0103 = 'set value error "%s" to import file "%s" for field "%s" in "%s" document';
    /**
     * @errorCode
     * preImport Method detect error (special) for physical id)
     */
    const DOC0104 = 'preImport error in "%s" system smart element : %s';
    /**
     * @errorCode
     * preImport Method detect error when create it
     * @note when policy import is add
     */
    const DOC0105 = 'preImport error in "%s" smart element when create it: %s';
    /**
     * @errorCode
     * preImport Method detect error when create it
     * @note when policy import is update
     */
    const DOC0106 = 'preImport error in "%s" smart element when create it: %s';
    /**
     * @errorCode
     * detect error when create it
     * @note when policy import is add
     */
    const DOC0107 = 'creation error in "%s" smart element : %s';
    /**
     * @errorCode
     * detect error when create it
     * @note when policy import is update
     */
    const DOC0108 = 'creation error in "%s" smart element : %s';
    /**
     * @errorCode
     * preImport Method detect error when update it
     * @note when policy import is update
     */
    const DOC0109 = 'preImport error in "%s" smart element when update it: %s';
    /**
     * @errorCode
     * too many similar smart element when try update by key ref
     * generaly  a smart element with same title has been found
     * @note when policy import is update
     */
    const DOC0110 = 'similar smart element "%s" smart element when update it';
    /**
     * @errorCode
     * preImport Method detect error when update it
     * @note when logical name is set
     */
    const DOC0111 = 'preImport error in "%s" smart element when update it: %s';
    /**
     * @errorCode
     * update doc error after postStore method
     * @see \Anakeen\Core\Internal\SmartElement::store
     */
    const DOC0112 = 'update error in "%s" smart element : %s';
    /**
     * @errorCode
     * update doc error after transfert values from old doc
     * @see \Anakeen\Core\Internal\SmartElement::store
     */
    const DOC0113 = 'transfertvalues error in smart element "%s" update: %s';
    /**
     * @errorCode the field must exists to get its value
     * @see       \Anakeen\Core\Internal\SmartElement::getAttributeValue
     */
    const DOC0114 = 'field "%s" not exists in smart element "%s" ( smart structure  "%s") : cannot get its value';
    /**
     * @errorCode the field must exists to set its value
     * @see       \Anakeen\Core\Internal\SmartElement::setAttributeValue
     */
    const DOC0115 = 'field "%s" not exists in smart element "%s" ( smart structure  "%s") : cannot set any value';
    /**
     * @errorCode a value cannot be associated to a structured  field. It must not be an TAB or FRAME field type.
     * @see       \Anakeen\Core\Internal\SmartElement::getAttributeValue
     */
    const DOC0116 = 'field "%s" is a structured field in smart element "%s" ( smart structure  "%s") : it cannot has any values';
    /**
     * @errorCode a value cannot be set to a structured  field. It must not be an TAB or FRAME field type.
     * @see       \Anakeen\Core\Internal\SmartElement::setAttributeValue
     */
    const DOC0117 = 'field "%s" is a structured field in smart element "%s" ( smart structure  "%s") : it cannot set values';
    /**
     * @errorCode try to update a smart element revised
     * @see       \Anakeen\Core\Internal\SmartElement::store
     */
    const DOC0118 = 'cannot update fixed smart element "%s" (#%d)';
    /**
     * @errorCode try to update a smart element revised
     * @see       \Anakeen\Core\Internal\SmartElement::store
     */
    const DOC0119 = 'the smart element "%s" (#%d) became fixed because another revision more recent has been created';
    /**
     * @errorCode when update  smart structure  parameter
     * @see       \Anakeen\Core\SmartStructure::setParam
     */
    const DOC0120 = 'cannot set  smart structure  parameter "%s". It is not a parameter for "%s" ("%s")  smart structure ';
    /**
     * @errorCode application tag must not contain \n character
     * @see       \Anakeen\Core\Internal\SmartElement::addATag
     */
    const DOC0121 = 'cannot add application tag "%s" (document #%d). Application tag must not contain \n character';
    /**
     * @errorCode application tag must not be empty
     * @see       \Anakeen\Core\Internal\SmartElement::addATag
     */
    const DOC0122 = 'cannot add application tag (document #%d). Application tag must not be empty';
    /**
     * @errorCode when update field  smart structure  default value
     * @see       \Anakeen\Core\SmartStructure::setDefValue
     */
    const DOC0123 = 'cannot set default value for "%s". It is not an field for "%s" ("%s")  smart structure ';
    /**
     * @errorCode problems with frame's structure
     * @see       \Anakeen\Core\Internal\SmartElement::viewbodycard
     */
    const DOC0124 = 'changeframe requested but current frame is empty (current field is "%s")';
    /**
     * @errorCode problems with frame's structure
     * @see       \Anakeen\Core\Internal\SmartElement::viewbodycard
     */
    const DOC0125 = 'changeframe requested but current frame "%s" does not exists (current field is "%s")';
    /**
     * @errorCode return of customSearchValues hook must be an array
     * @see       \Anakeen\Core\Internal\SmartElement::getCustomSearchValues
     */
    const DOC0126 = 'getCustomSearchValues must return an array of string (found "%s")';
    /**
     * @errorCode Dynamic profil reference an field which no refers to any document
     * @see       \Anakeen\Core\Internal\DocumentAccess::computeDProfil
     */
    const DOC0127 = 'Document with identifier %s not found for field %s';
    /**
     * @errorCode Dynamic profil reference an field refers to a document. But this smart element is not an account
     * @see       \Anakeen\Core\Internal\DocumentAccess::computeDProfil
     */
    const DOC0128 = 'Document with identifier "%s" from field "%s" has no property "us_whatid"';
    /**
     * @errorCode Dynamic profil reference an field which refers to an incomplete account
     * @see       \Anakeen\Core\Internal\DocumentAccess::computeDProfil
     */
    const DOC0129 = 'Document with identifier "%s" from field "%s" has an empty property "us_whatid"';
    /**
     * @errorCode The requested field was not found in \Anakeen\Core\Internal\SmartElement::getHtmlAttrValue()
     * @see       \Anakeen\Core\Internal\SmartElement::getHtmlAttrValue
     */
    const DOC0130 = 'Attribute "%s" not found on smart element "%s" from  smart structure  "%s"';
    /**
     * @errorCode Create acl not granted to duplication
     * @see       \Anakeen\Core\Internal\SmartElement::duplicate
     */
    const DOC0131 = 'Cannot duplicate for familu "%s"';
    /**
     * @errorCode The requested field was not found in \Anakeen\Core\Internal\SmartElement::getHtmlAttrValue()
     * @see       \Anakeen\Core\Internal\SmartElement::setValue
     */
    const DOC0132 = 'Element "%s", field "%s" update not granted';
    /**
     * @errorCode the field must exists to get its value
     * @see       \Anakeen\Core\Internal\SmartElement::getAttributeValue
     */
    const DOC0133 = 'field "%s" read access deny in smart element "%s" ( smart structure  "%s")';
    /**
     * @errorCode Dynamic profil reference an field which no refers to any document
     * @see       \Anakeen\Core\Internal\DocumentAccess::computeDProfil
     */
    const DOC0134 = 'Field "%s" use for profiled not exists for "%s"';
    /**
     * @errorCode
     * the smart element cannot be inserted in folder target
     * @note when DOC has defined a folder target
     */
    const DOC0200 = 'cannot insert "%s" smart element in "%s" folder : %s';
    /**
     * @errorCode
     * the folder target is not found
     * @note when DOC has defined a folder target
     */
    const DOC0201 = '"%s" folder not found. Cannot insert "%s" document';
    /**
     * @errorCode
     * the folder target is not a folder document
     * @note when DOC has defined a folder target
     */
    const DOC0202 = '"%s" folder is not a folder (is is a "%s"). Cannot insert "%s" document';
    /**
     * @errorCode The smart element one is trying to duplicate is invalid.
     * @note      An invalid smart element can be a non-existing smart element (e.g. obtained with `new_Doc("", "NON_EXISTING")`).
     */
    const DOC0203 = "Cannot duplicate an invalid document";
    /**
     * @errorCode
     * the mask cannot be applied
     */
    const DOC1000 = '"%s" mask is not found , cannot apply it to "%s" document';
    /**
     * @errorCode
     * the mask to apply is not mask
     */
    const DOC1001 = '"%s" smart element is not a mask  (is is a "%s"), cannot apply it to "%s" document';
    /**
     * @errorCode
     * the  smart structure  mask field is not compatible
     */
    const DOC1002 = '"%s" mask cannot be apply to "%s" document. It is set for "%s"  smart structure ';
    /**
     * @errorCode
     * the mask cannot be applied from its logical mask
     */
    const DOC1004 = '"%s" mask is not found , cannot apply it to "%s" document';
    /**
     * @errorCode
     * A method call by client must has a specific declaration in the comment part : @apiExpose
     *
     */
    const DOC1100 = 'Method %s::%s() not contains @apiExpose tag comment. Document %s';
    /**
     * @errorCode
     * A controller view method must has a specific declaration in the comment part : @templateController
     *
     */
    const DOC1101 = 'Method %s::%s() not contains @templateController tag comment. Document %s';
}

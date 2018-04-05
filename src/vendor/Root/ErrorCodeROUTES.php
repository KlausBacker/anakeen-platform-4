<?php

class ErrorCodeRoutes
{
    /**
     * @errorCode Cache is corrupted
     */
    const ROUTES0010 = 'Config : route cache config file is corrupted';
    /**
     * @errorCode The ressource is not found
     */
    const ROUTES0100 = 'Document "%s" not found';
    /**
     * @errorCode The ressource cannot be get
     */
    const ROUTES0101 = 'Document "%s" access deny : %s';

    /**
     * @errorCode Document "%s" deleted
     */
    const ROUTES0102 = 'Document "%s" deleted';
    /**
     * @errorCode The fieds partial response indicate a wrong key
     */
    const ROUTES0103 = 'Document fields "%s" not known';
    /**
     * @errorCode Search document in family colection
     */
    const ROUTES0104 = 'Document "%s" is not a document of the family "%s"';
    /**
     * @errorCode The ressource is not found
     */
    const ROUTES0105 = 'Family "%s" not found';
    /**
     * @errorCode Content-type said json and content must be contains {document:{attributes:[]}
     */
    const ROUTES0106 = 'Record fail. Json object not contains attributes // example : {"document":{"attributes":{"attributeId" : {"value" : "newValue"}}}} : %s';

    /**
     * @errorCode An attribute cannot be set
     */
    const ROUTES0107 = 'Update document "%s" fail - attribute "%s": "%s"';

    /**
     * @errorCode Content-type said json and content must be contains {document:{attributes:[]}
     */
    const ROUTES0108 = 'Record fail. Attribute "%s" has not "value" property in json';
    /**
     * @errorCode The document cannot be recorded
     */
    const ROUTES0109 = 'Update document "%s" fail  : "%s"';

    /**
     * @errorCode The document cannot be deleted
     */
    const ROUTES0110 = 'Delete deny for document "%s" fail : "%s" ';
    /**
     * @errorCode The document cannot be deleted
     */
    const ROUTES0111 = 'Delete Document "%s" fail : "%s" ';
    /**
     * @errorCode The document is not deleted
     */
    const ROUTES0112 = 'Document "%s" is not in the trash';
    /**
     * @errorCode The document is not deleted
     */
    const ROUTES0113 = 'Cannot update deleted document "%s"';
    /**
     * @errorCode The image file must be an real image
     */
    const ROUTES0114 = 'Asset Image file "%s" is not an image';
    /**
     * @errorCode The image file must be in "Images" directory
     */
    const ROUTES0115 = 'Asset Image file "%s" not found';
    /**
     * @errorCode The attribute set in url is not part of document
     */
    const ROUTES0116 = 'Cannot download file : Attribut "%s" of document "%s" not exists';
    /**
     * @errorCode The attribute as an "I" visibility
     */
    const ROUTES0117 = 'Access denied to download image : Attribut "%s" of document "%s" is protected';

    /**
     * @errorCode The attribute value is empty
     */
    const ROUTES0118 = 'No image in attribute "%s" (index "%s") in document "%s"';
    /**
     * @errorCode The vault id set in attribute not exists
     */
    const ROUTES0119 = 'Image id not exists in attribute "%s" (index "%s") in document "%s"';
    /**
     * @errorCode The attribute value is malformed
     */
    const ROUTES0120 = 'Incorrect value in file/image in attribute "%s" (index "%s") in document "%s"';
    /**
     * @errorCode The index must be greater or equal to 0
     */
    const ROUTES0121 = 'Incorrect index "%s" attribute "%s" is multiple in document "%s"';
    /**
     * @errorCode The index for a single value must be -1
     */
    const ROUTES0122 = 'Incorrect index "%s" (must be -1) attribute "%s" is not multiple in document "%s"';
    /**
     * @errorCode The index to select attribute value is -1 for single value or >=0 for multiple values
     */
    const ROUTES0123 = 'Incorrect index "%s" (must be >= -1) attribute "%s" in document "%s"';

    /**
     * @errorCode The attribute must reference a file
     */
    const ROUTES0124 = 'Attribute "%s" is not a file or image attribute in document "%s"';
    /**
     * @errorCode Document reference must be an folder id
     */
    const ROUTES0125 = 'Document "%s" not a folder';
    /**
     * @errorCode Document reference must be an search id
     */
    const ROUTES0126 = 'Document "%s" not a search';
    /**
     * @errorCode ApplicationContext of accesses must reference recorded application
     */
    const ROUTES0127 = 'Accesses : ApplicationContext  "%s" not found';
    /**
     * @errorCode Cannot defined same route twice
     */
    const ROUTES0128 = 'Config : route  "%s" already defined';
    /**
     * @errorCode Cannot defined same route twice
     */
    const ROUTES0129 = 'Config : override route  only partial or complete is allowed. Found "%s"';
    /**
     * @errorCode Cannot override a route not recorded
     */
    const ROUTES0130 = 'Config : override route. No find "%s" route to override.';
    /**
     * @errorCode Current user has no enought privileges to execute route
     */
    const ROUTES0131 = 'Route access deny : %s';
    /**
     * @errorCode The requiredAccess defined in route configuration not respect syntax
     */
    const ROUTES0132 = 'Route access misconfiguration';
    /**
     * @errorCode The requiredAccess reference unknow access right
     */
    const ROUTES0133 = 'Route access : Unknow acl "%s".';
    /**
     * @errorCode Cannot defined same app twice
     */
    const ROUTES0134 = 'Config : app  "%s" already defined';
    /**
     * @errorCode Override app config : only partial mode
     */
    const ROUTES0135 = 'Config : override app  only partial is allowed. Found "%s"';
}

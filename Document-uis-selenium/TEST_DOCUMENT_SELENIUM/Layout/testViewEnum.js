define(['underscore'], function enumOtherCustom(_)
{
    'use strict';
    /**
     * Add custom class for enum which are other value
     */
    window.dcp.document.documentController("addEventListener",
        "attributeReady",
        {
            "name": "tstddui.enumother",
            "documentCheck": function checkDduiEnumReady(document)
            {
                return document.family.name === "TST_DDUI_ENUM";
            },
            "attributeCheck": function isReadEnum(attribute)
            {
                var attributeProperties=attribute.getProperties();
                if (attributeProperties.type === "enum" && attributeProperties.mode==="read") {
                    return true;
                }
            }
        },
        function testDduiEnumReady(event, documentObject, attributeObject, $el, index)
        {
            var enumValue = attributeObject.getValue(), contentsEls, cssTarget;

            if (!_.isUndefined(index)) {
                // Attribute is in an array : get only row value
                enumValue = enumValue[index];
            }
            cssTarget = ".dcpAttribute__content__value";

            if (_.isArray(enumValue)) {
                //  Attribute has multiple option

                contentsEls = $el.find(cssTarget);

                _.each(enumValue, function setCustomClass(singleValue, $k)
                {
                    if (singleValue.exists === false) {
                        $(contentsEls[$k]).addClass("custom-enum--other");
                    }
                });
            } else {
                if (enumValue.exists === false) {
                    $el.find(cssTarget).addClass("custom-enum--other");
                }
            }
        }
    );
});
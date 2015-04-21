/*global define, setFixtures, sandbox */
define([
    "jquery",
    "underscore",
    'dcpDocument/models/mDocument'
], function ($, _, ModelDocument)
{

    "use strict";

    return {
        setAllValues: function setAllValues(value)
        {
            if (value.value && !_.has(value, "displayValue")) {
                value.displayValue = value.value;
            }
            return value;
        },

        generateModelDocument : function generateModelDocument(options, title, attributes, renderOptions) {
            var localId = _.uniqueId("Document");
            return new ModelDocument(
                {
                    properties: {
                        id: localId,
                        title: title + "_" + localId,
                        fromname: localId,
                        family: {
                            title: localId
                        }
                    },
                    menus: [],
                    locale: options.locale || "fr_FR",
                    renderMode: options.renderMode || "view",
                    attributes: attributes,
                    renderOptions: renderOptions || {}
                }
            );
        },

        generateFamilyStructure: function generateFamilyStructure(attrDef, renderMode, value)
        {
            var structure = [], secondStruct, attrStruct = {
                "id": "test_f_frame",
                "visibility": "W",
                "label": "frame",
                "type": "frame",
                "logicalOrder": 0,
                "multiple": false,
                "options": [],
                "renderMode": renderMode,
                "content": {}
            }, localeAttrId = attrDef.id || _.uniqueId(attrDef.type);

            structure.localeAttrId = localeAttrId;

            structure.push(attrStruct);

            if (localeAttrId) {
                value = _.clone(value);
                secondStruct = {
                    "id": localeAttrId,
                    "visibility": attrDef.visibility || 'W',
                    "label": attrDef.label || ("label of " + localeAttrId),
                    "label_old": localeAttrId,
                    "type": attrDef.type,
                    "logicalOrder": 0,
                    "multiple": false,
                    "options": attrDef.options || [],
                    "renderMode": renderMode,
                    "content": {},
                    "attributeValue": value,
                    "parent": "test_f_frame"
                };
                secondStruct = _.extend(secondStruct, attrDef);

                attrStruct.content[localeAttrId] = _.extend(secondStruct, attrDef);
                structure.push(secondStruct);
            }
            return structure;
        },

        generateSandBox: function generateSandBox(config, $renderZone)
        {
            var currentSandbox;
            if (config.noFixture || window.location.hash === "#displayDom") {
                currentSandbox = $("<div></div>");
                if ($renderZone.length === 0) {
                    $renderZone = $("body");
                }
                $renderZone.append(currentSandbox);
            } else {
                currentSandbox = setFixtures(sandbox());
            }
            return currentSandbox;
        }
    };
});

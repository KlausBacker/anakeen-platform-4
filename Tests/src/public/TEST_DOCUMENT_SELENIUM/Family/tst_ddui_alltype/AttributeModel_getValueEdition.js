
/**
 * test getValue method during document edition
 */

( function(window){
        'use strict';
        
        var attributeValue, attributeId, attributeType, msgColor = '';

        function test() {

            function msg(error){
                error ? msgColor = "warning": msgColor = "success";
                return DisplayMessage()
            }
            function attributType1(AttributeValue) {
                return AttributeValue.value == undefined ? msg(true) : msg(false);
            };
            function attributType2(AttributeValue) {
                if (AttributeValue[0] && AttributeValue[0].length) {
                    attributeValue.value = [];
                    AttributeValue.forEach(function (item) {
                        item.forEach(function (t) {
                            attributeValue.value.push(t.value)
                        });
                        return attributeValue.indexOf(undefined) >= 0 ? msg(true) : msg(false);
                    })
                }
                else
                {
                    AttributeValue.forEach(function (item) {
                        attributeValue = item
                        return  item.value == undefined ? msg(true) : msg(false)
                    })
                }
            }
            function DisplayMessage() {
                window.dcp.document.documentController("showMessage",
                    {
                        type: msgColor,
                        htmlMessage: "<p> attribute : " + attributeId + "<p/> " +
                        "<p> type : " + attributeType + "<p/>" +
                        "<p>  value : " + attributeValue.value + "<p/>"
                    }
                );
            };
            window.dcp.document.documentController("addEventListener",
                "attributeReady",
                {
                    "name": "tstddui.attributeGetValueEdition",
                    "documentCheck": function checkDduigetValueEdition(document) {
                        return document.family.name === "TST_DDUI_ALLTYPE";
                    },
                    "attributeCheck": function attributeGetValue(attribute) {
                        if (attribute.id) {
                            return true
                        }
                    }
                },
                function testDduigetValueEdition(event, documentObject, attributeObject, $el, message) {

                    attributeType = attributeObject._attributeModel.attributes.type;
                    attributeId = attributeObject.id;

                    if (attributeObject._attributeModel.attributes.isValueAttribute)
                    {
                        attributeValue = attributeObject.getValue("previous");
                        switch (attributeValue && attributeValue.length)
                        {
                            case undefined:
                                attributType1(attributeValue);
                                break;
                            default:
                                attributType2(attributeValue)
                        }
                    }
                    return true
                }
            )
        }
        window.dcp.document.documentController("addEventListener",
            "ready",
            function(event, documentObject, message) {
                this.documentController("showMessage", "I'm ready");
                test()
            }
        );
    }
)(window)


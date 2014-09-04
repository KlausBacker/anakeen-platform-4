/*global require, describe, beforeEach, setFixtures, expect, it, sandbox, spyOnEvent, jasmine, afterEach*/

var asset = "index.php";
if (window.__karma__) {
    asset = "guest.php";
}

require([
    'underscore',
    'jquery',
    'text!template/' + asset + '?app=DOCUMENT&action=TEMPLATE',
    'models/document',
    'views/document/vDocument'
], function (_, $, template, ModelDocument, ViewDocument) {
    "use strict";

    var testDocument,
        generateFamilyStructure,
        generateDocumentContent;

    template = JSON.parse(template);

    if (template.success !== true) {
        throw new Error("Unable to parse template " + template.error || "");
    }

    template = template.content;

    window.dcp = window.dcp || {};

    window.dcp.templates = _.defaults(window.dcp.templates, template) || template;

    generateFamilyStructure = function (localeAttrId, type) {
        var struct = {
            structure : {
                'test_f_frame' : {
                    id :         'test_f_frame',
                    label :      'frame',
                    multiple :   false,
                    options :    [],
                    type :       'frame',
                    visibility : 'W',
                    content :    {

                    }
                }
            }
        };
        if (localeAttrId) {
            struct.structure.content[localeAttrId] = {
                id :         localeAttrId,
                label :      localeAttrId,
                multiple :   false,
                options :    [],
                type :       type,
                visibility : 'W'
            };
        }
        return struct;
    };

    generateDocumentContent = function (localeAttrId, value) {
        var data = {};

        if (localeAttrId) {
            data[localeAttrId] = {
                displayValue : value.displayValue || value,
                value :        value.value || value
            };
        }

        return data;
    };

    testDocument = function (title, options) {
        var modelDocument, currentSandbox, getSandbox = function () {
            return currentSandbox;
        };

        describe(title + " Test Document", function () {
            beforeEach(function () {
                var localId = _.uniqueId("Document");
                var $renderZone = $("#render");
                if ($renderZone.length === 0) {
                    $renderZone = $("body");
                }
                currentSandbox = $("<div></div>");
                $renderZone.append(currentSandbox);
                //currentSandbox = setFixtures(sandbox());
                modelDocument = new ModelDocument(
                    {},
                    {
                        properties : {id : localId, title : title+"_"+localId, fromname : localId, fromtitle : localId},
                        menus :      [],
                        family :     options.familyContent || generateFamilyStructure(),
                        locale :     options.locale || "fr_FR",
                        renderMode : options.renderMode || "view",
                        attributes : options.attributes || generateDocumentContent()
                    }
                );
            });

            describe(title + " : creation", function () {

                it("content", function () {
                    var $sandBox = getSandbox();
                    (new ViewDocument({model : modelDocument, el : $sandBox}).render());
                    expect($sandBox).toHaveClass("dcpDocument");
                    expect($sandBox).toHaveClass("dcpDocument--" + modelDocument.get("renderMode"));
                });

                it("header", function () {
                    var $sandBox = getSandbox();
                    (new ViewDocument({model : modelDocument, el : $sandBox}).render());
                    expect($sandBox.find(".dcpDocument__header")).toExist();
                    expect($sandBox.find(".dcpDocument__header__icon")).toExist();
                    expect($sandBox.find(".dcpDocument__header__icon")).toHaveAttr('src', modelDocument.get("properties").get("icon") || "");
                    expect($sandBox.find(".dcpDocument__header__title")).toExist();
                    expect($sandBox.find(".dcpDocument__header__title")).toHaveText(modelDocument.get("properties").get("title"));
                    expect($sandBox.find(".dcpDocument__header__family")).toExist();
                    expect($sandBox.find(".dcpDocument__header__family")).toHaveText(modelDocument.get("properties").get("fromtitle") || "");
                });

                it("menu", function () {
                    var $sandBox = getSandbox();
                    (new ViewDocument({model : modelDocument, el : $sandBox}).render());
                    expect($sandBox.find(".dcpDocument__menu")).toExist();
                });

                it("content", function () {
                    var $sandBox = getSandbox();
                    (new ViewDocument({model : modelDocument, el : $sandBox}).render());
                    expect($sandBox.find(".dcpDocument__body")).toExist();
                    expect($sandBox.find(".dcpDocument__form")).toExist();
                    expect($sandBox.find(".dcpDocument__frames")).toExist();
                    expect($sandBox.find(".dcpDocument__tabs")).toExist();
                });

                it("footer", function () {
                    var $sandBox = getSandbox();
                    (new ViewDocument({model : modelDocument, el : $sandBox}).render());
                    expect($sandBox.find(".dcpDocument__footer")).toExist();
                });

                it("event", function () {
                    var $sandBox = getSandbox(), view, renderDone, elementRendered;
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    renderDone = jasmine.createSpy("renderDone");
                    elementRendered = jasmine.createSpy("elementRendered");
                    view.on("renderDone", renderDone);
                    view.on("partRender", elementRendered);
                    view.render();
                    expect(renderDone).toHaveBeenCalled();
                    expect(elementRendered.calls.count()).toEqual(modelDocument.get("attributes").length);
                });
            });
        });
    };

    testDocument("view", false, "Charles Bonnissent", {});
    testDocument("edit", "text", "Charles Bonnissent", {renderMode : "edit"});
    testDocument("view locale us", "text", "Charles Bonnissent", {locale : "en_US"});
    testDocument("edit locale us", "text", "Charles Bonnissent", {renderMode : "edit", locale : "en_US"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }
});
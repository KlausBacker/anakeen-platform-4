define([
    'underscore',
    'mustache',
    'kendo/kendo.core',
    'widgets/widget',
    'kendo/kendo.window'
], function (_, Mustache, kendo) {
    'use strict';

    $.widget("dcp.dcpDocumentProperties", {
        options: {
            documentId: 0,
            window: {
                modal: true,
                animation: {
                    open: {
                        effects: "fade:in",
                        duration: 1000
                    }, close: {
                        effects: "fade:out",
                        duration: 1000
                    }
                },
                actions: [
                    "Maximize",
                    "Close"
                ],
                visible: false,
                height: "300px",
                width: "500px",
                title: "Document properties"
            },
            labels: {
                identifier: "Identifier",
                title: "Title",
                logicalName: "Logical name",
                revision: "Revision number",
                version: "Version",
                family: "Family",
                lockedBy: "Locked by",
                createdBy: "Created by",
                notLocked: "Not locked",
                confidential: "Confidential",
                notConfidential: "Not confidential",
                creationDate: "Creation date",
                lastModificationDate: "Last modification date",
                lastAccessDate: "Last access date",
                profil: "Profil",
                profilReference: "Profil reference",
                viewController: "View controller",
                property: "Property",
                propertyValue: "Value",
                workflow: "Workflow",
                activity: "Activity"
            }
        },
        documentProperties: null,
        htmlCaneva: function () {
            return '<table class="properties-main table table-condensed table-hover"><thead>' +
            '<tr class="properties-header">' +
            '<th class="properties-header--description">{{labels.property}} : </th>' +
            '<th class="properties-header--value">{{labels.propertyValue}}</th>' +
            '</tr></thead>' +
            '<tbody>' +
            '<tr><td class="properties-description">{{labels.title}}</td><td class="properties-value">{{title}}</td></tr>' +
            '<tr><td class="properties-description">{{labels.identifier}}</td><td class="properties-value">{{initid}}</td></tr>' +
            '<tr><td class="properties-description">{{labels.logicalName}}</td><td class="properties-value">{{name}}</td></tr>' +
            '<tr><td class="properties-description">{{labels.revision}}</td><td class="properties-value">{{revision}}</td></tr>' +
            '<tr><td class="properties-description">{{labels.version}}</td><td class="properties-value">{{version}}</td></tr>' +

            '<tr><td class="properties-separator" colspan="2"></td></tr>' +

            '<tr><td class="properties-description">{{labels.workflow}}</td><td class="properties-value"><a href="?app=DOCUMENT&id={{workflow.id}}">{{#workflow.icon}}<img src="{{workflow.icon}}"/>{{/workflow.icon}}{{workflow.title}}</a></td></tr>' +
            '<tr><td class="properties-description">{{labels.activity}}</td><td class="properties-value">{{#state.reference}}<div class="properties-value-statecolor" style="background-color:{{state.color}}"/>{{state.displayValue}}{{/state.reference}}</td></tr>' +


            '<tr><td class="properties-description">{{labels.family}}</td><td class="properties-value"><a href="?app=DOCUMENT&id={{family.id}}"><img src="{{family.icon}}"/>{{family.title}}</a></td></tr>' +

            '<tr><td class="properties-separator" colspan="2"></td></tr>' +
            '<tr><td class="properties-description">{{labels.createdBy}}</td><td class="properties-value"><a href="?app=DOCUMENT&id={{createdBy.id}}"><img src="{{createdBy.icon}}"/>{{createdBy.title}}</a></td></tr>' +

            '<tr><td class="properties-description">{{labels.lockedBy}}</td><td class="properties-value">' +
            '{{#security.lock.lockedBy.id}}' +
            '<a href="?app=DOCUMENT&id={{security.lock.lockedBy.id}}"><img src="{{security.lock.lockedBy.icon}}"/>{{security.lock.lockedBy.title}}</a>' +
            '{{/security.lock.lockedBy.id}}' +
            '{{^security.lock.lockedBy.id}}{{labels.notLocked}}{{/security.lock.lockedBy.id}}' +
            '</td></tr>' +


            '<tr><td class="properties-description">{{labels.confidential}}</td><td class="properties-value">' +
            '{{#confidential}}' +
            '{{labels.confidential}}' +
            '{{/confidential}}' +
            '{{^confidential}}{{labels.notConfidential}}{{/confidential}}' +
            '</td></tr>' +

            '<tr><td class="properties-separator" colspan="2"></td></tr>' +


            '<tr><td class="properties-description">{{labels.creationDate}}</td><td class="properties-value">{{#formatDate}}{{creationDate}}{{/formatDate}}</td></tr>' +

            '<tr><td class="properties-description">{{labels.lastModificationDate}}</td><td class="properties-value">{{#formatDate}}{{lastModificationDate}}{{/formatDate}}</td></tr>' +
            '<tr><td class="properties-description">{{labels.lastAccessDate}}</td><td class="properties-value">{{#formatDate}}{{lastAccessDate}}{{/formatDate}}</td></tr>' +

            '<tr><td class="properties-separator" colspan="2"></td></tr>' +
            '<tr><td class="properties-description">{{labels.profil}}</td><td class="properties-value"><a href="?app=DOCUMENT&id={{security.profil.id}}">{{#security.profil.icon}}<img src="{{security.profil.icon}}"/>{{/security.profil.icon}}{{security.profil.title}}</a></td></tr>' +
            '<tr><td class="properties-description">{{labels.profilReference}}</td><td class="properties-value"><a href="?app=DOCUMENT&id={{security.profil.reference.id}}">{{#security.profil.reference.icon}}<img src="{{security.profil.reference.icon}}"/>{{/security.profil.reference.icon}}{{security.profil.reference.title}}</a></td></tr>' +

            '<tr><td class="properties-description">{{labels.viewController}}</td><td class="properties-value"><a href="?app=DOCUMENT&id={{viewController.id}}">{{#viewController.icon}}<img src="{{viewController.icon}}"/>{{/viewController.icon}}{{viewController.title}}</a></td></tr>' +

            '</tbody></table>';
        },

        currentWidget: null,
        _create: function () {
            this.currentWidget = $('<div class="document-properties"/>');


            this.element.append(this.currentWidget);

            this._displayProperties();

            this.element.data("dcpDocumentProperties", this);

            this.currentWidget.kendoWindow(this.options.window);

        },

        _displayProperties: function wPropertiesGetProperties() {
            var scope = this;
            $.getJSON("api/v1/documents/" + this.options.documentId +
            ".json?fields=document.properties.all").
                done(function (data) {
                    var info;
                    scope.documentProperties = data.data.document.properties;
                    info = _.extend(scope.documentProperties, {labels: scope.options.labels});
                    info.formatDate = function () {
                        return function (text, render) {
                            return kendo.toString(new Date(render(text).replace(' ','T')), "G");


                        };
                    };
                    scope.currentWidget.html(Mustache.render(scope.htmlCaneva(), info));

                    scope.currentWidget.data("kendoWindow").center();

                }).fail(function (xhr) {
                    var result = JSON.parse(xhr.responseText);
                    window.alert(result.exceptionMessage);
                });
        },

        open: function open() {
            this.currentWidget.data("kendoWindow").open();
        }


    });
});
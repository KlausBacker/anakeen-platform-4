/*global define*/
define([
    'jquery',
    'underscore',
    'backbone',
    'mustache',
    'dcpDocument/views/attributes/vAttribute',
    'dcpDocument/views/document/attributeTemplate'
], function vColumn($, _, Backbone, Mustache, ViewAttribute, attributeTemplate)
{
    'use strict';

    return ViewAttribute.extend({

        /**
         * Use special event to trigger only attributes of model
         */
        events: function vColumnEvents()
        {
            var absEvents = {
                "dcparraylineadded": "addNewWidget"
            };
            this._addEvent(absEvents, "changeattrsvalue", "changeAttributesValue");
            this._addEvent(absEvents, "delete", "deleteValue");
            this._addEvent(absEvents, "fetchdocument", "loadDocument");
            this._addEvent(absEvents, "downloadfile", "downloadFileSelect");
            this._addEvent(absEvents, "externallinkselected", "externalLinkSelected");
            this._addEvent(absEvents, "changeattrmenuvisibility", "changeMenuVisibility");
            this._addEvent(absEvents, "uploadfile", "uploadFileSelect");
            this.listenTo(this.model, "change:label", this.changeLabel);
            return absEvents;
        },

        _addEvent: function vColumn_addEvent(events, name, method)
        {
            events["dcpattribute" + name + ' .dcpArray__content__cell[data-attrid="' + this.model.id + '"]'] = method;
        },

        render: function vColumnRender()
        {
            var scope = this;
            if (this.displayLabel === false) {
                // Need to defer because thead is not construct yet
                _.defer(function vColumnHideHead()
                {
                    var $head = scope.$el.find('.dcpArray__head__cell[data-attrid="' + scope.model.id + '"]');
                    $head.hide();
                });
            } else {
                // Need to defer because thead is not construct yet
                _.defer(function vColumnDescriptionHead()
                {
                    var $head = scope.$el.find('.dcpArray__head__cell[data-attrid="' + scope.model.id + '"]');
                    attributeTemplate.insertDescription(scope, $head);
                });
            }
            this.model.trigger("renderColumnDone", {model: this.model, $el: this.$el});
            return this;
        },

        /**
         * Change the label of the column
         */
        changeLabel: function vColumnChangeLabel()
        {
            this.$el.find('.dcpArray__head__cell[data-attrid="' + this.model.id + '"]').text(this.model.get("label"));
        },

        /**
         * called by vArray::addLine()
         * @param index row index
         * @param customView HTML fragment to use for a custom view
         */
        addNewWidget: function vColumnAddNewWidget(index, customView)
        {
            if (this.options) {
                var cells = this.options.parentElement.find('.dcpArray__content__cell[data-attrid="' + this.model.id + '"]'),
                    $el, data = this.getData(index), event = {prevent: false};

                if (cells[index]) {
                    try {
                        $el = $(cells[index]);
                        this.model.trigger("beforeRender", event, {model: this.model, $el: $el, index: index});
                        if (event.prevent) {
                            return this;
                        }
                        if (customView) {
                            $el.append(customView);
                        } else {
                            this.widgetInit($el, data);
                            attributeTemplate.insertDescription(this, $el.parent());
                        }
                        this.model.trigger("renderDone", {model: this.model, $el: $el, index: index});
                        this.moveValueIndex({});
                    } catch (error) {
                        if (window.dcp.logger) {
                            window.dcp.logger(error);
                        } else {
                            console.error(error);
                        }
                    }
                }
            }
        },

        /**
         *
         * @param event
         * @param options
         */
        loadDocument: function vColumnLoadDocument(event, options)
        {
            var tableLine = options.tableLine,
                index = options.index,
                initid,
                valueLine = this.model.get("attributeValue")[tableLine],
                documentModel = this.model.getDocumentModel();
            if (_.isUndefined(index)) {
                initid = valueLine.value;
            } else {
                initid = valueLine[index].value;
            }

            this.model.trigger("internalLinkSelected", event, {
                eventId: "document.load",
                target: event.target,
                attrid: this.model.id,
                options: [initid, "!defaultConsultation"],
                index: options.index,
                row: tableLine
            });

            if (event.prevent) {
                return this;
            }

            documentModel.fetchDocument({
                "initid": initid,
                "revision": -1,
                "viewId": "!defaultConsultation"
            });
        },

        /**
         * Hide all items of the column
         */
        hide: function vColumnHide()
        {
            this.getDOMElements().each(function vColumnHideEach()
            {
                var $cell = $(this);
                var tagName = $cell.prop("tagName").toLowerCase();

                if (tagName !== "td" && tagName !== "th") {
                    $cell = $cell.closest("td.dcpArray__cell, th.dcpArray__head");
                }
                $cell.hide();
            });

            this.$el.find('thead th[data-attrid="' + this.model.id + '"]').hide();
        },
        /**
         * Show all hidden items of the column
         */
        show: function vColumnShow()
        {
            this.getDOMElements().each(function vColumnShowEach()
            {
                var $cell = $(this);
                var tagName = $cell.prop("tagName").toLowerCase();

                if (tagName !== "td" && tagName !== "th") {
                    $cell = $cell.closest("td.dcpArray__cell, th.dcpArray__head");
                }
                $cell.show();
            });
            this.$el.find('thead th[data-attrid="' + this.model.id + '"]').show();
        }
    });
});
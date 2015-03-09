/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'dcpDocument/views/attributes/vAttribute'
], function (_, Backbone, Mustache, ViewAttribute) {
    'use strict';

    return ViewAttribute.extend({

        /**
         * Use special event to trigger only attributes of model
         * @returns {}
         */
        events: function () {
            var absEvents = {
                "dcparraylineadded": "addNewWidget"
            };
            this._addEvent(absEvents, "changeattrsvalue", "changeAttributesValue");
            this._addEvent(absEvents, "delete", "deleteValue");
            this._addEvent(absEvents, "changedocument", "changeDocument");
            return absEvents;
        },

        _addEvent: function (events, name, method) {
            events["dcpattribute" + name + ' .dcpArray__content__cell[data-attrid="' + this.model.id + '"]'] = method;
        },

        render: function () {
            var scope=this;
            if (this.displayLabel === false) {
                // Need to defer because thead is not construct yet
                _.defer(function () {
                    scope.$el.find('.dcpArray__head__cell[data-attrid="'+scope.model.id+'"]').hide();
                });
            }
            this.model.trigger("renderDone", {model : this.model, $el : this.$el});
            return this;
        },

        /**
         * called by vArray::addLine()
         * @param index
         */
        addNewWidget: function addNewWidget(index, customView) {
            if (this.options) {
                var cells = this.options.parentElement.find('.dcpArray__content__cell[data-attrid="' + this.model.id + '"]');
                var data = this.getData(index);

                if (cells[index]) {
                    try {
                        if (customView) {
                            $(cells[index]).append(customView);
                        } else {
                            this.widgetInit($(cells[index]), data);
                        }
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
        changeDocument: function changeDocument(event, options) {
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
            documentModel.clear().set({
                "initid": initid,
                "revision": -1,
                "viewId": "!defaultConsultation"
            }).fetch();
        }

    });

});
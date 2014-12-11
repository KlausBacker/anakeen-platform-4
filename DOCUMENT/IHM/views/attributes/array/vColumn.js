/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'views/attributes/vAttribute'
], function (_, Backbone, Mustache, ViewAttribute) {
    'use strict';

    return ViewAttribute.extend({

        /**
         * Use special event to trigger only attributes of model
         * @returns {}
         */
        events : function () {
            var absEvents = {
                "dcparraylineadded" : "addNewWidget"
            };
            this._addEvent(absEvents, "changeattrsvalue", "changeAttributesValue");
            this._addEvent(absEvents, "delete", "deleteValue");
            this._addEvent(absEvents, "changedocument", "changeDocument");
            return absEvents;
        },

        _addEvent : function (events, name, method) {
            events["dcpattribute" + name + ' .dcpArray__content__cell[data-attrid="' + this.model.id + '"]'] = method;
        },

        render : function () {
            return this;
        },

        /**
         * called by vArray::addLine()
         * @param index
         */
        addNewWidget : function addNewWidget(index) {
            if (this.options) {
                var cells = this.options.parentElement.find('.dcpArray__content__cell[data-attrid="' + this.model.id + '"]');
                var data = this.getData(index);
                var widgetClass = this.getTypedWidgetClass(data.type);

                if (cells[index]) {
                    try {
                        widgetClass.apply($(cells[index]), [data]);
                    } catch (error) {
                        window.TraceKit.report(error);
                        console.error(error);
                    }
                }
            }
        },

        /**
         *
         * @param event
         * @param options
         */
        changeDocument : function changeDocument(event, options) {
            var tableLine = options.tableLine,
                index = options.index,
                initid,
                value = this.model.get("value")[tableLine],
                documentModel = this.model.getDocumentModel();
            if (_.isUndefined(index)) {
                initid = value.value;
            } else {
                initid = value[index].value;
            }
            documentModel.clear().set({
                "initid" :   initid,
                "revision" : -1,
                "viewId" :   "!defaultConsultation"
            }).fetch();
        }

    });

});
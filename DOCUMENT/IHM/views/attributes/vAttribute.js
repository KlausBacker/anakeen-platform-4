/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'widgets/attributes/label/wLabel',
    'widgets/attributes/text/wText',
    'widgets/attributes/int/wInt',
    'widgets/attributes/longtext/wLongtext',
    'widgets/attributes/htmltext/wHtmltext',
    'widgets/attributes/timestamp/wTimestamp',
    'widgets/attributes/time/wTime',
    'widgets/attributes/image/wImage',
    'widgets/attributes/file/wFile',
    'widgets/attributes/double/wDouble',
    'widgets/attributes/docid/wDocid'
], function (_, Backbone, Mustache) {
    'use strict';

    return Backbone.View.extend({

        className: "row dcpAttribute form-group",

        events: {
            "dcpattributechange .dcpAttribute__contentWrapper": "updateValue",
            "dcpattributedelete .dcpAttribute__contentWrapper": "deleteValue",
            "dcpattributechangeattrmenuvisibility .dcpAttribute__contentWrapper": "changeMenuVisibility",
            "dcpattributechangeattrsvalue .dcpAttribute__contentWrapper": "changeAttributesValue"
        },

        initialize: function () {
            this.listenTo(this.model, 'change:label', this.refreshLabel);
            this.listenTo(this.model, 'change:value', this.refreshValue);
            this.listenTo(this.model, 'change:errorMessage', this.refreshError);
            this.listenTo(this.model, 'destroy', this.remove);
            this.templateWrapper = window.dcp.templates.attribute.simpleWrapper;
        },


        getData: function (index) {
            var data;

            if (typeof index === "undefined" || index === null) {
                data = this.model.toJSON();
            } else {
                data = this.model.toData(index);
            }
            data.viewCid = this.cid;
            data.renderOptions = this.model.getOptions();
            data.deleteLabels = this.getDeleteLabels();
            data.locale = this.model.get("documentModel").get("locale");
            data.templates={};
            if (window.dcp && window.dcp.templates && window.dcp.templates.attribute) {
                if (window.dcp.templates.attribute[this.model.get("type")]) {
                data.templates = window.dcp.templates.attribute[this.model.get("type")];
                } else {
                    // fallback in case of no specific templates
                    data.templates = window.dcp.templates.attribute.default;
                }
            }
            data.templates.label = window.dcp.templates.attribute["label"];
            // autoComplete detected
            data.autocompleteRequest = _.bind(this.autocompleteRequestRead, this);
            return data;
        },


        render: function () {
            //console.time("render attribute " + this.model.id);
            var data = this.getData();


            this.$el.addClass("dcpAttribute--type--" + this.model.get("type"));
            this.$el.addClass("dcpAttribute--visibility--" + this.model.get("visibility"));
            if (this.model.get("needed")) {
                this.$el.addClass("dcpAttribute--needed");
            }
            this.$el.append($(Mustache.render(this.templateWrapper, data)));
            this.$el.find(".dcpAttribute__label").dcpLabel(data);


            this.widgetApply(this.$el.find(".dcpAttribute__contentWrapper"), data);

           // console.timeEnd("render attribute " + this.model.id);
            return this;
        },

        refreshLabel: function () {
            this.getDOMElements().find(".dcpAttribute__label").dcpLabel("setLabel", this.model.get("label"));
        },

        refreshValue: function () {
            var values = this.model.get("value");
            var scope = this;
            console.log("propagate setvalue to view", this.model.id, this.model.get("value"));
             if (this.model.inArray()) {
                 // adjust line number to column length
                 var arrayWrapper=this.$el;

                 console.log("find array", arrayWrapper,this.$el);
                 arrayWrapper.dcpArray("setLines", values.length);
             }


            var allWrapper = this.getDOMElements().find(".dcpAttribute__contentWrapper").add(this.getDOMElements().filter(".dcpAttribute__contentWrapper"));

            if (this.model.inArray()) {


                values = _.toArray(values);

                allWrapper.each(function (index, element) {
                    console.log("set widget setvalue", index, scope.model.id,values[index] );
                    scope.widgetApply($(element), "setValue", values[index]);
                });

            } else {
                this.widgetApply(allWrapper, "setValue", values);
            }

        },
        refreshError: function (event) {
            var parentId = this.model.get('parent');
            this.$el.find(".dcpAttribute__label").dcpLabel("setError", this.model.get("errorMessage"));
            this.widgetApply(this.getDOMElements().find(".dcpAttribute__contentWrapper"), "setError", this.model.get("errorMessage"));
            if (parentId) {
                var parentModel = this.getAttributeModel(parentId);
                if (parentModel) {
                    parentModel.trigger("errorMessage", event, this.model.get("errorMessage"));
                }

            }
        },


        /**
         * Modify several attribute
         * @param event event object
         * @param dataItem values {id: menuId, visibility: "disabled", "visible", "hidden"}
         * @param valueIndex the index which comes from modification action
         */
        changeAttributesValue: function (event, dataItem, valueIndex) {
            var scope = this;

            console.log("changeAttributesValue for", this.model.id);
            _.each(dataItem.values, function (val, aid) {
                if (typeof val === "object") {
                    console.log("changeAttributesValue", aid, scope.model, aid);
                    console.log("changeAttributesValue IDX", valueIndex);
                    var attrModel = scope.model.get("documentModel").get('attributes').get(aid);
                    if (attrModel) {

                        console.log("YEAH", attrModel.id, val, valueIndex);
                        if (attrModel.hasMultipleOption()) {
                            attrModel.addValue({value: val.value, displayValue: val.displayValue}, valueIndex);
                        } else {
                            console.log("SETTO", {value: val.value, displayValue: val.displayValue}, valueIndex);
                            attrModel.setValue({value: val.value, displayValue: val.displayValue}, valueIndex);
                        }
                    }
                }
            });
        },

        /**
         * Delete value,
         * If has help, clear also target attributes
         * @param event
         * @param data index info {index:"the index}
         */
        deleteValue: function (event, data) {
            if (data.id === this.model.id) {
                var attrToClear = this.model.get('helpOutputs');
                var docModel = this.model.get("documentModel");
                console.log("Need delete (1)", this.model.id, data, this.model);
                if ((!attrToClear) || typeof attrToClear === "undefined") {
                    attrToClear = [this.model.id];
                } else {
                    attrToClear = _.toArray(attrToClear);
                }
                console.log("Need delete", attrToClear);

                _.each(attrToClear, function (aid) {
                    var attr = docModel.get('attributes').get(aid);
                    if (attr) {
                        if (attr.hasMultipleOption()) {
                            attr.setValue([], data.index);
                        } else {
                            attr.setValue({value: null, displayValue: ''}, data.index);
                        }
                    }
                });
            } else {
                console.log("NO delete", this.model.id, data, this.model);
            }
        },

        getAttributeModel: function (attributeId) {
            var docModel = this.model.get("documentModel");
            return docModel.get('attributes').get(attributeId);
        },

        getDeleteLabels: function () {

            var attrToClear = this.model.get('helpOutputs');
            var scope = this;
            var attrLabels = '';
            if ((!attrToClear) || typeof attrToClear === "undefined") {
                attrToClear = [this.model.id];
            } else {
                attrToClear = _.toArray(attrToClear);
            }
            attrLabels = _.map(attrToClear, function (aid) {
                var attr = scope.getAttributeModel(aid);
                if (attr) {
                    return attr.attributes.label;
                }
                return '';
            });
            return attrLabels;
        },

        /**
         * method use for transport multiselect widget
         * @param options
         */
        autocompleteRequestRead: function (options) {
            var documentModel = this.model.get("documentModel");
            options.data.attributes = documentModel.getValues();
            $.ajax({
                type: "POST",
                url: "?app=DOCUMENT&action=AUTOCOMPLETE&attrid=" + this.model.id +
                    "&id=" + documentModel.id +
                    "&fromid=" + documentModel.get("properties").get("fromid"),
                data: options.data,

                dataType: "json", // "jsonp" is required for cross-domain requests; use "json" for same-domain requests
                success: function (result) {
                    // notify the data source that the request succeeded
                    options.success(result);
                },
                error: function (result) {
                    // notify the data source that the request failed
                    options.error(result);
                }
            });
        },

        /**
         * Modify visibility access of an item menu
         * @param event event object
         * @param data menu config {id: menuId, visibility: "disabled", "visible", "hidden"}
         */
        changeMenuVisibility: function changeMenuVisibility(event, data) {
            console.log("attr menu vis", arguments);
            this.model.trigger("changeMenuVisibility", event, data);
        },
        getDOMElements: function () {
            if (this.options && this.options.els) {
                return this.options.els();
            } else {
                return this.$el;
            }
        },
        updateValue: function (event, data) {
            console.log("view has receive change", data.id, data);
            this.model.setValue(data.value, data.index);
        },

        widgetApply: function ($element, method, argument) {
            return this.getWidgetClass().apply($element, [method, argument]);
        },

        getWidgetClass: function () {
            return this.getTypedWidgetClass(this.model.get("type"));
        },


        getTypedWidgetClass: function (type) {
            switch (type) {
                case "text" :
                    return $.fn.dcpText;
                case "int" :
                    return $.fn.dcpInt;
                case "double" :
                    return $.fn.dcpDouble;
                case "longtext" :
                    return $.fn.dcpLongtext;
                case "htmltext" :
                    return $.fn.dcpHtmltext;
                case "date" :
                    return $.fn.dcpDate;
                case "timestamp" :
                    return $.fn.dcpTimestamp;
                case "time" :
                    return $.fn.dcpTime;
                case "image" :
                    return $.fn.dcpImage;
                case "file" :
                    return $.fn.dcpFile;
                case "account" :
                case "docid" :
                    return $.fn.dcpDocid;
                default:
                    return $.fn.dcpText;
            }
        }
    });


});

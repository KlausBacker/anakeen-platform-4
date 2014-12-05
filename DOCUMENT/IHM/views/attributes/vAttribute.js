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
    'widgets/attributes/money/wMoney',
    'widgets/attributes/enum/wEnum',
    'widgets/attributes/color/wColor',
    'widgets/attributes/password/wPassword',
    'widgets/attributes/file/wFile',
    'widgets/attributes/double/wDouble',
    'widgets/attributes/docid/wDocid'
], function (_, Backbone, Mustache) {
    'use strict';

    return Backbone.View.extend({

        className : "row dcpAttribute form-group",

        events : {
            "dcpattributechange .dcpAttribute__contentWrapper" :                   "updateValue",
            "dcpattributedelete .dcpAttribute__contentWrapper" :                   "deleteValue",
            "dcpattributechangeattrmenuvisibility .dcpAttribute__contentWrapper" : "changeMenuVisibility",
            "dcpattributechangeattrsvalue .dcpAttribute__contentWrapper" :         "changeAttributesValue"
        },

        initialize : function initialize(options) {
            this.listenTo(this.model, 'change:label', this.refreshLabel);
            this.listenTo(this.model, 'change:value', this.refreshValue);
            this.listenTo(this.model, 'change:errorMessage', this.refreshError);
            this.listenTo(this.model, 'destroy', this.remove);
            this.templateWrapper = this.model.getTemplates().attribute.simpleWrapper;
            this.listenTo(this.model, 'showTab', this.afterShow);
            this.options = options;
        },

        /**
         * The Data are the source of data shared with widget and templates
         *
         * @param index
         * @returns {*}
         */
        getData : function getData(index) {
            var data;

            //Made to JSON for all the values, or to data for value indexed (in cas of multiple)
            if (typeof index === "undefined" || index === null) {
                data = this.model.toJSON();
            } else {
                data = this.model.toData(index);
            }
            data.viewCid = this.cid;
            data.renderOptions = this.model.getOptions();
            data.labels = data.labels || {};
            data.labels.deleteAttributeNames = this.getDeleteLabels();
            data.locale = this.model.getDocumentModel().get("locale");
            data.templates = {};
            if (this.model.getTemplates().attribute) {
                if (this.model.getTemplates().attribute[this.model.get("type")]) {
                    data.templates = this.model.getTemplates().attribute[this.model.get("type")];
                } else {
                    // fallback in case of no specific templates
                    data.templates = this.model.getTemplates().attribute["default"];
                }
            }
            data.deleteButton = true;

            data.sourceValues = this.model.get("enumItems");
            data.sourceUri = this.model.get("enumUri");
            data.templates.label = this.model.getTemplates().attribute.label;
            // autoComplete detected
            data.autocompleteRequest = _.bind(this.autocompleteRequestRead, this);
            return data;
        },

        render : function render() {
            //console.time("render attribute " + this.model.id);
            var data = this.getData();
            this.$el.addClass("dcpAttribute--type--" + this.model.get("type"));
            this.$el.addClass("dcpAttribute--visibility--" + this.model.get("visibility"));
            this.$el.attr("data-attrid", this.model.get("id"));
            if (this.model.get("needed")) {
                this.$el.addClass("dcpAttribute--needed");
            }
            this.$el.append($(Mustache.render(this.templateWrapper, data)));
            this.$el.find(".dcpAttribute__label").dcpLabel(data);
            this.currentDcpWidget = this.widgetApply(this.$el.find(".dcpAttribute__contentWrapper"), data);
            return this;
        },

        refreshLabel : function refreshLabel() {
            this.getDOMElements().find(".dcpAttribute__label").dcpLabel("setLabel", this.model.get("label"));
        },

        /**
         * Autorefresh value when model change
         */
        refreshValue : function refreshValue() {
            var values = this.model.get("value"),
                scope = this, allWrapper, arrayWrapper;
            if (this.model.inArray()) {
                // adjust line number to column length
                arrayWrapper = this.$el;
                arrayWrapper.dcpArray("setLines", values.length);
            }

            allWrapper = this.getDOMElements().find(".dcpAttribute__contentWrapper")
                .add(this.getDOMElements().filter(".dcpAttribute__contentWrapper"));

            if (this.model.inArray()) {
                values = _.toArray(values);
                allWrapper.each(function (index, element) {
                    scope.widgetApply($(element), "setValue", values[index]);
                });
            } else {
                this.widgetApply(allWrapper, "setValue", values);
            }
        },

        /**
         * Dispay error message around the widget if needed
         * @param event
         */
        refreshError : function refreshError(event) {
            var parentId = this.model.get('parent');
            this.$el.find(".dcpAttribute__label").dcpLabel("setError", this.model.get("errorMessage"));
            this.widgetApply(this.getDOMElements().find(".dcpAttribute__contentWrapper").andSelf().filter(".dcpAttribute__contentWrapper"),
                "setError", this.model.get("errorMessage"));
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
        changeAttributesValue : function (event, dataItem, valueIndex) {
            var currentView = this;
            _.each(dataItem.values, function (val, aid) {
                if (typeof val === "object") {
                    var attrModel = currentView.model.getDocumentModel().get('attributes').get(aid);
                    if (attrModel) {
                        if (attrModel.hasMultipleOption()) {
                            attrModel.addValue({value : val.value, displayValue : val.displayValue}, valueIndex);
                        } else {
                            attrModel.setValue({value : val.value, displayValue : val.displayValue}, valueIndex);
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
        deleteValue : function (event, data) {
            if (data.id === this.model.id) {
                var attrToClear = this.model.get('helpOutputs'),
                    docModel = this.model.getDocumentModel();
                if ((!attrToClear) || typeof attrToClear === "undefined") {
                    attrToClear = [this.model.id];
                } else {
                    attrToClear = _.toArray(attrToClear);
                }
                _.each(attrToClear, function (aid) {
                    var attr = docModel.get('attributes').get(aid);
                    if (attr) {
                        if (attr.hasMultipleOption()) {
                            attr.setValue([], data.index);
                        } else {
                            attr.setValue({value : null, displayValue : ''}, data.index);
                        }
                    }
                });
            } else {
                console.log("NO delete", this.model.id, data, this.model);
            }
        },

        /**
         * Return another attribute model
         *
         * @param attributeId
         * @returns {*}
         */
        getAttributeModel : function (attributeId) {
            var docModel = this.model.getDocumentModel();
            return docModel.get('attributes').get(attributeId);
        },

        /**
         * Used for render attribute
         *
         * @returns {Array}
         */
        getDeleteLabels : function getDeleteLabels() {
            var attrToClear = this.model.get('helpOutputs'),
                scope = this, attrLabels;
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
        autocompleteRequestRead : function (options) {
            var documentModel = this.model.getDocumentModel();
            options.data.attributes = documentModel.getValues();
            $.ajax({
                type : "POST",
                url : "?app=DOCUMENT&action=AUTOCOMPLETE&attrid=" + this.model.id + "&id=" +
                (documentModel.id || "0" ) +
                      "&fromid=" + documentModel.get("properties").get("family").id,
                data : options.data,

                dataType : "json", // "jsonp" is required for cross-domain requests; use "json" for same-domain requests
                success :  function (result) {
                    // notify the data source that the request succeeded
                    options.success(result);
                },
                error :    function (result) {
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
        changeMenuVisibility : function changeMenuVisibility(event, data) {
            this.model.trigger("changeMenuVisibility", event, data);
        },

        getDOMElements : function () {
            if (this.options && this.options.els) {
                return this.options.els();
            } else {
                return this.$el;
            }
        },

        afterShow :   function afterShow(event, data) {
            // propagate event to widgets
            this.getDOMElements().trigger("show");
        },
        /**
         *
         * @param event
         * @param data
         */
        updateValue : function updateValue(event, data) {
            this.model.setValue(data.value, data.index);
        },

        widgetApply : function widgetApply($element, method, argument) {
            return this.getWidgetClass().call($element, method, argument);
        },

        getWidgetClass : function getTypedWidgetClass() {
            return this.getTypedWidgetClass(this.model.get("type"));
        },

        getTypedWidgetClass : function getTypedWidgetClass(type) {
            switch (type) {
                case "text" :
                    return $.fn.dcpText;
                case "int" :
                    return $.fn.dcpInt;
                case "double" :
                    return $.fn.dcpDouble;
                case "money" :
                    return $.fn.dcpMoney;
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
                case "color" :
                    return $.fn.dcpColor;
                case "file" :
                    return $.fn.dcpFile;
                case "enum" :
                    return $.fn.dcpEnum;
                case "password" :
                    return $.fn.dcpPassword;
                case "account" :
                case "docid" :
                    return $.fn.dcpDocid;
                default:
                    return $.fn.dcpText;
            }
        },

        remove : function remove() {
            try {
                if (this.currentDcpWidget && this._findWidgetName(this.currentDcpWidget)) {
                    this.widgetApply(this.currentDcpWidget, "destroy");
                }
            } catch (e) {
                TraceKit.report(e);
                console.error(e);
            }
            return Backbone.View.prototype.remove.call(this);
        },

        _findWidgetName : function ($element) {
            return _.find(_.keys($element.data()), function (currentKey) {
                return currentKey.indexOf("dcpDcp") !== -1;
            });
        }
    });

});

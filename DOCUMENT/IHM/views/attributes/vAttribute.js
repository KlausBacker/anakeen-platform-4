/*global define, console*/
define([
    'jquery',
    'underscore',
    'backbone',
    'mustache',
    'dcpDocument/views/document/attributeTemplate',
    'dcpDocument/widgets/attributes/label/wLabel',
    'dcpDocument/widgets/attributes/text/wText',
    'dcpDocument/widgets/attributes/int/wInt',
    'dcpDocument/widgets/attributes/longtext/wLongtext',
    'dcpDocument/widgets/attributes/htmltext/wHtmltext',
    'dcpDocument/widgets/attributes/timestamp/wTimestamp',
    'dcpDocument/widgets/attributes/time/wTime',
    'dcpDocument/widgets/attributes/image/wImage',
    'dcpDocument/widgets/attributes/money/wMoney',
    'dcpDocument/widgets/attributes/enum/wEnum',
    'dcpDocument/widgets/attributes/color/wColor',
    'dcpDocument/widgets/attributes/password/wPassword',
    'dcpDocument/widgets/attributes/file/wFile',
    'dcpDocument/widgets/attributes/double/wDouble',
    'dcpDocument/widgets/attributes/docid/wDocid'
], function ($, _, Backbone, Mustache, attributeTemplate)
{
    'use strict';

    return Backbone.View.extend({

        className: "row dcpAttribute form-group",
        customView: false,
        displayLabel: true,
        events: function ()
        {
            if (this.customView === false) {

                return {
                    "dcpattributechange .dcpAttribute__content": "updateValue",
                    "dcpattributedelete .dcpAttribute__content": "deleteValue",
                    "dcpattributechangeattrmenuvisibility .dcpAttribute__content": "changeMenuVisibility",
                    "dcpattributechangeattrsvalue .dcpAttribute__content": "changeAttributesValue",
                    "dcpattributechangedocument .dcpAttribute__content": "changeDocument",
                    "dcpattributeexternallinkselected .dcpAttribute__content": "externalLinkSelected"
                };
            } else {
                // No events in custom
                return {};
            }
        },

        initialize: function vAttributeInitialize(options)
        {
            this.listenTo(this.model, 'change:label', this.refreshLabel);
            this.listenTo(this.model, 'change:attributeValue', this.refreshValue);
            this.listenTo(this.model, 'change:errorMessage', this.refreshError);
            this.listenTo(this.model, 'moved', this.moveValueIndex);
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'showTab', this.afterShow);
            this.listenTo(this.model, 'hide', this.hide);
            this.listenTo(this.model, 'show', this.show);
            this.listenTo(this.model, 'haveView', this._identifyView);
            this.templateWrapper = this.model.getTemplates().attribute.simpleWrapper;

            if (options.displayLabel === false || this.model.getOption("labelPosition") === "none") {
                this.displayLabel = false;
            }

            if (options.originalView !== true) {
                if (this.model.getOption("template")) {
                    this.customView = attributeTemplate.customView(this.model);
                }
            } else {
                this.customView = false;
            }
            this.options = options;
        },

        /**
         * The Data are the source of data shared with widget and templates
         *
         * @param index
         * @returns {*}
         */
        getData: function vAttributeGetData(index)
        {
            var data;

            //Made to JSON for all the values, or to data for value indexed (in cas of multiple)
            if (typeof index === "undefined" || index === null) {
                data = this.model.toJSON();
            } else {
                data = this.model.toData(index);
            }
            data.viewCid = this.cid + '-' + this.model.id;
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

        render: function vAttributeRender()
        {
            //console.time("render attribute " + this.model.id);
            var data = this.getData();
            this.$el.addClass("dcpAttribute--type--" + this.model.get("type"));
            this.$el.addClass("dcpAttribute--visibility--" + this.model.get("visibility"));
            this.$el.attr("data-attrid", this.model.get("id"));
            if (this.model.get("needed")) {
                this.$el.addClass("dcpAttribute--needed");
            }

            this.$el.append($(Mustache.render(this.templateWrapper, data)));

            if (this.customView) {
                this.$el.find(".dcpAttribute__content").append(this.customView);
            } else {
                this.currentDcpWidget = this.widgetInit(this.$el.find(".dcpAttribute__content"), data);
            }

            if (this.displayLabel === false) {
                this.$el.find(".dcpAttribute__label").remove();
                // set to 100% width
                this.$el.find(".dcpAttribute__right").addClass("dcpAttribute__right--full");
            } else {
                if (this.model.getOption("labelPosition") === "left") {
                    this.$el.find(".dcpAttribute__right").addClass("dcpAttribute__labelPosition--left");
                    this.$el.find(".dcpAttribute__left").addClass("dcpAttribute__labelPosition--left");
                }
                if (this.model.getOption("labelPosition") === "up") {
                    this.$el.find(".dcpAttribute__right").addClass("dcpAttribute__labelPosition--up");
                    this.$el.find(".dcpAttribute__left").addClass("dcpAttribute__labelPosition--up");
                }

                this.$el.find(".dcpAttribute__label").dcpLabel(data);
            }

            // console.timeEnd("render attribute " + this.model.id);
            this.model.trigger("renderDone", {model: this.model, $el: this.$el});
            return this;
        },

        refreshLabel: function vAttributeRefreshLabel()
        {
            this.getDOMElements().find(".dcpAttribute__label").dcpLabel("setLabel", this.model.get("label"));
        },

        /**
         * Autorefresh value when model change
         */
        refreshValue: function vAttributeRefreshValue(model, values, options)
        {
            var scope = this, allWrapper, arrayWrapper;
            if (options.updateArray) {
                return this;
            }
            if (this.model.isInArray()) {
                // adjust line number to column length
                arrayWrapper = this.$el;
                arrayWrapper.dcpArray("setLines", values.length);
            }

            allWrapper = this.getDOMElements().find(".dcpAttribute__content--widget")
                .add(this.getDOMElements().filter(".dcpAttribute__content--widget"));

            if (this.model.isInArray()) {
                values = _.toArray(values);
                allWrapper.each(function vAttributeRefreshOneValue(index, element)
                {
                    if (!_.isUndefined(values[index])) {
                        scope.widgetApply($(element), "setValue", values[index]);
                    }
                });
            } else {
                this.widgetApply(allWrapper, "setValue", values);
            }
        },

        /**
         * Dispay error message around the widget if needed
         * @param event
         */
        refreshError: function vAttributeRefreshError(event)
        {
            var parentId = this.model.get('parent');
            this.$el.find(".dcpAttribute__label").dcpLabel("setError", this.model.get("errorMessage"));
            this.widgetApply(this.getDOMElements().find(".dcpAttribute__content").andSelf().filter(".dcpAttribute__content"),
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
         * @param options object {dataItem :, valueIndex :}
         */
        changeAttributesValue: function vAttributeChangeAttributesValue(event, options)
        {
            var externalEvent = {prevent: false},
                currentView = this,
                dataItem = options.dataItem,
                valueIndex = options.valueIndex;
            this.model.trigger("helperSelect", externalEvent, this.model.id, dataItem);
            if (externalEvent.prevent) {
                return this;
            }
            _.each(dataItem.values, function vAttributeChangeAttributeValue(attributeValue, attributeId)
            {
                if (typeof attributeValue === "object") {
                    var attrModel = currentView.model.getDocumentModel().get('attributes').get(attributeId);
                    if (attrModel) {
                        if (attrModel.hasMultipleOption()) {
                            attrModel.addValue({
                                value: attributeValue.value,
                                displayValue: attributeValue.displayValue
                            }, valueIndex);
                        } else {
                            attrModel.setValue({
                                value: attributeValue.value,
                                displayValue: attributeValue.displayValue
                            }, valueIndex);
                        }
                    }
                    else {
                        console.error("Unable to find " + attributeId);
                    }
                }
            });
        },

        changeDocument: function changeAttributesValueChangeDocument(event, options)
        {
            var index = options.index, initid = null, attributeValue = this.model.get("attributeValue"), documentModel = this.model.getDocumentModel();
            if (_.isUndefined(index)) {
                initid = attributeValue.value;
            } else {
                initid = attributeValue[index].value;
            }
            documentModel.clear().set({
                "initid": initid,
                "revision": -1,
                "viewId": "!defaultConsultation"
            }).fetch();
        },

        externalLinkSelected: function changeAttributesValueExternalLinkSelected(event, options)
        {
            options.attrid = this.model.id;
            this.model.trigger("internalLinkSelected", options);
        },

        /**
         * Delete value,
         * If has help, clear also target attributes
         * @param event
         * @param data index info {index:"the index}
         */
        deleteValue: function changeAttributesValueDeleteValue(event, data)
        {

            if (data.id === this.model.id) {
                var attrToClear = this.model.get('helpOutputs'),
                    docModel = this.model.getDocumentModel();
                if ((!attrToClear) || typeof attrToClear === "undefined") {
                    attrToClear = [this.model.id];
                } else {
                    attrToClear = _.toArray(attrToClear);
                }
                _.each(attrToClear, function vAttributeCleanAssociatedElement(aid)
                {
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

        /**
         * Return another attribute model
         *
         * @param attributeId
         * @returns {*}
         */
        getAttributeModel: function vAttributeGetAttributeModel(attributeId)
        {
            var docModel = this.model.getDocumentModel();
            return docModel.get('attributes').get(attributeId);
        },

        /**
         * Used for render attribute
         *
         * @returns {Array}
         */
        getDeleteLabels: function vAttributeGetDeleteLabels()
        {
            var attrToClear = this.model.get('helpOutputs'),
                scope = this, attrLabels;
            if ((!attrToClear) || typeof attrToClear === "undefined") {
                attrToClear = [this.model.id];
            } else {
                attrToClear = _.toArray(attrToClear);
            }
            attrLabels = _.map(attrToClear, function vAttributeGetAssociatedLabel(aid)
            {
                var attr = scope.getAttributeModel(aid);
                if (attr) {
                    return attr.attributes.label;
                }
                return '';
            });
            return attrLabels;
        },

        /**
         * Propagate move value event to widgets
         * @param eventData
         */
        moveValueIndex: function vAttributeMoveValueIndex(eventData)
        {
            this.getDOMElements().trigger("postMoved", eventData);
        },

        /**
         * method use for transport multiselect widget
         * @param options
         */
        autocompleteRequestRead: function vAttributeAutocompleteRequestRead(options)
        {
            var currentView = this,
                documentModel = this.model.getDocumentModel(),
                success = options.success,
                externalOptions = {
                    setResult: function (content)
                    {
                        success(content);
                    },
                    data: options.data
                },
                event = {prevent: false};
            //Add helperResonse event (can be used to reprocess the content of the request)
            success = _.wrap(success, function (success, content)
            {
                var options = {}, event = {prevent: false};
                options.data = content;
                currentView.model.trigger("helperResponse", event, currentView.model.id, options);
                if (event.prevent) {
                    return success([]);
                }
                success(content);
            });

            //Add helperSearch event (can prevent default ajax request)
            options.data.attributes = documentModel.getValues();
            this.model.trigger("helperSearch", event, this.model.id, externalOptions);
            if (event.prevent) {
                return this;
            }
            $.ajax({
                type: "POST",
                url: "?app=DOCUMENT&action=AUTOCOMPLETE&attrid=" + this.model.id + "&id=" +
                (documentModel.id || "0" ) +
                "&fromid=" + documentModel.get("properties").get("family").id,
                data: options.data,
                dataType: "json" // "jsonp" is required for cross-domain requests; use "json" for same-domain requestsons.error(result);
            }).pipe(
                function vAttributeAutocompletehandleSuccessRequest(response)
                {
                    if (response.success) {
                        return response;
                    } else {
                        return ($.Deferred().reject(response));
                    }
                },
                function vAttributeAutocompletehandleErrorRequest(response)
                {
                    if (response.status === 0) {
                        return {
                            success: false,
                            error: "Your navigator seems offline, try later"
                        };
                    }
                    return ({
                        success: false,
                        error: "Unexpected error: " + response.status + " " + response.statusText
                    });
                }
            ).then(function vAttributeAutocompleteSuccessResult(result)
                {
                    // notify the data source that the request succeeded
                    success(result.data);
                }, function vAttributeAutocompleteErrorResult(result)
                {
                    // notify the data source that the request failed
                    if (_.isArray(result.error)) {
                        result.error = result.error.join(" ");
                    }
                    //use the sucess callback because http error are handling by the pipe
                    success([{"title": "", "error": result.error}]);
                }
            );
        },

        /**
         * Modify visibility access of an item menu
         * @param event event object
         * @param data menu config {id: menuId, visibility: "disabled", "visible", "hidden"}
         */
        changeMenuVisibility: function vAttributeChangeMenuVisibility(event, data)
        {
            this.model.trigger("changeMenuVisibility", event, data);
        },

        getDOMElements: function vAttributeGetDOMElements()
        {
            if (this.options && this.options.els) {
                return this.options.els();
            } else {
                return this.$el;
            }
        },

        afterShow: function vAttributeAfterShow(event, data)
        {
            // propagate event to widgets
            this.getDOMElements().trigger("show");
        },
        /**
         *
         * @param event
         * @param data
         */
        updateValue: function vAttributeUpdateValue(event, data)
        {
            this.model.setValue(data.value, data.index);
        },

        widgetInit: function vAttributeWidgetInit($element, data)
        {
            $element.addClass("dcpAttribute__content--widget");
            return this.getWidgetClass().call($element, data);
        },

        widgetApply: function vAttributeWidgetApply($element, method, argument)
        {
            try {
                if (_.isString(method) && $element && this._findWidgetName($element)) {
                    this.getWidgetClass().call($element, method, argument);
                }
            } catch (e) {
                if (window.dcp.logger) {
                    window.dcp.logger(e);
                } else {
                    console.error(e);
                }
            }
            return this;
        },

        getWidgetClass: function vAttributeGetTypedWidgetClass()
        {
            return this.getTypedWidgetClass(this.model.get("type"));
        },

        getTypedWidgetClass: function vAttributeGetTypedWidgetClass(type)
        {
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
                case "thesaurus" :
                case "account" :
                case "docid" :
                    return $.fn.dcpDocid;
                default:
                    return $.fn.dcpText;
            }
        },

        remove: function vAttributeRemove()
        {
            try {
                if (this.currentDcpWidget && this._findWidgetName(this.currentDcpWidget)) {
                    this.widgetApply(this.currentDcpWidget, "destroy");
                }
            } catch (e) {
                if (window.dcp.logger) {
                    window.dcp.logger(e);
                } else {
                    console.error(e);
                }
            }
            return Backbone.View.prototype.remove.call(this);
        },

        hide: function vAttributeHide()
        {
            this.$el.hide();
        },

        show: function vAttributeShow()
        {
            this.$el.show();
        },

        _findWidgetName: function vAttribute_findWidgetName($element)
        {
            return _.find(_.keys($element.data()), function (currentKey)
            {
                return currentKey.indexOf("dcpDcp") !== -1;
            });
        },

        _identifyView: function vAttribute_identifyView(event)
        {
            event.haveView = true;
            //Add the pointer to the current jquery element to a list passed by the event
            event.elements = event.elements.add(this.$el);
        }
    });

});

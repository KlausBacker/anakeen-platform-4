define([
    'underscore',
    'mustache',
    'widgets/widget'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpAttribute", {

        options: {
            eventPrefix: "dcpAttribute",
            id: null,
            type: "abstract",
            mode: "read",
            index: -1

        },

        _create: function () {
            if (this.options.value === null) {
                this.options.value = {};
            }
            if (this.options.helpOutputs) {
                this.options.hasAutocomplete = true;
            }
            this.options.emptyValue = _.bind(this._emptyValue, this);
            this._initDom();
            this._initEvent();
        },
        _emptyValue: function () {
            if (_.isEmpty(this.options.value) || this.options.value.value === null) {
                var model = this._model();
                if (model) {
                    return model.getOption('showEmptyContent');
                }
                return "";
            }
            return "";
        },
        _initDom: function () {
            this.element.append(Mustache.render(this._getTemplate(), this.options));
        },

        _initEvent: function () {
            if (this.getMode() === "write") {
                this._initDeleteEvent();
            }
        },

        _initDeleteEvent: function () {
            var currentWidget = this;
            var attrModel = currentWidget._model();
            var docModel = currentWidget._documentModel();
            var attrToClear = attrModel.attributes.helpOutputs;

            if (!attrToClear) {
                attrToClear = [attrModel.id];
            } else {
                attrToClear = _.toArray(attrToClear);
            }
            // Compose delete button title
            var $deleteButton = this.element.find(".dcpAttribute__content--delete--button");
            var titleDelete = $deleteButton.find("button").attr('title');
            var attrLabels = _.map(attrToClear, function (aid) {
                var attr = docModel.get('attributes').get(aid);
                if (attr) {
                    return attr.attributes.label;
                }
                return '';
            });
            titleDelete += attrLabels.join(", ");
            $deleteButton.on("click." + this.eventNamespace,function (event) {

                event.preventDefault();
                _.each(attrToClear, function (aid) {
                    var attr = docModel.get('attributes').get(aid);
                    if (attr) {
                        if (attr.hasMultipleOption()) {
                            attr.setValue([], currentWidget._getIndex());
                        } else {
                            attr.setValue({value: null, displayValue: ''}, currentWidget._getIndex());
                        }
                    }
                });

                currentWidget.element.find("input").focus();
            }).attr('title', titleDelete).kendoTooltip({
                position: "left"
            });
        },
        _initLinkEvent: function _initLinkEvent() {
            var htmlLink = this.getLink();
            var scope=this;
            if (htmlLink) {

                this.element.find('.dcpAttribute__content__link').on("click", function (event) {

                    if (htmlLink.target === "_dialog") {
                        event.preventDefault();

                        var renderTitle;
                        var index=$(this).data("index");
                        if (typeof index !== "undefined" && index !== null) {
                            renderTitle=Mustache.render(htmlLink.windowTitle,scope.options.value[index]);
                        } else {
                            renderTitle=Mustache.render(htmlLink.windowTitle,scope.options.value);
                        }

                        var bdw = $('<div/>');
                        $('body').append(bdw);

                        var dw = bdw.dcpWindow({
                            title: renderTitle,
                            width: htmlLink.windowWidth,
                            height: htmlLink.windowHeight,
                            content: $(this).attr("href"),
                            iframe: true
                        });


                        dw.data('dcpWindow').kendoWindow().center();
                        dw.data('dcpWindow').open();
                        if (!htmlLink.windowTitle) {
                            _.defer(function () {
                                dw.data('dcpWindow').currentWidget.find('iframe').on("load", function () {
                                    dw.data('dcpWindow').kendoWindow().setOptions({
                                        title: $(this).contents().find("title").html()
                                    });
                                });
                            });
                        }
                    }
                });

                this.element.find('.dcpAttribute__content__link[title]').kendoTooltip({
                    position: "top"
                });

            }
        },
        /**
         * Verify if a common link option is set
         * @returns {Array|renderOptions|*|dcp.dcpDocid.options.renderOptions.htmlLink|.options.renderOptions.htmlLink|url}
         */
        hasLink: function hasLink() {
            return (this.options.renderOptions && this.options.renderOptions.htmlLink && this.options.renderOptions.htmlLink.url);
        },
        /**
         * Return the url of common link
         * @returns {*}
         */
        getLink: function getLink() {
            if (this.options.renderOptions && this.options.renderOptions.htmlLink) {
                return this.options.renderOptions.htmlLink;
            }
            return null;
        },
        _getIndex: function () {
            if (this.options.index !== -1) {

                this.options.index = this.element.closest('.dcpArray__content__line').data('line');
            }
            return this.options.index;
        },

        _model: function () {

            return this._documentModel().get('attributes').get(this.options.id);
        },

        _documentModel: function () {
            return  window.dcp.documents.get(window.dcp.documentData.document.properties.id);
        },
        _getTemplate: function () {
            if (window.dcp && window.dcp.templates && window.dcp.templates.attribute && window.dcp.templates.attribute[this.getType()]) {
                return window.dcp.templates.attribute[this.getType()];
            }
            return "";
        },

        _isMultiple: function () {
            return (this.options.options && this.options.options.multiple === "yes");
        },

        flashElement: function () {
            this.element.addClass('dcpAttribute__content--flash');
            var currentElement = this.element;
            _.delay(function () {
                currentElement.removeClass('dcpAttribute__content--flash').addClass('dcpAttribute__content--endflash');
                _.delay(function () {
                    currentElement.removeClass('dcpAttribute__content--endflash');
                }, 600);
            }, 10);
        },

        setError: function (message, index) {
            var parentId = this._model().get('parent');
            var greatParentId;
            if (message) {
                this.element.addClass("has-error");
                this.element.kendoTooltip({
                    position: "bottom",
                    content: message,
                    autoHide: false,
                    show: function onShow(e) {
                        this.popup.element.addClass("has-error");
                    }
                });
                // this.element.find('input').focus();
                if (parentId) {
                    $('.dcpFrame__label[data-id="' + parentId + '"]').addClass("has-warning");
                    greatParentId = this._documentModel().get('attributes').get(parentId).get('parent');

                    if (greatParentId) {
                        $('.dcpTab__label[data-id="' + greatParentId + '"]').addClass("has-warning");
                    }

                }
            } else {
                this.element.removeClass("has-error");
                this.element.data("kendoTooltip").destroy();
                if (parentId) {
                    $('.dcpFrame__label[data-id="' + parentId + '"]').removeClass("has-warning");
                    greatParentId = this._documentModel().get('attributes').get(parentId).get('parent');

                    if (greatParentId) {
                        $('.dcpTab__label[data-id="' + greatParentId + '"]').removeClass("has-warning");
                    }

                }
            }
        },
        getType: function () {
            return this.options.type;
        },

        getMode: function () {
            if (this.options.mode !== "read" && this.options.mode !== "write" && this.options.mode !== "hidden") {
                throw new Error("Attribute " + this.options.id + " have unknown mode " + this.options.mode);
            }
            return this.options.mode;
        },

        getValue: function () {
            return this.options.value;
        },

        setValue: function (value, event) {
            console.log("dcpAttribute::setValue trigger", this.options.value, value);
            if (!_.isEqual(this.options.value, value)) {
                this.options.value = value;
                console.log("send change trigger from widget", this.options.id, this._getIndex());
                this._trigger("change", event, {
                    id: this.options.id,
                    value: value,
                    index: this._getIndex()
                });
            }
        },


        getTypedWidgetClass: function (type) {
            return this._model().getTypedWidgetClass(type);
        }

    });
});
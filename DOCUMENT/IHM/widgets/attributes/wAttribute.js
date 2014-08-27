/*global define*/
define([
    'underscore',
    'mustache',
    'widgets/widget',
    'kendo/kendo.tooltip'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpAttribute", {

        options: {
            eventPrefix: "dcpAttribute",
            id: null,
            type: "abstract",
            mode: "read",
            index: -1,
            labels: {
                deleteAttributeNames: "",
                deleteLabel: ""
            },
            template: null,
            deleteButton: false,
            renderOptions: null
        },

        /**
         * Redraw element with updated values
         */
        redraw: function wAttributeRedraw() {
            this.element.empty();
            this._initDom();
            this.element.off("." + this.eventNamespace);
            this._initEvent();
            return this;
        },

        /**
         * Verify if a common link option is set
         *
         * @returns boolean
         */
        hasLink: function hasLink() {
            return !!(this.options.renderOptions && this.options.renderOptions.htmlLink && this.options.renderOptions.htmlLink.url);
        },
        /**
         * Return the url of link
         * @returns string
         */
        getLink: function wAttributeGetLink() {
            if (this.options.renderOptions && this.options.renderOptions.htmlLink) {
                return this.options.renderOptions.htmlLink;
            }
            return null;
        },

        /**
         * Flash the element to attract user attention
         *
         * @param currentElement
         */
        flashElement: function wAttributeFlashElement(currentElement) {
            if (!currentElement) {
                currentElement = this.element;
            }
            currentElement.addClass('dcpAttribute__content--flash');
            _.delay(function () {
                currentElement.removeClass('dcpAttribute__content--flash').addClass('dcpAttribute__content--endflash');
                _.delay(function () {
                    currentElement.removeClass('dcpAttribute__content--endflash');
                }, 600);
            }, 10);
        },

        /**
         * Display an error message
         *
         * @param message
         * @param index
         */
        setError: function wAttributeSetError(message, index) {
            var kt;
            if (message) {
                this.element.addClass("has-error");
                this.element.kendoTooltip({
                    position: "bottom",
                    content: message,
                    autoHide: false,
                    show: function onShow(e) {
                        var contain = this.popup.element.parent();
                        var ktop = parseFloat(contain.css("top"));
                        if (ktop > 0) {
                            contain.css("top", ktop + 6);
                        }
                        this.popup.element.addClass("has-error");
                    }
                });

            } else {
                this.element.removeClass("has-error");
                kt = this.element.data("kendoTooltip");
                if (kt) {
                    kt.destroy();
                }

            }
        },

        /**
         * Get the type of the widget
         *
         * @returns {string}
         */
        getType: function () {
            return this.options.type;
        },

        /**
         * Get the mode of the widget
         *
         * @returns {string} Read|Write
         */
        getMode: function () {
            if (this.options.mode !== "read" && this.options.mode !== "write" && this.options.mode !== "hidden") {
                throw new Error("Attribute " + this.option.id + " have unknown mode " + this.options.mode);
            }
            return this.options.mode;
        },

        /**
         * Return the value stored in the wiget
         *
         * @returns {*|number|.options.value}
         */
        getValue: function () {
            return this.options.value;
        },

        /**
         * Identify the input where is the raw value
         * @returns {*}
         */
        getContentElements: function () {
            return this.element.find('.dcpAttribute__content[name="' + this.options.id + '"]');
        },

        /**
         * Return the value of something
         *
         * TODO : ask to eric why ?
         *
         * @returns {*}
         */
        getWidgetValue: function () {
            return this.getContentElements().val();

        },
        /**
         * Set options value element and trigger the view
         *
         * @param value
         * @param event
         */
        setValue: function wAttributeSetValue(value, event) {
            this._checkValue(value);
            if (!_.isEqual(this.options.value.value, value.value)) {
                this.options.value = value;
                this._trigger("change", event, {
                    id: this.options.id,
                    value: value,
                    index: this._getIndex()
                });
            }
        },

        /**
         * Show the input tooltip
         * @param ktTarget //TODO Eric ; préciser le type de kTarget ou passer la méthode en privé
         *
         * @return this
         */
        hideInputTooltip: function wAttributeHideInputTooltip(ktTarget) {
            var kTooltip = ktTarget.data("kendoTooltip");
            if (kTooltip) {
                kTooltip.hide();
            }
            return this;
        },

        /**
         * Show the input tooltip
         * @param ktTarget //TODO Eric ; préciser le type de kTarget ou passer la méthode en privé
         *
         * @return this
         */
        showInputTooltip: function (ktTarget) {
            var scope = this;

            if (scope.options.renderOptions.inputHtmlTooltip) {
                var kt = ktTarget.data("kendoTooltip");

                if (!kt) {
                    kt = ktTarget.kendoTooltip({
                        autoHide: false,
                        content: scope.options.renderOptions.inputHtmlTooltip,
                        showOn: _.uniqueId(),
                        show: function () {
                            var contain = this.popup.element.parent();
                            var ktop = parseFloat(contain.css("top"));
                            if (ktop > 0) {
                                contain.css("top", ktop + 6);
                            }
                            this.popup.element.addClass("dcpAttribute__editlabel");
                        }
                    }).data("kendoTooltip");
                }
                kt.show();
            }
            return this;
        },

        /**
         * Create the widget
         * @private
         */
        _create: function () {
            //If no id is provided one id generated
            if (this.options.id === null) {
                this.options.id = _.uniqueId("widget_" + this.getType());
            }

            if (_.isUndefined(this.options.value) || this.options.value === null) {
                this.options.value = {
                    "value": null,
                    displayValue: ""
                };
            }
            if (this.options.helpOutputs) {
                this.options.hasAutocomplete = true;
            }

            if (this.getMode() === "write") {
                if (this.options.renderOptions && this.options.renderOptions.buttons) {
                    // Add index for template to identify buttons
                    this.options.renderOptions.buttons = _.map(this.options.renderOptions.buttons, function (val, index) {
                        val.index = index;
                        return val;
                    });
                }
            }
            this.options.emptyValue = _.bind(this._getEmptyValue, this);
            this.options.hadButtons = this._hasButtons();
            if (this.options.renderOptions && this.options.renderOptions.labels) {
                this.options.labels = _.extend(this.options.labels, this.options.renderOptions.labels);
            }
            this._initDom();
            this._initEvent();
        },

        /**
         * Destroy the widget
         *
         * Suppress widget defined events and delete added dom
         *
         * @private
         */
        _destroy: function () {
            this.element.removeClass("dcpAttribute__contentWrapper");
            this.element.removeAttr("data-type");
            this.element.removeAttr("data-id");
            this.element.empty();
            this._trigger("destroy");
        },

        /**
         * Init the DOM of the template
         *
         * @private
         */
        _initDom: function _initDom() {
            this.element.addClass("dcpAttribute__contentWrapper");
            this.element.attr("data-type", this.getType());
            this.element.attr("data-id", this.options.id);
            this.element.append(Mustache.render(this._getTemplate(this.options.mode), this.options));
        },

        /**
         * Init the events
         *
         * @protected
         */
        _initEvent: function _initEvent() {
            if (this.getMode() === "write") {
                this._initDeleteEvent();
                this._initButtonsEvent();
                this._initFocusEvent();
            }
            if (this.getMode() === "read") {
                this._initLinkEvent();
            }
            return this;
        },

        /**
         * Init the focus event for tooltips (only for write attr)
         *
         * @protected
         */
        _initFocusEvent: function wAttributeInitFocusEvent() {
            if (this.options.renderOptions && this.options.renderOptions.inputHtmlTooltip) {
                var scope = this;

                var inputTargetFilter = ".dcpAttribute__content";
                this._getFocusInput().on("focus." + this.eventNamespace, function (event) {
                    var ktTarget = $(event.currentTarget).closest(inputTargetFilter);
                    scope.showInputTooltip(ktTarget);
                });
                this._getFocusInput().on("blur." + this.eventNamespace, function (event) {
                    var ktTarget = $(event.currentTarget).closest(inputTargetFilter);
                    scope.hideInputTooltip(ktTarget);
                });
            }
            return this;
        },

        /**
         * Init the events associated to buttons (only for write attributes)
         *
         * @protected
         */
        _initButtonsEvent: function _initButtonsEvent() {
            var scope = this;
            var $extraButtons = this.element.find(".dcpAttribute__content__button--extra");
            $extraButtons.on("click." + this.eventNamespace, function (event) {
                var buttonsConfig = scope.options.renderOptions.buttons;
                var buttonIndex = $(this).data("index");
                var buttonConfig = buttonsConfig[buttonIndex];
                if (buttonConfig && buttonConfig.url) {
                    var url = Mustache.render(buttonConfig.url, scope.options.value);
                    if (buttonConfig.target !== "_dialog") {
                        window.open(url, buttonConfig.target);
                    } else {
                        var bdw = $('<div/>');
                        $('body').append(bdw);
                        var renderTitle = Mustache.render(buttonConfig.windowTitle, scope.options.value);
                        var dw = bdw.dcpWindow({
                            title: renderTitle,
                            width: buttonConfig.windowWidth,
                            height: buttonConfig.windowHeight,
                            content: url,
                            iframe: true
                        });
                        dw.data('dcpWindow').kendoWindow().center();
                        dw.data('dcpWindow').open();
                    }
                }

                scope._trigger("click", event, {
                    id: scope.option.id,
                    value: scope.options.value,
                    index: scope._getIndex()
                });
            });
            return this;
        },

        /**
         * Init events for delete button (only for write attributes)
         *
         * @protected
         */
        _initDeleteEvent: function wAttributeInitDeleteEvent() {
            var currentWidget = this;

            // Compose delete button title
            var $deleteButton = this.element.find(".dcpAttribute__content__button--delete");
            var titleDelete;
            if (this.options.labels.deleteLabel) {

                titleDelete = this.options.labels.deleteLabel;
            } else {
                titleDelete = $deleteButton.attr('title');
                titleDelete += this.options.labels.deleteAttributeNames;
            }
            $deleteButton.on("mousedown." + this.eventNamespace,function (event) {

                // Hide tooltip because it mask the input focus
                var kt = $(this).data("kendoTooltip");
                if (kt) {
                    kt.hide();
                }

            }).attr('title', titleDelete);

            $deleteButton.on("click." + this.eventNamespace, function (event) {
                currentWidget._trigger("delete", event, {index: currentWidget._getIndex(), id: currentWidget.options.id});
                // main input is focuses after deletion
                _.defer(function () {
                    currentWidget.element.find("input").focus();
                });
            });

            this.element.find(".dcpAttribute__content__buttons button").kendoTooltip({
                position: "left",
                autoHide: true,
                callout: true,
                show: function (event) {
                    var contain = this.popup.element.parent();
                    var kleft = parseFloat(contain.css("left"));
                    if (kleft > 0) {
                        contain.css("left", kleft - 6);
                    }
                }
            });
            return this;
        },
        /**
         * Init event when a hyperlink is associated to the attribute
         *
         * @protected
         */
        _initLinkEvent: function wAttributeInitLinkEvent() {
            var htmlLink = this.getLink();
            var scope = this;
            if (htmlLink) {

                this.element.find('.dcpAttribute__content__link').on("click." + this.eventNamespace, function (event) {

                    if (htmlLink.target === "_dialog") {
                        event.preventDefault();

                        var renderTitle;
                        var index = $(this).data("index");
                        if (typeof index !== "undefined" && index !== null) {
                            renderTitle = Mustache.render(htmlLink.windowTitle, scope.options.value[index]);
                        } else {
                            renderTitle = Mustache.render(htmlLink.windowTitle, scope.options.value);
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

                    }
                });

                this.element.find('.dcpAttribute__content__link[title]').kendoTooltip({
                    position: "top",
                    show: function () {
                        var contain = this.popup.element.parent();
                        var ktop = parseFloat(contain.css("top"));
                        if (ktop > 0) {
                            contain.css("top", ktop - 6);
                        }
                        this.popup.element.addClass("dcpAttribute__editlabel");
                    }
                });

            }
            return this;
        },

        /**
         * Get input that can handle focus class
         *
         * For the display of the focus class (in helpers)
         *
         * @return jquery elements
         *
         * @protected
         */
        _getFocusInput: function wAttributeFocusInput() {
            return this.element.find('input[name="' + this.options.id + '"]');
        },

        /**
         * Return the index of the attributes (for attribute in a widget array)
         *
         * @returns int
         * @protected
         */
        _getIndex: function () {
            if (this.options.index !== -1) {
                this.options.index = this.element.closest('.dcpArray__content__line').data('line');
            }
            return this.options.index;
        },

        /**
         * Return the empty value (default value if the attribute is empty)
         *
         * @returns {*}
         * @private
         */
        _getEmptyValue: function () {
            if (_.isEmpty(this.options.value) || this.options.value.value === null) {

                if (this.options.renderOptions && this.options.renderOptions.showEmptyContent) {
                    return this.options.renderOptions.showEmptyContent;
                }
                return "";
            }
            return "";
        },

        /**
         * Get the template of the current attribute
         *
         * The template can be in the options or in a global var of dcp namespace (initiated by require for widget)
         *
         * @param key
         * @returns string
         * @private
         */
        _getTemplate: function (key) {
            if (this.options.templates && this.options.templates[key]) {
                return this.options.templates[key];
            }
            if (window.dcp && window.dcp.templates && window.dcp.templates[this.getType()] && window.dcp.templates[this.getType()][key]) {
                return window.dcp.templates[this.getType()][key];
            }
            if (window.dcp && window.dcp.templates && window.dcp.templates["default"] && window.dcp.templates["default"][key]) {
                return window.dcp.templates["default"][key];
            }
            throw new Error("Unknown template  " + key + "/" + this.options.type);

        },

        /**
         * Test if the value of the setValue is correct
         *
         * @param value
         * @returns {boolean}
         */
        _checkValue: function wAttributeTestValue(value) {
            if (!_.isObject(value) || !_.has(value, "value") || !_.has(value, "displayValue")) {
                throw new Error("The value must be an object with value and displayValue properties (attrid id :" + this.options.id + ")");
            }
            return true;
        },
        /**
         * Check if the attribute is multiple
         *
         * @returns boolean
         * @private
         */
        _isMultiple: function () {
            return (this.options.options && this.options.options.multiple === "yes");
        },

        /**
         * Check if the widget has buttons
         *
         * Used by template for rendering options
         *
         * @returns boolean
         */
        _hasButtons: function wAttributeHasButtons() {
            return this.options.hasAutocomplete || this.options.deleteButton || (this.options.renderOptions && this.options.renderOptions.buttons);
        }

    });
});
/*global define*/
define([
    'underscore',
    'mustache',
    'widgets/widget',
    'bootstrap'
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
                deleteLabel: "",
                closeErrorMessage: "Close message"
            },
            template: null,
            deleteButton: false,
            renderOptions: null,
            locale: "fr_FR"
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
            currentElement.addClass('dcpAttribute__value--flash');
            _.delay(function () {
                currentElement.removeClass('dcpAttribute__value--flash').addClass('dcpAttribute__value--endflash');
                _.delay(function () {
                    currentElement.removeClass('dcpAttribute__value--endflash');
                }, 600);
            }, 10);
        },

        /**
         * Display an error message
         *
         * @param message string or array of [{message:, index:}, ...]
         */
        setError: function wAttributeSetError(message) {
            var kt;
            var scope = this;
            if (message) {
                var messages;
                if (!_.isArray(message)) {
                    messages = [
                        {message: message, index: -1}
                    ];
                } else {
                    messages = _.toArray(message);
                }
                _.each(messages, function (indexMessage) {

                    if ((indexMessage.index === -1) ||
                        (scope.element.closest('tr').data("line") === indexMessage.index)) {
                        scope.element.addClass("has-error");
                        // need to use sub element because tooltip add a div after element
                        scope.element.find(".input-group").tooltip({
                            placement: "bottom",
                            html: true,
                            title: function () {
                                var rawMessage = $('<div/>').text(indexMessage.message).html();
                                return '<div>'  + '<i title="' + scope.options.labels.closeErrorMessage + '" class="btn fa fa-times button-close-error">&nbsp;</i>'+ rawMessage+'</div>';
                            },
                            trigger: "manual",
                            template: '<div class="tooltip has-error" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'

                        });
                        scope.element.data("hasErrorTooltip", true);
                        scope.element.find(".input-group").tooltip("show");

                    }
                });
            } else {
                this.element.removeClass("has-error");

                if (this.element.data("hasErrorTooltip")) {
                    this.element.find(".input-group").tooltip("destroy");
                    this.element.data("hasErrorTooltip", false);
                }

            }
        },

        /**
         * Get the type of the widget
         *
         * @returns {string}
         */
        getType: function getType() {
            return this.options.type;
        },

        /**
         * Get the mode of the widget
         *
         * @returns {string} Read|Write
         */
        getMode: function getMode() {
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
        getValue: function getValue() {
            return this.options.value;
        },

        /**
         * Identify the input where is the raw value
         * @returns {*}
         */
        getContentElements: function () {
            return this.element.find('.dcpAttribute__value[name="' + this.options.id + '"]');
        },

        /**
         * Return the value of something
         *
         *
         * @returns {*}
         */
        getWidgetValue: function getWidgetValue() {
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

            var isEqual = false;

            if (this._isMultiple()) {
                isEqual = _.toArray(this.options.value).length === value.length;
                if (isEqual) {

                    isEqual = _.isEqual(this.options.value, value);
                }
            } else {
                isEqual = _.isEqual(this.options.value.value, value.value);
            }
            if (!isEqual) {
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
            var $ktTarger = $(ktTarget).closest(".input-group");
            if ($ktTarger.data("hasTooltip")) {
                $ktTarger.tooltip("hide");
            }
            return this;
        },

        /**
         * Show the input tooltip
         * @param ktTarget //TODO Eric ; préciser le type de kTarget ou passer la méthode en privé
         *
         * @return this
         */
        showInputTooltip: function showInputTooltip(ktTarget) {
            var scope = this;

            if (scope.options.renderOptions.inputHtmlTooltip) {
                var $ktTarger = $(ktTarget).closest(".input-group");
                var kt = $ktTarger.data("hasTooltip");

                if (!kt) {
                    $ktTarger.tooltip({
                        trigger: "manual",
                        title: scope.options.renderOptions.inputHtmlTooltip,
                        placement: "bottom",
                        template: '<div class="tooltip dcpAttribute__editlabel" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'


                    });
                    $ktTarger.data("hasTooltip", true);
                }
                $ktTarger.tooltip("show");
            }
            return this;
        },

        /**
         * Create the widget
         * @private
         */
        _create: function _create() {
            //If no id is provided one id generated
            if (this.options.id === null) {
                this.options.id = _.uniqueId("widget_" + this.getType());
            }

            if (_.isUndefined(this.options.value) || this.options.value === null) {
                if (this._isMultiple()) {
                    this.options.value = [];
                } else {
                    this.options.value = {
                        "value": null,
                        displayValue: ""
                    };
                }
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
        _destroy: function _destroy() {
            this.element.removeClass("dcpAttribute__content");
            this.element.removeAttr("data-type");
            this.element.removeAttr("data-attrid");
            this.element.empty();
            this._trigger("destroy");
            this._super();
        },

        /**
         * Init the DOM of the template
         *
         * @private
         */
        _initDom: function wAttributeInitDom() {

            this._initMainElemeentClass();
            this.element.append(Mustache.render(this._getTemplate(this.options.mode), this.options));
        },

        /**
         * Init the DOM of the template
         *
         * @public
         */
        _initMainElemeentClass: function wAttributeInitMainElemeentClass() {
            this.element.addClass("dcpAttribute__content");
            this.element.attr("data-type", this.getType());
            this.element.attr("data-attrid", this.options.id);
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
            this._initErrorEvent();
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

                var inputTargetFilter = ".dcpAttribute__value";
                this._getFocusInput().on("focus" + this.eventNamespace, function (event) {
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
            $extraButtons.on("click" + this.eventNamespace, function (event) {
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
         * Init events for delete button on error tooltip
         *
         * @protected
         */
        _initErrorEvent: function wAttributeInitErrotEvent() {
            var scope = this;
            // tooltip is created in same parent
            this.element.parent().on("click", ".button-close-error", function (event) {
                if (scope.element.data("hasErrorTooltip")) {
                    scope.element.find(".input-group").tooltip("destroy");
                    scope.element.data("hasErrorTooltip", false);
                }
            });
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
            $deleteButton.on("mousedown" + this.eventNamespace, function (event) {


            }).attr('title', titleDelete);

            $deleteButton.on("click" + this.eventNamespace, function (event) {
                currentWidget._trigger("delete", event, {
                    index: currentWidget._getIndex(),
                    id: currentWidget.options.id
                });
                // main input is focuses after deletion
                _.defer(function () {
                    currentWidget.element.find("input").focus();
                });
            });

            this.element.find(".dcpAttribute__content__buttons button").tooltip({
                placement: "left",
                trigger: "hover"
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

                this.element.find('.dcpAttribute__content__link[title]').tooltip({
                    placement: "top",
                    template: '<div class="tooltip dcpAttribute__editlabel" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
                    trigger: "hover"
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
        _getIndex: function _getIndex() {
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
        _getEmptyValue: function _getEmptyValue() {
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
        _getTemplate: function _getTemplate(key) {
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
            //noinspection JSHint
            if (this._isMultiple()) {
                // TODO : Verify each array entry
            } else {
                if (!_.isObject(value) || !_.has(value, "value") || !_.has(value, "displayValue")) {
                    throw new Error("The value must be an object with value and displayValue properties (attrid id :" + this.options.id + ")");
                }
            }


            return true;
        },
        /**
         * Check if the attribute is multiple
         *
         * @returns boolean
         * @public
         */
        _isMultiple: function _isMultiple() {
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

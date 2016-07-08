/*global define*/

(function umdRequire(root, factory)
{
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'underscore',
            'mustache',
            'dcpDocument/widgets/widget',
            'tooltip'
        ], factory);
    } else {
        //noinspection JSUnresolvedVariable
        factory(window.jQuery, window._, window.Mustache);
    }
}(window, function wAttributeWidget($, _, Mustache)
{
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
            renderOptions: {
                displayDeleteButton: true
            },
            locale: "fr_FR"
        },

        /**
         * Redraw element with updated values
         */
        redraw: function wAttributeRedraw()
        {
            this.element.find("[aria-describedby*='tooltip']").tooltip("hide");
            this.element.empty();
            this._initDom();
            this.element.off(this.eventNamespace);
            this._initEvent();
            return this;
        },

        /**
         * Verify if a common link option is set
         *
         * @returns boolean
         */
        hasLink: function hasLink()
        {
            return Boolean(this.options.renderOptions && this.options.renderOptions.htmlLink && this.options.renderOptions.htmlLink.url);
        },
        /**
         * Return the url of link
         * @returns string
         */
        getLink: function wAttributeGetLink()
        {
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
        flashElement: function wAttributeFlashElement(currentElement)
        {
            if (!currentElement) {
                currentElement = this.element;
            }
            currentElement.addClass('dcpAttribute__value--flash');
            _.delay(function wAttributeFlashDelay()
            {
                currentElement.removeClass('dcpAttribute__value--flash').addClass('dcpAttribute__value--endflash');
                _.delay(function wAttributeFlashSecondDelay()
                {
                    currentElement.removeClass('dcpAttribute__value--endflash');
                }, 600);
            }, 10);
        },

        /**
         * Display tooltip an error message
         *
         * @param message string or array of [{message:, index:}, ...]
         */
        setError: function wAttributeSetError(message)
        {
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
                _.each(messages, function wAttributeSetErrorMsg(indexMessage)
                {
                    if ((indexMessage.index === -1) ||
                        (scope.element.closest('tr').data("line") === indexMessage.index)) {
                        scope.element.addClass("has-error");
                        // need to use sub element because tooltip add a div after element
                        scope.element.find(".input-group").tooltip({
                            placement: "auto bottom",
                            html: true,
                            animation: false,
                            container: scope.element, //".dcpDocument",// no use scope.element because when item is in the bottom of the page a scrollbar can appear
                            title: function wAttributeSetErrorTitle()
                            {
                                var rawMessage = $('<div/>').text(indexMessage.message).html();
                                return '<div>' + '<span title="' + scope.options.labels.closeErrorMessage + '" class="btn fa fa-times button-close-error">&nbsp;</span>' + rawMessage + '</div>';
                            },
                            trigger: "manual"
                        }).each(function wAttributErrorLinkTooltip()
                        {
                            $(this).data("bs.tooltip").tip().addClass("has-error");
                        });
                        scope.element.data("hasErrorTooltip", true);
                        scope.element.find(".input-group").tooltip("show");
                        // Need to refresh to update position after possible change on element value
                        _.delay(function wAttributeSetErrorDelay()
                        {
                            scope.element.find(".input-group").tooltip("hide").tooltip("show");
                        }, 100);
                    }
                });
            } else {
                this.element.removeClass("has-error");
                if (this.element.data("hasErrorTooltip")) {
                    // No use destroy because the destruction is deferred
                    kt = this.element.find(".input-group");
                    kt.tooltip("hide").data("bs.tooltip", null);
                    this.element.data("hasErrorTooltip", false);
                }
            }

        },

        /**
         * Get the type of the widget
         *
         * @returns {string}
         */
        getType: function getType()
        {
            return this.options.type;
        },

        /**
         * Get the mode of the widget
         *
         * @returns {string} Read|Write
         */
        getMode: function getMode()
        {
            if (this.options.mode !== "read" && this.options.mode !== "write" && this.options.mode !== "hidden") {
                throw new Error("Attribute " + this.option.id + " have unknown mode " + this.options.mode);
            }
            return this.options.mode;
        },

        /**
         * Return the value stored in the wiget
         *
         * @returns {*|number|.options.attributeValue}
         */
        getValue: function wAttributegetValue()
        {
            return this.options.attributeValue;
        },

        /**
         * Identify the input where is the raw value
         * @returns {*}
         */
        getContentElements: function wAttributeGetContentElements()
        {
            return this.element.find('.dcpAttribute__value[name="' + this.options.id + '"]');
        },

        /**
         * Return the value of something
         *
         *
         * @returns {*}
         */
        getWidgetValue: function getWidgetValue()
        {
            return this.getContentElements().val();

        },
        /**
         * Set options.attributeValue element and trigger the view
         *
         * @param value
         * @param event
         */
        setValue: function wAttributeSetValue(value, event)
        {
            this._checkValue(value);

            var isEqual = false;

            if (this._isMultiple()) {
                isEqual = _.toArray(this.options.attributeValue).length === value.length;
                if (isEqual) {

                    isEqual = _.isEqual(this.options.attributeValue, value);
                }
            } else {
                isEqual = _.isEqual(this.options.attributeValue.value, value.value);
            }
            if (!isEqual) {
                this.options.attributeValue = value;

                this._trigger("change", event, {
                    id: this.options.id,
                    value: this.getValue(),
                    index: this._getIndex()
                });
            }
        },

        /**
         * Show the input tooltip
         * @param  ktTarget DOMElement
         *
         * @return dcp.dcpAttribute
         */
        hideInputTooltip: function wAttributeHideInputTooltip(ktTarget)
        {
            var $ktTarger = $(ktTarget).closest(".input-group");
            if ($ktTarger.data("hasTooltip")) {
                $ktTarger.tooltip("hide");
            }
            return this;
        },

        /**
         * Show the input tooltip
         * @param  ktTarget DOMElement
         *
         * @return dcp.dcpAttribute
         */
        showInputTooltip: function showInputTooltip(ktTarget)
        {
            var scope = this;

            if (scope.options.renderOptions.inputHtmlTooltip) {
                var $ktTarger = $(ktTarget).closest(".input-group");
                var kt = $ktTarger.data("hasTooltip");

                if (!kt) {
                    $ktTarger.tooltip({
                        trigger: "manual",
                        html: true,
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
         * Close the attribute if open
         *
         * @returns {dcp.dcpAttribute}
         */
        close: function wAttribute_close()
        {
            return this;
        },

        /**
         * Create the widget
         * @private
         */
        _create: function _create()
        {
            var scope = this;
            //If no id is provided one id generated
            if (this.options.id === null) {
                this.options.id = _.uniqueId("widget_" + this.getType());
            }

            if (_.isUndefined(this.options.attributeValue) || this.options.attributeValue === null) {
                if (this._isMultiple()) {
                    this.options.attributeValue = [];
                } else {
                    this.options.attributeValue = {
                        "value": null,
                        displayValue: ""
                    };
                }
            }
            if (this.options.helpOutputs) {
                this.options.hasAutocomplete = true;
            }

            if (this.options.renderOptions && this.options.renderOptions.buttons) {
                // Add index for template to identify buttons
                this.options.renderOptions.buttons = _.map(this.options.renderOptions.buttons, function wAttributeOptionMap(val, index)
                {
                    val.renderHtmlContent = Mustache.render(val.htmlContent || "", scope.options.attributeValue);
                    val.index = index;
                    return val;
                });
            }
            this.options.emptyValue = _.bind(this._getEmptyValue, this);
            this.options.hadButtons = this._hasButtons();
            if (this.options.renderOptions && this.options.renderOptions.labels) {
                this.options.labels = _.extend(this.options.labels, this.options.renderOptions.labels);
            }
            if (this.options.renderOptions && this.options.renderOptions.displayDeleteButton === false) {
                this.options.deleteButton = false;
            }
            if (this.getMode() !== "hidden") {
                this._initDom();

                if (this.element.find(".dcpAttribute__content__buttons button").length === 0) {
                    this.element.find(".dcpAttribute__content__buttons").hide();
                    this.element.find(".dcpAttribute__value").addClass("dcpAttribute__content__nobutton");
                }

                this._initEvent();
            }

            this._triggerReady();
        },

        /**
         * Destroy the widget
         *
         * Suppress widget defined events and delete added dom
         *
         * @private
         */
        _destroy: function _destroy()
        {
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
         * @protected
         */
        _initDom: function wAttributeInitDom()
        {
            var htmlLink = this.getLink();
            var scopeWidget = this;
            this._initMainElementClass();
            if (htmlLink) {
                // Add render Url and title on links
                var originalEscape = Mustache.escape;

                if (this._isMultiple()) {
                    this.options.attributeValues = _.map(this.options.attributeValue, function wAttributeLinkMultiple(val, index)
                    {
                        var urlIndex = index;
                        Mustache.escape = encodeURIComponent;
                        scopeWidget._completeRevisionData(val);
                        if (scopeWidget.options.index >= 0) {
                            // Use index of row prior to index of multiple value
                            urlIndex = scopeWidget.options.index;
                        }

                        if (htmlLink.urls && htmlLink.urls[urlIndex]) {
                            val.renderUrl = Mustache.render(htmlLink.urls[urlIndex], val);
                        } else {
                            val.renderUrl = Mustache.render(htmlLink.url || "", val);
                        }
                        Mustache.escape = originalEscape;
                        val.renderTitle = Mustache.render(htmlLink.title || "", val);
                        val.index = index;
                        return val;
                    });
                } else {
                    Mustache.escape = encodeURIComponent;
                    this._completeRevisionData(this.options.attributeValue);
                    if (htmlLink.urls && htmlLink.urls[this.options.index]) {
                        this.options.renderOptions.htmlLink.renderUrl = Mustache.render(htmlLink.urls[this.options.index], this.options.attributeValue);
                    } else {
                        this.options.renderOptions.htmlLink.renderUrl = Mustache.render(htmlLink.url || "", this.options.attributeValue);
                    }
                    Mustache.escape = originalEscape;
                    this.options.renderOptions.htmlLink.renderTitle = Mustache.render(htmlLink.title || "", this.options.attributeValue);
                }
            }
            this.element.append(Mustache.render(this._getTemplate(this.options.mode) || "", this.options));
        },

        /**
         * Add revision extra data to render link
         * @param data
         * @private
         */
        _completeRevisionData: function wAttribute_completeRevisionData(data)
        {
            data.isRevision = data.revision !== -1 && data.revision !== null && !_.isUndefined(data.revision);
            if (data.isRevision) {
                if (data.revision.state) {
                    data.revisionTarget = "state:" + data.revision.state;
                } else {
                    data.revisionTarget = data.revision;
                }
            }
        },

        /**
         * Init the DOM of the template
         *
         * @public
         */
        _initMainElementClass: function wAttributeInitMainElementClass()
        {
            this.element.addClass("dcpAttribute__content");
            this.element.attr("data-type", this.getType());
            this.element.attr("data-attrid", this.options.id);
        },
        /**
         * Init the events
         *
         * @protected
         */
        _initEvent: function _initEvent()
        {
            if (this.getMode() === "write") {
                this._initDeleteEvent();
                this._initButtonsEvent();
                this._initFocusEvent();
                this._initMoveEvent();
            }
            if (this.getMode() === "read") {
                this._initButtonsEvent();
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
        _initFocusEvent: function wAttributeInitFocusEvent()
        {
            if (this.options.renderOptions && this.options.renderOptions.inputHtmlTooltip) {
                var scope = this;

                var inputTargetFilter = ".dcpAttribute__value";
                this._getFocusInput().on("focus" + this.eventNamespace, function wAttributeFocus(event)
                {
                    var ktTarget = $(event.currentTarget).closest(inputTargetFilter);
                    scope.showInputTooltip(ktTarget);
                });
                this._getFocusInput().on("blur." + this.eventNamespace, function wAttributeBlur(event)
                {
                    var ktTarget = $(event.currentTarget).closest(inputTargetFilter);
                    scope.hideInputTooltip(ktTarget);
                });
            }
            return this;
        },
        /**
         * Reindex widget when a move is performed in an array
         *
         * @protected
         */
        _initMoveEvent: function wAttributeInitFocusEvent()
        {
            var scope = this;
            if (this.options.index !== -1) {
                this.element.on("postMoved" + this.eventNamespace, function wAttributeinitMoveEvent()
                    {
                        var domLine = scope.element.closest('tr').data('line');
                        if (!_.isUndefined(domLine)) {
                            scope.options.index = domLine;
                        }
                    }
                );
            }
            return this;
        },
        /**
         * Init the events associated to buttons (only for write attributes)
         *
         * @protected
         */
        _initButtonsEvent: function _initButtonsEvent()
        {
            var currentWidget = this;
            this.element.on("click" + this.eventNamespace, ".dcpAttribute__content__button--extra", function wAttributeButtonClick(event)
            {
                var buttonsConfig = currentWidget.options.renderOptions.buttons;
                var $button = $(this);
                var buttonIndex = $button.data("index");
                var buttonConfig = buttonsConfig[buttonIndex];
                var wFeature = '';

                if (buttonConfig && buttonConfig.url) {
                    var originalEscape = Mustache.escape;
                    Mustache.escape = encodeURIComponent;
                    var url = Mustache.render(buttonConfig.url || "", currentWidget.options.attributeValue);
                    Mustache.escape = originalEscape;

                    if (buttonConfig.target !== "_dialog") {
                        if (buttonConfig && (buttonConfig.windowWidth || buttonConfig.windowHeight)) {
                            if (buttonConfig.windowWidth) {
                                wFeature += "width=" + parseInt(buttonConfig.windowWidth, 10) + ",";
                            }
                            if (buttonConfig.windowHeight) {
                                wFeature += "height=" + parseInt(buttonConfig.windowHeight, 10) + ",";
                            }
                            wFeature += "resizable=yes,scrollbars=yes";
                        }
                        window.open(url, buttonConfig.target, wFeature);
                    } else {
                        var $bdw = $('<div/>');
                        $('body').append($bdw);
                        var renderTitle = Mustache.render(buttonConfig.windowTitle || "", currentWidget.options.attributeValue);
                        var dw = $bdw.dcpWindow({
                            title: renderTitle,
                            width: buttonConfig.windowWidth,
                            height: buttonConfig.windowHeight,
                            content: url,
                            iframe: true
                        }).data('dcpWindow');
                        dw.kendoWindow().center();
                        dw.open();
                    }
                }

                currentWidget._trigger("click", event, {
                    id: currentWidget.option.id,
                    value: currentWidget.options.attributeValue,
                    index: currentWidget._getIndex()
                });
            });
            this.element.tooltip({
                selector: ".dcpAttribute__content__buttons button",
                placement: "top",
                trigger: "hover",
                html: true,
                title: function wAttributeGetButtonTitle()
                {
                    var title = $(this).data("title");
                    var attrValue = currentWidget.getValue();
                    return Mustache.render(title || "", attrValue);
                },
                container: this.element
            });

            return this;
        },

        /**
         * Init events for delete button on error tooltip
         *
         * @protected
         */
        _initErrorEvent: function wAttributeInitErrotEvent()
        {
            var scope = this;
            // tooltip is created in same parent
            this.element.parent().on("click" + this.eventNamespace, ".button-close-error", function closeError(/*event*/)
            {
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
        _initDeleteEvent: function wAttributeInitDeleteEvent()
        {
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
            $deleteButton.attr('title', titleDelete);

            this.element.on("click" + this.eventNamespace, ".dcpAttribute__content__button--delete", function destroyTable(event)
            {
                currentWidget._trigger("delete", event, {
                    index: currentWidget._getIndex(),
                    id: currentWidget.options.id
                });
                // main input is focuses after deletion
                _.defer(function wAttributeDeferDelete()
                {
                    currentWidget.element.find("input").focus();
                });
            });
            return this;
        },
        /**
         * Init event when a hyperlink is associated to the attribute
         *
         * @protected
         */
        _initLinkEvent: function wAttributeInitLinkEvent()
        {
            var htmlLink = this.getLink();
            var scopeWidget = this;

            if (htmlLink) {

                this.element.on("click." + this.eventNamespace, '.dcpAttribute__content__link', function wAttributeAttributeClick(event)
                {

                    var renderTitle, index, $dialogDiv, dpcWindow, href = $(this).attr("href"), eventContent;

                    if (href.substring(0, 8) === "#action/") {
                        event.preventDefault();
                        eventContent = href.substring(8).split(":");
                        scopeWidget._trigger("externalLinkSelected", event, {
                            target: event.target,
                            eventId: eventContent.shift(),
                            index: scopeWidget._getIndex(),
                            options: eventContent
                        });
                        return this;
                    }

                    if (htmlLink.target === "_dialog") {
                        event.preventDefault();

                        index = $(this).data("index");
                        if (typeof index !== "undefined" && index !== null) {
                            renderTitle = Mustache.render(htmlLink.windowTitle || "", scopeWidget.options.attributeValue[index]);
                        } else {
                            renderTitle = Mustache.render(htmlLink.windowTitle || "", scopeWidget.options.attributeValue);
                        }

                        $dialogDiv = $('<div/>');
                        $('body').append($dialogDiv);

                        dpcWindow = $dialogDiv.dcpWindow({
                            title: renderTitle,
                            width: htmlLink.windowWidth,
                            height: htmlLink.windowHeight,
                            content: href,
                            iframe: true
                        });

                        dpcWindow.data('dcpWindow').kendoWindow().center();
                        dpcWindow.data('dcpWindow').open();

                    }
                });

                this.element.find('.dcpAttribute__content__link[title]').tooltip({
                    placement: "top",
                    container: this.element.parent(),
                    html: true,
                    trigger: "hover"
                }).each(function wAttributeInitLinkTooltip()
                {
                    $(this).data("bs.tooltip").tip().addClass("dcpAttribute__linkvalue");
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
        _getFocusInput: function wAttributeFocusInput()
        {
            return this.element.find('input[name="' + this.options.id + '"]');
        },

        /**
         * Return the index of the attributes (for attribute in a widget array)
         *
         * @returns int
         * @protected
         */
        _getIndex: function _getIndex()
        {
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
        _getEmptyValue: function _getEmptyValue()
        {
            if (_.isEmpty(this.options.attributeValue) || this.options.attributeValue.value === null) {
                if (this.options.renderOptions && this.options.renderOptions.showEmptyContent) {
                    return this.options.renderOptions.showEmptyContent === true ? " " : this.options.renderOptions.showEmptyContent;
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
        _getTemplate: function _getTemplate(key)
        {
            if (this.options.templates && this.options.templates[key]) {
                return this.options.templates[key];
            }
            if (window.dcp && window.dcp.templates && window.dcp.templates[this.getType()] && window.dcp.templates[this.getType()][key]) {
                return window.dcp.templates[this.getType()][key];
            }
            if (window.dcp && window.dcp.templates && window.dcp.templates["default"] && window.dcp.templates["default"][key]) {
                return window.dcp.templates["default"][key];
            }
            throw new Error("Unknown template  " + key + "/" + this.options.type + " for " + this.options.id);
        },

        /**
         * Test if the value of the setValue is correct
         *
         * @param value
         * @returns {boolean}
         */
        _checkValue: function wAttributeTestValue(value)
        {
            //noinspection JSHint
            if (this._isMultiple()) { // jshint ignore:line
                // TODO : Verify each array entry
// jscs:disable disallowEmptyBlocks
            } else {
// jscs:enable disallowEmptyBlocks
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
        _isMultiple: function _isMultiple()
        {
            return (this.options.options && this.options.options.multiple === "yes");
        },

        /**
         * Check if the widget has buttons
         *
         * Used by template for rendering options
         *
         * @returns boolean
         */
        _hasButtons: function wAttributeHasButtons()
        {
            if (this.getMode() === "write") {
                return this.options.hasAutocomplete || this.options.deleteButton || (this.options.renderOptions && this.options.renderOptions.buttons && true);
            } else {
                return (this.options.renderOptions && this.options.renderOptions.buttons && true);
            }
        },
        /**
         * Trigger a ready event when widget is render
         */
        _triggerReady: function wAttributeReady()
        {
            this._trigger("widgetReady");
        },

        /**
         * Trigger an event that should disable save menu on document
         *
         * @param visibility
         * @private
         */
        _setVisibilitySavingMenu: function wAttribute_DisableSavingMenu(visibility)
        {
            var event = {prevent: false};
            this._trigger("changeattrmenuvisibility", event, {
                id: "save",
                onlyIfVisible: true,
                visibility: visibility
            });
            this._trigger("changeattrmenuvisibility", event, {
                id: "saveAndClose",
                onlyIfVisible: true,
                visibility: visibility
            });
            this._trigger("changeattrmenuvisibility", event, {
                id: "createAndClose",
                onlyIfVisible: true,
                visibility: visibility
            });
            this._trigger("changeattrmenuvisibility", event, {
                id: "create",
                onlyIfVisible: true,
                visibility: visibility
            });
        }

    });
}));

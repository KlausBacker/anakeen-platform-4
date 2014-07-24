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
            index: -1,
            deleteLabels : ""

        },

        _create: function () {
            if (typeof this.options.value === "undefined") {
                this.options.value = null;
                this.options.displayValue = null;
            }
            if (this.options.value === null) {
                this.options.value = {};
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
            this.options.emptyValue = _.bind(this._emptyValue, this);
            this._initDom();
            this._initEvent();
        },
        _emptyValue: function () {
            if (_.isEmpty(this.options.value) || this.options.value.value === null) {

                if (this.options.renderOptions  && this.options.renderOptions.showEmptyContent) {
                    return this.options.renderOptions.showEmptyContent;
                }
                return "";
            }
            return "";
        },
        _initDom: function _initDom() {
            this.element.append(Mustache.render(this._getTemplate(), this.options));
        },

        _initEvent: function _initEvent() {
            if (this.getMode() === "write") {
                this._initDeleteEvent();
                this._initButtonsEvent();
                this._initFocusEvent();
            }
        },

        /**
         * Define inputs for focus
         * @protected
         */
        _focusInput: function () {
            console.log("FOCUS default");
            return this.element.find('input[name="' + this.options.id + '"]');
        },

        _initFocusEvent: function _initFocusEvent() {
            if (this.options.renderOptions.inputHtmlTooltip) {
                var scope = this;

                this._focusInput().on("focus", function (event) {
                    var ktTarget = $(event.currentTarget).closest(".dcpAttribute__contentWrapper");
                    scope.showInputTooltip(ktTarget);
                });
                this._focusInput().on("blur", function (event) {
                    var ktTarget = $(event.currentTarget).closest(".dcpAttribute__contentWrapper");
                    scope.hideInputTooltip(ktTarget);
                });
            }
        },

        hideInputTooltip: function (ktTarget) {
            var kTooltip = ktTarget.data("kendoTooltip");
            if (kTooltip) {
                kTooltip.hide();
            }
        },
        showInputTooltip: function (ktTarget) {

            var scope = this;

            if (scope.options.renderOptions.inputHtmlTooltip) {
                var kt = ktTarget.data("kendoTooltip");

                if (!kt) {
                    kt = ktTarget.kendoTooltip({
                        autoHide: false,
                        content: scope.options.renderOptions.inputHtmlTooltip,
                        showOn: "zou",
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
                console.log("focus kt", ktTarget);
                // $(event.delegateTarget).data("kendoTooltip").popup.element.addClass("dcpAttribute__editlabel");
                kt.show();
            }
        },

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
        },

        _initDeleteEvent: function _initDeleteEvent() {
            var currentWidget = this;

            // Compose delete button title
            var $deleteButton = this.element.find(".dcpAttribute__content__button--delete");
            var titleDelete = $deleteButton.attr('title');


            titleDelete += this.options.deleteLabels;
            console.log("delete button", this.options.id,$deleteButton );
            $deleteButton.on("mousedown." + this.eventNamespace,function (event) {
                console.log("Click to delete",{index:currentWidget._getIndex()} );
                currentWidget._trigger("delete", event,  {index:currentWidget._getIndex(), id:currentWidget.options.id});
                currentWidget.element.find("input").focus();
            }).attr('title', titleDelete);

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
        },
        _initLinkEvent: function _initLinkEvent() {
            var htmlLink = this.getLink();
            var scope = this;
            if (htmlLink) {

                this.element.find('.dcpAttribute__content__link').on("click", function (event) {

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

        flashElement: function (currentElement) {
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
                        var contain = this.popup.element.parent();
                        var ktop = parseFloat(contain.css("top"));
                        if (ktop > 0) {
                            contain.css("top", ktop + 6);
                        }
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
                throw new Error("Attribute " + this.option.id + " have unknown mode " + this.options.mode);
            }
            return this.options.mode;
        },

        getValue: function () {
            return this.options.value;
        },

        /**
         * Send notification to the view
         * @param value
         * @param event
         */
        setValue: function wAttributeSetValue(value, event) {
            console.log("dcpAttribute::setValue trigger", this.options.value, value);
            if (!_.isEqual(this.options.value, value)) {
                this.options.value = value;
                console.log("send change trigger from widget", this.options.id,value, this._getIndex());
                this._trigger("change", event, {
                    id: this.options.id,
                    value: value,
                    index: this._getIndex()
                });
            }
        }




    });
});
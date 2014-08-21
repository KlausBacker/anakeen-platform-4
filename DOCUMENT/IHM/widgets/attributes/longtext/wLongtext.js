/*global define, _super*/
define([
    'underscore',
    'widgets/attributes/text/wText'
], function (_) {
    'use strict';

    $.widget("dcp.dcpLongtext", $.dcp.dcpText, {

        options: {
            type: "longtext"
        },

        _initDom: function () {
            var maxDisplayedLine = 5;
            if (this.options.renderOptions && this.options.renderOptions.displayedLineNumber) {
                maxDisplayedLine = this.options.renderOptions.displayedLineNumber;
            }
            this._super();
            this._maxLinesNumber(maxDisplayedLine);
        },

        _initEvent: function _initEvent() {
            if (this.getMode() === "write") {
                this._initKeyPress();
            }
            this._super();
        },

        _maxLinesNumber: function _maxLinesNumber(lineNumber) {
            lineNumber = parseInt(lineNumber);
            if (lineNumber > 0) {
                var scope = this;
                _.defer(function () {
                    var $element = scope.getContentElements();
                    var delta = parseFloat($element.css("padding-top")) + parseFloat($element.css("padding-bottom"));
                    var lineH = $element.css("line-height");
                    // In IE9 , the result is just a number without unit
                    if (lineH && lineH.indexOf("px") > 0) {
                       $element.css("max-height", (lineNumber * parseFloat(lineH) + delta) + "px");
                    }
                });
            }
        },
        /**
         * Define inputs for focus
         * @protected
         */
        _getFocusInput: function () {
            return this.element.find('textarea[name="' + this.options.id + '"]');
        },

        _fitToContent: function _fitToContent($element) {
            var delta = parseFloat($element.css("padding-top")) + parseFloat($element.css("padding-bottom")),
                element = $element.get(0),
                maxHeight=parseFloat($element.css("max-height"));
            if (element) {
                $element.height(element.scrollHeight - delta);
                if (maxHeight > 0) {
                    if (element.scrollHeight > maxHeight) {
                        $element.css("resize", "none");
                    } else {
                        $element.css("resize", "");
                    }
                }
            }
        },

        _initKeyPress: function () {
            var scope = this;
            _.defer(function () {
                scope._fitToContent(scope.getContentElements());
            });
            this.getContentElements().on("keyup", function (event) {
                scope._fitToContent($(this));
            });
        },

        getType: function () {
            return "longtext";
        }

    });

    return $.fn.dcpLongtext;
});
define([
    'underscore',
    'mustache',
    'kendo',
    '../wAttribute',
    'widgets/attributes/text/wText'
], function (_, Mustache, kendo) {
    'use strict';

    $.widget("dcp.dcpLongtext", $.dcp.dcpText, {

        options: {
            id: "",
            type: "longtext"
        },

        _initDom: function () {
            this._super();
            this._maxLinesNumber(this.options.renderOptions.displayedLineNumber);
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
                    var $element = scope.contentElements();
                    var delta = parseFloat($element.css("padding-top")) + parseFloat($element.css("padding-bottom"));
                    var lineH = $element.css("line-height");
                    // In IE9 , the result is just a number without unit
                    if (lineH.indexOf("px") > 0) {
                       $element.css("max-height", (lineNumber * parseFloat(lineH) + delta) + "px");
                    }
                });
            }
        },
        /**
         * Define inputs for focus
         * @protected
         */
        _focusInput: function () {
            return this.element.find('textarea[name="' + this.options.id + '"]');
        },

        _fitToContent: function _fitToContent($element) {

            var delta = parseFloat($element.css("padding-top")) + parseFloat($element.css("padding-bottom"));
            var element = $element.get(0);
            var maxHeight=parseFloat($element.css("max-height"));
            $element.height(element.scrollHeight - delta);
            if (maxHeight > 0) {
                if( element.scrollHeight > maxHeight){
                $element.css("resize","none");
                } else {
                    $element.css("resize","");
                }
            }

        },

        _initKeyPress: function () {
            var scope = this;
            _.defer(function () {
                scope._fitToContent(scope.contentElements());
            });
            this.contentElements().on("keyup", function (event) {
                scope._fitToContent($(this));
            });
        },


        getType: function () {
            return "longtext";
        }

    });
});
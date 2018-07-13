/*global define*/
(function umdRequire(root, factory) {
  "use strict";

  if (typeof define === "function" && define.amd) {
    define([
      "jquery",
      "underscore",
      "dcpDocument/widgets/attributes/text/wText"
    ], factory);
  } else {
    //noinspection JSUnresolvedVariable
    factory(window.jQuery, window._);
  }
})(window, function requireDcpLongText($, _) {
  "use strict";

  $.widget("dcp.dcpLongtext", $.dcp.dcpText, {
    options: {
      type: "longtext",
      renderOptions: {
        displayedLineNumber: 5
      }
    },

    _initDom: function dcpLongtext_initDom() {
      var maxDisplayedLine = this.options.renderOptions.displayedLineNumber;

      this._super();
      this._maxLinesNumber(maxDisplayedLine);
    },

    _initEvent: function dcpLongtext_initEvent() {
      if (this.getMode() === "write") {
        this._initAutoFit();
      }
      this._super();
    },

    _maxLinesNumber: function dcpLongtext_maxLinesNumber(lineNumber) {
      lineNumber = parseInt(lineNumber, 10);
      if (lineNumber > 0) {
        var scope = this;
        _.defer(function dcpLongtext_deferComputeSize() {
          var $element = scope.getContentElements();
          var delta =
            parseFloat($element.css("padding-top")) +
            parseFloat($element.css("padding-bottom"));
          var lineH = $element.css("line-height");
          // In IE9 , the result is just a number without unit
          if (lineH) {
            if (lineH.indexOf("px") > 0) {
              $element.css(
                "max-height",
                lineNumber * parseFloat(lineH) + delta + "px"
              );
            } else {
              $element.css("max-height", lineNumber * 1.1 + "em");
            }
          }
        });
      }
    },
    /**
     * Define inputs for focus
     * @protected
     */
    _getFocusInput: function dcpLongtext_getFocusInput() {
      return this.element.find('textarea[name="' + this.options.id + '"]');
    },

    _fitToContent: function dcpLongtext_fitToContent($element) {
      var delta =
          parseFloat($element.css("padding-top")) +
          parseFloat($element.css("padding-bottom")),
        element = $element.get(0),
        maxHeight = parseFloat($element.css("max-height"));
      if (element && element.scrollHeight > element.clientHeight) {
        $element.height(element.scrollHeight - delta + 1);
        if (maxHeight > 0) {
          if (element.scrollHeight > maxHeight) {
            $element.css("resize", "none");
          } else {
            $element.css("resize", "");
          }
        }
      }
    },

    _initAutoFit: function dcpLongtext_initAutoFit() {
      var scope = this;
      _.defer(function dcpLongtext_computeSize() {
        scope._fitToContent(scope.getContentElements());
      });
      this.getContentElements()
        .on(
          "keyup" + this.eventNamespace,
          function dcpLongtext_keyUpUpdateSize() {
            scope._fitToContent($(this));
          }
        )
        .on(
          "focus" + this.eventNamespace,
          function dcpLongtext_focusUpdateSize() {
            scope._fitToContent($(this));
          }
        );

      this.element.on(
        "show" + this.eventNamespace,
        function dcpLongtext_showUpdateSize() {
          scope._fitToContent(scope.getContentElements());
        }
      );
    },

    getType: function dcpLongtext_getType() {
      return "longtext";
    }
  });

  return $.fn.dcpLongtext;
});

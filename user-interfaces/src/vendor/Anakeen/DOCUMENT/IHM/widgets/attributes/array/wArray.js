(function umdRequire(root, factory) {
  "use strict";

  if (typeof define === "function" && define.amd) {
    define(["jquery", "underscore", "mustache", "dcpDocument/widgets/widget"], factory);
  } else {
    //noinspection JSUnresolvedVariable
    factory(window.jQuery, window._, window.Mustache);
  }
})(window, function require_array($, _, Mustache) {
  "use strict";

  $.widget("dcp.dcpArray", {
    options: {
      tools: true,
      nbLines: 0,
      renderOptions: {
        rowCountThreshold: -1,
        rowAddDisable: false,
        rowDelDisable: false,
        rowMoveDisable: false,
        rowMinLimit: -1,
        rowMinDefault: 0,
        rowMaxLimit: -1,
        collapse: false
      },
      displayLabel: true,
      customTemplate: false,
      labels: {
        closeErrorMessage: "Close message",
        limitMaxMessage: "Row count limit to {{limit}}",
        limitMinMessage: "Min row limit is {{limit}}"
      }
    },

    /**
     * get Selected line element (jquery, length = 0 if no selected)
     * @returns {*}
     */
    getSelectedLineElement: function dcpArraygetSelectedLineElement() {
      return this.element.find(".dcpArray__content__line--selected.active");
    },
    /**
     * get Selected line index (0 : first, null : no selected line)
     * @returns {*}
     */
    getSelectedLineIndex: function dcpArray_getSelectedLineIndex() {
      var index = this.getSelectedLineElement().data("line");
      if (_.isUndefined(index) || _.isNull(index)) {
        return null;
      }
      return index;
    },
    /**
     *
     * @private
     */
    _create: function dcpArray_create() {
      this.options.tools = this.options.mode === "write" && this.options.visibility !== "U";
      if (
        this.options.renderOptions.rowAddDisable === true &&
        this.options.renderOptions.rowDelDisable === true &&
        this.options.renderOptions.rowMoveDisable === true
      ) {
        this.options.tools = false;
      }
      if (this.options.renderOptions.attributeLabel) {
        this.options.label = this.options.renderOptions.attributeLabel;
      }

      this.initializing = true;
      this._initDom().then(
        _.bind(function onDomOK() {
          this._trigger("widgetReady");
        }, this)
      );
      if (!this._isEventBinded()) {
        this._initActionClickEvent();
        this._bindEvents();
        this._isEventBinded(true);
      }
      this.element.on(
        "click" + this.eventNamespace,
        ".dcpArray--collapsable",
        _.bind(function toogleTable() {
          this.toggleCollapse.apply(this);
        }, this)
      );

      if (this.options.renderOptions.collapse === "collapse") {
        this.toggleCollapse(null, true);
      }

      this.initializing = false;
    },

    _isInitialized: function dcpArray_isInitialized(value) {
      var $tbody = this.element.find(".dcpArray__body");
      if (value !== undefined) {
        $tbody.data("bodyGenerated", value);
      }
      return $tbody.data("bodyGenerated");
    },
    _isEventBinded: function dcpArray_isEventBinded(value) {
      var $tbody = this.element.find(".dcpArray__body");
      if (value !== undefined) {
        $tbody.data("eventsBinded", value);
      }
      return $tbody.data("eventsBinded");
    },

    _initDom: function dcpArray_initDom() {
      return new Promise(
        _.bind(function dcpArray_initDom_initDom(resolve, reject) {
          var scope = this,
            $table;
          if (this.options.mode === "read" && this.options.nbLines === 0 && this.options.showEmpty !== true) {
            if (this.options.showEmpty) {
              this.element.addClass("card card-default");
              // showEmptyCOntent option
              if (this.options.displayLabel !== false) {
                this.element.append(Mustache.render(this._getTemplate("label") || "", this.options));
                if (this.options.renderOptions.labelPosition === "left") {
                  this.element.find(".dcpLabel").addClass("dcpArray__label--left");
                }
              }

              this.element.append(this.options.showEmpty);
            }
          } else {
            this.element.addClass("card card-default");
            if (this.options.displayLabel !== false) {
              this.element.append(
                Mustache.render(
                  this._getTemplate("label") || "",
                  _.extend(this.options, {
                    collapsable: this.options.renderOptions.collapse !== "none",
                    displayCount:
                      this.options.renderOptions.rowCountThreshold >= 0 &&
                      this.options.nbLines >= this.options.renderOptions.rowCountThreshold
                  })
                )
              );
            }

            if (this.options.customTemplate) {
              // The template is already composed on view
              this.element.append(this.options.customTemplate);
              this.element
                .find(".dcpCustomTemplate table.dcpArray__table > tbody > tr")
                .addClass("dcpArray__content__line");
              this._indexLine();
              this.element.find(".dcpArray__content__line").attr("data-attrid", this.options.id);
              this.element.find(".dcpCustomTemplate").addClass("dcpArray__content dcpArray__content--open");
            } else {
              _.each(this.options.content, function wArrayCopyRenderContent(anOption) {
                // Need duplicate because Mustache is confused when 2 attributes has same name
                anOption.contentRenderOptions = anOption.renderOptions;
              });
              this.element.append(Mustache.render(this._getTemplate("content") || "", this.options));

              if (this.options.mode === "write") {
                this.element.find(".dcpArray__content").addClass("dcpArray--tooltips");
                this.element.tooltip({
                  selector:
                    ".dcpArray--tooltips .dcpArray__content__toolCell span, .dcpArray--tooltips .dcpArray__tools .dcpArray__button",
                  placement: function(tooltipDom, targetDom) {
                    // Auto hide after 3s
                    _.delay(function() {
                      $(targetDom).tooltip("hide");
                    }, 3000);
                    if ($(targetDom).closest(".dcpArray__tools").length > 0) {
                      return "bottom";
                    }
                    return "top";
                  },
                  container: ".dcpDocument",

                  delay: {
                    hide: 0,
                    show: 500
                  }
                });
              }
            }
            if (this.options.displayLabel !== false) {
              var labelPosition = this.options.renderOptions.labelPosition;
              if (labelPosition === "auto" || labelPosition === "left") {
                this.element.find(".dcpArray__label").addClass("dcpAttribute__left");
                this.element.find(".dcpArray__content").addClass("dcpAttribute__right");
                this.element.addClass("dcpArray--left");
              }

              this.element.find(".dcpAttribute__right").addClass("dcpAttribute__labelPosition--" + labelPosition);
              this.element.find(".dcpAttribute__left").addClass("dcpAttribute__labelPosition--" + labelPosition);
              this.element.addClass("dcpAttribute__labelPosition--" + labelPosition);
            }

            if (this.options.renderOptions.rowAddDisable === true) {
              this.element.find(".dcpArray__button--add, .dcpArray__button--copy").hide();
            }

            // Set system css classes
            $table = this.element.find(".dcpArray__table");
            $table.addClass("table table-condensed table-hover table-bordered");
            $table
              .find("> tbody")
              .addClass("dcpArray__body")
              .attr("data-attrid", this.options.id);
            $table
              .find("> thead")
              .attr("data-attrid", this.options.id)
              .find("tr")
              .addClass("dcpArray__head")
              .attr("data-attrid", this.options.id);

            if (this.options.mode === "write" && this.options.renderOptions.rowMoveDisable !== true) {
              //Initiate drag drop events
              this.element.find("tbody").kendoDraggable({
                axis: "y",
                container: scope.element.find("tbody"),
                filter: ".dcpArray__content__toolCell__dragDrop",
                hint: function dcpArrayhint(element) {
                  var dragLine = element.closest("tr");
                  var lineWidth = dragLine.width();
                  var classTable = element.closest("table").attr("class");

                  scope._hideTooltips();
                  scope._disableTooltips();

                  var $table = $("<table/>")
                    .addClass("dcpArray__dragLine " + classTable)
                    .css("width", lineWidth)
                    .css("margin-left", "-" + (element.offset().left - dragLine.offset().left) + "px");

                  return $table.append($("<tbody/>").append(dragLine.clone()));
                },
                dragstart: function dcpArraydragstart(event) {
                  if (event.currentTarget) {
                    var dragLine = $(event.currentTarget).closest("tr");
                    dragLine.css("opacity", "0");
                    dragLine.data("fromLine", dragLine.data("line"));
                  }
                },
                dragend: function dcpArraydragend(event) {
                  if (event.currentTarget) {
                    var dragLine = $(event.currentTarget).closest("tr");
                    dragLine.css("opacity", "");
                    scope._trigger(
                      "lineMoved",
                      {},
                      {
                        fromLine: dragLine.data("fromLine"),
                        toLine: dragLine.data("line")
                      }
                    );
                  }

                  scope._enableTooltips();
                }
              });

              this.element.find("tbody").kendoDropTargetArea({
                filter: '.dcpArray__content__line[data-attrid="' + this.options.id + '"]',
                dragenter: function dragenter(event) {
                  if (event.currentTarget) {
                    var drap = event.draggable.currentTarget.closest("tr");
                    var drop = event.dropTarget;
                    var drapLine = drap.data("line");
                    var dropLine = drop.data("line");
                    if (drapLine > dropLine) {
                      drap.insertBefore(drop);
                    } else {
                      drap.insertAfter(drop);
                    }
                    scope._indexLine();
                  }
                }
              });
            }
            this.element.on("click" + this.eventNamespace, ".button-close-error", function destroyTable(/*event*/) {
              scope.element.find(".dcpArray__content table.table").tooltip("destroy");
            });
          }

          if (!this._isInitialized()) {
            this.addAllLines(this.options.nbLines)
              .then(resolve)
              .catch(reject);
            this._isInitialized(true);
          }
        }, this)
      );
    },
    /**
     * Init event for #action/ links
     *
     * @protected
     */
    _initActionClickEvent: function wAttributeInitActionClickEvent() {
      var scopeWidget = this;

      this.element.on(
        "click." + this.eventNamespace,
        'a[href^="#action/"], a[data-action], button[data-action]',
        function wAttributeActionClick(event) {
          var $this = $(this),
            action,
            options,
            eventOptions;

          event.preventDefault();
          if (event.stopPropagation) {
            event.stopPropagation();
          }

          action = $this.data("action") || $this.attr("href");
          options = action.substring(8).split(":");
          eventOptions = {
            target: event.target,
            index: -1,
            eventId: options.shift(),
            options: options
          };

          scopeWidget._trigger("externalLinkSelected", event, eventOptions);
          return this;
        }
      );
    },

    _hideTooltips: function wArray__hideTooltips() {
      var $element = this.element;
      $element.find("[aria-describedby^=tooltip]").tooltip("hide");
      _.delay(function _hideTooltipsOneAgain() {
        // Need redo cause animation delay of 500ms
        $element.find("[aria-describedby^=tooltip]").tooltip("hide");
      }, 500);
    },
    _disableTooltips: function wArray__disableTooltips() {
      this.element.find(".dcpArray__content").removeClass("dcpArray--tooltips");
    },
    _enableTooltips: function wArray__enableTooltips() {
      this.element.find(".dcpArray__content").addClass("dcpArray--tooltips");
    },

    _bindEvents: function dcpArray_bindEvents() {
      var currentWidget = this;
      this.element.on(
        "click" + this.eventNamespace,
        ".dcpArray__content__toolCell__check input",
        function selectLineEvent() {
          var $this = $(this);
          var isAlreadyChecked = $this
            .closest(".dcpArray__content__line")
            .hasClass("dcpArray__content__line--selected");
          currentWidget._hideTooltips();
          currentWidget._unSelectLines();
          if (isAlreadyChecked) {
            currentWidget.element.find(".dcpArray__copy").prop("disabled", true);
            $(this)
              .prop("checked", false)
              .removeAttr("checked");
          } else {
            $this.find(".fa-check").show();
            $this.closest(".dcpArray__content__line").addClass("dcpArray__content__line--selected active");
            currentWidget.element.find(".dcpArray__copy").prop("disabled", false);
            $(this)
              .prop("checked", true)
              .attr("checked", "checked");
          }
        }
      );
      this.element.on("click" + this.eventNamespace, ".dcpArray__add", function addLineEvent() {
        var selectedLine = currentWidget.getSelectedLineIndex();
        currentWidget._hideTooltips();
        if (
          currentWidget.options.renderOptions.rowMaxLimit < 0 ||
          currentWidget.options.nbLines < currentWidget.options.renderOptions.rowMaxLimit
        ) {
          if (selectedLine === null || _.isUndefined(selectedLine)) {
            currentWidget.options.nbLines += 1;
            currentWidget.addLine(currentWidget.options.nbLines - 1, {
              needAddValue: true,
              useSelectedLine: true
            });
          } else {
            currentWidget.options.nbLines += 1;
            currentWidget.addLine(selectedLine, {
              needAddValue: true,
              useSelectedLine: true
            });
          }
        }
      });
      this.element.on("click" + this.eventNamespace, ".dcpArray__copy", function copyLineEvent() {
        var selectedLine = currentWidget.getSelectedLineIndex();

        currentWidget._hideTooltips();
        if (
          currentWidget.options.renderOptions.rowMaxLimit < 0 ||
          currentWidget.options.nbLines < currentWidget.options.renderOptions.rowMaxLimit
        ) {
          currentWidget.options.nbLines += 1;
          currentWidget.copyLine(selectedLine, {
            needAddValue: true,
            useSelectedLine: true
          });
        }
      });
      this.element.on(
        "click" + this.eventNamespace,
        ".dcpArray__content__toolCell__delete button",
        function deleteLineEvent() {
          currentWidget._hideTooltips();
          currentWidget.removeLine(
            $(this)
              .closest(".dcpArray__content__line")
              .data("line")
          );
          currentWidget.element.find(".dcpArray__copy").prop("disabled", true);
        }
      );
    },

    toggleCollapse: function toggleCollapse(event, hideNow) {
      this._hideTooltips();
      var $contentElement = this.element.find(".dcpArray__content");
      this.element.find(".dcp__array__caret").toggleClass("fa-caret-right fa-caret-down");
      $contentElement.toggleClass("dcpArray__content--open dcpArray__content--close");
      if (hideNow) {
        $contentElement.hide();
      } else {
        $contentElement.slideToggle(200);
      }
    },

    /**
     * Redraw label with current count
     */
    redrawLabel: function wArrayRedrawLabel() {
      this.element.find(".dcpArray__label").html(
        $(
          Mustache.render(
            this._getTemplate("label") || "",
            _.extend(this.options, {
              displayCount:
                this.options.renderOptions.rowCountThreshold >= 0 &&
                this.options.nbLines >= this.options.renderOptions.rowCountThreshold
            })
          )
        ).html()
      );
    },
    setLines: function wArraySetLines(lineNumber, options) {
      return new Promise(
        _.bind(function wArraySetLines_promise(resolve, reject) {
          var linesPromise = [];
          if (!this.initializing) {
            // No auto add lines when array is initializing itself
            var currentLineNumber = this.options.nbLines;
            var i;
            if (lineNumber > currentLineNumber) {
              for (i = 0; i < lineNumber - currentLineNumber; i += 1) {
                linesPromise.push(this.addLine(currentLineNumber + i, options));
              }
              Promise.all(linesPromise)
                .then(resolve)
                .catch(reject);
            } else if (lineNumber < currentLineNumber) {
              for (i = 0; i < currentLineNumber - lineNumber; i += 1) {
                this.removeLine(this.options.nbLines - 1, options);
              }
              resolve();
            } else {
              resolve();
            }
          } else {
            resolve();
          }
        }, this)
      );
    },

    addAllLines: function dcpArrayaddAllLines(lineNumber) {
      var i,
        min,
        allPromiseLines = [];
      var $tbody = this.element.find(".dcpArray__body");
      $tbody.empty();

      for (i = 0; i < lineNumber; i += 1) {
        this.addLine(i);
      }

      min = Math.max(this.options.renderOptions.rowMinLimit, this.options.renderOptions.rowMinDefault);
      if (min > 0) {
        if (this.options.nbLines < min) {
          for (i = this.options.nbLines; i < min; i += 1) {
            allPromiseLines.push(this.addLine(i, { needAddValue: true }));
          }
        }
      }

      return Promise.all(allPromiseLines).then(
        _.bind(function allLineAdded() {
          this._trigger("linesGenerated");
        }, this)
      );
    },

    _getLineContent: function dcpArray_getLineContent(index) {
      var $content = "NULL LINE";

      this.options.lineCid = _.uniqueId(this.options.id);
      if (this.options.customTemplate) {
        $content = this.options.customLineCallback.apply(this, [index]);
        $content.addClass("dcpArray__content__line");
        $content.attr("data-attrid", this.options.id);
      } else {
        $content = $(Mustache.render(this._getTemplate("line") || "", _.extend({ lineNumber: index }, this.options)));
      }
      $content
        .find(".dcpArray__content__toolCell")
        .closest("td")
        .addClass("dcpArray__toolCell");

      if (this.options.customTemplate) {
        // add data-heading for responsive by code when custom
        this.element.find(".dcpCustomTemplate table.dcpArray__table > thead > tr > th").each((index, thElt) => {
          $content.find("> td:nth-child(" + (index + 1) + ")").attr(
            "data-heading",
            $(thElt)
              .text()
              .trim()
          );
        });
      }

      if (this.options.renderOptions.rowDelDisable === true) {
        $content.find(".dcpArray__content__toolCell__delete").hide();
        if (this.options.renderOptions.rowAddDisable === true) {
          // Delete row selector also
          $content.find(".dcpArray__content__toolCell__check").hide();
        }
      }
      if (this.options.renderOptions.rowMoveDisable === true) {
        $content.find(".dcpArray__content__toolCell__dragDrop").hide();
      }
      _.each($content.find(">td"), function dcpArray_addCssClass(currentCell) {
        $(currentCell)
          .find(".dcpArray__content__cell")
          .closest("td")
          .addClass("dcpArray__cell");
      });
      return $content;
    },

    _addNewLine: function dcpArray_addNewLine(lineNumber, options) {
      if (!_.isNumber(lineNumber)) {
        throw new Error("You need to indicate the line number");
      }

      var $content = this._getLineContent(lineNumber);
      var selectedLine = [];

      if (options && options.useSelectedLine) {
        selectedLine = this.getSelectedLineElement();
      }

      if (selectedLine.length === 1) {
        $content.insertBefore(selectedLine);
      } else {
        this.element.find(".dcpArray__body").append($content);
      }
      this._indexLine();
      this.redrawLabel();
      this._activateRowLimits();

      return $content;
    },

    /**
     * Disable/Enable Add/copy button
     */
    _activateRowLimits: function wArray_activateRowLimits() {
      var currentWidget = this;
      if (this.options.renderOptions.rowMaxLimit >= 0) {
        if (this.options.nbLines >= this.options.renderOptions.rowMaxLimit) {
          this.element.find(".dcpArray__add, .dcpArray__copy").prop("disabled", true);
          this.element.find(".dcpArray__button--add, .dcpArray__button--copy").each(function dcpArray_initLine() {
            var $this = $(this);
            if (!$this.data("originalTitle")) {
              $this.data("originalTitle", $this.attr("title"));
            }
            // reset tooltip
            $this.tooltip("hide").data("bs.tooltip", null);

            $this.attr(
              "title",
              Mustache.render(currentWidget.options.labels.limitMaxMessage || "", {
                limit: currentWidget.options.renderOptions.rowMaxLimit
              })
            );
          });
        } else {
          this.element.find(".dcpArray__add, .dcpArray__copy").prop("disabled", false);
          this.element.find(".dcpArray__button--add, .dcpArray__button--copy").each(function dcpArray_initLine() {
            // reset tooltip
            $(this)
              .tooltip("hide")
              .data("bs.tooltip", null);
            $(this).attr("title", $(this).data("originalTitle"));
          });
        }
      }

      if (this.options.renderOptions.rowMinLimit >= 0) {
        if (this.options.nbLines <= this.options.renderOptions.rowMinLimit) {
          this.element.find(".dcpArray__content__toolCell__delete button").prop("disabled", true);
          this.element.find(".dcpArray__content__toolCell__delete").each(function dcpArray_initLine() {
            if (!$(this).data("originalTitle")) {
              $(this).data("originalTitle", $(this).attr("title"));
            }
            // reset tooltip
            $(this)
              .tooltip("hide")
              .data("bs.tooltip", null);

            $(this).attr(
              "title",
              Mustache.render(currentWidget.options.labels.limitMinMessage || "", {
                limit: currentWidget.options.renderOptions.rowMinLimit
              })
            );
          });
        } else {
          this.element.find(".dcpArray__content__toolCell__delete button").prop("disabled", false);
          this.element.find(".dcpArray__content__toolCell__delete").each(function dcpArray_initLine() {
            // reset tooltip
            $(this)
              .tooltip("hide")
              .data("bs.tooltip", null);
            $(this).attr("title", $(this).data("originalTitle"));
          });
        }
      }
    },

    addLine: function dcpArrayaddLine(lineNumber, options) {
      return new Promise(
        _.bind(function dcpArrayaddLine_promise(resolve, reject) {
          var $content = this._addNewLine(lineNumber, options);
          if ($content) {
            options = _.defaults(options || {}, {
              silent: false,
              needAddValue: false
            });
            if (options.silent !== true) {
              this._trigger(
                "lineAdded",
                {},
                {
                  line: lineNumber,
                  element: $content,
                  needAddValue: options.needAddValue,
                  resolve: resolve,
                  reject: reject
                }
              );
            }
          }
        }, this)
      );
    },

    copyLine: function dcpArraycopyLine(lineNumber, options) {
      return new Promise(
        _.bind(function dcpArrayaddLine() {
          var $content = this._addNewLine(lineNumber, options);
          if ($content) {
            this._trigger("lineAdded", {}, { line: lineNumber, element: $content, copyValue: true });
          }
        }, this)
      );
    },

    removeLine: function dcpArrayremoveLine(line, options) {
      options = options || {};
      this.element.find("[data-line=" + line + "]").remove();
      this._indexLine();
      if (options.silent !== true) {
        this._trigger("lineRemoved", {}, { line: line });
      }
      this.redrawLabel();
      this._activateRowLimits();
    },

    _destroy: function dcpArray_destroy() {
      var tbody = this.element.find("tbody");
      if (tbody && tbody.data("kendoDropTargetArea")) {
        tbody.data("kendoDropTargetArea").destroy();
      }
      if (tbody && tbody.data("kendoDraggable")) {
        tbody.data("kendoDraggable").destroy();
      }
      this.element.empty();
      this._super();
    },

    _indexLine: function dcpArray_indexLine() {
      var i = 0;
      this.element.find(".dcpArray__content__line").each(function numeroteLine() {
        $(this)
          .attr("data-line", i)
          .data("line", i);
        i += 1;
      });
      this.options.nbLines = i;
    },

    _unSelectLines: function dcpArray_unSelectLines() {
      this.element.find(".dcpArray__content__toolCell__check .fa-check").hide();
      this.element.find(".dcpArray__content__line--selected").removeClass("dcpArray__content__line--selected active");
    },
    /**
     * Display tooltip an error message
     *
     * @param message string or array of [{message:, index:}, ...]
     */
    setError: function dcpArray_SetError(message) {
      var scope = this;
      var $target = this.element.find(".dcpArray__content table.table");
      if (message) {
        $target
          .tooltip({
            placement: "top",
            trigger: "manual",
            animation: false,
            html: true,
            title: function dcpArray_computeTitleError() {
              var rawMessage = $("<div/>")
                .text(message)
                .html();
              return (
                "<div>" +
                '<span title="' +
                scope.options.labels.closeErrorMessage +
                '" class="btn fa fa-times button-close-error"> &nbsp;</span>' +
                rawMessage +
                "</div>"
              );
            }
          })
          .one("shown.bs.tooltip", function wErrorTooltip() {
            var tipElement = $(this).data("bs.tooltip").tip;
            if (tipElement) {
              $(tipElement).addClass("has-error");
            }
          })
          .tooltip("show")
          .addClass("dcpArray--error");
      } else {
        $target.tooltip("hide").removeClass("dcpArray--error");
      }
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
    _getTemplate: function dcpArray_getTemplate(key) {
      if (this.options.templates && this.options.templates[key]) {
        return this.options.templates[key];
      }
      if (
        window.dcp &&
        window.dcp.templates &&
        window.dcp.templates[this.getType()] &&
        window.dcp.templates[this.getType()][key]
      ) {
        return window.dcp.templates[this.getType()][key];
      }
      if (
        window.dcp &&
        window.dcp.templates &&
        window.dcp.templates["default"] &&
        window.dcp.templates["default"][key]
      ) {
        return window.dcp.templates["default"][key];
      }
      throw new Error("Unknown template  " + key + "/" + this.options.type);
    },

    getType: function dcpArray_getType() {
      return "array";
    }
  });

  return $.fn.dcpArray;
});

define([
    'underscore',
    'mustache',
    'jquery',
    'widgets/widget'
], function (_, Mustache, $) {
    'use strict';

    $.widget("dcp.dcpArray", {

        options: {
            tools: true,
            nbLines: 0,
            renderOptions: {
                rowCountThreshold: -1
            },
            displayLabel: true,
            customTemplate: false
        },

        /**
         * get Selected line element (jquery, length = 0 if no selected)
         * @returns {*}
         */
        getSelectedLineElement: function dcpArraygetSelectedLineElement() {
            return this.element.find('.dcpArray__content__line--selected.active');
        },
        /**
         * get Selected line index (0 : first, null : no selected line)
         * @returns {*}
         */
        selectedLineIndex: function dcpArrayselectedLineIndex() {
            return this.getSelectedLineElement().data("line");
        },
        /**
         *
         * @private
         */
        _create: function dcpArray_create() {
            this.options.tools = this.options.mode === "write" && this.options.visibility !== "U";
            this._initDom();
            this._bindEvents();
        },

        _initDom: function dcpArray_initDom() {
            var scope = this, content = '';
            if (this.options.mode === "read" && this.options.nbLines === 0) {
                if (this.options.showEmpty) {
                    this.element.addClass("panel panel-default");
                    // showEmptyCOntent option
                    if (this.options.displayLabel !== false) {
                        this.element.append(Mustache.render(this._getTemplate("label"), this.options));
                    }

                    this.element.append(this.options.showEmpty);

                }
            } else {
                this.element.addClass("panel panel-default");
                if (this.options.displayLabel !== false) {
                    this.element.append(Mustache.render(this._getTemplate("label"), _.extend(this.options, {
                        displayCount: (this.options.renderOptions.rowCountThreshold >= 0 && this.options.nbLines >= this.options.renderOptions.rowCountThreshold)
                    })));
                }

                if (this.options.customTemplate) {
                    // The template is already composed on view
                    this.element.append(this.options.customTemplate);
                    this.element.find(".dcpCustomTemplate tbody tr").addClass("dcpArray__content__line");
                    this._indexLine();
                    this.element.find(".dcpArray__content__line").attr("data-attrid", this.options.id);
                    this.element.find(".dcpCustomTemplate").addClass("dcpArray__content dcpArray__content--open");
                    this.element.find(".dcpCustomTemplate tbody").addClass("dcpArray__body");

                } else {
                    this.element.append(Mustache.render(this._getTemplate("content"), this.options));


                    if (this.options.mode === "write") {
                        this.element.find('.dcpArray__tools button').tooltip({
                            placement: "top"
                        });
                        this.element.tooltip({
                            selector: ".dcpArray__content__toolCell span",
                            placement: "top"
                        });
                    }
                }
                this.addAllLines(this.options.nbLines);
                if (this.options.mode === "write") {
                    this.element.find('tbody').kendoDraggable({
                        axis: "y",
                        container: scope.element.find('tbody'),
                        filter: '.dcpArray__content__toolCell__dragDrop',
                        hint: function dcpArrayhint(element) {
                            var dragLine = element.closest('tr');
                            var lineWidth = dragLine.width();
                            var classTable = element.closest('table').attr("class");
                            return $('<table/>').addClass("dcpArray__dragLine " + classTable).
                                css("width", lineWidth).
                                css("margin-left", "-" + (element.offset().left - dragLine.offset().left) + "px"). // @TODO compute delta left
                                append(dragLine.clone());
                        },
                        dragstart: function dcpArraydragstart(event) {
                            if (event.currentTarget) {
                                var dragLine = $(event.currentTarget).closest('tr');
                                dragLine.css("opacity", "0");
                                dragLine.data("fromLine", dragLine.data("line"));
                            }
                        },
                        dragend: function dcpArraydragend(event) {
                            if (event.currentTarget) {
                                var dragLine = $(event.currentTarget).closest('tr');
                                dragLine.css("opacity", "");
                                scope._trigger("lineMoved", {}, {
                                    fromLine: dragLine.data("fromLine"),
                                    toLine: dragLine.data("line")
                                });
                            }
                        }
                    });

                    this.element.find('tbody').kendoDropTargetArea({
                        filter: '.dcpArray__content__line[data-attrid="' + this.options.id + '"]',
                        dragenter: function (event) {
                            if (event.currentTarget) {
                                var drap = event.draggable.currentTarget.closest('tr');
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

            }
            this._initCSSResponsive();
        },


        _initCSSResponsive: function _initCSSResponsive() {
            var cssString, cssTemplate, headers = _.map(this.element.find(".dcpArray__head__cell"), function (currentElement) {
                var $currentElement = $(currentElement);
                return {
                    "attrid": $currentElement.data("attrid"),
                    "label": $currentElement.text().trim()
                };
            });

            // Generate CSS string
            cssString = "<style>@media only screen and (max-width: 760px), (min-device-width: 768px) and (max-device-width: 1024px) { ";

            cssTemplate = _.template('.dcpArray__content[data-attrid=' + this.options.id + '] .dcpAttribute__content[data-attrid=<%= attrid %>]:before { content: "<%= label %>"; }');

            _.each(headers, function initCssHeader(currentHeader) {
                currentHeader.label = currentHeader.label.replace(/([\\"])/g, "\\$1").replace(/\n/g, " ");
                cssString += cssTemplate(currentHeader);
            });
            cssString += " }</style>";

            this.element.append(cssString);
        },

        _bindEvents: function dcpArray_bindEvents() {
            var currentWidget = this;
            this.element.on("click" + this.eventNamespace, ".dcpArray__content__toolCell__check", function selectLineEvent() {
                var $this = $(this);
                currentWidget._unSelectLines();
                if ($this.data("selectedRow") === "1") {
                    $this.data("selectedRow", "0");
                    currentWidget.element.find(".dcpArray__copy").attr("disabled", "disabled");
                } else {
                    $this.find('.fa-check').show();
                    $this.closest(".dcpArray__content__line").addClass("dcpArray__content__line--selected active");
                    $this.data("selectedRow", "1");
                    currentWidget.element.find(".dcpArray__copy").removeAttr("disabled");
                }
            });
            this.element.on("click" + this.eventNamespace, ".dcpArray__add", function addLineEvent() {
                var selectedLine = currentWidget.selectedLineIndex();
                if (selectedLine === null) {
                    currentWidget.options.nbLines += 1;
                    currentWidget.addLine(currentWidget.options.nbLines - 1, {needAddValue: true});
                } else {
                    currentWidget.options.nbLines += 1;
                    currentWidget.addLine(selectedLine, {needAddValue: true});
                }
            });
            this.element.on("click" + this.eventNamespace, ".dcpArray__copy", function copyLineEvent() {
                var selectedLine = currentWidget.selectedLineIndex();
                currentWidget.options.nbLines += 1;
                currentWidget.copyLine(selectedLine, {needAddValue: true});

            });
            this.element.on("click" + this.eventNamespace, ".dcpArray__content__toolCell__delete", function deleteLineEvent() {
                currentWidget.removeLine($(this).closest(".dcpArray__content__line").data("line"));
            });
            this.element.on("click" + this.eventNamespace, ".dcpArray__label", function () {
                var $contentElement = currentWidget.element.find(".dcpArray__content");
                if ($contentElement.hasClass("dcpArray__content--open")) {
                    // Hide array panel
                    currentWidget.element.find(".dcp__array__caret").addClass("fa-caret-right").removeClass("fa-caret-down");
                    $contentElement.removeClass("dcpArray__content--open").addClass("dcpArray__content--close");
                    $contentElement.slideUp();
                } else {
                    // Show array panel
                    currentWidget.element.find(".dcp__array__caret").removeClass("fa-caret-right").addClass("fa-caret-down");
                    $contentElement.addClass("dcpArray__content--open").removeClass("dcpArray__content--close");
                    $contentElement.slideDown();
                }
            });
        },

        /**
         * Redraw label with current count
         */
        redrawLabel: function wArrayRedrawLabel() {
            this.element.find(".dcpArray__label").html(
                $(Mustache.render(this._getTemplate("label"), _.extend(this.options, {
                    displayCount: (this.options.renderOptions.rowCountThreshold >= 0 && this.options.nbLines >= this.options.renderOptions.rowCountThreshold)
                }))).html()
            );
        },
        setLines: function wArraySetLines(lineNumber) {
            var currentLineNumber = this.options.nbLines;
            var i;
            if (lineNumber > currentLineNumber) {
                for (i = 0; i < (lineNumber - currentLineNumber); i += 1) {
                    this.addLine(currentLineNumber + i);
                }
            } else if (lineNumber < currentLineNumber) {
                for (i = 0; i < (currentLineNumber - lineNumber ); i += 1) {
                    this.removeLine(this.options.nbLines - 1);
                }
            }
        },

        addAllLines: function dcpArrayaddAllLines(lineNumber) {
            var i;
            this.element.find(".dcpArray__body").empty();

            for (i = 0; i < lineNumber; i += 1) {
                this.addLine(i);
            }
            this._trigger("linesGenerated");
        },

        _getLineContent: function (index) {
            var $content = "NULL LINE";
            if (this.options.customTemplate) {
                //$content=$("<tr><td>CUSOMLINE</td><td>CUSOMLINE</td><td>CUSOMLINE</td></tr>");
                $content = this.options.customLineCallback.apply(this, [index]);
                $content.addClass("dcpArray__content__line");
                $content.attr("data-attrid", this.options.id);
            } else {
                $content = $(Mustache.render(this._getTemplate("line"), _.extend({lineNumber: index}, this.options)));

            }
            return $content;
        },

        _addNewLine: function dcpArray_addNewLine(lineNumber) {
            if (!_.isNumber(lineNumber)) {
                throw new Error("You need to indicate the line number");
            }
            var $content = this._getLineContent(lineNumber);
            var selectedLine = this.getSelectedLineElement();
            if (selectedLine.length === 1) {
                $content.insertBefore(selectedLine);
            } else {
                this.element.find(".dcpArray__body").append($content);
            }
            this._indexLine();
            this.redrawLabel();
            return $content;
        },

        addLine: function dcpArrayaddLine(lineNumber, options) {
            var $content = this._addNewLine(lineNumber);
            options = _.defaults(options || {}, {"silent": false, "needAddValue": false});
            if (options.silent !== true) {
                this._trigger("lineAdded", {}, {
                    line: lineNumber,
                    element: $content,
                    needAddValue: options.needAddValue
                });
            }
        },

        copyLine: function dcpArraycopyLine(lineNumber) {
            var $content = this._addNewLine(lineNumber);
            this._trigger("lineAdded", {}, {line: lineNumber, element: $content, copyValue: true});
        },

        removeLine: function dcpArrayremoveLine(line, options) {
            options = options || {};
            this.element.find("[data-line=" + line + "]").remove();
            this._indexLine();
            if (options.silent !== true) {
                this._trigger("lineRemoved", {}, {line: line});
            }

            this.redrawLabel();
        },

        _destroy: function dcpArray_destroy() {
            var tbody = this.element.find('tbody');
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
            this.element.find(".dcpArray__content__line").each(
                function numeroteLine() {
                    $(this).attr("data-line", i).data("line", i);
                    i += 1;
                }
            );
            this.options.nbLines = i;
        },

        _unSelectLines: function dcpArray_unSelectLines() {
            this.element.find(".dcpArray__content__toolCell__check .fa-check").hide();
            this.element.find(".dcpArray__content__line--selected").removeClass("dcpArray__content__line--selected active");
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
            if (window.dcp && window.dcp.templates && window.dcp.templates[this.getType()] && window.dcp.templates[this.getType()][key]) {
                return window.dcp.templates[this.getType()][key];
            }
            if (window.dcp && window.dcp.templates && window.dcp.templates["default"] && window.dcp.templates["default"][key]) {
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

define([
    'underscore',
    'mustache',
    'widgets/widget'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpArray", {

        options: {
            tools: true,
            nbLines: 0
        },

        /**
         * get Selected line element (jquery, length = 0 if no selected)
         * @returns {*}
         */
        getSelectedLineElement: function () {
            return this.element.find('.dcpArray__content__line--selected.active');
        },
        /**
         * get Selected line index (0 : first, null : no selected line)
         * @returns {*}
         */
        selectedLineIndex: function () {
            return this.getSelectedLineElement().data("line");


        },
        _create: function () {
            this.options.tools = this.options.mode === "write" && this.options.visibility !== "U";
            this._initDom();
            this._bindEvents();
        },

        _initDom: function () {
            var scope = this;
            if (this.options.mode === "read" && this.options.nbLines === 0) {
                if (this.options.showEmpty) {
                    this.element.addClass("panel panel-default");
                    // showEmptyCOntent option
                    this.element.append(Mustache.render(this._getTemplate("label"), this.options));
                    this.element.append(this.options.showEmpty);
                }
            } else {
                this.element.addClass("panel panel-default");
                this.element.append(Mustache.render(this._getTemplate("label"), this.options));
                this.element.append(Mustache.render(this._getTemplate("content"), this.options));

                if (this.options.mode === "write") {
                    this.element.find('.dcpArray__tools button').kendoTooltip({
                        position: "top",
                        show: function (event) {
                            // need to shift to bottom because callout is in target
                            var contain = this.popup.element.parent();
                            var ktop = parseFloat(contain.css("top"));
                            if (ktop > 0) {
                                contain.css("top", ktop - 6);
                            }
                        }
                    });
                    this.element.kendoTooltip({
                        filter: ".dcpArray__content__toolCell span",
                        position: "top",
                        show: function (event) {
                            // need to shift to bottom because callout is in target
                            var contain = this.popup.element.parent();
                            var ktop = parseFloat(contain.css("top"));
                            if (ktop > 0) {
                                contain.css("top", ktop - 10);
                            }
                        }
                    });


                }
                this.addAllLines();
                this.element.find('tbody').kendoDraggable({
                    axis: "y",
                    container: scope.element.find('tbody'),
                    filter: '.dcpArray__content__toolCell__dragDrop',
                    hint: function (element) {
                        var dragLine = element.closest('tr');
                        var lineWidth = dragLine.width();
                        var classTable = element.closest('table').attr("class");
                        return $('<table/>').addClass("dcpArray__dragLine " + classTable).css("width", lineWidth).append(dragLine.clone());
                    },
                    dragstart: function (event) {
                        if (event.currentTarget) {
                            var dragLine = $(event.currentTarget).closest('tr');
                            dragLine.css("opacity", "0");
                            dragLine.data("fromLine", dragLine.data("line"));

                        }
                    },
                    dragend: function (event) {
                        if (event.currentTarget) {
                            var dragLine = $(event.currentTarget).closest('tr');
                            dragLine.css("opacity", "");

                            scope._trigger("lineMoved", {}, {fromLine: dragLine.data("fromLine"), toLine: dragLine.data("line")});
                        }
                    }
                });
                this.element.find('tbody').kendoDropTargetArea({
                    filter: '.dcpArray__content__line',
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
        },

        _bindEvents: function () {
            var currentWidget = this;
            this.element.on("click." + this.eventNamespace, ".dcpArray__content__toolCell__check", function () {
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
            this.element.on("click." + this.eventNamespace, ".dcpArray__add", function () {
                var sLine = currentWidget.selectedLineIndex();
                if (sLine === null) {
                    currentWidget.addLine(currentWidget.options.nbLines++, true);
                } else {
                    currentWidget.options.nbLines++;
                    currentWidget.addLine(sLine, true);
                    currentWidget._indexLine();
                }
            });
            this.element.on("click." + this.eventNamespace, ".dcpArray__copy", function () {
                var sLine = currentWidget.selectedLineIndex();

                currentWidget.options.nbLines++;
                currentWidget.copyLine(sLine, true);
                currentWidget._indexLine();

            });
            this.element.on("click." + this.eventNamespace, ".dcpArray__content__toolCell__delete", function () {
                currentWidget.removeLine($(this).closest(".dcpArray__content__line").data("line"));
            });
        },


        addAllLines: function () {
            var i;
            this.element.find(".dcpArray__body").empty();
            for (i = 0; i < this.options.nbLines; i++) {
                this.addLine(i);
            }
            this._trigger("linesGenerated");
        },

        _addNewLine: function (lineNumber) {
            if (!_.isNumber(lineNumber)) {
                throw new Error("You need to indicate the line number");
            }
            var $content = $(Mustache.render(this._getTemplate("line"), _.extend({lineNumber: lineNumber}, this.options)));
            var selectedLine = this.getSelectedLineElement();
            if (selectedLine.length === 1) {
                $content.insertBefore(selectedLine);
            } else {
                this.element.find(".dcpArray__body").append($content);
            }
            return $content;
        },

        addLine: function (lineNumber, needAddValue) {
            var $content = this._addNewLine(lineNumber);

            this._trigger("lineAdded", {}, {line: lineNumber, element: $content, needAddValue: needAddValue});
        },

        copyLine: function (lineNumber) {

            var $content = this._addNewLine(lineNumber);
            this._trigger("lineAdded", {}, {line: lineNumber, element: $content, copyValue: true});
        },

        removeLine: function (line) {
            this.element.find("[data-line=" + line + "]").remove();
            this._indexLine();
            this._trigger("lineRemoved", {}, {line: line});
        },

        _destroy: function () {
            this.element.empty();
            this._super();
        },

        _indexLine: function () {
            var i = 0;
            this.element.find(".dcpArray__content__line").each(
                function () {
                    $(this).attr("data-line", i);
                    $(this).data("line", i);
                    i++;
                }
            );
            this.options.nbLines = i;
        },

        _unSelectLines: function () {
            this.element.find(".dcpArray__content__toolCell__check .fa-check").hide();
            this.element.find(".dcpArray__content__line--selected").removeClass("dcpArray__content__line--selected active");
        },

        _getTemplate: function (key) {
            if (this.options.templates[key]) {
                return this.options.templates[key];
            }

            throw new Error("Unknown template  " + key + "/" + this.getType());

        }
    });
});
define([
    'underscore',
    'mustache',
    'widgets/widget'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpArray", {

        options : {
            tools : true,
            nbLine : 0
        },

        _create : function () {
            this.options.tools = this.options.mode === "write";
            this._initDom();
            this._bindEvents();
        },

        _initDom : function () {
            this.element.addClass("panel panel-default");
            this.element.append(Mustache.render(this._getTemplate("label"), this.options));
            this.element.append(Mustache.render(this._getTemplate("content"), this.options));
            this.addAllLines();
        },

        _bindEvents : function() {
            var currentWidget = this;
            this.element.on("click", ".dcpArray__content__toolCell__check", function() {
                var $this = $(this);
                currentWidget._unSelectLines();
                $this.find('.fa-check').show();
                $this.closest(".dcpArray__content__line").addClass("dcpArray__content__line--selected active");
            });
            this.element.on("click", ".dcpArray__add", function() {
                currentWidget.addLine(currentWidget.options.nbLine++);
            });
            this.element.on("click", ".dcpArray__content__toolCell__delete", function () {
                currentWidget.removeLine($(this).closest(".dcpArray__content__line").data("line"));
            });
        },

        addAllLines : function() {
            var i;
            this.element.find(".dcpArray__body").empty();
            for (i = 0; i < this.options.nbLines; i++) {
                this.addLine(i);
            }
            this.options.nbLine = i;
            this._trigger("linesGenerated");
        },

        addLine : function(lineNumber) {
            if (!_.isNumber(lineNumber)) {
                throw new Error("You need to indicate the nbLine");
            }
            var $content = $(Mustache.render(this._getTemplate("line"), _.extend({lineNumber : lineNumber}, this.options)));
            this.element.find(".dcpArray__body").append($content);
            this._trigger("lineAdded", {}, {line : lineNumber, element : $content});
        },

        removeLine : function(line) {
            this.element.find("[data-line="+line+"]").remove();
            this._indexLine();
            this._trigger("lineRemoved", {}, {line : line});
        },

        _indexLine : function() {
            var i = 0;
            this.element.find(".dcpArray__content__line").each(
                function() {
                    $(this).attr("data-line", i);
                    i++;
                }
            );
            this.options.nbLine = i;
        },

        _unSelectLines : function() {
            this.element.find(".dcpArray__content__toolCell__check .fa-check").hide();
            this.element.find(".dcpArray__content__line--selected").removeClass("dcpArray__content__line--selected active");
        },

        _getTemplate : function (name) {
            if (window.dcp && window.dcp.templates && window.dcp.templates.attribute
                && window.dcp.templates.attribute.array && window.dcp.templates.attribute.array[name]) {
                return window.dcp.templates.attribute.array[name];
            }
            throw new Error("Unknown array template "+name);
        }
    });
});
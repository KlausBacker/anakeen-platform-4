$(document).ready(function ()
{
    "use strict";

    $.widget("custom.combobox", {
        _create: function ()
        {
            this.wrapper = $("<span>")
                .addClass("custom-combobox")
                .insertAfter(this.element);

            this.element.hide();
            this._createAutocomplete();
            this._createShowAllButton();
        },

        _createAutocomplete: function ()
        {
            var selected = this.element.children(":selected"),
                value = selected.val() ? selected.text() : "",
                options = this.options;


            this.input = $("<input>")
                .appendTo(this.wrapper)
                .val(value)
                .attr("title", "")
                .attr("placeholder", this.element.attr("placeholder"))
                .addClass("custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left")
                .autocomplete({
                    delay: 0,
                    minLength: 0,
                    source: this.options.source
                })
                .tooltip({
                    classes: {
                        "ui-tooltip": "ui-state-highlight"
                    }
                });

            this._on(this.input, {
                autocompleteselect: function (event, ui)
                {
                    $(this.element).val(ui.item.value);
                    $(this.input).val(ui.item.label);
                    if (options.action) {
                        options.action.apply(this, [event, ui]);
                    }
                    return false;
                },
                autocompletechange: "_removeIfInvalid"
            });


        },

        _createShowAllButton: function ()
        {
            var input = this.input,
                wasOpen = false;

            $("<a>")
                .attr("tabIndex", -1)
                .attr("title", "Show All Items")
                .tooltip()
                .appendTo(this.wrapper)
                .button({
                    icons: {
                        primary: "ui-icon-triangle-1-s"
                    },
                    text: false
                })
                .removeClass("ui-corner-all")
                .addClass("custom-combobox-toggle ui-corner-right")
                .on("mousedown", function ()
                {
                    wasOpen = input.autocomplete("widget").is(":visible");
                })
                .on("click", function ()
                {
                    input.trigger("focus");

                    // Close if already visible
                    if (wasOpen) {
                        return;
                    }

                    // Pass empty string as value to search for, displaying all results
                    input.autocomplete("search", "");
                });
        },


        _removeIfInvalid: function (event, ui)
        {

            // Selected an item, nothing to do
            if (ui.item) {
                return;
            }

            // Search for a match (case-insensitive)
            var value = this.input.val(),
                valueLowerCase = value.toLowerCase(),
                valid = false;
            this.element.children("option").each(function ()
            {
                if ($(this).text().toLowerCase() === valueLowerCase) {
                    this.selected = valid = true;
                    return false;
                }
            });

            // Found a match, nothing to do
            if (valid) {
                return;
            }

            // Remove invalid value
            this.input
                .val("")
                .attr("title", value + " didn't match any item")
                .tooltip("open");
            this.element.val("");
            this._delay(function ()
            {
                this.input.tooltip("close").attr("title", "");
            }, 2500);
            this.input.autocomplete("instance").term = "";
        },

        _destroy: function ()
        {
            this.wrapper.remove();
            this.element.show();
        },


        disable: function ()
        {
            this.input.autocomplete("disable");
            this.input.prop("disabled", true);
            this.wrapper.find("a.ui-button").button("disable");

        },

        enable: function ()
        {
            this.input.autocomplete("enable");
            this.input.prop("disabled", false);
            this.wrapper.find("a.ui-button").button("enable");
        },
        focus: function ()
        {
            this.input.focus();
        },
        placeholder: function (text)
        {
            this.input.attr("placeholder", text);
        },

        text: function (label) {
            this.input.val(label);
        },

        _setOption: function (key, value)
        {

            if (key === "source") {
                this.input.autocomplete("option", "source", value);
            }
            this._super(key, value);
        },
        hide: function  () {
            this.input.hide();
        },
        show: function  () {
            this.input.show();
        }
    });
});
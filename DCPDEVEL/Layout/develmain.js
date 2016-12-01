$(document).ready(function ()
{
    "use strict";
    $('.develmain').dataTable({

        "dom": '<"ui-state-default attributesHeader"p>fr',
        "paging": false,
        "ordering": false,
        "autoWidth": false,
        "heigth": "200px",
        "language": {
            "search": ""
        }
    });


    $('.family-filter').append($(".dataTables_filter"));
    $(".dataTables_filter input").attr("placeholder", "Filter Families");

    function resizeScroll()
    {
        var $scrollDiv = $('.scrolldiv');
        var h = $(window).height() - $scrollDiv.offset().top;
        $scrollDiv.height(h);


        var $iframe = $('iframe.config-result');
        if ($iframe.length === 1) {
            h = $(window).height() - $iframe.offset().top - 40;
            //$iframe.width($(".devel-left").width() - 40);
            $(".config-result").dialog("option", {width:$(".devel-left").width() - 40});
            $iframe.css("width","");
            $iframe.height(h);
        }
    }

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

                    var href = options.action;
                    href = href.replace('{{docid}}', ui.item.value);

                    $(".reload-anchor").attr("href", href).trigger("click");
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
        }
    });

    $(window).on("resize", function ()
    {
        resizeScroll();
    });
    resizeScroll();

    $(".family-anchor").on("click", function ()
    {
        var family = $(this).data("familyid");
        $(".family-anchor").removeClass("selected");
        $(this).addClass("selected");
        $(".ui-dialog").remove();

        $.get("?app=DCPDEVEL&action=FAMILYCONF&family=" + family).done(function (htmlResponse)
        {
            var $openIcon, $closeIcon;
            $(".devel-left ").html(htmlResponse);

            $(".barmenu").menu({ position: { my: "left top", at: "right-100 top+30" } }).hide();
            $(".changeIcon").button().on("click", function ()
            {
                $(".form-icon input[type=submit]").button().prop("disabled", true).button("disable");
                $(".dialog--icon").dialog({});

                $(".dialog--icon input[type=file]").button().on("change", function ()
                {
                    var file = this.files[0];
                    var reader = new FileReader();

                    reader.onloadend = function ()
                    {
                        $(".bigicon").attr("src", reader.result);
                        $(".form-icon input[type=submit]").prop("enable", true).button("enable");
                    };

                    if (file) {
                        reader.readAsDataURL(file);
                    } else {
                        preview.src = "";
                    }
                });
                $('.form-icon').on('submit', function (e)
                {
                    // On empêche le navigateur de soumettre le formulaire
                    e.preventDefault();

                    var $form = $(this);
                    var formdata = (window.FormData) ? new FormData($form[0]) : null;
                    var data = (formdata !== null) ? formdata : $form.serialize();

                    $.ajax({
                        url: $form.attr('action'),
                        type: $form.attr('method'),
                        contentType: false, // obligatoire pour de l'upload
                        processData: false, // obligatoire pour de l'upload
                        dataType: 'json', // selon le retour attendu
                        data: data
                    }).done(function ()
                    {
                        $(".dialog--icon").dialog("destroy");
                        $(".family-anchor.selected").trigger("click");
                    }).fail(function (response)
                    {
                        alert(response);
                    });
                });

            });
            $(".ui-menu-icon.ui-icon-caret-1-e").addClass("ui-icon-caret-1-s").removeClass("ui-icon-caret-1-e");
            $(".config-result").dialog({resizable: false}).dialog("close");
            $(".config-result").on("load", function () {
                try {
                    var title=$(this.contentDocument).find("title").text();
                    $(this).dialog("option", "title", title);
                } catch (e) {

                }
            });
            $closeIcon=$(".ui-dialog-titlebar-close");
            $openIcon=$closeIcon.clone().addClass("ui-openwindow").attr("title","Open in new window");
            $openIcon.find(".ui-icon-closethick").removeClass("ui-icon-closethick").addClass("ui-icon-arrowthick-1-ne");
            $openIcon.insertBefore($closeIcon);
            $openIcon.on("click", function () {
                var $iframe=$(".config-result");
                window.open($iframe.get(0).contentWindow.location.href,"_blank");
                $iframe.dialog("close");
            });


            $(".home a").button().not("[data-reload]").on("click", function ()
            {
                $(".config-result").dialog("option", {
                    width: $(".devel-left").width() - 40,
                    position: {
                        my: "left+20 top",
                        at: "left bottom",
                        of: $("header.header")
                    }
                }).dialog("open").dialog("option", {height:300});
                setTimeout(resizeScroll, 100);

            });

            $(".abstract").on("click", function refresh()
            {
                $(".family-anchor.selected").trigger("click");
            });


            $("a[data-reload]").on("click", function (event)
            {
                $.get($(this).attr("href")).done(function ()
                {


                    $(".family-anchor.selected").trigger("click");
                }).fail(function (response)
                {
                    alert("Error" + $(this).attr("href"));
                    console.error(response);
                });

                event.preventDefault();
            });


            $(".cv-fams").selectmenu().on("selectmenucreate", function (event, element)
            {

            }).on("selectmenuchange", function (event, element)
            {
                var $cvCreate = $(".cv-create");
                $cvCreate.data("cv", $(this).val());

            }).selectmenu("widget").addClass("nolabel");
            $(".cv-create").on("click", function ()
            {
                var url="?app=DCPDEVEL&action=MODIFYFAMILY&type=newCvid&value={{cvfamily}}&famid={{family}}";
                url = url.replace("{{family}}", family);
                url = url.replace("{{cvfamily}}", $(this).data("cv"));


                $.getJSON(url).done(function () {
                    $(".family-anchor.selected").trigger("click");
                }).fail(function (response) {
                    $('<div/>').html(response.responseText).dialog();
                });
            });
            $(".cv-idgroup").controlgroup();


            $(".w-fams").selectmenu().on("selectmenucreate", function (event, element)
            {

            }).on("selectmenuchange", function (event, element)
            {
                var $wCreate = $(".w-create");
                var wfam= $(this).val();
                if (wfam) {
                    $wCreate.data("wfam", wfam);
                    $wCreate.button("enable");
                } else {
                    $wCreate.button("disable");
                }

            }).selectmenu("widget").addClass("nolabel");
            $(".w-create").on("click", function ()
            {
                var url="?app=DCPDEVEL&action=MODIFYFAMILY&type=newWid&value={{wfamily}}&famid={{family}}";
                url = url.replace("{{family}}", family);
                url = url.replace("{{wfamily}}", $(this).data("wfam"));


                $.getJSON(url).done(function () {
                    $(".family-anchor.selected").trigger("click");
                }).fail(function (response) {
                    $('<div/>').html(response.responseText).dialog();
                });
            }).button("disable");
            $(".wid-idgroup").controlgroup();


            $(".profil--label").each(function ()
            {
                var wAction = "?app=DCPDEVEL&action=MODIFYFAMILY&type={{type}}&value={{docid}}&famid={{family}}";
                var wSource = "?app=DCPDEVEL&action=SEARCHSYSDOC&type={{type}}&famid={{family}}";
                wAction = wAction.replace("{{family}}", family);
                wAction = wAction.replace("{{type}}", $(this).data("type"));
                wSource = wSource.replace("{{family}}", family);
                wSource = wSource.replace("{{type}}", $(this).data("type"));


                $(this).combobox({

                    source: wSource,
                    action: wAction
                });
            });


            setTimeout(resizeScroll, 500);
        });
    });

});
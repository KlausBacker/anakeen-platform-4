$(document).ready(function ()
{
    "use strict";

    var $listExport = $(".list-export");
    $(".list-export a, .logical-name input").button();

    $listExport.on("change", ".logical-name input", function ()
    {
        var $this = $(this);
        var newLogicalName = $this.val();
        var docid = $(this).data("docid");
        var url = "?app=DCPDEVEL&action=SETLOGICALNAME&id=" + docid + "&name=" + encodeURI(newLogicalName || ":initial:");
        var $message = $this.closest("tr").find(".message");
        console.log("LN", newLogicalName);
        $("input[type=submit]").prop("disabled", true).button("disable");

        $.getJSON(url).done(function (response)
        {
            $message.text(response.message);
            $this.val(response.logicalName);
            $this.removeClass("error");
            $this.addClass("updated");

            verifyEmptyLn();
        }).fail(function (response)
        {
            try {
                var msg = JSON.parse(response.responseText);
                $message.text(msg.error);
            } catch (e) {
                $message.html(response.responseText);
            }
            $this.addClass("error").removeClass("updated");
            console.error(response);
        });
    });

    if ($(".list-export tbody tr").length === 0) {
        $listExport.hide();
    }

    $("input[type=submit]").button();

    function verifyEmptyLn()
    {
        var $inputs = $(".logical-name input");
        var empty = false;

        $(".list-export .auto").remove();

        $inputs.each(function ()
        {
            var $button;
            var $input = $(this);
            if (!$input.val()) {
                if (!$input.closest("tr").hasClass("other--deleted")) {
                    empty = true;
                }
                $input.addClass("empty");
                $button = $("<button />").addClass("auto").button({ icon: " ui-icon-lightbulb" }).on("click", function ()
                {
                    $input.val(":auto:").trigger("change");

                });
                $button.insertAfter($input);
            } else {
                $input.removeClass("empty");
            }
            $(".other-delete").not(".ui-button").button({ icon: " ui-icon-trash" }).on("click", function ()
            {
                var famid = $("meta[name=family]").attr("content");
                var docid = $(this).data("docid");
                var $tr = $(this).closest("tr");
                var method = $tr.hasClass("other--deleted") ? "ADD" : "DELETE";

                $tr.removeClass("other--deleted").addClass("other--deleted--processing");
                $.getJSON("?app=DCPDEVEL&action=OTHERDOCUMENT&method=" + method + "&famid=" + famid + "&docid=" + docid).done(function ()
                {
                    $tr.removeClass("other--deleted--processing");
                    if (method === "DELETE") {
                        $tr.addClass("other--deleted");
                        $tr.find(".ui-icon-trash").removeClass("ui-icon-trash").addClass("ui-icon-refresh");
                    } else {
                        $tr.removeClass("other--deleted");
                        $tr.find(".ui-icon-refresh").removeClass("ui-icon-refresh").addClass("ui-icon-trash");
                    }

                    verifyEmptyLn();
                }).fail(function (response)
                {
                    $("<div/>").html(response.responseText).dialog();
                    console.error(response);

                });

            });
        });

        if (empty) {
            $("input[type=submit]").prop("disabled", true).button("disable");
            $(".warning").show();
        } else {
            $("input[type=submit]").prop("disabled", false).button("enable");
            $(".warning").hide();

        }

    }

    $("select").selectmenu();
    verifyEmptyLn();

    $(".family--other").combobox({

        source: "?app=DCPDEVEL&action=SEARCHSYSDOC&type=families&famid=1",
        action: function (event, ui)
        {
            var $familyList = $(".family--list");
            var chooseText=this.element.data("choosetext");
            console.log("actions is", ui);
            $familyList.combobox("option", "source", "?app=DCPDEVEL&action=SEARCHSYSDOC&type=documents&famid=" + ui.item.value);
            $familyList.data("family", ui.item.value);

            $familyList.combobox("enable").combobox("show");
            $familyList.combobox("placeholder", chooseText.replace('{{family}}', ui.item.label));
            window.setTimeout(function () {
                $familyList.combobox("focus");
            }, 100);


        }
    });

    $(".family--list").combobox({

        source: null,

        action: function (event, ui)
        {
            var docid = ui.item.value;
            var famid = $("meta[name=family]").attr("content");
            var widget = this;

            this.text("Processing...");
            this.disable();
            $.getJSON("?app=DCPDEVEL&action=OTHERDOCUMENT&method=ADD&famid=" + famid + "&docid=" + docid).done(function (data)
            {
                var $tpl = $('script.othertpl'), $tr;
                var tplContent = $tpl.html();
                tplContent = tplContent.replace('{{icon}}', data.document.icon);
                tplContent = tplContent.replace('{{famLabel}}', data.family.title);
                tplContent = tplContent.replace('{{label}}', data.document.title);
                tplContent = tplContent.replace(/\{\{docid\}\}/g, data.document.id);
                tplContent = tplContent.replace('{{name}}', data.document.name || '');

                $tr = $(tplContent);


                $tr.find("a, .logical-name input").button();
                $(".other-documents").append($tr);

                widget.text("");
                widget.enable();
                verifyEmptyLn();
            }).fail(function (response)
            {
                $("<div/>").html(response.responseText).dialog();
                console.error(response);

                widget.text("");
                widget.enable();
            });
        }
    }).combobox("disable").combobox("hide");

});
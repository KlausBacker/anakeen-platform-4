$(document).ready(function ()
{
    "use strict";

    $(".list-export a").button();
    $(".logical-name input").button().on("change", function () {
        var $this=$(this);
        var newLogicalName=$this.val();
        var docid=$(this).data("docid");
        var url="?app=DCPDEVEL&action=SETLOGICALNAME&id="+docid+"&name="+encodeURI(newLogicalName||":initial:");
        var $message=$this.closest("tr").find(".message");
        console.log("LN", newLogicalName);

        $.getJSON(url).done(function (response) {
            $message.text(response.message);
            $this.val(response.logicalName);
            $this.removeClass("error");
            $this.addClass("updated");

            verifyEmptyLn();
        }).fail(function (response) {
            try {
                var msg=JSON.parse(response.responseText);
                $message.text(msg.error);
            } catch (e) {
                $message.html(response.responseText);
            }
            $this.addClass("error").removeClass("updated");
            console.error(response);
        })
    });

    if ($(".list-export tbody tr").length === 0) {
        $(".list-export").hide();
    }

    $("input[type=submit]").button();

    function verifyEmptyLn() {
        var $inputs=$(".logical-name input");
        var empty=false;

        $(".list-export .auto").remove();

        $inputs.each(function () {
            var $button;
            var $input=$(this);
            if (!$input.val()) {
                empty=true;
                $input.addClass("empty");
                $button=$("<button />").addClass("auto").button({icon:" ui-icon-lightbulb"}).on("click", function () {
                    $input.val(":auto:").trigger("change");

                });
                $button.insertAfter($input);
            } else {
                $input.removeClass("empty");
            }
        });

        if (empty) {
            $("input[type=submit]").prop("disabled",true).button("disable");
            $(".warning").show();
        } else {
            $("input[type=submit]").prop("disabled",false).button("enable");
            $(".warning").hide();

        }

    }
    $("select").selectmenu();
    verifyEmptyLn();
});
$(document).ready(function ()
{
    "use strict";

    var $resultzone = $(".resultzone");
    $(".searchzone input").button();
    var $form = $(".searchform");
    var $inputs = $(".searchform input");

    $(".searching").dialog({ title: null, resizable: false, autoOpen: true });
    $(".ui-dialog-titlebar").hide();

    $resultzone.on("refresh", function (event, options)
    {

        var data = {};
        var template = $("#zoneItem").html();

        if (!$form.data("originalAction")) {
            $form.data("originalAction", $form.attr("action"));
        }
        data = {};
        $inputs.each(function (index, input)
        {

            if ($(input).val() && !$(input).attr("readonly")) {
                data[$(input).attr("name")] = $(input).val();
            }
        });


        $(".ui-dialog").show();


        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            dataType: 'json', // selon le retour attendu
            data: data
        }).done(function (data)
        {
            //  $waiting.dialog("close");
            $(".ui-dialog").hide();
            var content = Mustache.render(template, data);

            if (options && options.append) {
                $resultzone.append(content);
            } else {
                $resultzone.html(content);
            }
            $resultzone.find("button").button();


        }).fail(function (response)
        {
            //$waiting.dialog("close");

            $(".ui-dialog").hide();
            $resultzone.text(response.contentText);
            console.error(response);
        });
    });

    $resultzone.trigger("refresh");

    $resultzone.on("click", ".document-button", function ()
    {
        var url = "?app=FDL&action=FDL_CARD&id=" + $(this).data("docid");
        window.open(url, "_blank");
    });
    $resultzone.on("click", ".document-next", function ()
    {
        var url = $(this).data("url");
        $form.attr("action", url);
        $(this).hide();
        $resultzone.trigger("refresh", { append: true });
    });

    $inputs.on("focus", function ()
    {
        $inputs.attr("readonly", "readonly");
        $inputs.addClass("search-disabled");
        $(this).removeClass("search-disabled");
        $(this).removeAttr("readonly");
        if ($(this).val()) {
            $resultzone.trigger("refresh");
        }

    });
    $inputs.on("keyup change", function ()
    {
        $resultzone.trigger("refresh");
    });
});
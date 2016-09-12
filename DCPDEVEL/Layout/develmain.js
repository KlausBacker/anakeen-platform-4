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
            h = $(window).height() - $iframe.offset().top - 4;
            $iframe.height(h);
        }
    }

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

        $.get("?app=DCPDEVEL&action=FAMILYCONF&family=" + family).done(function (htmlResponse)
        {
            $(".devel-left ").html(htmlResponse);

            $(".barmenu").menu({ position: { my: "left top", at: "right-100 top+20" } }).hide();
            $(".changeIcon").button();
            $(".ui-menu-icon.ui-icon-caret-1-e").addClass("ui-icon-caret-1-s").removeClass("ui-icon-caret-1-e");

            $(".home a").button().not("[data-reload]").on("click", function ()
            {

                $(".barmenu").show();
                $(".home").hide();
                $(".config-result").show();
                setTimeout(resizeScroll, 100);

            });

            $(".abstract").on("click", function refresh()
            {
                $(".family-anchor.selected").trigger("click");
            });


            $("a[data-reload]").on("click", function (event)
            {
                $.get($(this).attr("href")).done(function (htmlResponse)
                {
                    var $div=$("<div/>").html(htmlResponse);
                    var $error=$div.find(".error");

                    if ($error.length) {
                        alert($error.text());
                    }

                    $(".family-anchor.selected").trigger("click");
                }).fail(function (response)
                {
                    alert("Error" + $(this).attr("href"));
                    console.error(response);
                });

                event.preventDefault();
            });


            setTimeout(resizeScroll, 500);
        });
    });
});
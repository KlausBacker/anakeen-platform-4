$(function() {
    $(".nav-link").on("click", function (event) {
        var target=$(this).data("targetId");
        $(".nav-item").removeClass("active");
        $(this).closest(".nav-item").addClass("active");
        if (target) {
            $(".control-container").hide().removeClass("active");
            $('#'+target).show().addClass("active");
        }
    });
    
    $(".php-info table").addClass("table  table-hover");
    $(".nav-item.active a").trigger("click");
});
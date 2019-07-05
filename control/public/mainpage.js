$(function ()
{
    $('.nav-link').on('click', function (event)
    {
        var target = $(this).data('targetId')
        $('.nav-item').removeClass('active')
        $(this).closest('.nav-item').addClass('active')
        if (target) {
            $('.control-container').hide().removeClass('active')
            $('#' + target).show().addClass('active')
        }
    })
    
    $('.php-info table').addClass('table  table-hover')
    
    var $atab
    if (window.location.hash) {
        $atab = $('.nav-item a[data-target-id=' + window.location.hash.substr(1) + ']')
    }
    if (!$atab || $atab.length === 0) {
        $atab = $('.nav-item.active a')
    }
    $atab.trigger('click')
    
})
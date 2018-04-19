/*global define*/


const _ = require('underscore');
export default (searchId, $target) =>
{
    var $r = $.Deferred();


    $target.find(".search-grid").remove();
    $target.removeClass("result--on");
    var $table = $('<table class="search-grid table table-stripped table-condensed table-bordered" />');
    $target.append($table);
    $.getJSON("api/v1/search_UI_HTML5/searchAttributes/" + searchId).done(function displaySearchGrid(response) {

        $table.docGrid({
            collection: searchId,
            columnsDef: {
                "defaultFam": response.data.config.family,
                "columns": response.data.attributes
            },
            temporarySearch: "api/v1/documentGrid/content/" + searchId,
            filterable: false,
            dataTableOptions: {
                paging: true,
                pageLength: response.data.config.paging,
                autoWidth: false,
                scrollCollapse: false,
                ordering: true
            }
        });
        $table.on('init.dt', function (event, dt) {
            $r.resolve(dt);
        });
        $table.on('error.dt', function (event, settings, techNote, message) {
            $r.reject(message, dt);
        });
        $table.on('draw.dt', function (event, dt) {
            if (response.data.footer && response.data.footer.length > 0 && $table.find("tfoot").length === 0) {
                var $tr = $("<tr/>").append($("<td />"));
                var $theadCells = $table.find("thead th");
                _.each(response.data.footer, function (footItem, footIndex) {
                    $tr.append($("<td />").addClass($($theadCells[footIndex + 1]).attr("class")).html(footItem));
                });

                $table.append($("<tfoot/>").append($tr));
            }

            if (dt._iRecordsTotal <= dt._iDisplayLength) {

                $(dt.nTableWrapper).find(".dataTables_paginate").hide();
            } else {
                $(dt.nTableWrapper).find(".dataTables_paginate").show();
            }
            $target.removeClass("result--waiting").addClass("result--on");

            $table.stickyTableHeaders({
                fixedOffset: $(".dcpDocument__menu")
            });
            $(".dcpTab__content.result--content").on("scroll", function () {
                $table.data("plugin_stickyTableHeaders").toggleHeaders()
            });
        });
    });


    return $r;
}
;
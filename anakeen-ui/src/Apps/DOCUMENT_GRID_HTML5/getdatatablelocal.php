<?php
/**
 * Return the dataTable translation
 *
 * @param \Anakeen\Core\Internal\Action $action
 */
function getdatatablelocal(\Anakeen\Core\Internal\Action &$action) {

    $element=$action->getArgument("element");
    $action->lay->template = sprintf('{
            "sProcessing":     "%s",
            "sLengthMenu":     "%s",
            "sZeroRecords":    "%s",
            "sInfo":           "%s",
            "sInfoEmpty":      "%s",
            "sInfoFiltered":   "%s",
            "sInfoPostFix":    "%s",
            "sSearch":         "%s:",
            "sLoadingRecords": "%s",
            "oPaginate": {
                "sFirst":    "%s",
                "sPrevious": "%s",
                "sNext":     "%s",
                "sLast":     "%s"
            }
        }'
        , _("DOCGRIDHTML5: sProcessing")
        , _("DOCGRIDHTML5: sLengthMenu")
        , _("DOCGRIDHTML5: sZeroRecords")
        , _("DOCGRIDHTML5: sInfo")
        , _("DOCGRIDHTML5: sInfoEmpty")
        , _("DOCGRIDHTML5: sInfoFiltered")
        , _("DOCGRIDHTML5: sInfoPostFix")
        , _("DOCGRIDHTML5: sSearch")
        , _("DOCGRIDHTML5: sLoadingRecords")
        , _("DOCGRIDHTML5: sFirst")
        , _("DOCGRIDHTML5: sPrevious")
        , _("DOCGRIDHTML5: sNext")
        , _("DOCGRIDHTML5: sLast")
    );

    if ($element) {
        $action->lay->template=str_replace("élément", $element, $action->lay->template);
    }
    $action->lay->noparse = true;

}


?>
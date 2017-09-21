$(document).ready(function ()
{
    'use strict';


    var $select = $("select.family-select");
    var $newAttribute = $("<a/>").text("[TEXT:fva:New attribute]").addClass("new-attribute");
    var dt, $dt = $('table.attributes'), $header;

    $('table.attributes thead tr.filters th').each(function ()
    {
        $(this).html('<input class="search"  placeholder="' + "[TEXT:fva:Filter]" + '" />');
    });

    $dt.dataTable({

            "dom": '<"ui-state-default attributesHeader"p>r',
            "paging": false,
            "ordering": false,
            "autoWidth": true,
            "language": {
                "search": ""
            }

        }
    );
    dt = $dt.DataTable();
    // Apply the search
    dt.columns().every(function ()
    {
        var that = this;

        $('input', this.header()).on('keyup change', function ()
        {
            if (that.search() !== this.value) {
                that
                    .search(this.value)
                    .draw();
            }
        });
    });

    $select.on("change", function ()
    {
        var href = '?app=DOCADMIN&action=FAMILY_VIEWATTRIBUTES&id=';
        window.location.href = href + $(this).val();
    });
    $header=$(".attributesHeader");
    $header.prepend($(".header"));
    if ($("#tplEdit").length === 1) {
        $header.append($newAttribute);
    }

    $(".attributesHeader a").button();
    $newAttribute.button("option", "icons", {primary: "ui-icon-circle-plus"});
    $("tr.attribute").on("click", function ()
    {
        var aid = $(this).data("attrid");
        var famid = $(this).data("famid");
        var famName = $select.find("option[value=" + famid + "]").text();
        var dialogModal = $("#dialogModal");
        var data = {"id": aid, attrs: [], "famid": famid, "famname": famName};
        var content;
        $(this).find("td").each(function (index)
        {
            data[this.className] = {
                value: $(this).text(),
                label: $($(this).closest("table").find("thead th").get(index)).text()
            };
            if (index === 0) {
                data.attrs.push({
                    id: "id",
                    value: aid,
                    label: $($(this).closest("table").find("thead th").get(index)).text()
                });
            } else {
                data.attrs.push({
                    id: this.className,
                    value: $(this).text(),
                    label: $($(this).closest("table").find("thead th").get(index)).text()
                });
            }
        });
        content = Mustache.render($("#tplView").text(), data);
        dialogModal.html(content)
            .dialog({
                modal: false,
                width: 'auto',
                title: aid,
                buttons: [
                    {
                        "text": "[TEXT:fva:Modify]",
                        "class": "dialogModal-modify",
                        "click": function ()
                        {
                            $(this).dialog("close");

                            content = Mustache.render($("#tplEdit").text(), data);
                            dialogModal.html(content)
                                .dialog({
                                    modal: true,
                                    width: 'auto',
                                    title: aid,

                                    buttons: [
                                        {
                                            "text": "[TEXT:fva:Record]",
                                            "class": "record",
                                            "click": function ()
                                            {
                                                var $form = $("#formModAttribute");
                                                $.ajax({
                                                    type: "POST",
                                                    url: $form.attr("action"),
                                                    data: $form.serialize() // serializes the form's elements.
                                                }).fail(function (response)
                                                {
                                                    alert(response.responseText);
                                                }).done(function ()
                                                {
                                                    dialogModal.dialog("close");
                                                    window.location.href = window.location.href;
                                                });
                                            }
                                        },
                                        {
                                            text: "[TEXT:fva:Cancel]",
                                            "class": "cancel",
                                            click: function ()
                                            {
                                                $(this).dialog("close");
                                            }
                                        }
                                    ],

                                    close: function ()
                                    {
                                        $(this).dialog("destroy");
                                    }
                                });

                        }
                    }, {
                        "text": "[TEXT:fva:Close]",
                        "click": function ()
                        {
                            $(this).dialog("close");
                        }
                    }],
                close: function ()
                {
                    $(this).dialog("destroy");
                }
            });
        if (!$(this).hasClass("direct") || $(this).hasClass("parent") || $("#tplEdit").length === 0) {
            // hide modify button if not direct attribute
            $(".dialogModal-modify").hide();
        }
    });

    $newAttribute.on("click", function ()
    {
        var content;
        var famid = $select.val();
        var famName = $select.find('option:selected').text();
        var data = {attrs: [], "famid": famid, "famname": famName};
        var dialogModal = $("#dialogModal");
        $($("table.attributes tbody tr").get(0)).find("td").each(function (index)
        {
            data[this.className] = {
                value: $(this).text(),
                label: $($(this).closest("table").find("thead th").get(index)).text()
            };
            if (index === 0) {
                data.attrs.push({
                    id: "id",
                    value: '',
                    label: $($(this).closest("table").find("thead th").get(index)).text()
                });
            } else {
                data.attrs.push({
                    id: this.className,
                    value: '',
                    label: $($(this).closest("table").find("thead th").get(index)).text()
                });
            }
        });
        data.pathId.value = "[TEXT:fva:Enter new attribute definition]";

        content = Mustache.render($("#tplEdit").text(), data);
        dialogModal.html(content)
            .dialog({
                modal: true,
                width: 'auto',
                title: "[TEXT:Add new attribute]",

                buttons: [
                    {
                        "text": "[TEXT:fva:Create]",
                        "class": "record",
                        "click": function ()
                        {
                            var $form = $("#formModAttribute");
                            $.ajax({
                                type: "POST",
                                url: $form.attr("action"),
                                data: $form.serialize() // serializes the form's elements.

                            }).fail(function (response)
                            {
                                console.log("response", response);
                                alert(response.responseText);
                            }).done(function ()
                            {
                                dialogModal.dialog("close");
                                window.location.href = window.location.href;
                            });
                        }
                    },
                    {
                        text: "[TEXT:fva:Cancel]",
                        "class": "cancel",
                        click: function ()
                        {
                            $(this).dialog("close");
                        }
                    }
                ],

                close: function ()
                {
                    $(this).dialog("destroy");
                }
            });
    });
});
function computeTTC(ht, tva) {
    var tvaVal = parseFloat(tva);
    if (isNaN(tvaVal)) {
        return 0;
    }
    return ht*(1 + (tvaVal/100))
}

function findModifiedIndex(current, previous) {
    var changedIndex = [];
    if (current && previous && current.length === previous.length) {
        for (var i = 0; i < current.length; i++) {
            if (current[i].value !== previous[i].value) {
                changedIndex.push(i);
            }
        }
    }
    return changedIndex;
}

window.dcp.document.documentController("addEventListener",
    "change",
    {
        "name": "BA_FEES::fee_exp_pretax",
        "documentCheck": function (documentObject) {
            return documentObject.family.name === 'BA_FEES'
        },
        "attributeCheck": function (attribute) {
            if (attribute.id === 'fee_exp_pretax') {
                return true;
            }
        }
    },
    function (event, documentObject, attributeObject, values) {
        var tvaAmount = $(this).documentController("getValue", "fee_exp_tva");
        var indexes = findModifiedIndex(values.current, values.previous);
        if (indexes.length) {
            for (var i = 0; i < indexes.length; i++) {
                var index = indexes[i];
                if (tvaAmount[index].value && values.current[index].value) {
                    var ttc = computeTTC(values.current[index].value, tvaAmount[index].value);
                    $(this).documentController('setValue',
                        'fee_exp_tax',
                        {
                            displayValue: ttc + ' €',
                            value: ttc,
                            index: index
                        }
                    );
                }
            }
        }
    }
);

window.dcp.document.documentController("addEventListener",
    "change",
    {
        "name": "BA_FEES::fee_exp_tva",
        "documentCheck": function (documentObject) {
            return documentObject.family.name === 'BA_FEES'
        },
        "attributeCheck": function (attribute) {
            if (attribute.id === 'fee_exp_tva') {
                return true;
            }
        }
    },
    function (event, documentObject, attributeObject, values) {
        const preTaxedAmount = $(this).documentController("getValue", "fee_exp_pretax");
        const indexes = findModifiedIndex(values.current, values.previous);
        if (indexes.length) {
            for (var i = 0; i < indexes.length; i++) {
                const index = indexes[i];
                if (preTaxedAmount[index].value && values.current[index].value) {
                    var ttc = computeTTC(preTaxedAmount[index].value, values.current[index].value);
                    $(this).documentController('setValue',
                        'fee_exp_tax',
                        {
                            displayValue: ttc + ' €',
                            value: ttc,
                            index: index
                        })
                }
            }
        }
    }
);

window.dcp.document.documentController(
    "addEventListener",
    "actionClick",
    {
        "name": "preview",
        "documentCheck": function (documentObject) {
            return (documentObject.family.name === "BA_FEES");
        }
    },
    function(event, documentObject, data) {
        if (data.eventId === "preview") {
            $(document).ready(() => {
                var previewWindow = $('<div class="pdf-preview-window"></div>');
                $('body').append(previewWindow);

                function onClose() {
                    previewWindow.data("kendoWindow").destroy();
                }
                var pdf = $(this).documentController('getValue', 'fee_pdffile');
                previewWindow.kendoWindow({
                    width: '30%',
                    height: '70%',
                    title: "Fee Note preview",
                    visible: false,
                    iframe: true,
                    content: pdf.url+'?inline=true',
                    modal: true,
                    actions: [
                        "Pin",
                        "Close"
                    ],
                    close: onClose,
                }).data("kendoWindow").center().open();
            });
        }
    }
);
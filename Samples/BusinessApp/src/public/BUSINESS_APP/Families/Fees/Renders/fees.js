function computeTVA(ht, ttc) {
    var tvas = [20, 5.5, 2.1, 10];
    var tva = ((ttc/ht) - 1)*100;
    var tvaid = -1;
    var delta = 100;
    for (var i = 0; i < tvas.length; i++) {
        var distance = Math.abs(tvas[i] - tva);
        if (distance < delta) {
            delta = distance;
            tvaid = i;
        }
    }
    return tvas[tvaid];

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
        var allTaxedAmount = $(this).documentController("getValue", "fee_exp_tax");
        var indexes = findModifiedIndex(values.current, values.previous);
        if (indexes.length) {
            for (var i = 0; i < indexes.length; i++) {
                var index = indexes[i];
                if (allTaxedAmount[index].value && values.current[index].value) {
                    console.log('setValue', {
                        displayValue: computeTVA(values.current[index].value, allTaxedAmount[index].value)+ '%',
                        value: computeTVA(values.current[index].value, allTaxedAmount[index].value)+ '%',
                        index: index
                    });
                    $(this).documentController('setValue',
                        'fee_exp_tva',
                        {
                            displayValue: computeTVA(values.current[index].value, allTaxedAmount[index].value)+ '%',
                            value: computeTVA(values.current[index].value, allTaxedAmount[index].value)+ '%',
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
        "name": "BA_FEES::fee_exp_tax",
        "documentCheck": function (documentObject) {
            return documentObject.family.name === 'BA_FEES'
        },
        "attributeCheck": function (attribute) {
            if (attribute.id === 'fee_exp_tax') {
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
                    $(this).documentController('setValue',
                        'fee_exp_tva',
                        {
                            displayValue: computeTVA(preTaxedAmount[index].value, values.current[index].value)+ ' %',
                            value: computeTVA(preTaxedAmount[index].value, values.current[index].value)+ ' %',
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
                console.log(pdf);
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
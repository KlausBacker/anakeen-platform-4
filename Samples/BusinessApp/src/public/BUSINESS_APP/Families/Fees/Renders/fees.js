window.dcp.document.documentController(
    'addEventListener',
    'actionClick',
    {
        name: 'preview',
        documentCheck: function (documentObject) {
            return (documentObject.family.name === 'BA_FEES');
        },
    },
    function (event, documentObject, data) {
        if (data.eventId === 'preview') {
            $(document).ready(() => {
                var previewWindow = $('<div class="pdf-preview-window"></div>');
                $('body').append(previewWindow);

                function onClose() {
                    previewWindow.data('kendoWindow').destroy();
                }

                var pdf = $(this).documentController('getValue', 'fee_pdffile');
                previewWindow.kendoWindow({
                    width: '30%',
                    height: '70%',
                    title: 'Fee Note preview',
                    visible: false,
                    iframe: true,
                    content: pdf.url + '?inline=true',
                    modal: true,
                    actions: [
                        'Pin',
                        'Close',
                    ],
                    close: onClose,
                }).data('kendoWindow').center().open();
            });
        }
    }
);

window.dcp.document.documentController(
    'addEventListener',
    'attributeReady',
    {
        name: 'attachMoneyChangeCurrency',
        documentCheck: function (documentObject) {
            return (documentObject.family.name === 'BA_FEES');
        },

        attributeCheck: function (attributeObject) {
            return (attributeObject.getProperties().type === 'money');
        },
    },
    function (event, documentObject, attributeObject, $el) {
        var _this = this;
        var currencySelector = $el.find('select.money__currency__selector');
        currencySelector.val('€');
        currencySelector.on('change', function (e) {
            attributeObject.setOption('currency', e.target.value);
            console.log('Currency option changed to : ' + attributeObject.getOption('currency'));
        });
    }
);

window.dcp.document.documentController(
  'addEventListener',
  'change',
    {
        name: 'BA_FEES::changePeriod',
        documentCheck: function (documentObject) {
            return (documentObject.family.name === 'BA_FEES');
        },

        attributeCheck: function (attributeObject) {
            return (attributeObject.id === 'fee_period');
        },
    },
    function (event, documentObject, attributeObject, values) {
        var $datePicker = $(this).find('div[data-attrid=fee_t_all_exp] input[data-role=datepicker]');
        var dateMin = new Date(values.current.value);
        dateMin.setDate(1);
        var dateMax = new Date(values.current.value);
        dateMax.setMonth(dateMax.getMonth() + 1);
        dateMax.setDate(0);
        for (var i = 0; i < $datePicker.length; i++) {
            var kendoDatePicker = $($datePicker[i]).data('kendoDatePicker');

            kendoDatePicker.setOptions({
                min: dateMin,
                max: dateMax,
            });
        }
    }
);

window.dcp.document.documentController('addEventListener',
    'attributeArrayChange',
    {
        name: 'BA_FEES::addNewLine',
        documentCheck: function (documentObject) {
            return (documentObject.family.name === 'BA_FEES');
        },

        attributeCheck: function (attributeObject) {
            return (attributeObject.id === 'fee_t_all_exp');
        },
    }, function displayArrayModified(event, document, attribut, type, options) {
        if (type === 'addLine') {
            var date = $(this).documentController('getValue', 'fee_period');
            if (date && date.value) {
                var dateMin = new Date(date.value);
                dateMin.setDate(1);
                var dateMax = new Date(date.value);
                dateMax.setMonth(dateMax.getMonth() + 1);
                dateMax.setDate(0);
                var $line = $(this).find('div[data-attrid=fee_t_all_exp] .dcpArray__content__line');
                var $datePicker = $($line[options]).find('input[data-role=datepicker]');
                if ($datePicker.length) {
                    var kendoDatePicker = $datePicker.data('kendoDatePicker');

                    kendoDatePicker.setOptions({
                        min: dateMin,
                        max: dateMax,
                    });
                }
            }
        }
    }
);


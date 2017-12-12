window.dcp.document.documentController('addEventListener',
    'change',
    {
        name: 'BA_RHDIR::rh_person_mail',
        documentCheck: function (documentObject) {
            return documentObject.family.name === 'BA_RHDIR';
        },

        attributeCheck: function isTitle(attribute) {
            if (attribute.id === 'rh_person_mail') {
                return true;
            }
        },
    },
    function changeDisplayError(event, documentObject, attributeObject, values) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        console.log(values);
        if (values.current.value && !re.test(values.current.value)) {
            $(this).documentController('setAttributeErrorMessage', attributeObject.id, 'Adresse de courriel incorrecte');
        } else {
            $(this).documentController('cleanAttributeErrorMessage', attributeObject.id);
        }
    }
);

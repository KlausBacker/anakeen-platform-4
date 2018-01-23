// jscs:disable disallowFunctionDeclarations
function onDocumentSelected() {
    var width = window.innerWidth || document.body.clientWidth;
    if (width < 958) {
        var splitter = document.getElementById('splitter');
        splitter.publicMethods.closeSplitter();
    }
}

function onReportingButtonClick() {
    var tabs = document.getElementById('documentsTabs');
    tabs.publicMethods.addCustomTab({
        tabId: 'REPORTING_TAB',
        headerTemplate: '<span class="tab__document__header__content"><i class="tab__document__icon material-icons" style="height: auto; font-size: 1.3rem; color:black;">insert_chart</i><span class="tab__document__title">Reporting</span></span>',
        contentTemplate: '<ank-reporting-tab></ank-reporting-tab>',
        data: {},
    });
}

function setVisitedTagToDocument(document, axios) {
    axios.put(`documents/${document.initid}/usertags/open_document`, {
        counter: 1,
    }).then((response) => {
        // console.log(response);
    }).catch((error) => {
        console.error(error);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    var list = document.getElementById('documentsList');
    var collections = document.getElementById('collectionsList');
    var splitter = document.getElementById('splitter');
    collections.addEventListener('reporting-click', function displayReportTab() {
        onReportingButtonClick();
    });
    list.addEventListener('document-selected', function selectDoc() {
        onDocumentSelected();
    });
    var tab = document.getElementById('documentsTabs');
    tab.addEventListener('document-tab-selected', function tagDoc(event) {
        var document = event.detail[0];
        var http = event.detail[1];
        setVisitedTagToDocument(document, http);
    });

    tab.publicMethods.setNewTabConfig({
        headerTemplate: '<span class="tab__document__header__content">\n' +
        '    <img class="tab__document__icon" src="api/v1/images/assets/sizes/15x15/anakeen_monogramme_S.png"\n' +
        '         style="position:relative; top: 0.1666rem;"/>\n' +
        '    <span class="tab__document__title">Nouvel Onglet</span>\n' +
        '</span>',
        contentTemplate: '<ank-welcome-tab prompt-message="Que voulez-vous faire ?"></ank-welcome-tab>',
        data: {},
    });
    tab.publicMethods.initWithWelcomeTab({
        headerTemplate: '<span class="tab__document__header__content">\n' +
        '    <img class="tab__document__icon" src="api/v1/images/assets/sizes/15x15/anakeen_monogramme_S.png"\n' +
        '         style="position:relative; top: 0.1666rem;"/>\n' +
        '    <span class="tab__document__title">Bienvenue</span>\n' +
        '</span>',
        contentTemplate: '<ank-welcome-tab welcome-message="bienvenue sur xPs Business App" ' +
        'prompt-message="Que voulez-vous faire ?"></ank-welcome-tab>',
        data: {},
    });
});

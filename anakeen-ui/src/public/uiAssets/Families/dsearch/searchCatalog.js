define(["dcpDocument/i18n/translatorFactory", "text!dcpContextRoot/api/v1/i18n/SEARCH_UI_HTML5"],
    function searchCatalog(i18nFactory, catalog)
    {
        return i18nFactory(catalog);
    }
);

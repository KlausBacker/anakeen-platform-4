import "./searchRender.css";

$.getJSON("api/v2/i18n/SEARCH_UI_HTML5").done(catalog => {
  window.dsearch = window.dsearch || {};
  window.dsearch.catalog = catalog;
  require("./jquery.stickytableheaders.min");
  require("./searchAttributeHelper");
  require("./searchUI");
  require("./searchUIEventEdit");
  require("./searchUICreationEvent");
  require("./searchUIEventView");
});

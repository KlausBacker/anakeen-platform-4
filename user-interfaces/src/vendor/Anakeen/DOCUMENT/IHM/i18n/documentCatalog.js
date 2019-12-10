import * as catalogStorage from "./catalogStorage";
import translatorFactory from "./translatorFactory";

window.dcp = window.dcp || {};

//Register document translation in the global window.dcp.documentCatalog
const catalogData = catalogStorage.loadCatalog();
window.dcp.documentCatalog = translatorFactory(catalogData);
export default window.dcp.documentCatalog;

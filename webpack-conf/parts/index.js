const kendo = require("./kendo");
const loaders = require("./loaders");
const outputs = require("./outputs");

//Loader part
exports.cssLoader = loaders.cssLoader;
exports.scssLoader = loaders.scssLoader;
exports.typeScriptLoader = loaders.typescriptLoader;
exports.vueLoader = loaders.vueLoader;
exports.jsModernLoader = loaders.jsModernLoader;
exports.jsLegacyLoader = loaders.jsLegacyLoader;
//Utilities part
exports.checkDuplicatePackage = outputs.checkDuplicatePackage;
exports.clean = outputs.clean;
exports.extractAssets = outputs.extractAssets;
exports.generateNamedChunk = outputs.generateNamedChunk;
exports.generateHashModuleName = outputs.generateHashModuleName;
exports.progressPlugin = outputs.progressPlugin;
exports.addFalseKendoGlobal = kendo.addFalseKendoGlobal;
exports.dllPlugin = outputs.dllPlugin;
exports.addDll = outputs.addDll;

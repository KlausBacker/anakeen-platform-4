const global = require("./global");
const loaders = require("./loaders");
const outputs = require("./outputs");

//Loader part
exports.cssLoader = loaders.cssLoader;
exports.scssLoader = loaders.scssLoader;
exports.typeScriptLoader = loaders.typescriptLoader;
exports.vueLoader = loaders.vueLoader;
exports.jsModernLoader = loaders.jsModernLoader;
exports.sourceMapLoader = loaders.sourceMapLoader;
exports.excludeChunkFromMinification = loaders.excludeChunkFromMinification;
//Utilities part
exports.checkDuplicatePackage = outputs.checkDuplicatePackage;
exports.clean = outputs.clean;
exports.extractAssets = outputs.extractAssets;
exports.generateNamedChunk = outputs.generateNamedChunk;
exports.generateHashModuleName = outputs.generateHashModuleName;
exports.progressPlugin = outputs.progressPlugin;
exports.addKendoGlobal = global.addKendoGlobal;
exports.addJqueryGlobal = global.addJqueryGlobal;
exports.addVueGlobal = global.addVueGlobal;
exports.dllPlugin = outputs.dllPlugin;
exports.addCache = outputs.addCache;

const path = require('path');
const BASE_DIR = path.resolve(__dirname,"../../");
const PATHS = {
    account: path.resolve(BASE_DIR, 'admin-center/src/vendor/Anakeen/AdminCenter/Account/accountMain.js'),
    parameters: path.resolve(BASE_DIR, 'admin-center/src/vendor/Anakeen/AdminCenter/Parameters/parametersMain.js'),
    routes: path.resolve(BASE_DIR, 'admin-center/src/vendor/Anakeen/AdminCenter/Routes/routePluginMain.js'),
};

const entries = {
    'userAdmin': PATHS.account,
    'parametersManagement': PATHS.parameters,
    'routesManagement': PATHS.routes,
};

module.exports = entries;

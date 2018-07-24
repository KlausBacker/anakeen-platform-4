const path = require('path');
const BASE_DIR = __PROJECT_ROOT;
const PATHS = {
    account: path.resolve(BASE_DIR, 'admin-center/src/vendor/Anakeen/AdminCenter/Account/accountMain.js'),
    parameters: path.resolve(BASE_DIR, 'admin-center/src/vendor/Anakeen/AdminCenter/Parameters/parametersMain.js'),
    routes: path.resolve(BASE_DIR, 'admin-center/src/vendor/Anakeen/AdminCenter/Routes/routePluginMain.js'),
};

const entries = {
    'ank-admin-account': PATHS.account,
    'ank-admin-parameters': PATHS.parameters,
    'ank-admin-routes': PATHS.routes,
};

module.exports = entries;
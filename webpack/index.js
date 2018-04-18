const path = require('path');
global.__PROJECT_ROOT = path.resolve(__dirname, '..');

module.exports = (env) => {
    return require('./config')(env);
};
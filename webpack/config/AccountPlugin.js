const path = require('path');

const merge = require('webpack-merge');
const parts = require('../parts');

const BASE_DIR = __PROJECT_ROOT;

const PATHS = {
    account: path.resolve(BASE_DIR, 'admin-center/src/vendor/Anakeen/AdminCenter/Account/accountMain.js'),
    build: path.resolve(BASE_DIR, 'admin-center/src/public'),
};

const productionComponentConfig = merge([
    {
        mode: 'production',
        entry: {
            'ank-admin-account': PATHS.account,
        },
        output: {
            publicPath: '/AdminCenter/prod/',
            path: path.resolve(PATHS.build, 'AdminCenter/prod/'),
            filename: '[name].js'
        },
    },
    parts.useVueLoader(/node_modules/),
    parts.setFreeVariable('process.env.NODE_ENV', 'production'),
    parts.clean(path.resolve(PATHS.build, 'AdminCenter/prod/')),
    parts.generateHashModuleName()
]);

const debugComponentConfig = merge([
    {
        mode: 'development',
        entry: {
            'ank-admin-account': PATHS.account,
        },
        output: {
            publicPath: '/AdminCenter/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'AdminCenter/debug/'),
        }
    },
    parts.useVueLoader(/node_modules/),
    parts.setFreeVariable('process.env.NODE_ENV', 'debug'),
    parts.clean(path.resolve(PATHS.build, 'AdminCenter/debug/'))
]);

const devComponentConfig = merge([
    {
        mode: 'development',
        entry: {
            'ank-admin-account': PATHS.account
        },
        output: {
            publicPath: '/AdminCenter/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'AdminCenter/debug/'),
        }
    },
    parts.useVueLoader(/node_modules/),
    parts.setFreeVariable('process.env.NODE_ENV', 'debug'),
    parts.devServer({
        contentBase: PATHS.build,
        publicPath: 'AdminCenter/debug/',
        host: process.env.HOST,
        port: process.env.PORT,
        proxy: {
            "!/AdminCenter/debug/*.js": {
                target: process.env.PROXY_URL || 'http://localhost',
            }
        },
    })
]);

module.exports = env => {
    if (env === 'prod') {
        return productionComponentConfig;
    } else if (env === 'debug') {
        return debugComponentConfig;
    } else if (env === 'dev') {
        return devComponentConfig;
    }
    return debugComponentConfig;
};
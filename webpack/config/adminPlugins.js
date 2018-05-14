const path = require('path');

const merge = require('webpack-merge');
const parts = require('../parts');

const BASE_DIR = __PROJECT_ROOT;
const PATHS = {
    adminPlugins: path.resolve(BASE_DIR, 'src/vendor/Anakeen/AdminPlugins/Components/main.js'),
    build: path.resolve(BASE_DIR, 'src/vendor/Anakeen/AdminPlugins/dist'),
};

const productionComponentConfig = merge([
    {
        mode: 'production',
        entry: {
            'ank-admin-plugins-components': PATHS.adminPlugins,
        },
        output: {
            publicPath: '/AdminPlugins/prod/',
            path: path.resolve(PATHS.build, 'AdminPlugins/prod/'),
            filename: '[name].js'
        },
        externals: {
            vue: 'window.Vue'
        }
    },
    parts.useVueLoader(/node_modules/),
    parts.setFreeVariable('process.env.NODE_ENV', 'production'),
    parts.clean(path.resolve(PATHS.build, 'AdminPlugins/prod/')),
    parts.generateHashModuleName(),
    parts.extractAssets(
        {
            "filename": "ank-admin-plugins-components.json",
            "path": path.resolve(PATHS.build, 'AdminPlugins/prod/'),
        }
    )
]);

const debugComponentConfig = merge([
    {
        mode: 'development',
        entry: {
            'ank-admin-plugins-components': PATHS.adminPlugins
        },
        output: {
            publicPath: '/AdminPlugins/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'AdminPlugins/debug/'),
        },
        externals: {
            vue: 'window.Vue'
        }
    },
    parts.useVueLoader(/node_modules/),
    parts.setFreeVariable('process.env.NODE_ENV', 'debug'),
    parts.clean(path.resolve(PATHS.build, 'AdminPlugins/debug/')),
    parts.extractAssets(
        {
            "filename": "ank-admin-plugins-components.json",
            "path": path.resolve(PATHS.build, 'AdminPlugins/debug/')
        }
    )
]);

const devComponentConfig = merge([
    {
        mode: 'development',
        entry: {
            'ank-admin-plugins-components': PATHS.adminPlugins
        },
        output: {
            publicPath: '/AdminPlugins/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'AdminPlugins/debug/'),
        }
    },
    parts.useVueLoader(/node_modules/),
    parts.setFreeVariable('process.env.NODE_ENV', 'debug'),
    parts.devServer({
        contentBase: PATHS.build,
        publicPath: 'AdminPlugins/debug/',
        host: process.env.HOST,
        port: process.env.PORT,
        proxy: {
            "!/AdminPlugins/debug/*.js": {
                target: process.env.PROXY_URL || 'localhost',
            }
        },
    })
]);

module.exports = env => {
    if (env === 'production') {
        return productionComponentConfig;
    } else if (env === 'debug') {
        return debugComponentConfig;
    } else if (env === 'dev') {
        return devComponentConfig;
    }
    return debugComponentConfig;
};
const path = require('path');

const merge = require('webpack-merge');
const parts = require('../parts');

const BASE_DIR = __PROJECT_ROOT;
const PATHS = {
    adminCenter: path.resolve(BASE_DIR, 'admin-center/src/vendor/Anakeen/AdminCenter/Components/main.js'),
    build: path.resolve(BASE_DIR, 'admin-center/src/public'),
};

const adminPluginsEntries = require('./pluginsEntries');
const adminPluginsChunks = Object.keys(adminPluginsEntries);

const productionFilename = (bundle) => {
    const bundleName = bundle.chunk.name;
    if (adminPluginsChunks.indexOf(bundleName) >= 0) {
        return '[name].js';
    }
    return '[name]-[chunkhash].js';
};

const commonConfig = merge([
    {
        entry: {
            'ank-admin-center-components': PATHS.adminCenter,
            ...adminPluginsEntries
        },
    },
    parts.splitChunksPlugin(),
    parts.useVueLoader(/node_modules/),
]);

const productionComponentConfig = merge([
    {
        mode: 'production',
        devtool: 'source-map',
        output: {
            publicPath: '/AdminCenter/prod/',
            path: path.resolve(PATHS.build, 'AdminCenter/prod/'),
            filename: productionFilename,
            chunkFilename: '[name].js',
        },
    },
    parts.setFreeVariable('process.env.NODE_ENV', 'production'),
    parts.clean(path.resolve(PATHS.build, 'AdminCenter/prod/')),
    parts.generateHashModuleName(),
    parts.generateViewHtml({
        destination: 'admin-center/src/vendor/Anakeen/AdminCenter/Layout/adminCenterMainPage.html',
        template: 'admin-center/src/vendor/Anakeen/AdminCenter/Components/adminCenterMainPage.ejs',
        env: 'prod',
        excludeChunks: adminPluginsChunks
    }),
    parts.extractAssets(
        {
            "filename": "ank-admin-center-components.json",
            "path": path.resolve(PATHS.build, 'AdminCenter/prod/'),
        }
    )
]);

const debugComponentConfig = merge([
    {
        mode: 'development',
        devtool: 'inline-source-map',
        output: {
            publicPath: '/AdminCenter/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'AdminCenter/debug/'),
        },
    },
    parts.setFreeVariable('process.env.NODE_ENV', 'debug'),
    parts.clean(path.resolve(PATHS.build, 'AdminCenter/debug/')),
    parts.generateViewHtml({
        destination: 'admin-center/src/vendor/Anakeen/AdminCenter/Layout/adminCenterMainPage-debug.html',
        template: 'admin-center/src/vendor/Anakeen/AdminCenter/Components/adminCenterMainPage.ejs',
        env: 'debug',
        excludeChunks: adminPluginsChunks
    }),
    parts.extractAssets(
        {
            "filename": "ank-admin-center-components.json",
            "path": path.resolve(PATHS.build, 'AdminCenter/debug/')
        }
    )
]);

const devComponentConfig = merge([
    {
        mode: 'development',
        devtool: 'inline-source-map',
        output: {
            publicPath: '/AdminCenter/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'AdminCenter/debug/'),
        },
    },
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
    }),
    parts.generateViewHtml({
        destination: 'admin-center/src/vendor/Anakeen/AdminCenter/Layout/adminCenterMainPage-debug.html',
        template: 'admin-center/src/vendor/Anakeen/AdminCenter/Components/adminCenterMainPage.ejs',
        env: 'debug',
        excludeChunks: adminPluginsChunks
    }),
]);

module.exports = env => {
    if (env === 'prod') {
        return merge(commonConfig, productionComponentConfig);
    } else if (env === 'debug') {
        return merge(commonConfig, debugComponentConfig);
    } else if (env === 'dev') {
        return merge(commonConfig, devComponentConfig);
    }
    return merge(commonConfig, debugComponentConfig);
};
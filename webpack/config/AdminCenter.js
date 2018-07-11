const path = require('path');

const merge = require('webpack-merge');
const parts = require('../parts');
const AnalyzeBundle = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const Visualizer = require('webpack-visualizer-plugin');

const BASE_DIR = __PROJECT_ROOT;
const PATHS = {
    adminCenter: path.resolve(BASE_DIR, 'admin-center/src/vendor/Anakeen/AdminCenter/Components/main.js'),
    account: path.resolve(BASE_DIR, 'admin-center/src/vendor/Anakeen/AdminCenter/Account/accountMain.js'),
    parameters: path.resolve(BASE_DIR, 'admin-center/src/vendor/Anakeen/AdminCenter/Parameters/parametersMain.js'),
    routes: path.resolve(BASE_DIR, 'admin-center/src/vendor/Anakeen/AdminCenter/Routes/routePluginMain.js'),
    build: path.resolve(BASE_DIR, 'admin-center/src/public'),
};

const adminPluginsChunks = ['ank-admin-account', 'ank-admin-parameters', 'ank-admin-routes'];

const productionComponentConfig = merge([
    {
        mode: 'production',
        devtool: 'source-map',
        entry: {
            'ank-admin-center-components': PATHS.adminCenter,
            'ank-admin-account': PATHS.account,
            'ank-admin-parameters': PATHS.parameters,
            'ank-admin-routes': PATHS.routes,
        },
        output: {
            publicPath: '/AdminCenter/prod/',
            path: path.resolve(PATHS.build, 'AdminCenter/prod/'),
            filename: function(bundle) {
                const bundleName = bundle.chunk.name;
                if (adminPluginsChunks.indexOf(bundleName) >= 0) {
                    return '[name].js';
                }
                return '[name]-[chunkhash].js';
            },
            chunkFilename: '[name]-[chunkhash].js',
        },
        // Uncomment to enable webpack bundle analyzer
        plugins: [
            // new AnalyzeBundle(),
            // new Visualizer(),
        ]
    },
    parts.splitChunksPlugin(),
    parts.useVueLoader(/node_modules/),
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
        entry: {
            'ank-admin-center-components': PATHS.adminCenter,
            'ank-admin-account': PATHS.account,
            'ank-admin-parameters': PATHS.parameters,
            'ank-admin-routes': PATHS.routes,
        },
        output: {
            publicPath: '/AdminCenter/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'AdminCenter/debug/'),
        },
    },
    parts.splitChunksPlugin(),
    parts.useVueLoader(/node_modules/),
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
        entry: {
            'ank-admin-center-components': PATHS.adminCenter,
            'ank-admin-account': PATHS.account,
            'ank-admin-parameters': PATHS.parameters,
            'ank-admin-routes': PATHS.routes,
        },
        output: {
            publicPath: '/AdminCenter/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'AdminCenter/debug/'),
        },
    },
    parts.splitChunksPlugin(),
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
        return productionComponentConfig;
    } else if (env === 'debug') {
        return debugComponentConfig;
    } else if (env === 'dev') {
        return devComponentConfig;
    }
    return debugComponentConfig;
};
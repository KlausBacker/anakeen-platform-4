const path = require('path');

const merge = require('webpack-merge');
const parts = require('../parts');

const BASE_DIR = __PROJECT_ROOT;
const PATHS = {
    hub: path.resolve(BASE_DIR, 'src/vendor/Anakeen/Hub/IHM/JS/hub.js'),
    build: path.resolve(BASE_DIR, 'src/public'),
};

const commonConfig = merge([
    {
        entry: {
            'hub': PATHS.hub
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
            publicPath: '/anakeen/hub/prod/',
            path: path.resolve(PATHS.build, 'anakeen/hub/prod/'),
            filename: '[name].js',
            chunkFilename: '[name].js',
        },
    },
    parts.setFreeVariable('process.env.NODE_ENV', 'production'),
    parts.clean(path.resolve(PATHS.build, 'anakeen/hub/prod/')),
    parts.generateHashModuleName(),
    parts.generateViewHtml({
        destination: 'src/vendor/Anakeen/Hub/IHM/hub.html',
        template: 'src/vendor/Anakeen/Hub/IHM/Layout/hub.html',
        env: 'prod'
    })
]);

const debugComponentConfig = merge([
    {
        mode: 'development',
        devtool: 'inline-source-map',
        output: {
            publicPath: '/anakeen/hub/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'anakeen/hub/debug/'),
        },
    },
    parts.setFreeVariable('process.env.NODE_ENV', 'debug'),
    parts.clean(path.resolve(PATHS.build, 'anakeen/hub/debug/')),
    parts.generateViewHtml({
      destination: 'src/vendor/Anakeen/Hub/IHM/hub-debug.html',
      template: 'src/vendor/Anakeen/Hub/IHM/Layout/hub.html',
        env: 'debug'
    })
]);

const devComponentConfig = merge([
      {
        mode: 'development',
        devtool: 'inline-source-map',
        output: {
          publicPath: '/anakeen/hub/debug/',
          filename: '[name].js',
          path: path.resolve(PATHS.build, 'anakeen/hub/debug/'),
        },
      },
    parts.setFreeVariable('process.env.NODE_ENV', 'debug'),
    parts.devServer({
        contentBase: false,
        publicPath: 'anakeen/hub/debug/',
        host: process.env.HOST,
        port: process.env.PORT,
        proxy: {
            "!/anakeen/hub/debug/*.js": {
                target: process.env.PROXY_URL || 'http://localhost',
            }
        },
    }),
      parts.generateViewHtml({
        destination: 'src/vendor/Anakeen/Hub/IHM/hub-debug.html',
        template: 'src/vendor/Anakeen/Hub/IHM/Layout/hub.html',
        env: 'debug'
      })
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
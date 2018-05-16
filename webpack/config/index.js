// Prevent the EMFILE too many open file error
const fs = require('fs');
const gracefulFs = require('graceful-fs');
gracefulFs.gracefulify(fs);

const path = require('path');

const merge = require('webpack-merge');
const parts = require('../parts');

const BASE_DIR = __PROJECT_ROOT;

const AdminCenterWebpackConfig = require('./adminCenter');

const commonConfig = merge([
    {
        devtool: 'source-map',
        bail: true,
        output: {
            filename: '[name]-[chunkhash].js'
        },
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader'
                    }
                }
            ]
        }
    },
    parts.addExternals(),
    parts.progressBar(),
    parts.friendlyErrors(),
]);

module.exports = env => {
    if (env === 'prod' || env === 'debug' || env === 'dev') {
        return [
            merge(commonConfig, AdminCenterWebpackConfig(env))
        ];
    }
};
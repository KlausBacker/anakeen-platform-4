// Prevent the EMFILE too many open file error
const fs = require('fs');
const gracefulFs = require('graceful-fs');
gracefulFs.gracefulify(fs);

const merge = require('webpack-merge');
const parts = require('../parts');

const AdminCenterWebpackConfig = require('./AdminCenter');

const commonConfig = merge([
    {
        devtool: 'source-map',
        bail: true,
        output: {
            filename: '[name]-[chunkhash].js',
        },
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader',
                    },
                },
            ],
        },
    },
    parts.progressBar(),
    parts.friendlyErrors(),
]);

module.exports = env => {
    return [
        merge(commonConfig, AdminCenterWebpackConfig(env)),
    ];
};
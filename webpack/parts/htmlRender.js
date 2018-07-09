const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');

const BASE_DIR = __PROJECT_ROOT;

exports.generateViewHtml = ({destination, template, excludeChunks,chunks, env}) => {
    const plugins = [
        new HtmlWebpackPlugin({
            filename: path.resolve(BASE_DIR, destination),
            template: path.resolve(BASE_DIR, template),
            title: 'Admin Center',
            env: process.env.NODE_ENV || env,
            excludeChunks,
            chunks
        })
    ];
    return {
        plugins
    };
};
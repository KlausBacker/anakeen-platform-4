// Prevent the EMFILE too many open file error
const fs = require('fs');
const gracefulFs = require('graceful-fs');
gracefulFs.gracefulify(fs);

const merge = require("webpack-merge");
const parts = require("./webpack.parts");

const path = require('path');
const CopyWebpackPlugin = require('copy-webpack-plugin');

const commonConfig = merge([{
    entry: {
        'KendoUI': path.resolve(__dirname, 'webpack/kendo.js')
    },
    output: {
        path: path.resolve(__dirname, 'src/public/uiAssets/externals/'),
        publicPath: "/uiAssets/externals/",
        libraryTarget: "umd",
        filename: '[name]/[name].js'
    },
    externals: {
        jquery: 'jQuery'
    }
}
]);

const cleanAndCopyCssConfig = merge([
    parts.clean(path.resolve(__dirname, 'src/public/uiAssets/externals/')),
    {plugins: [
        new CopyWebpackPlugin(
            [
                //KendoUI
                {
                    context: './node_modules/@progress/kendo-theme-bootstrap/scss/',
                    from: '**/*.scss',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/KendoUI/scss/')
                },
                {
                    context: './node_modules/@progress/kendo-theme-bootstrap/modules/',
                    from: '**/*.scss',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/KendoUI/modules/')
                },
                //datatables
                {
                    from: './node_modules/datatables.net-bs4/css',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/datatables/css/')
                },
                //font-awesome
                {
                    from: './node_modules/font-awesome/fonts',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/font-awesome/fonts/')
                },
                {
                    from: './node_modules/font-awesome/scss',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/font-awesome/scss/')
                },
                //Roboto
                {
                    from: './node_modules/roboto-fontface/fonts',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/roboto-fontface/fonts/')
                },
                {
                    from: './node_modules/roboto-fontface/css',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/roboto-fontface/scss/'),
                    ignore: [
                        '*.less',
                        '*.css'
                    ]
                },
                // Material Icons
                {
                    from: './node_modules/material-design-icons-iconfont/dist/fonts/',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/material-design-icons/fonts')
                },
                {
                    from: './node_modules/material-design-icons-iconfont/dist/material-design-icons.scss',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/material-design-icons/material-design-icons.scss'),
                },
                //TraceKit
                {
                    from: './node_modules/tracekit/tracekit.js',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/traceKit/traceKit.js')
                },
                //jQuery
                {
                    context: './node_modules/jquery/dist/',
                    from: '*',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/jquery/')
                }
            ]
        )
    ]}
]);

const productionConfig = merge([
    {
        output: {
            filename: '[name]/[name].built.js'
        }
    },
    parts.minifyJavaScript()
]);

module.exports = env => {
    if (env === "production") {
        return merge(commonConfig, productionConfig);
    } else {
        return merge(commonConfig, cleanAndCopyCssConfig);
    }
};
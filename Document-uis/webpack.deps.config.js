// Prevent the EMFILE too many open file error
const fs = require('fs');
const gracefulFs = require('graceful-fs');
gracefulFs.gracefulify(fs);

const path = require('path');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const webpack = require('webpack');

module.exports = {
    entry: {
        'KendoUI': path.resolve(__dirname, 'webpack/kendo.js'),
        'tooltip': path.resolve(__dirname, 'webpack/tooltip.js'),
    },
    output: {
        path: path.resolve(__dirname, 'src/public/uiAssets/externals/'),
        publicPath: "/uiAssets/externals/",
        libraryTarget: "umd",
        filename: '[name]/[name].js'
    },
    externals: {
        jquery: 'jQuery'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['env'],
                        babelrc:false // No use bootstrap .babelrc
                    }
                }
            },
        ]
    },
    plugins: [
        new CopyWebpackPlugin(
            [
                //Underscore
                {
                    context: './node_modules/underscore/',
                    from: "*",
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/underscore/')
                },
                //Backbone
                {
                    context: './node_modules/backbone/',
                    from: "*",
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/backbone/')
                },
                //Mustache
                {
                    context: './node_modules/mustache/',
                    from: "*",
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/mustache/')
                },
                //CKeditor
                {
                    from: './node_modules/ckeditor/lang',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/ckeditor/lang/')
                },
                {
                    from: './node_modules/ckeditor/plugins',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/ckeditor/plugins/')
                },
                {
                    from: './node_modules/ckeditor/skins',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/ckeditor/skins/')
                },
                {
                    context: './node_modules/ckeditor/',
                    from : "*.+(js|css)",
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/ckeditor/')
                },
                {
                    context: './node_modules/ckeditor/adapters/',
                    from : "**.js",
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/ckeditor/adapters/')
                },
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
                {
                    context: './node_modules/kendo-ui-core/js/cultures/',
                    from: '**/*.js',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/KendoUI/cultures/')
                },
                {
                    context: './node_modules/kendo-ui-core/js/messages/',
                    from: '**/*.js',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/KendoUI/messages/')
                },
                //datatables
                {
                    from: './node_modules/datatables.net/js',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/datatables/')
                },
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
                    ignore : [
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
                //RequireJS
                {
                    context: './node_modules/requirejs/',
                    from: '*.js',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/RequireJS/')
                },
                {
                    from: './node_modules/requirejs-text/text.js',
                    to: path.resolve(__dirname, 'src/public/uiAssets/externals/RequireJS/')
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
        ),
        new webpack.ProvidePlugin({
            Popper: ['popper.js', 'default']
        })
    ]
};
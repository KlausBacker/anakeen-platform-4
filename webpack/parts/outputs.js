const path = require('path');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const ProgressBarPlugin = require('progress-bar-webpack-plugin');
const AssetsWebpackPlugin = require('assets-webpack-plugin');
const FriendlyErrorWebpackPlugin = require('friendly-errors-webpack-plugin');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

const webpack = require('webpack');

exports.clean = currentPath => ({
    plugins: [
        new CleanWebpackPlugin([currentPath], {
            root: path.resolve(currentPath, '..')
        }),
    ],
});

exports.extractAssets = ({filename, path}) => ({
    plugins: [
        new AssetsWebpackPlugin({
            filename,
            path
        })
    ]
});

exports.generateHashModuleName = () => ({
    plugins: [
        new webpack.HashedModuleIdsPlugin({
            hashFunction: 'sha256',
            hashDigest: 'hex',
            hashDigestLength: 20
        })
    ]
});

exports.generateNamedChunk = () => ({
    plugins: [
        new webpack.NamedModulesPlugin(),
        new webpack.NamedChunksPlugin(),
        {
            apply(compiler) {
                compiler.plugin("compilation", (compilation) => {
                    compilation.plugin("before-module-ids", (modules) => {
                        modules.forEach((module) => {
                            if (module.id !== null) {
                                return;
                            }
                            module.id = module.identifier();
                        });
                    });
                });
            }
        }
    ]
});

exports.setFreeVariable = (key, value) => {
    const env = {};
    env[key] = JSON.stringify(value);
    return {
        plugins: [new webpack.DefinePlugin(env)],
    };
};

exports.addExternals = () => ({
    externals: {
        "./kendo.core" : "kendo",
        "./kendo.autocomplete" : "jQuery.fn.kendoX",
        "./kendo.binder" : "jQuery.fn.kendoX",
        "./kendo.button" : "jQuery.fn.kendoX",
        "./kendo.calendar" : "jQuery.fn.kendoX",
        "./kendo.color" : "jQuery.fn.kendoX",
        "./kendo.colorpicker" : "jQuery.fn.kendoX",
        "./kendo.combobox" : "jQuery.fn.kendoX",
        //"./kendo.data" : "jQuery.fn.kendoX",
        "./kendo.data.odata" : "jQuery.fn.kendoX",
        "./kendo.data.xml" : "jQuery.fn.kendoX",
        "./kendo.dateinput" : "jQuery.fn.kendoX",
        "./kendo.datepicker" : "jQuery.fn.kendoX",
        "./kendo.datetimepicker" : "jQuery.fn.kendoX",
        "./kendo.draganddrop" : "jQuery.fn.kendoX",
        "./kendo.dropdownlist" : "jQuery.fn.kendoX",
        "./kendo.editable" : "jQuery.fn.kendoX",
        "./kendo.fx" : "jQuery.fn.kendoX",
        "./kendo.list" : "jQuery.fn.kendoX",
        "./kendo.listview" : "jQuery.fn.kendoX",
        "./kendo.menu" : "jQuery.fn.kendoX",
        "./kendo.mobile.scroller" : "jQuery.fn.kendoX",
        "./kendo.multiselect" : "jQuery.fn.kendoX",
        "./kendo.notification" : "jQuery.fn.kendoX",
        "./kendo.numerictextbox" : "jQuery.fn.kendoX",
        "./kendo.pager" : "jQuery.fn.kendoX",
        "./kendo.popup" : "jQuery.fn.kendoX",
        "./kendo.resizable" : "jQuery.fn.kendoX",
        "./kendo.selectable" : "jQuery.fn.kendoX",
        "./kendo.slider" : "jQuery.fn.kendoX",
        "./kendo.splitter" : "jQuery.fn.kendoX",
        "./kendo.tabstrip" : "jQuery.fn.kendoX",
        "./kendo.timepicker" : "jQuery.fn.kendoX",
        "./kendo.userevents" : "jQuery.fn.kendoX",
        "./kendo.validator" : "jQuery.fn.kendoX",
        "./kendo.virtuallist" : "jQuery.fn.kendoX",
        "./kendo.window" : "jQuery.fn.kendoX",
        jquery: 'jQuery'
    },
});

exports.progressBar = () => ({
    plugins: [
        new ProgressBarPlugin({
            format: '  build [:bar] :percent (:elapsed seconds)',
            clear: false
        })
    ]
});

exports.friendlyErrors = () => ({
    plugins: [
        new FriendlyErrorWebpackPlugin()
    ]
});

exports.cssLoader = (filesOutputDir, exclude) => ({
    module: {
        rules: [
            {
                test: /\.css$/,
                exclude,
                use: [ 'style-loader', 'css-loader' ]
            },
            {
                test: /\.(png|jpg|gif|svg)$/,
                use: [
                    {
                        loader: 'file-loader',
                        options: {
                            name: filesOutputDir+'[name].[ext]',
                            publicPath: 'BUSINESS_APP/Families/'
                        }
                    }
                ]
            }
        ],
    },
});

exports.splitChunksPlugin = () => ({
    optimization: {
        runtimeChunk: false,
        splitChunks: {
            cacheGroups: {
                default: false,
                vendor: {
                    test: /node_modules/,
                    name: 'libs',
                    chunks: 'all',
                    minChunks: 1
                },
                kendo: {
                    test:/node_modules\/@progress/,
                    name: 'kendo',
                    priority: 2,
                    chunks: 'all',
                    minChunks: 1
                },
            }
        }
    },
});

exports.useVueLoader = (exclude) => ({
    resolve: {
        extensions: ['.js', '.vue', '.json'],
        alias: {
            'vue$': 'vue/dist/vue.esm.js',
        }
    },
    module: {
        rules: [
            {
                test: /\.vue$/,
                exclude,
                use: 'vue-loader'
            },
            {
                test: /\.template.kd$/,
                exclude,
                use: 'raw-loader'
            },
            {
                test: /\.sass/,
                use: [
                    'vue-style-loader',
                    'css-loader',
                    'sass-loader'
                ]
            },
            {
                test: /\.scss/,
                use: [
                    'vue-style-loader',
                    'css-loader',
                    'sass-loader'
                ]
            },
            {
                test: /\.css/,
                use: [
                    'vue-style-loader',
                    'css-loader'
                ]
            },
            {
                test: /\.svg/,
                use: 'file-loader',
            }
        ]
    },
    plugins: [
        new VueLoaderPlugin()
    ]
});
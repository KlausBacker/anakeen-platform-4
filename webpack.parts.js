const path = require('path');
const UglifyWebpackPlugin = require("uglifyjs-webpack-plugin");
const CleanWebpackPlugin = require("clean-webpack-plugin");
const HtmlWebpackPlugin = require("html-webpack-plugin");
const ExtractTextWebpackPlugin = require("extract-text-webpack-plugin");
const StyleExtHtmlWebpackPlugin = require("style-ext-html-webpack-plugin");
const HtmlWebpackInlineSVGPlugin = require('html-webpack-inline-svg-plugin');
const GitRevisionPlugin = require('git-revision-webpack-plugin');
const ProgressBarPlugin = require('progress-bar-webpack-plugin');
const AssetsWebpackPlugin = require('assets-webpack-plugin');
const webpack = require("webpack");

exports.minifyJavaScript = () => ({
    devtool: "source-map",
    plugins: [new UglifyWebpackPlugin({
        sourceMap: true
    })],
});

exports.clean = path => ({
    plugins: [new CleanWebpackPlugin([path])],
});

exports.generateViewHtml = (currentPath, excludeChunks) => ({
    module: {
        rules: [
            {
                issuer: /mainDocument\.js$/,
                test: /\.css$/,
                loader: ExtractTextWebpackPlugin.extract({use: "css-loader"})
            },
        ]
    },
    plugins: [
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, currentPath, 'document-view.mustache.html'),
            template: path.resolve(__dirname, 'anakeen-ui/src/vendor/Anakeen/Routes/Ui/Templates/document-view.mustache.html'),
            excludeChunks
        }),
        new ExtractTextWebpackPlugin('loading.css'),
        new StyleExtHtmlWebpackPlugin('loading.css'),
        new HtmlWebpackInlineSVGPlugin()
    ],
});


exports.devServer = ({host, port, proxy} = {}) => ({
    devServer: {
        contentBase: path.resolve(__dirname, 'anakeen-ui/src/public/'),
        host, // Defaults to `localhost`
        port, // Defaults to 8080
        overlay: {
            errors: true,
            warnings: true,
        },
        proxy
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

exports.addExternals = () => ({
    externals: {
        "dcpExternals/KendoUI/KendoUI": 'kendo', // For require("kendo") in mainDocument.js
        "./kendo.core" : "kendo",
        "./kendo.autocomplete" : "jQuery.fn.kendoX",
        "./kendo.binder" : "jQuery.fn.kendoX",
        "./kendo.button" : "jQuery.fn.kendoX",
        "./kendo.calendar" : "jQuery.fn.kendoX",
        "./kendo.color" : "jQuery.fn.kendoX",
        "./kendo.colorpicker" : "jQuery.fn.kendoX",
        "./kendo.combobox" : "jQuery.fn.kendoX",
        "./kendo.data" : "jQuery.fn.kendoX",
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
    }
});

exports.providePopper = () => (
    {
        plugins: [
            new webpack.ProvidePlugin({
                Popper: ['popper.js', 'default']
            })
        ]
    }
);

exports.attachRevision = () => ({
    plugins: [
        new webpack.BannerPlugin({
            banner: new GitRevisionPlugin().version(),
        }),
    ],
});

exports.setFreeVariable = (key, value) => {
    const env = {};
    env[key] = JSON.stringify(value);
    return {
        plugins: [new webpack.DefinePlugin(env)],
    };
};

exports.extractCommonChunk = () => ({
    plugins: [
        new webpack.optimize.CommonsChunkPlugin({
            name: "vendor",
            minChunks: Infinity
        }),
        new webpack.optimize.CommonsChunkPlugin({
            name: ['runtime']
        })
    ]
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

exports.getSmartElementResolve = () => ({
    resolve: {
        extensions: [".js"],
        alias: {
            "dcpContextRoot": "",
            "dcpDocument": path.resolve(__dirname, "anakeen-ui/src/Apps/DOCUMENT/IHM/"),
            "dcpExternals": path.resolve(__dirname, "anakeen-ui/src/public/uiAssets/externals/"),
            "datatables": "datatables.net",
            "datatables-bootstrap": "datatables.net-bs4",
            "kendo-culture-fr": "@progress/kendo-ui/js/cultures/kendo.culture.fr-FR",
            "tooltip": "bootstrap/js/src/tooltip",
            "documentCkEditor": path.resolve(__dirname, "anakeen-ui/webpack/ckeditor.js")
        }
    }
});

exports.cssLoader = (exclude) => ({
    module: {
        rules: [
            {
                test: /\.css$/,
                exclude,
                use: [ 'style-loader', 'css-loader' ]
            }
        ],
    },
});
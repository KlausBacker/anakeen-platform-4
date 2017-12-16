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
            filename: path.resolve(__dirname, currentPath, 'view.html'),
            template: path.resolve(__dirname, 'anakeen-ui/src/Apps/DOCUMENT/IHM/view.html'),
            excludeChunks
        }),
        new ExtractTextWebpackPlugin('loading.css'),
        new StyleExtHtmlWebpackPlugin('loading.css'),
        new HtmlWebpackInlineSVGPlugin()
    ],
});


exports.devServer = ({host, port} = {}) => ({
    devServer: {
        contentBase: path.resolve(__dirname, 'anakeen-ui/src/public/'),
        host, // Defaults to `localhost`
        port, // Defaults to 8080
        overlay: {
            errors: true,
            warnings: true,
        },
        proxy: {
            "!/uiAssets/anakeen/debug/*.js": {
                "target": "http://localhost"
            }
        }
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
        kendo: 'kendo',
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
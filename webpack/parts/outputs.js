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
                    reuseExistingChunk: true,
                    name: 'libs',
                    chunks: 'all',
                    minChunks: 1
                },
                kendo: {
                    test:/node_modules\/@progress/,
                    reuseExistingChunk: true,
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
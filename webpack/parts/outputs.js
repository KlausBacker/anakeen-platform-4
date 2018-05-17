const CleanWebpackPlugin = require('clean-webpack-plugin');
const ProgressBarPlugin = require('progress-bar-webpack-plugin');
const AssetsWebpackPlugin = require('assets-webpack-plugin');
const FriendlyErrorWebpackPlugin = require('friendly-errors-webpack-plugin');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
// const ExtractTextWebpackPlugin = require('extract-text-webpack-plugin');
const webpack = require('webpack');
exports.clean = path => ({
    plugins: [
        new CleanWebpackPlugin([path]),
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
        kendo: 'kendo',
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

// exports.extractCss = (({issuer, test, filename}) => {
//     const rules = [];
//     const loader = ExtractTextWebpackPlugin
//         .extract({ use: ['css-loader', 'sass-loader'], fallback: 'style-loader'});
//     if (Array.isArray(issuer) && Array.isArray(test)) {
//         issuer.forEach((i, index) => {
//             rules.push({
//                 issuer: i,
//                 test: test[index],
//                 loader,
//             });
//         });
//     } else {
//         rules.push({
//             issuer,
//             test,
//             loader,
//         });
//     }
//     return {
//         module: {
//             rules,
//         },
//         plugins: [
//             new ExtractTextWebpackPlugin(filename)
//         ]};
// });

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
        ]
    },
    plugins: [
        new VueLoaderPlugin()
    ]
});
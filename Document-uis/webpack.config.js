const path = require('path');
const webpack = require('webpack');
const confPerso = require("./webpack-perso.js");

module.exports = {
    entry: {
        app: path.resolve(__dirname, 'src/vendor/Anakeen/Components/main.js')
    },
    output: {
        path: path.resolve(__dirname, 'src/public/login/dist/'),
        publicPath: "/login/dist/",
        filename: 'a4-login-components.js'
    },
    externals: {
        kendo: 'kendo',
        jquery: 'jQuery'
    },
    module: {
        rules: [
            {
                test: /\.vue$/,
                use: {
                    loader: 'vue-loader',
                    options: {
                        extractCSS: true, loaders: {
                            sass: 'vue-style-loader!css-loader!sass-loader?indentedSyntax=1',
                            scss: 'vue-style-loader!css-loader!sass-loader'
                        }
                    }
                }
            },
            {
                test: /\.js$/,
                include: [path.resolve(__dirname, 'src/vendor/Anakeen/Components/')],
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['env']
                    }
                }
            }
        ]
    },
    resolve: {
        alias: {
            kendo: path.resolve(__dirname, 'webpack/kendo.js')
        }
    }
};

if (process.env.NODE_ENV !== 'production') {
    module.exports.devtool = "#cheap-module-eval-source-map";
    module.exports.devServer = {
        contentBase: path.resolve(__dirname, 'src/public/'),
        openPage: '?app=BUSINESS_APP',
        hot: true,
        proxy: {
            "!/src/public/login/dist/*.js": {
                "target": confPerso.devServerURL
            }
        }
    };
    module.exports.plugins = (module.exports.plugins || []).concat([
        new webpack.HotModuleReplacementPlugin()
    ]);
}

if (process.env.NODE_ENV === 'production') {
    module.exports.devtool = '#source-map';
    // http://vue-loader.vuejs.org/en/workflow/production.html
    module.exports.plugins = (module.exports.plugins || []).concat([
        new webpack.LoaderOptionsPlugin({
            minimize: true
        }),
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: '"production"'
            }
        }),
        new webpack.optimize.UglifyJsPlugin({
            sourceMap: true,
            compress: {
                warnings: false
            }
        })
    ])
}

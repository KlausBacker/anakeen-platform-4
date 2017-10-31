const path = require('path');
const fs = require('fs');
const webpack = require('webpack');
let confPerso;

if (fs.existsSync('./webpack-perso.js')) {
    confPerso = require('./webpack-perso.js');
} else {
    confPerso = require('./webpack-perso.js.sample');
    console.error('\n============= WARNING =============\n' +
        'By default, "webpack-perso.js.sample" is used but ' +
        '\nyou must define your own "webpack-perso.js" file' +
        '\nin order to configure the anakeen server host' +
        '\n===================================\n');
}
module.exports = {
    entry: {
        app: path.resolve(__dirname, 'src/vendor/Anakeen/Components/main.js')
    },
    output: {
        path: path.resolve(__dirname, 'src/public/components/dist/'),
        publicPath: "/components/dist/",
        filename: 'a4-components.js'
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
            },
            {
                test: /\.template.kd$/,
                include: [path.resolve(__dirname, 'src/vendor/Anakeen/Components/')],
                use: 'raw-loader'
            },
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
            "!/src/public/components/dist/*.js": {
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

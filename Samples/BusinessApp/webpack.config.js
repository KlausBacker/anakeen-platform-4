const path = require('path');
const fs = require('fs');
const webpack = require('webpack');
let confPerso = require('./webpack-perso.js.sample');
const confPersoExists = fs.existsSync('./webpack-perso.js');
if (confPersoExists) {
  confPerso = require("./webpack-perso.js");
} else {
  console.error('\n============= WARNING =============\n' +
    'By default, "webpack-perso.js.sample" is used but ' +
    '\nyou must define your own "webpack-perso.js" file' +
    '\nin order to configure the anakeen server host' +
    '\n===================================\n');
}
const localpub = process.env.NODE_ENV === 'production' ? '..' : '.';
module.exports = {
    entry: {
        app: path.resolve(__dirname, 'src/public/BUSINESS_APP/src/components/main.js')
    },
    output: {
        path: path.resolve(__dirname, 'src/public/BUSINESS_APP/dist/'),
        publicPath: "/BUSINESS_APP/dist/",
        filename: 'a4-business-app-components.js'
    },
      externals: {
        kendo: 'kendo',
        jquery: 'jQuery'
      },
    module: {
        rules: [
            {
              test:    /\.js$/,
              include: [path.resolve(__dirname, 'src/public/BUSINESS_APP/src')],
              loader: 'jscs-loader',
              enforce: 'pre'
            },
            {
                test: /\.vue$/,
                use: {
                  loader: 'vue-loader',
                  options: {
                    extractCSS: process.env.NODE_ENV === 'production',
                    loaders: {
                      sass: 'vue-style-loader!css-loader!sass-loader?indentedSyntax=1',
                      scss: 'vue-style-loader!css-loader!sass-loader'
                    }
                  }
                }
            },
            {
                test: /\.js$/,
                include: [path.resolve(__dirname, 'src/public/BUSINESS_APP/src')],
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['env']
                    }
                }
            },
            {
                test: /\.template.kd$/,
                include: [path.resolve(__dirname, 'src/public/BUSINESS_APP/src')],
                use: 'raw-loader'
            },
        ]
    },
    resolve: {
        alias: {
            kendo: path.resolve(__dirname, '../../Document-uis/webpack/kendo.js'),
            '@': path.resolve(__dirname, 'src/public/BUSINESS_APP/src/components'),
            '@~': path.resolve(__dirname, '../../Document-uis/src/vendor/Anakeen/Components')
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
            "!/BUSINESS_APP/dist/*.js": {
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
    module.exports.plugins = (module.exports.plugins || []).concat([
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

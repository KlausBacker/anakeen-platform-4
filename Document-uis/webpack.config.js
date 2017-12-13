// Prevent the EMFILE too many open file error
const fs = require('fs');
const gracefulFs = require('graceful-fs');
gracefulFs.gracefulify(fs);

const path = require('path');
const CopyWebpackPlugin = require('copy-webpack-plugin');

const merge = require("webpack-merge");
const parts = require("./webpack.parts");

const PATHS = {
    "document": path.resolve(__dirname, 'src/Apps/DOCUMENT/IHM/main.js'),
    "components": path.resolve(__dirname, 'src/vendor/Anakeen/Components/main.js'),
    "build": path.resolve(__dirname, 'src/public/'),
};

const commonConfig = merge([{
    devtool: "cheap-module-eval-source-map",
    output: {
        filename: '[name]-[chunkhash].js'
    },
    resolve: {
        extensions: [".js"],
        alias: {
            "dcpContextRoot": "",
            "dcpDocument": path.resolve(__dirname, "src/Apps/DOCUMENT/IHM/"),
            "datatables": "datatables.net",
            "kendo-culture-fr": "kendo-ui-core/js/cultures/kendo.culture.fr-FR",
            "tooltip": "bootstrap/js/src/tooltip",
            "documentCkEditor": path.resolve(__dirname, "webpack/ckeditor.js")
        }
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: [
                    path.resolve(__dirname, 'node_modules/underscore/'),
                    path.resolve(__dirname, 'node_modules/ckeditor/')
                ],
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['env'],
                        babelrc: false
                    }
                }
            },
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
                test: /\.template.kd$/,
                include: [path.resolve(__dirname, 'src/vendor/Anakeen/Components/')],
                use: 'raw-loader'
            }
        ],
    },
    plugins: [
        new CopyWebpackPlugin(
            [
                //dynacase-report
                {
                    context: path.resolve(__dirname, "src/Apps/DOCUMENT/IHM/"),
                    from: "dynacaseReport.js",
                    to: path.resolve(__dirname, 'src/public/uiAssets/anakeen/')
                }
            ]
        )
    ]
    },
    parts.providePopper(),
    parts.addExternals()
    ]
);

const productionDocumentConfig = merge([
    {
        entry: {
            'document': PATHS.document,
        },
        output: {
            publicPath: 'uiAssets/anakeen/prod/',
            path: path.resolve(PATHS.build, 'uiAssets/anakeen/prod/')
        }
    },
    parts.setFreeVariable("process.env.NODE_ENV", "production"),
    parts.clean(path.resolve(PATHS.build, 'uiAssets/anakeen/prod/')),
    parts.minifyJavaScript(),
    parts.attachRevision(),
    parts.generateViewHtml('src/Apps/DOCUMENT/Layout/prod/')
]);

const productionComponentConfig = merge([
    {
        entry: {
            'a4-components': PATHS.components
        },
        output: {
            publicPath: 'components/dist/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'components/dist/')
        }
    },
    parts.clean(path.resolve(PATHS.build, 'components/dist/')),
    parts.minifyJavaScript(),
    parts.attachRevision()
]);

const debugDocumentConfig = merge([
    {
        entry: {
            'document': PATHS.document
        },
        output: {
            publicPath: 'uiAssets/anakeen/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'uiAssets/anakeen/debug/')
        }
    },
    parts.setFreeVariable("process.env.NODE_ENV", "debug"),
    parts.generateViewHtml('src/Apps/DOCUMENT/Layout/debug/'),
    parts.clean(path.resolve(PATHS.build, 'uiAssets/anakeen/debug/'))
]);

const debugComponentConfig = merge([
    {
        entry: {
            'a4-components': PATHS.components
        },
        output: {
            publicPath: 'components/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'components/debug/')
        }
    },
    parts.clean(path.resolve(PATHS.build, 'components/debug/'))
]);

const devConfig = merge([
    {
        entry: {
            'document': PATHS.document
        },
        output: {
            publicPath: 'uiAssets/anakeen/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'uiAssets/anakeen/debug/')
        }
    },
    parts.setFreeVariable("process.env.NODE_ENV", "debug"),
    parts.generateViewHtml('src/Apps/DOCUMENT/Layout/debug/'),
    parts.devServer(
        {
            host: process.env.HOST,
            port: process.env.PORT,
        }
    )
]);


module.exports = env => {
    if (env === "production") {
        return [
            merge(commonConfig, productionDocumentConfig),
            merge(commonConfig, productionComponentConfig)
        ];
    }
    if (env === "debug") {
        return [
            merge(commonConfig, debugDocumentConfig),
            merge(commonConfig, debugComponentConfig)
        ];
    }
    if (env === "documentDev") {
        return merge(commonConfig, devConfig);
    }
};
// Prevent the EMFILE too many open file error
const fs = require('fs');
const gracefulFs = require('graceful-fs');
gracefulFs.gracefulify(fs);

const path = require('path');
const CopyWebpackPlugin = require('copy-webpack-plugin');

const merge = require("webpack-merge");
const parts = require("./webpack.parts");

const PATHS = {
    "mainSmartElement": path.resolve(__dirname, 'anakeen-ui/src/Apps/DOCUMENT/IHM/mainDocument.js'),
    "smartElementGrid": path.resolve(__dirname, 'anakeen-ui/src/Apps/DOCUMENT_GRID_HTML5/widgets/documentGrid.js'),
    "smartElement": path.resolve(__dirname, 'anakeen-ui/src/Apps/DOCUMENT/IHM/smartElement.js'),
    "components": path.resolve(__dirname, 'anakeen-ui/src/vendor/Anakeen/Components/main.js'),
    "build": path.resolve(__dirname, 'anakeen-ui/src/public/'),
};

const commonConfig = merge([{
    devtool: "source-map",
    output: {
        filename: '[name]-[chunkhash].js'
    },
    resolve: {
        extensions: [".js"],
        alias: {
            "dcpContextRoot": "",
            "dcpDocument": path.resolve(__dirname, "anakeen-ui/src/Apps/DOCUMENT/IHM/"),
            "datatables": "datatables.net",
            "datatables-bootstrap": "datatables.net-bs4",
            "kendo-culture-fr": "kendo-ui-core/js/cultures/kendo.culture.fr-FR",
            "tooltip": "bootstrap/js/src/tooltip",
            "documentCkEditor": path.resolve(__dirname, "anakeen-ui/webpack/ckeditor.js")
        }
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: [
                    path.resolve(__dirname, 'node_modules/underscore/'),
                    path.resolve(__dirname, 'node_modules/ckeditor/'),
                    path.resolve(__dirname, 'node_modules/lodash/')
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
                test: /\.css$/,
                exclude: [
                    /loading\.css/
                ],
                use: [ 'style-loader', 'css-loader' ]
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
                include: [path.resolve(__dirname, 'anakeen-ui/src/vendor/Anakeen/Components/')],
                use: 'raw-loader'
            }
        ],
    },
    plugins: [
        new CopyWebpackPlugin(
            [
                //dynacase-report
                {
                    context: path.resolve(__dirname, "anakeen-ui/src/Apps/DOCUMENT/IHM/"),
                    from: "dynacaseReport.js",
                    to: path.resolve(__dirname, 'anakeen-ui/src/public/uiAssets/anakeen/')
                }
            ]
        )
    ]
    },
    parts.providePopper(),
    parts.addExternals(),
    parts.progressBar()
    ]
);

const productionDocumentConfig = merge([
    {
        entry: {
            'mainSmartElement': PATHS.mainSmartElement
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
    parts.generateHashModuleName(),
    parts.generateNamedChunk(),
    parts.extractAssets({
        "filename": "documentElements.json",
        "path": path.resolve(PATHS.build, 'uiAssets/anakeen/prod/')
    }),
    parts.generateViewHtml('anakeen-ui/src/Apps/DOCUMENT/Layout/prod/')
]);

const productionSmartElementConfig = merge([
    {
        entry: {
            'smartElementGrid': PATHS.smartElementGrid,
            'smartElement': PATHS.smartElement
        },
        output: {
            libraryTarget: "umd",
            publicPath: 'uiAssets/widgets/prod/',
            path: path.resolve(PATHS.build, 'uiAssets/widgets/prod/')
        }
    },
    parts.clean(path.resolve(PATHS.build, 'uiAssets/widgets/prod/')),
    parts.minifyJavaScript(),
    parts.attachRevision(),
    parts.generateHashModuleName(),
    parts.generateNamedChunk(),
    parts.extractAssets({
        "filename": "smartElement.json",
        "path": path.resolve(PATHS.build, 'uiAssets/widgets/prod/')
    })
]);

const productionComponentConfig = merge([
    {
        entry: {
            'ank-components': PATHS.components
        },
        output: {
            publicPath: 'components/dist/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'components/dist/')
        }
    },
    parts.clean(path.resolve(PATHS.build, 'components/dist/')),
    parts.minifyJavaScript(),
    parts.attachRevision(),
    parts.generateHashModuleName(),
    parts.extractAssets(
        {
            "filename": "ank-components.json",
            "path": path.resolve(PATHS.build, 'components/dist/')
        }
    )
]);

const debugDocumentConfig = merge([
    {
        entry: {
            'mainSmartElement': PATHS.mainSmartElement,
        },
        output: {
            publicPath: 'uiAssets/anakeen/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'uiAssets/anakeen/debug/')
        }
    },
    parts.generateNamedChunk(),
    parts.setFreeVariable("process.env.NODE_ENV", "debug"),
    parts.generateViewHtml('anakeen-ui/src/Apps/DOCUMENT/Layout/debug/'),
    parts.extractAssets({
        "filename": "documentElements.json",
        "path": path.resolve(PATHS.build, 'uiAssets/anakeen/debug/')
    }),
    parts.clean(path.resolve(PATHS.build, 'uiAssets/anakeen/debug/'))
]);

const debugSmartElementConfig = merge([
    {
        entry: {
            'smartElementGrid': PATHS.smartElementGrid,
            'smartElement': PATHS.smartElement
        },
        output: {
            libraryTarget: "umd",
            publicPath: 'uiAssets/widgets/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'uiAssets/widgets/debug/')
        }
    },
    parts.generateNamedChunk(),
    parts.extractAssets({
        "filename": "smartElement.json",
        "path": path.resolve(PATHS.build, 'uiAssets/widgets/debug/')
    }),
    parts.clean(path.resolve(PATHS.build, 'uiAssets/widgets/debug/'))
]);

const debugComponentConfig = merge([
    {
        entry: {
            'ank-components': PATHS.components
        },
        output: {
            publicPath: 'components/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'components/debug/')
        }
    },
    parts.clean(path.resolve(PATHS.build, 'components/debug/')),
    parts.extractAssets(
        {
            "filename": "ank-components.json",
            "path": path.resolve(PATHS.build, 'components/debug/')
        }
    )
]);

const devConfig = merge([
    {
        entry: {
            'mainSmartElement': PATHS.mainSmartElement,
            'smartElementGrid': PATHS.smartElementGrid,
            'smartElement': PATHS.smartElement,
            'vendor': ['dcpDocument/widgets/widget', 'underscore']
        },
        output: {
            publicPath: 'uiAssets/anakeen/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'uiAssets/anakeen/debug/')
        }
    },
    parts.setFreeVariable("process.env.NODE_ENV", "debug"),
    parts.generateViewHtml('anakeen-ui/src/Apps/DOCUMENT/Layout/debug/', ["smartElementGrid", "jquery-smartElement"]),
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
            merge(commonConfig, productionComponentConfig),
            merge(commonConfig, productionSmartElementConfig),
            merge(commonConfig, productionDocumentConfig)
        ];
    }
    if (env === "debug") {
        return [
            merge(commonConfig, debugComponentConfig),
            merge(commonConfig, debugSmartElementConfig),
            merge(commonConfig, debugDocumentConfig)
        ];
    }
    if (env === "documentDev") {
        return merge(commonConfig, devConfig);
    }
};
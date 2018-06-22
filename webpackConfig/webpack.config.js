//Prevent the EMFILE too many open file error

const fs = require('fs');
const gracefulFs = require('graceful-fs');
gracefulFs.gracefulify(fs);

const path = require('path');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

const merge = require('webpack-merge');
const parts = require('./webpack.parts');

const PATHS = {
    mainSmartElement: path.resolve(__dirname, '../anakeen-ui/src/Apps/DOCUMENT/IHM/mainDocument.js'),
    smartElementGrid: path.resolve(__dirname, '../anakeen-ui/src/Apps/DOCUMENT_GRID_HTML5/widgets/documentGrid.js'),
    smartElement: path.resolve(__dirname, '../anakeen-ui/src/Apps/DOCUMENT/IHM/smartElement.js'),
    build: path.resolve(__dirname, '../anakeen-ui/src/public/'),
};

const commonConfig = merge([{
    devtool: 'source-map',
    output: {
        filename: '[name]-[chunkhash].js',
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                include: [
                    path.resolve(__dirname, '../anakeen-ui/'),
                    path.resolve(__dirname, 'node_modules/popper.js/'),
                ],
                use: {
                    loader: 'babel-loader',
                },
            },
            {
                test: /\.vue$/,
                use: {
                    loader: 'vue-loader',
                    options: {
                        extractCSS: true, loaders: {
                            sass: 'vue-style-loader!css-loader!sass-loader?indentedSyntax=1',
                            scss: 'vue-style-loader!css-loader!sass-loader',
                        },
                    },
                },
            },
            {
                test: /\.template.kd$/,
                include: [path.resolve(__dirname, '../anakeen-ui/src/vendor/Anakeen/Components/')],
                use: 'raw-loader',
            },
        ],
    },
    plugins: [
        new VueLoaderPlugin(),
        new CopyWebpackPlugin(
            [
                {
                    //dynacase-report
                    context: path.resolve(__dirname, '../anakeen-ui/src/Apps/DOCUMENT/IHM/'),
                    from: 'dynacaseReport.js',
                    to: path.resolve(__dirname, '../anakeen-ui/src/public/uiAssets/anakeen/'),
                },
            ]
        ),
    ],
},
    parts.cssLoader([
        /loading\.css/,
    ]),
    parts.getSmartElementResolve(),
    parts.providePopper(),
    parts.addExternals(),
    parts.progressBar(),
    ]
);

const productionDocumentConfig = merge([
    {
        entry: {
            //inject promise polyfill
            mainSmartElement: ['core-js/es6/promise', PATHS.mainSmartElement],
        },
        output: {
            publicPath: 'uiAssets/anakeen/prod/',
            path: path.resolve(PATHS.build, 'uiAssets/anakeen/prod/'),
        },
    },
    parts.setFreeVariable('process.env.NODE_ENV', 'production'),
    parts.clean(path.resolve(PATHS.build, 'uiAssets/anakeen/prod/')),
    parts.minifyJavaScript(),
    parts.attachRevision(),
    parts.generateHashModuleName(),
    parts.generateNamedChunk(),
    parts.extractAssets({
        filename: 'documentElements.json',
        path: path.resolve(PATHS.build, 'uiAssets/anakeen/prod/'),
    }),
    parts.generateViewHtml('../anakeen-ui/src/Apps/DOCUMENT/Layout/prod/'),
]);

const productionSmartElementConfig = merge([
    {
        entry: {
            smartElementGrid: PATHS.smartElementGrid,
            smartElement: PATHS.smartElement,
        },
        output: {
            libraryTarget: 'umd',
            publicPath: 'uiAssets/widgets/prod/',
            path: path.resolve(PATHS.build, 'uiAssets/widgets/prod/'),
        },
    },
    parts.clean(path.resolve(PATHS.build, 'uiAssets/widgets/prod/')),
    parts.minifyJavaScript(),
    parts.attachRevision(),
    parts.generateHashModuleName(),
    parts.generateNamedChunk(),
    parts.extractAssets({
        filename: 'smartElement.json',
        path: path.resolve(PATHS.build, 'uiAssets/widgets/prod/'),
    }),
]);

const debugDocumentConfig = merge([
    {
        entry: {
            mainSmartElement: ['core-js/es6/promise', PATHS.mainSmartElement],
        },
        output: {
            publicPath: 'uiAssets/anakeen/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'uiAssets/anakeen/debug/'),
        },
    },
    parts.generateNamedChunk(),
    parts.setFreeVariable('process.env.NODE_ENV', 'debug'),
    parts.generateViewHtml('../anakeen-ui/src/Apps/DOCUMENT/Layout/debug/'),
    parts.extractAssets({
        filename: 'documentElements.json',
        path: path.resolve(PATHS.build, 'uiAssets/anakeen/debug/'),
    }),
    parts.clean(path.resolve(PATHS.build, 'uiAssets/anakeen/debug/')),
]);

const debugSmartElementConfig = merge([
    {
        entry: {
            smartElementGrid: PATHS.smartElementGrid,
            smartElement: PATHS.smartElement,
        },
        output: {
            libraryTarget: 'umd',
            publicPath: 'uiAssets/widgets/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'uiAssets/widgets/debug/'),
        },
    },
    parts.generateNamedChunk(),
    parts.extractAssets({
        filename: 'smartElement.json',
        path: path.resolve(PATHS.build, 'uiAssets/widgets/debug/'),
    }),
    parts.clean(path.resolve(PATHS.build, 'uiAssets/widgets/debug/')),
]);

const devConfig = merge([
    {
        entry: {
            mainSmartElement: ['core-js/es6/promise', PATHS.mainSmartElement],
        },
        output: {
            publicPath: 'uiAssets/anakeen/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'uiAssets/anakeen/debug/'),
        },
    },
    parts.setFreeVariable('process.env.NODE_ENV', 'debug'),
    parts.generateViewHtml('../anakeen-ui/src/Apps/DOCUMENT/Layout/debug/'),
    parts.devServer(
        {
            host: process.env.HOST,
            port: process.env.PORT,
            proxy: {
                '!/uiAssets/anakeen/debug/*.js': {
                    target: process.env.PROXY_URL || 'http://localhost',
                },
            },
        }
    ),
]);

module.exports = env => {
    if (env === 'production') {
        return [
            merge(commonConfig, productionSmartElementConfig),
            merge(commonConfig, productionDocumentConfig),
        ];
    }

    if (env === 'debug') {
        return [
            merge(commonConfig, debugSmartElementConfig),
            merge(commonConfig, debugDocumentConfig),
        ];
    }

    if (env === 'documentDev') {
        return merge(commonConfig, devConfig);
    }
};

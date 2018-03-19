// Prevent the EMFILE too many open file error

const fs = require('fs');
const gracefulFs = require('graceful-fs');
gracefulFs.gracefulify(fs);

const path = require('path');

const merge = require('webpack-merge');
const parts = require('./webpack.parts');

const PATHS = {
    familyIHMDsearch: path.resolve(__dirname, 'anakeen-ui/src/vendor/Anakeen/Families/Dsearch/IHM/dsearch.js'),
    familyIHMHelppage: path.resolve(__dirname, 'anakeen-ui/src/vendor/Anakeen/Families/helppage/Render/helppage.js'),
    familyIHMMask: path.resolve(__dirname, 'anakeen-ui/src/vendor/Anakeen/Families/mask/Render/MaskView.js'),
    build: path.resolve(__dirname, 'anakeen-ui/src/public/uiAssets/Families/'),
};

const commonConfig = merge([{
        devtool: 'source-map',
        output: {
            publicPath: 'uiAssets/Families/',
            path: path.resolve(PATHS.build),
            filename: '[name].js',
        },
        module: {
            rules: [
                {
                    test: /\.js$/,
                    include: [
                        path.resolve(__dirname, 'anakeen-ui/'),
                    ],
                    use: {
                        loader: 'babel-loader',
                    },
                },
            ],
        },
    },
        parts.cssLoader([]),
        parts.getSmartElementResolve(),
        parts.providePopper(),
        parts.addExternals(),
        parts.progressBar(),
    ]
);

const productionDocumentConfig = merge([
    {
        entry: {
            'dsearch/prod/dsearch': PATHS.familyIHMDsearch,
            'helppage/prod/helppage': PATHS.familyIHMHelppage,
            'mask/prod/MaskView': PATHS.familyIHMMask,
        },
    },
    parts.clean(path.resolve(PATHS.build, 'dsearch/prod/')),
    parts.clean(path.resolve(PATHS.build, 'helppage/prod/')),
    parts.clean(path.resolve(PATHS.build, 'mask/prod/')),
    parts.minifyJavaScript(),
    parts.attachRevision(),
    parts.generateHashModuleName(),
    parts.generateNamedChunk(),
]);

const debugDocumentConfig = merge([
    {
        entry: {
            'dsearch/debug/dsearch': PATHS.familyIHMDsearch,
            'helppage/debug/helppage': PATHS.familyIHMHelppage,
            'mask/debug/MaskView': PATHS.familyIHMMask,
        },
    },
    parts.generateNamedChunk(),
    parts.clean(path.resolve(PATHS.build, 'dsearch/debug/')),
    parts.clean(path.resolve(PATHS.build, 'helppage/debug/')),
    parts.clean(path.resolve(PATHS.build, 'mask/debug/')),
]);

const devConfig = merge([
    {
        entry: {
            'dsearch/debug/dsearch': PATHS.familyIHMDsearch,
            'helppage/debug/helppage': PATHS.familyIHMHelppage,
            'mask/debug/MaskView': PATHS.familyIHMMask,
        },
    },
    parts.devServer(
        {
            host: process.env.HOST,
            port: process.env.PORT,
            proxy: {
                '!/uiAssets/Families/dsearch/debug/*.js': {
                    target: process.env.PROXY_URL || 'http://localhost',
                },
                '!/uiAssets/Families/helppage/debug/*.js': {
                    target: process.env.PROXY_URL || 'http://localhost',
                },
                '!/uiAssets/Families/mask/debug/*.js': {
                    target: process.env.PROXY_URL || 'http://localhost',
                },
            },
        }
    ),
]);

module.exports = env => {
    console.log(env);
    if (env === 'production') {
        return [
            merge(commonConfig, productionDocumentConfig),
        ];
    }

    if (env === 'debug') {
        return [
            merge(commonConfig, debugDocumentConfig),
        ];
    }

    if (env === 'dev') {
        return merge(commonConfig, devConfig);
    }
};

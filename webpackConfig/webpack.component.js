//Prevent the EMFILE too many open file error

const fs = require('fs');
const gracefulFs = require('graceful-fs');
gracefulFs.gracefulify(fs);

const path = require('path');
const CopyWebpackPlugin = require('copy-webpack-plugin');

const merge = require('webpack-merge');
const parts = require('./webpack.parts');

const PATHS = {
    components: path.resolve(__dirname, '../anakeen-ui/src/vendor/Anakeen/Components/main.js'),
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
                        options: {
                            presets: ['env'],
                            babelrc: false,
                        },
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
                            shadowMode: true,
                        },
                    },
                },
                {
                    test: /\.template.kd$/,
                    include: [path.resolve(__dirname, '../anakeen-ui/src/vendor/Anakeen/Components/')],
                    use: 'raw-loader',
                },
            ],
        }
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

const productionComponentConfig = merge([
    {
        entry: {
            'ank-components': ['core-js/es6/promise', PATHS.components],
        },
        output: {
            publicPath: '/components/dist/',
            filename: '[name]-[chunkhash].js',
            path: path.resolve(PATHS.build, 'components/dist/'),
        },
    },
    parts.clean(path.resolve(PATHS.build, 'components/dist/')),
    parts.minifyJavaScript(),
    parts.attachRevision(),
    parts.generateHashModuleName(),
    parts.extractAssets(
        {
            filename: 'ank-components.json',
            path: path.resolve(PATHS.build, 'components/dist/'),
        }
    ),
]);

const debugComponentConfig = merge([
    {
        entry: {
            'ank-components': ['core-js/es6/promise', PATHS.components],
        },
        output: {
            publicPath: '/components/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'components/debug/'),
        },
    },
    parts.clean(path.resolve(PATHS.build, 'components/debug/')),
    parts.extractAssets(
        {
            filename: 'ank-components.json',
            path: path.resolve(PATHS.build, 'components/debug/'),
        }
    ),
]);
const devComponentConfig = merge([
    {
        entry: {
            'ank-components': ['core-js/es6/promise', PATHS.components],
        },
        output: {
            publicPath: '/components/debug/',
            filename: '[name].js',
            path: path.resolve(PATHS.build, 'components/debug/'),
        },
    },
    parts.setFreeVariable('process.env.NODE_ENV', 'debug'),
    parts.devServer(
        {
            host: process.env.HOST,
            port: process.env.PORT,
            proxy: {
                '!/components/debug/*.js': {
                    target: process.env.PROXY_URL || 'http://localhost',
                },
            },
        }
    ),
]);

module.exports = env => {
    if (env === 'production') {
        return [
            merge(commonConfig, productionComponentConfig)
        ];
    }

    if (env === 'debug') {
        return [
            merge(commonConfig, debugComponentConfig)
        ];
    }

    if (env === 'componentsDev') {
        return merge(commonConfig, devComponentConfig);
    }

};

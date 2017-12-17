// Prevent the EMFILE too many open file error
const fs = require('fs');
const gracefulFs = require('graceful-fs');
gracefulFs.gracefulify(fs);

console.log(process.env);

const path = require('path');

const merge = require("webpack-merge");
const parts = require("./webpack.parts");

const PATHS = {
    "testmain": path.resolve(__dirname, 'Tests/src/Apps/TEST_DOCUMENT_SELENIUM/IHM/testmain.js'),
    "testrender": path.resolve(__dirname, 'Tests/src/Apps/TEST_DOCUMENT_SELENIUM/IHM/testrender.js'),
    "familyTestRender": path.resolve(__dirname, 'Tests/src/Apps/TEST_DOCUMENT_SELENIUM/Family/TestRender/testRender.js'),
    "build": path.resolve(__dirname, 'Tests/src/public/TEST_DOCUMENT_SELENIUM/dist/'),
};

const commonConfig = merge([{
        devtool: "source-map",
        entry: {
            'testmain': PATHS.testmain,
            'testrender': PATHS.testrender,
            'family/TestRender': PATHS.familyTestRender
        },
        output: {
            publicPath: 'TEST_DOCUMENT_SELENIUM/dist/',
            filename: '[name].js',
            path: path.resolve(PATHS.build)
        },
        module: {
            rules: [
                {
                    test: /\.js$/,
                    include: [
                        path.resolve(__dirname, 'Tests/src/Apps/TEST_DOCUMENT_SELENIUM/IHM/')
                    ],
                    use: {
                        loader: 'babel-loader'
                    }
                }
                ]
        }
    },
        parts.cssLoader([]),
        parts.clean(path.resolve(PATHS.build)),
        parts.addExternals(),
        parts.progressBar()
    ]
);

const devConfig = merge([
    parts.devServer({
        host: process.env.HOST,
        port: process.env.PORT,
        proxy : {
            "!/TEST_DOCUMENT_SELENIUM/dist/*.js": {
                "target": process.env.PROXY_URL || "http://localhost"
            }
        }
    })
]);

module.exports = env => {
    if (env === "dev") {
        return merge(commonConfig, devConfig);
    }
    return merge(commonConfig);
};


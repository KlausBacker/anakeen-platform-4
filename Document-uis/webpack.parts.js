const path = require('path');
const UglifyWebpackPlugin = require("uglifyjs-webpack-plugin");
const CleanWebpackPlugin = require("clean-webpack-plugin");
const HtmlWebpackPlugin = require("html-webpack-plugin");
const ExtractTextWebpackPlugin = require("extract-text-webpack-plugin");
const StyleExtHtmlWebpackPlugin = require("style-ext-html-webpack-plugin");
const HtmlWebpackInlineSVGPlugin = require('html-webpack-inline-svg-plugin');
const GitRevisionPlugin = require('git-revision-webpack-plugin');
const webpack = require("webpack");

exports.minifyJavaScript = () => ({
    devtool: "source-map",
    plugins: [new UglifyWebpackPlugin({
        sourceMap: true
    })],
});

exports.clean = path => ({
    plugins: [new CleanWebpackPlugin([path])],
});

exports.generateViewHtml = (currentPath) => ({
    module: {
        rules: [
            {
                issuer: /main\.js$/,
                test: /\.css$/,
                loader: ExtractTextWebpackPlugin.extract({use: "css-loader"})
            },
        ]
    },
    plugins: [
        new HtmlWebpackPlugin({
        filename: path.resolve(__dirname, currentPath, 'view.html'),
        template: path.resolve(__dirname, 'src/Apps/DOCUMENT/IHM/view.html')
        }),
        new ExtractTextWebpackPlugin('loading.css'),
        new StyleExtHtmlWebpackPlugin(),
        new HtmlWebpackInlineSVGPlugin()
    ],
});


exports.devServer = ({host, port} = {}) => ({
    devServer: {
        contentBase: path.resolve(__dirname, 'src/public/'),
        host, // Defaults to `localhost`
        port, // Defaults to 8080
        overlay: {
            errors: true,
            warnings: true,
        },
        proxy: {
            "!/uiAssets/anakeen/debug/*.js": {
                "target": "http://localhost"
            }
        }
    },
});

exports.addExternals = () => ({
    externals: {
        kendo: 'kendo',
        jquery: 'jQuery'
    }
});

exports.providePopper = () => (
    {
        plugins: [
            new webpack.ProvidePlugin({
                Popper: ['popper.js', 'default']
            })
        ]
    }
);

exports.attachRevision = () => ({
    plugins: [
        new webpack.BannerPlugin({
            banner: new GitRevisionPlugin().version(),
        }),
    ],
});

exports.setFreeVariable = (key, value) => {
    const env = {};
    env[key] = JSON.stringify(value);
    return {
        plugins: [new webpack.DefinePlugin(env)],
    };
};
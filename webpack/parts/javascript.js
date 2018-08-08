const UglifyWebpackPlugin = require('uglifyjs-webpack-plugin');

exports.minifyJavaScript = () => ({
    devtool: 'source-map',
    plugins: [
        new UglifyWebpackPlugin({
            sourceMap: true,
        }),
    ],
});
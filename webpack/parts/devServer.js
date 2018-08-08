const webpack = require('webpack');
exports.devServer = ({contentBase, host, port, proxy, publicPath}) => ({
    devServer: {
        overlay: true,
        open: true,
        host,
        port,
        proxy,
        contentBase,
    }
});
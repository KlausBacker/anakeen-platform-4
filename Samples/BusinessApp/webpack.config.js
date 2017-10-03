const path = require('path');
const webpack = require('webpack');

module.exports = {
  entry: './src/public/BUSINESS_APP/src/components/main.js',
  output: {
    path: path.resolve(__dirname, 'src/public/BUSINESS_APP/dist/'),
    filename: 'a4-business-app-components.js'
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        use: 'vue-loader'
      },
      {
        test: /\.js$/,
        use: {
            loader: 'babel-loader',
            options: {
                presets: ['env']
            }
        }
      }
    ]
  }
};

if (process.env.NODE_ENV === 'production') {
  module.exports.devtool = '#source-map';
  // http://vue-loader.vuejs.org/en/workflow/production.html
  module.exports.plugins = (module.exports.plugins || []).concat([
    new webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: '"production"'
      }
    }),
    new webpack.optimize.UglifyJsPlugin({
      // uncomment to enable sourcemap
      sourceMap: true,
      compress: {
        warnings: false
      }
    }),
    new webpack.LoaderOptionsPlugin({
      minimize: true
    })
  ])
}

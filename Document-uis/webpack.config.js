const path = require('path');
const webpack = require('webpack');

module.exports = {
  entry: {
    app: path.resolve(__dirname, 'src/vendor/Anakeen/Components/main.js'),
    externals: [
      'kendo'
    ]
  },
  output: {
    path: path.resolve(__dirname, 'src/public/login/dist/'),
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
        include: [ path.resolve(__dirname, 'src/vendor/Anakeen/Components/')],
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['env']
          }
        }
      }
    ]
  },
  resolve: {
    alias: {
      jquery: path.resolve(__dirname, 'src/public/uiAssets/externals/KendoUI/js/jquery.js'),
      kendo: path.resolve(__dirname, 'src/public/uiAssets/externals/KendoUI/js/kendo-ddui-builded.js')
    }
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
    }),
    new webpack.ProvidePlugin({
      "jQuery": "jquery",
      "$": "jquery",
      "window.jQuery": "jquery"
    }),
    new webpack.optimize.CommonsChunkPlugin({ name: 'externals', filename: 'a4-externals.bundle.js'})
  ])
}

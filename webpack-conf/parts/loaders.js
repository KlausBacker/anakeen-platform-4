const VueLoaderPlugin = require("vue-loader/lib/plugin");
const OptimizeCssAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const FixStyleOnlyEntriesPlugin = require("webpack-fix-style-only-entries");

/**
 * Add a basic loader for css rules
 *
 * @param exclude
 * @returns {{module: {rules: {test: RegExp, use: string[], exclude: *}[]}}}
 */
exports.cssLoader = exclude => ({
  module: {
    rules: [
      {
        test: /\.css$/,
        exclude,
        use: ["style-loader", "css-loader"]
      }
    ]
  }
});

/**
 * Add a loader for SCSS part
 *
 * @param filename
 * @param minify
 * @param removeJS
 * @param includePaths
 * @returns {{plugins: MiniCssExtractPlugin[], module: {rules: *[]}}}
 */
exports.scssLoader = ({
  filename,
  minify = false,
  removeJS = false,
  includePaths = []
}) => {
  const plugins = [
    new MiniCssExtractPlugin({
      // define where to save the file
      filename: filename
    })
  ];
  if (minify) {
    plugins.push(new OptimizeCssAssetsPlugin());
  }
  if (removeJS) {
    plugins.push(new FixStyleOnlyEntriesPlugin());
  }
  return {
    module: {
      rules: [
        {
          // sass / scss / css loader for webpack
          test: /\.(sa|sc|c)ss$/,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: "css-loader",
              options: {
                importLoaders: 1
              }
            },
            {
              loader: "sass-loader",
              options: {
                includePaths
              }
            }
          ]
        },
        {
          test: /\.(ttf|eot|woff|woff2)$/,
          use: {
            loader: "file-loader",
            options: {
              name: "fonts/[name].[ext]",
              publicPath: "./"
            }
          }
        },
        {
          test: /\.(jpe?g|png|gif|svg)$/i,
          use: [
            {
              loader: "file-loader",
              options: {
                name: "images/[name].[ext]",
                publicPath: "./"
              }
            }
          ]
        }
      ]
    },
    plugins
  };
};

/**
 * TypeScript loader
 *
 * @returns {{resolve: {extensions: string[]}, module: {rules: {test: RegExp, loader: string, options: {appendTsSuffixTo: RegExp[], compilerOptions: {declarationDir: string, declaration: boolean}}}[]}}}
 */
exports.typescriptLoader = (customOptions = {}) => {
  const options = {
    ...{
      appendTsSuffixTo: [/\.vue$/],
      compilerOptions: {
        declaration: true,
        declarationDir: "./lib/types"
      }
    },
    ...customOptions
  };
  return {
    resolve: {
      extensions: [".js", ".vue", ".json", ".ts", ".tsx"]
    },
    module: {
      rules: [
        {
          test: /\.ts$/,
          loader: "ts-loader",
          options
        }
      ]
    }
  };
};

/**
 * Vue loader (include loader for sass, css and svg)
 *
 * @param exclude
 * @returns {{resolve: {extensions: string[], alias: {vue$: string}}, plugins: VueLoaderPlugin[], module: {rules: *[]}}}
 */
exports.vueLoader = exclude => {
  return {
    resolve: {
      extensions: [".js", ".vue", ".json"],
      alias: {
        vue$: "vue/dist/vue.esm.js"
      }
    },
    module: {
      rules: [
        {
          test: /\.vue$/,
          exclude,
          use: "vue-loader"
        },
        {
          test: /\.template.kd$/,
          exclude,
          use: "raw-loader"
        },
        {
          test: /\.s[ac]ss/,
          use: ["vue-style-loader", "css-loader", "sass-loader"]
        },
        {
          test: /\.css/,
          use: ["vue-style-loader", "css-loader"]
        },
        {
          test: /\.svg/,
          use: "file-loader"
        }
      ]
    },
    plugins: [new VueLoaderPlugin()]
  };
};

/**
 * Internal : configure some rules for babel-loader
 *
 * @param browserlist
 * @param exclude
 * @param useBuiltIns
 * @returns {{test: RegExp, use: {loader: string, options: {presets: *[][], babelrc: boolean, plugins: string[], exclude: RegExp[], cacheDirectory: boolean}}}}
 */
const configureBabelLoader = ({
  browserlist,
  exclude = [],
  useBuiltIns = "usage"
}) => {
  const conf = {
    test: /\.js$/,
    use: {
      loader: "babel-loader",
      options: {
        babelrc: false,
        exclude: [/node_modules\/core-js/],
        cacheDirectory: true,
        presets: [
          [
            "@babel/preset-env",
            {
              useBuiltIns,
              corejs: 3,
              modules: "umd",
              targets: {
                browsers: browserlist
              }
            }
          ]
        ],
        plugins: ["@babel/plugin-syntax-dynamic-import"]
      }
    }
  };
  if (exclude) {
    conf.use.options.exclude = [...conf.use.options.exclude, ...exclude];
  }
  return conf;
};

/**
 * Add rules for babel but only for modern browser (support script type module)
 *
 * @param exclude
 * @returns {{module: {rules: {test: RegExp, use: {loader: string, options: {presets: *[][], babelrc: boolean, plugins: string[], exclude: RegExp[], cacheDirectory: boolean}}}[]}}}
 */
exports.jsModernLoader = exclude => {
  return {
    module: {
      rules: [
        configureBabelLoader({
          browserlist: [
            // The last two versions of each browser, excluding versions
            // that don't support <script type="module">.
            "last 2 Chrome versions",
            "not Chrome < 60",
            "last 2 Safari versions",
            "not Safari < 10.1",
            "last 2 iOS versions",
            "not iOS < 10.3",
            "last 2 Firefox versions",
            "not Firefox < 54",
            "last 2 Edge versions",
            "not Edge < 15"
          ],
          exclude
        })
      ]
    }
  };
};

/**
 * Add rules for babel for old browser (99% and IE11)
 *
 * @param exclude
 * @returns {{module: {rules: {test: RegExp, use: {loader: string, options: {presets: *[][], babelrc: boolean, plugins: string[], exclude: RegExp[], cacheDirectory: boolean}}}[]}}}
 */
exports.jsLegacyLoader = exclude => {
  return {
    module: {
      rules: [
        configureBabelLoader({
          browserlist: ["> 1%", "last 2 versions", "Firefox ESR", "ie >= 11"],
          exclude
        })
      ]
    }
  };
};

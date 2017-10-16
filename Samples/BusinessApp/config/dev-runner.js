'use strict'

const path = require('path');
const { spawn } = require('child_process');
const webpack = require('webpack');
const WebpackDevServer = require('webpack-dev-server');

const webpackConfig = require('./webpack.config.js');

function logStats(proc, data) {
  let log = '';

  log += `┏ ${proc} Process ${new Array((19 - proc.length) + 1).join('-')}`;
  log += '\n\n';

  if (typeof data === 'object') {
    data.toString({
      colors: true,
      chunks: false,
    }).split(/\r?\n/).forEach(line => {
      log += '  ' + line + '\n';
    });
  } else {
    log += `  ${data}
`;
  }

  log += '\n' + `┗ ${new Array(28 + 1).join('-')}` + '\n';

  console.log(log);
}

function startDev() {
  return new Promise((resolve, reject) => {

    const compiler = webpack(webpackConfig);

    compiler.plugin('done', stats => {
      logStats('Webpack', stats);
    });

    compiler.watch({}, (error, stats) => {
      if (error) {
        reject(error);
      }
    });
    const server = new WebpackDevServer(
      compiler,
      {
        contentBase: path.join(__dirname, '../src/public/BUSINESS_APP/dist'),
        hot: true,
        inline: true,
        setup(app, ctx) {
          ctx.middleware.waitUntilValid(() => {
            resolve();
          });
        },
      }
    );

    server.listen(9080);
  });
}

function greeting() {
  console.log('\n  Sample Business App');
  console.log('  getting ready...' + '\n');
}

function init() {
  greeting();

  startDev()
    .then(() => {
      console.log('Dev server is currently running and assets are served on port 9080 of your local machine');
    })
    .catch(err => {
      console.error(err);
    });
}

init();

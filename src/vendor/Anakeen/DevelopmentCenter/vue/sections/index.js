const routes = require.context(".", true, /index\.js$/);

const moduleExport = [];
routes.keys().forEach(file => {
  if (file !== "." + __filename) {
    moduleExport.push(routes(file).default);
  }
});

export default moduleExport;

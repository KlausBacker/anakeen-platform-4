const routes = require.context(".", true, /index\.js$/);

let moduleExport = [];
routes.keys().forEach(file => {
  if (file !== "." + __filename) {
    const routesSectionExport = routes(file).default;
    if (routesSectionExport) {
      if (Array.isArray(routesSectionExport)) {
        moduleExport = moduleExport.concat(routesSectionExport);
      } else {
        moduleExport.push(routesSectionExport);
      }
    } else {
      console.warn(
        `File "${file}" does not export a valid route configuration`
      );
    }
  }
});

// Compute sections order
moduleExport.sort((e1, e2) => {
  const DEFAULT_ORDER = Infinity;
  const order1 = e1.order === undefined ? DEFAULT_ORDER : e1.order;
  const order2 = e2.order === undefined ? DEFAULT_ORDER : e2.order;
  if (order1 < order2) return -1;
  else if (order1 > order2) return 1;
  else return 0;
});

export default moduleExport;

const { lib } = require("@anakeen/webpack-conf");
const testFulltextSmartCriteria = require("./testFulltextSmartCriteria");
const testFulltextSmartElementGrid = require("./testFulltextSmartElementGrid");

module.exports = () => {
  if (process.env.conf === "PROD") {
    return [lib(testFulltextSmartCriteria), lib(testFulltextSmartElementGrid)];
  }
  if (process.env.conf === "DEV") {
    return [
      lib({ ...testFulltextSmartCriteria, ...{ mode: "dev" } }),
      lib({ ...testFulltextSmartElementGrid, ...{ mode: "dev" } })
    ];
  }
  return [
    lib(testFulltextSmartCriteria),
    lib({ ...testFulltextSmartCriteria, ...{ mode: "dev" } }),
    lib(testFulltextSmartElementGrid),
    lib({ ...testFulltextSmartElementGrid, ...{ mode: "dev" } })
  ];
};

const path = require("path");
const { useCache } = require("./common");
const { lib } = require("@anakeen/webpack-conf");
const {
  useVueLoader
} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");

module.exports = () => {
  const libConf = {
    moduleName: "helloWorld",
    libName: "HubHelloWorld",
    entry: {
      "hello-world": [path.resolve(BASE_DIR, "src/vendor/Anakeen/Hub/IHM/Components/HelloWorld/index.js")]
    },
    buildPath: PUBLIC_PATH,
    customParts: [
      useCache,
      useVueLoader()
    ]
  };
  return [lib(libConf)];
};

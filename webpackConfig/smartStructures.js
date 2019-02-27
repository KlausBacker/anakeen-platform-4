const path = require("path");
const { prod, dev, legacy } = require("@anakeen/webpack-conf");
const { cssLoader } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "/src/public");

module.exports = () => {
  const conf = {
    moduleName: "smartStructures",
    entry: {
      Dsearch: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/SmartStructures/Dsearch/Render/dsearch.js"
        )
      ],
      Helppage: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/SmartStructures/Helppage/Render/helppage.js"
        )
      ],
      Mask: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/SmartStructures/Mask/Render/MaskView.js"
        )
      ],
      IuserGroup: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/SmartStructures/Iuser/Render/changeGroupView.js"
        )
      ],
      Iuser: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/SmartStructures/Iuser/Render/mainIuser.js"
        )
      ],
      Task: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/SmartStructures/Task/Render/task.js"
        )
      ]
    },
    buildPath: PUBLIC_PATH,
    excludeBabel: [
      /node_modules\/style-loader/
    ],
    customParts: [
      {
        resolve: {
          extensions: [".js"],
          alias: {
            dcpContextRoot: "",
            dcpDocument: path.resolve(
              __dirname,
              "../src/vendor/Anakeen/DOCUMENT/IHM/"
            ),
            dcpExternals: path.resolve(
              __dirname,
              "../src/public/uiAssets/externals/"
            )
          }
        }
      },
      {
        externals: {
          jquery: "jQuery"
        }
      },
      cssLoader()
    ]
  };
  return [prod(conf), dev(conf), legacy(conf)];
};

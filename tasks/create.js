const gulp = require("gulp");
const fs = require("fs");
const path = require("path");
const xml2js = require("xml2js");

const {
  checkModuleName,
  checkVendorName,
  checkNamespace
} = require("../utils/checkName");

const createRouteXML = namespace => {
  return {
    "sde:config": {
      $: {
        "xmlns:sde": "http://www.anakeen.com/ns/sde/1.0"
      },
      "sde:routes": {
        $: {
          namespace: namespace
        }
      },
      "sde:accesses": {
        $: {
          namespace: namespace
        }
      },
      "sde:parameters": {
        $: {
          namespace: namespace
        }
      }
    }
  };
};

const createInfoXML = ({ moduleName, vendorName }) => {
  return {
    module: {
      $: {
        xmlns: "urn:dynacase:webinst:module/1.0",
        name: moduleName,
        vendor: vendorName,
        version: "1.0.0",
        release: "0"
      },
      "post-install": {
        process: {
          $: {
            command: "programs/update_catalog"
          }
        }
      },
      "post-upgrade": {
        process: {
          $: {
            command: "programs/update_catalog"
          }
        }
      }
    }
  };
};

const createBuildXML = () => {
  return {
    "acli:config": {
      $: {
        "xmlns:acli": "http://www.anakeen.com/ns/acli/1.0"
      },
      "acli:sources": {
        "acli:source": {
          path: "src"
        }
      }
    }
  };
};

exports.create = ({
  sourcePath,
  moduleName,
  vendorName,
  namespace,
  withSmartStructure,
  withConfig,
  withPublic
}) => {
  return gulp.task("create", () => {
    //Create the vendor dir
    return new Promise((resolve, reject) => {
      //Check name of the element
      if (!checkModuleName(moduleName)) {
        reject("The module name is invalid " + moduleName);
      }
      if (!checkVendorName(vendorName)) {
        reject("The vendor name is invalid " + vendorName);
      }
      if (!checkNamespace(namespace)) {
        reject("The namespace is invalid " + namespace);
      }
      fs.mkdir(path.join(sourcePath, "src"), err => {
        if (err) {
          return reject(err);
        }
        fs.mkdir(path.join(sourcePath, "src", "vendor"), err => {
          if (err) {
            return reject(err);
          }
          fs.mkdir(path.join(sourcePath, "src", "vendor", vendorName), err => {
            if (err) {
              return reject(err);
            }
            if (!withSmartStructure) {
              return resolve();
            }
            fs.mkdir(
              path.join(
                sourcePath,
                "src",
                "vendor",
                vendorName,
                "smartStructure"
              ),
              err => {
                if (err) {
                  return reject(err);
                }
                resolve();
              }
            );
          });
        });
      });
    }) // Create the public (if needed)
      .then(() => {
        if (withPublic) {
          return new Promise((resolve, reject) => {
            fs.mkdir(path.join(sourcePath, "src", "public"), err => {
              if (err) {
                return reject(err);
              }
              resolve();
            });
          });
        }
        return Promise.resolve();
      })
      .then(() => {
        if (withConfig) {
          return new Promise((resolve, reject) => {
            fs.mkdir(path.join(sourcePath, "src", "config"), err => {
              if (err) {
                return reject(err);
              }
              if (err) {
                return reject(err);
              }
              const builder = new xml2js.Builder();
              const xml = builder.buildObject(createRouteXML(namespace));
              fs.writeFile(
                path.join(sourcePath, "src", "config", moduleName + ".xml"),
                xml,
                err => {
                  if (err) {
                    return reject(err);
                  }
                  resolve();
                }
              );
            });
          });
        }
      })
      .then(() => {
        return new Promise((resolve, reject) => {
          const builder = new xml2js.Builder();
          const xml = builder.buildObject(
            createInfoXML({ moduleName, vendorName })
          );
          fs.writeFile(path.join(sourcePath, "info.xml"), xml, err => {
            if (err) {
              return reject(err);
            }
            resolve();
          });
        });
      })
      .then(() => {
        return new Promise((resolve, reject) => {
          const builder = new xml2js.Builder();
          const xml = builder.buildObject(createBuildXML());
          fs.writeFile(path.join(sourcePath, "build.xml"), xml, err => {
            if (err) {
              return reject(err);
            }
            resolve();
          });
        });
      });
  });
};

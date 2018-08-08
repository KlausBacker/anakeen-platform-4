const gulp = require("gulp");
const { getModuleInfo } = require("../utils/moduleInfo");
const xml2js = require("xml2js");
const fs = require("fs");
const path = require("path");
const appConst = require("../utils/appConst");

const convertPathInPhpNamespace = ({ vendorPath, smartStructurePath }) => {
  return path
    .relative(vendorPath, smartStructurePath)
    .split(path.sep)
    .join("\\");
};

const generateSmartStructureXML = ({
  name,
  parentName,
  withClass,
  namespace
}) => {
  const structureConf = {
    "smart:config": {
      $: {
        "xmlns:smart": "https://platform.anakeen.com/4/schemas/smart/1.0"
      },
      "smart:structure-configuration": {
        $: {
          name
        },
        "smart:icon": {
          $: {
            file: `${name}.png`
          }
        }
      }
    }
  };
  if (parentName) {
    structureConf["smart:config"]["smart:structure-configuration"][
      "$"
    ].extends = parentName;
  }
  if (withClass) {
    structureConf["smart:config"]["smart:structure-configuration"][
      "smart:class"
    ] = `${namespace}\\${name}SmartStructure`;
  }
  structureConf["smart:config"]["smart:structure-configuration"][
    "smart:fields"
  ] = {};
  structureConf["smart:config"]["smart:structure-configuration"][
    "smart:hooks"
  ] = {};
  structureConf["smart:config"]["smart:structure-configuration"][
    "smart:defaults"
  ] = {};
  return structureConf;
};

const generateStructurePhp = ({ name, namespace, parentName }) => {
  let extend = "\\Anakeen\\SmartElement";
  if (parentName !== false) {
    extend = `\\SmartStructure\\${name}`;
  }
  return `<?php

namespace ${namespace};

use SmartStructure\\Fields\\${name} as ${name}Fields;

class ${name}SmartStructure extends ${extend}
{

    public function registerHooks()
    {
        parent::registerHooks();
    }
}
`;
};

const generateRenderAccess = ({ name, namespace }) => {
  return `<?php

namespace ${namespace};

class ${name}Access implements \\Dcp\\Ui\\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return \\Dcp\\Ui\\IRenderConfig
     */
    public function getRenderConfig($mode, \\Anakeen\\Core\\Internal\\SmartElement $document)
    {
        switch ($mode) {
            case \\Dcp\\Ui\\RenderConfigManager::CreateMode:
            case \\Dcp\\Ui\\RenderConfigManager::EditMode:
                return new ${name}EditRender();
            case \\Dcp\\Ui\\RenderConfigManager::ViewMode:
                return new ${name}ViewRender();
        }
        return null;
    }
}
`;
};

const generateRender = ({ name, namespace, type = "View" }) => {
  return `<?php

namespace ${namespace};

use SmartStructure\\Fields\\${name} as ${name}Fields;

class ${name}${type}Render extends \\Anakeen\\Ui\\DefaultConfig${type}Render
{

}`;
};

exports.createSmartStructure = ({
  sourcePath,
  name,
  inSelfDirectory = true,
  withRender = true,
  withClass = true,
  parentName = false,
  insertIntoInfo = true,
  smartStructurePath,
  vendorName
}) => {
  return gulp.task("createSmartStructure", async () => {
    //Get module info
    const moduleInfo = await getModuleInfo(sourcePath);
    //Check the path for the xml file
    if (!vendorName) {
      vendorName = moduleInfo.vendorName;
    }
    let srcPath;
    let vendorPath;
    let xmlPath;
    if (!smartStructurePath && !vendorName) {
      throw new Error(
        "You need to specify a vendor name or smartStructurePath"
      );
    }
    if (!smartStructurePath && vendorName) {
      //Compute and test the smartStructurePath for the vendor name
      srcPath = moduleInfo.buildInfo.buildPath.find(currentPath => {
        //Check current path
        const smartPath = path.join(
          currentPath,
          "vendor",
          vendorName,
          "SmartStructures"
        );
        try {
          return fs.statSync(smartPath).isDirectory();
        } catch (e) {
          return false;
        }
      });
      if (!srcPath) {
        throw new Error(
          `Unable to find a smartStructure path for this vendor (${vendorName}), you should create it or indicate the smartStructurePath`
        );
      }
      smartStructurePath = path.join(
        srcPath,
        "vendor",
        vendorName,
        "SmartStructures"
      );
      vendorPath = path.join(srcPath, "vendor");
    }
    //Create the directory if needed
    let directoryPromise = Promise.resolve(smartStructurePath);
    if (inSelfDirectory) {
      const smartStructureDirectory = path.join(smartStructurePath, name);
      directoryPromise = new Promise((resolve, reject) => {
        fs.mkdir(smartStructureDirectory, err => {
          if (err) {
            reject(err);
          }
          resolve(smartStructureDirectory);
        });
      });
    }
    return directoryPromise
      .then(currentPath => {
        return new Promise((resolve, reject) => {
          //Build the xml
          const builder = new xml2js.Builder();
          const xml = builder.buildObject(
            generateSmartStructureXML({
              name,
              parentName,
              withClass,
              namespace: convertPathInPhpNamespace({
                vendorPath,
                smartStructurePath: currentPath
              })
            })
          );
          //Write the xml
          xmlPath = path.join(currentPath, `${name}SmartStructure.xml`);
          fs.writeFile(xmlPath, xml, err => {
            if (err) {
              return reject(err);
            }
            resolve(currentPath);
          });
        });
      })
      .then(currentPath => {
        //Write the php if needed
        if (withClass) {
          return new Promise((resolve, reject) => {
            const structurePHP = generateStructurePhp({
              name,
              parentName,
              namespace: convertPathInPhpNamespace({
                vendorPath,
                smartStructurePath: currentPath
              })
            });
            fs.writeFile(
              path.join(currentPath, `${name}SmartStructure.php`),
              structurePHP,
              err => {
                if (err) {
                  return reject(err);
                }
                resolve(currentPath);
              }
            );
          });
        }
      })
      .then(currentPath => {
        //with render
        if (withRender) {
          //Generate the render access and two renders (one view, one edit)
          return new Promise((resolve, reject) => {
            const renderPath = path.join(currentPath, "Render");
            let createDirPromise;
            let renderPathExist;
            try {
              renderPathExist = fs.statSync(renderPath).isDirectory();
            } catch (e) {
              renderPathExist = false;
            }
            if (renderPathExist) {
              createDirPromise = Promise.resolve();
            } else {
              createDirPromise = new Promise((resolve, reject) => {
                fs.mkdir(renderPath, err => {
                  if (err) {
                    return reject(err);
                  }
                  resolve();
                });
              });
            }
            return createDirPromise.then(() => {
              const renderAccess = generateRenderAccess({
                name,
                namespace: convertPathInPhpNamespace({
                  vendorPath,
                  smartStructurePath: renderPath
                })
              });
              fs.writeFile(
                path.join(renderPath, `${name}Access.php`),
                renderAccess,
                err => {
                  if (err) {
                    return reject(err);
                  }
                  const generateRenderView = generateRender({
                    name,
                    namespace: convertPathInPhpNamespace({
                      vendorPath,
                      smartStructurePath: renderPath
                    })
                  });
                  fs.writeFile(
                    path.join(renderPath, `${name}ViewRender.php`),
                    generateRenderView,
                    err => {
                      if (err) {
                        return reject(err);
                      }
                      const generateRenderEdit = generateRender({
                        name,
                        namespace: convertPathInPhpNamespace({
                          vendorPath,
                          smartStructurePath: renderPath
                        }),
                        type: "Edit"
                      });
                      fs.writeFile(
                        path.join(renderPath, `${name}EditRender.php`),
                        generateRenderEdit,
                        err => {
                          if (err) {
                            return reject(err);
                          }
                          //Add or update json

                          resolve();
                        }
                      );
                    }
                  );
                }
              );
            });
          });
        }
      })
      .then(() => {
        //Complete the info.xml if needed
        if (insertIntoInfo) {
          const infoXMLPath = path.join(sourcePath, appConst.infoPath);
          const parser = new xml2js.Parser();
          return new Promise((resolve, reject) => {
            fs.readFile(infoXMLPath, { encoding: "utf8" }, (err, content) => {
              if (err) {
                return reject(err);
              }
              parser.parseString(content, (err, data) => {
                if (err) {
                  return reject(err);
                }
                //Add the xml to the postInstall and the postUpgrade tags
                const postInstall = data.module["post-install"];
                const postUpgrade = data.module["post-upgrade"];
                postInstall[0].process.push({
                  $: {
                    command: `./ank.php --script=importConfiguration --file=./${path.relative(
                      srcPath,
                      xmlPath
                    )}`
                  }
                });
                postUpgrade[0].process.push({
                  $: {
                    command: `./ank.php --script=importConfiguration --file=./${path.relative(
                      srcPath,
                      xmlPath
                    )}`
                  }
                });
                const builder = new xml2js.Builder();
                fs.writeFile(infoXMLPath, builder.buildObject(data), err => {
                  if (err) {
                    reject(err);
                  }
                  resolve();
                });
              });
            });
          });
        }
      });
  });
};

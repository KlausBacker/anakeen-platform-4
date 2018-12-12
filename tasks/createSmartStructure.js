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
  withBehavior,
  namespace
}) => {
  const ssNAME = name.toUpperCase();
  const ssName = name.charAt(0).toUpperCase() + name.slice(1).toLowerCase();
  const ssname = name.toLowerCase();
  const structureConf = {
    "smart:config": {
      $: {
        "xmlns:smart": "https://platform.anakeen.com/4/schemas/smart/1.0"
      },
      "smart:structure-configuration": {
        $: {
          name: ssNAME
        },
        "smart:icon": {
          $: {
            file: `${ssname}.png`
          }
        }
      }
    }
  };
  if (parentName) {
    structureConf["smart:config"]["smart:structure-configuration"][
      "smart:extends"
    ] = { $: { ref: parentName } };
  }
  if (withBehavior) {
    structureConf["smart:config"]["smart:structure-configuration"][
      "smart:class"
    ] = `${namespace}\\${ssName}Behavior`;
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

const generateSmartStructureParametersXML = ({ name }) => {
  const ssNAME = name.toUpperCase();
  const structureConf = {
    "smart:config": {
      $: {
        "xmlns:smart": "https://platform.anakeen.com/4/schemas/smart/1.0"
      },
      "smart:structure-configuration": {
        $: {
          name: ssNAME
        }
      }
    }
  };
  structureConf["smart:config"]["smart:structure-configuration"][
    "smart:parameters"
  ] = {};
  structureConf["smart:config"]["smart:structure-configuration"][
    "smart:defaults"
  ] = {};
  return structureConf;
};

const generateSmartStructureSettingsXML = ({
  name,
  workflow,
  withRender,
  namespace
}) => {
  const ssNAME = name.toUpperCase();
  const ssName = name.charAt(0).toUpperCase() + name.slice(1).toLowerCase();
  const structureConf = {
    "smart:config": {
      $: {
        "xmlns:smart": "https://platform.anakeen.com/4/schemas/smart/1.0",
        "xmlns:ui": "https://platform.anakeen.com/4/schemas/ui/1.0"
      }
    }
  };
  if (withRender) {
    structureConf["smart:config"]["ui:render"] = {
      $: {
        ref: ssNAME
      },
      "ui:render-access": {
        $: {
          class: namespace + "\\Render\\" + ssName + "Access"
        }
      }
    };
  }
  if (workflow) {
    structureConf["smart:config"]["smart:structure-configuration"] = {
      $: {
        name: ssNAME
      },
      "smart:default-workflow": {
        $: {
          ref: workflow
        }
      }
    };
  }
  return structureConf;
};

const generateStructurePhp = ({ name, namespace, parentName }) => {
  let extend = "\\Anakeen\\SmartElement";
  const ssName = name.charAt(0).toUpperCase() + name.slice(1).toLowerCase();
  if (parentName) {
    extend = `\\SmartStructures\\${ssName}`;
  }
  return `<?php

namespace ${namespace};

use SmartStructure\\Fields\\${ssName} as ${ssName}Fields;

class ${ssName}Behavior extends ${extend}
{
    public function registerHooks()
    {
        parent::registerHooks();
    }
}
`;
};

const generateRenderAccess = ({ name, namespace }) => {
  const ssName = name.charAt(0).toUpperCase() + name.slice(1).toLowerCase();
  return `<?php

namespace ${namespace};

class ${ssName}Access implements \\Dcp\\Ui\\IRenderConfigAccess
{
    /**
     * Choose good render from view or edit mode
     * @param string $mode
     * @param \\Anakeen\\Core\\Internal\\SmartElement $element
     * @return \\Dcp\\Ui\\IRenderConfig 
     */
    public function getRenderConfig($mode, \\Anakeen\\Core\\Internal\\SmartElement $element)
    {
        switch ($mode) {
            case \\Dcp\\Ui\\RenderConfigManager::CreateMode:
            case \\Dcp\\Ui\\RenderConfigManager::EditMode:
                return new ${ssName}EditRender();
            case \\Dcp\\Ui\\RenderConfigManager::ViewMode:
                return new ${ssName}ViewRender();
        }
        return null;
    }
}
`;
};

const generateRender = ({ name, namespace, type = "View" }) => {
  const ssName = name.charAt(0).toUpperCase() + name.slice(1).toLowerCase();
  return `<?php

namespace ${namespace};

use SmartStructure\\Fields\\${ssName} as ${ssName}Fields;

class ${ssName}${type}Render extends \\Anakeen\\Ui\\DefaultConfig${type}Render
{

}`;
};

exports.createSmartStructure = ({
  sourcePath,
  smartStructurePath,
  vendorName,
  moduleName,
  name,
  inSelfDirectory = true,
  withRender = true,
  withBehavior = true,
  withSettings = true,
  withParameters = true,
  withAutocompletion = true,
  workflow,
  parentName = false,
  insertIntoInfo = true
}) => {
  let srcPath = path.join(sourcePath, "src");
  let vendorPath = path.join(srcPath, "vendor");
  return gulp.task("createSmartStructure", async () => {
    //Get module info
    const moduleInfo = await getModuleInfo(sourcePath);
    //Check the path for the xml file
    if (!vendorName) {
      vendorName = moduleInfo.moduleInfo.vendor;
    }
    if (!moduleName) {
      moduleName = moduleInfo.moduleInfo.name;
    }
    let xmlStructPath;
    let xmlSettingsPath;
    let xmlParametersPath;
    if (!smartStructurePath && !vendorName) {
      throw new Error(
        "You need to specify a vendor name or smartStructurePath"
      );
    }
    if (!smartStructurePath && vendorName && moduleName) {
      //Compute and test the smartStructurePath for the vendor name
      srcPath = moduleInfo.buildInfo.buildPath.find(currentPath => {
        //Check current path
        const smartPath = path.join(
          currentPath,
          "vendor",
          vendorName,
          moduleName,
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
        moduleName,
        "SmartStructures"
      );
      vendorPath = path.join(srcPath, "vendor");
    }
    //Create the directory if needed
    let directoryPromise = Promise.resolve(smartStructurePath);
    const Name = name.charAt(0).toUpperCase() + name.slice(1).toLowerCase();
    if (inSelfDirectory) {
      const smartStructureDirectory = path.join(smartStructurePath, Name);
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
          const xmlStruct = builder.buildObject(
            generateSmartStructureXML({
              name,
              parentName,
              withBehavior,
              namespace: convertPathInPhpNamespace({
                vendorPath,
                smartStructurePath: currentPath
              })
            })
          );
          //Write the xml
          xmlStructPath = path.join(currentPath, `100-${Name}Structure.xml`);
          fs.writeFile(xmlStructPath, xmlStruct, err => {
            if (err) {
              return reject(err);
            }
            resolve(currentPath);
          });
        });
      })
      .then(currentPath => {
        if (withParameters) {
          return new Promise((resolve, reject) => {
            //Build the xml
            const builder = new xml2js.Builder();
            const xmlStruct = builder.buildObject(
              generateSmartStructureParametersXML({
                name,
                parentName,
                withBehavior,
                namespace: convertPathInPhpNamespace({
                  vendorPath,
                  smartStructurePath: currentPath
                })
              })
            );
            //Write the xml
            xmlParametersPath = path.join(
              currentPath,
              `110-${Name}Parameters.xml`
            );
            fs.writeFile(xmlParametersPath, xmlStruct, err => {
              if (err) {
                return reject(err);
              }
              resolve(currentPath);
            });
          });
        }
        return currentPath;
      })
      .then(currentPath => {
        //Write the php if needed
        if (withBehavior) {
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
              path.join(currentPath, `${Name}Behavior.php`),
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
        return currentPath;
      })
      .then(currentPath => {
        // With settings
        if (withSettings) {
          return new Promise((resolve, reject) => {
            const settingsPath = path.join(currentPath, `${Name}Settings`);
            fs.mkdir(settingsPath, err => {
              if (err) {
                reject(err);
              } else {
                //Build the xml
                const builder = new xml2js.Builder();
                const xmlData = builder.buildObject(
                  generateSmartStructureSettingsXML({
                    name,
                    workflow,
                    withRender,
                    namespace: convertPathInPhpNamespace({
                      vendorPath,
                      smartStructurePath: currentPath
                    })
                  })
                );
                //Write the xml
                xmlSettingsPath = path.join(
                  currentPath,
                  `500-${Name}Settings.xml`
                );
                fs.writeFile(xmlSettingsPath, xmlData, err => {
                  if (err) {
                    return reject(err);
                  }
                  resolve(currentPath);
                });
              }
            });
          });
        }
        return currentPath;
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
                  resolve(currentPath);
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
                path.join(renderPath, `${Name}Access.php`),
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
                    path.join(renderPath, `${Name}ViewRender.php`),
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
                        path.join(renderPath, `${Name}EditRender.php`),
                        generateRenderEdit,
                        err => {
                          if (err) {
                            return reject(err);
                          }
                          //Add or update json

                          resolve(currentPath);
                        }
                      );
                    }
                  );
                }
              );
            });
          });
        }
        return currentPath;
      })
      .then(currentPath => {
        // With autocompletion
        if (withAutocompletion) {
          return new Promise((resolve, reject) => {
            const autocompletionPath = path.join(
              currentPath,
              `${Name}Autocompletion`
            );
            fs.mkdir(autocompletionPath, err => {
              if (err) {
                reject(err);
              } else {
                resolve(currentPath);
              }
            });
          });
        }
        return currentPath;
      })
      .then(currentPath => {
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

                if (!postInstall[0].process) {
                  postInstall[0]["process"] = [];
                  if (!postInstall[0].process) {
                    // case of no empty tag
                    postInstall[0] = { process: [] };
                  }
                }
                if (!postUpgrade[0].process) {
                  postUpgrade[0].process = [];
                  if (!postUpgrade[0].process) {
                    // case of no empty tag
                    postUpgrade[0] = { process: [] };
                  }
                }

                postInstall[0].process.push({
                  $: {
                    command: `./ank.php --script=importConfiguration --glob=./${path.relative(
                      srcPath,
                      currentPath
                    )}/**/*.xml`
                  }
                });

                postUpgrade[0].process.push({
                  $: {
                    command: `./ank.php --script=importConfiguration --glob=./${path.relative(
                      srcPath,
                      currentPath
                    )}/**/*.xml`
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

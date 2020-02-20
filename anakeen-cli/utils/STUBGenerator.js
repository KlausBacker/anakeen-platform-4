const xml2js = require("xml2js");
const path = require("path");
const fs = require("fs");
const babelParser = require("@babel/parser");
const prettier = require("prettier");
const libxml = require("libxmljs");

const mustache = require("mustache");
const { parseAndConcatGlob } = require("../utils/globAnalyze");

const attrType = require("./appConst").sfType;

const generateDescription = attr => {
  const field = attr.field;
  if (!field) {
    return "";
  }
  let desc = "        /**\n";
  if (attr.type) {
    field.type = attr.type;
  }
  if (attr.kind) {
    field.kind = attr.kind;
  }
  if (field.label) {
    desc += `        * ${field.label}\n`;
  }
  desc += `        * <ul>\n`;
  Object.keys(field).forEach(currentKey => {
    if (currentKey !== "name" && currentKey !== "label") {
      desc += `        * <li> <i>${currentKey}</i> ${field[currentKey]} </li>\n`;
    }
  });
  desc += `        * </ul>\n`;
  desc += "        */ \n";
  desc += `        const ${field.name}='${field.name}';\n`;
  return desc;
};

const extractSmartField = (fields, currentFilePath, kind = null) => {
  return Object.keys(fields).reduce((acc, currentKey) => {
    if (currentKey === "$") {
      //We are on the properties
      acc[fields[currentKey].name] = {
        field: fields[currentKey],
        fileName: currentFilePath,
        kind
      };
      return acc;
    }

    //If the current element is a fieldset, we iterate on sub element
    if (currentKey === "fieldset") {
      const subFields = fields[currentKey].reduce((acc, currentSubField) => {
        const subElement = extractSmartField(currentSubField, currentFilePath, kind);
        return {
          ...acc,
          ...subElement
        };
      }, {});
      return {
        ...acc,
        ...subFields
      };
    }

    //Suppress all non field elements
    if (!attrType[currentKey]) {
      return acc;
    }

    //If we are here, this is a non scalar field
    const nonScalarFields = fields[currentKey].reduce((acc, currentAttr) => {
      if (currentAttr.$) {
        acc[currentAttr.$.name] = {
          field: currentAttr.$,
          type: attrType[currentKey],
          fileName: currentFilePath,
          kind
        };
      }
      return acc;
    }, {});

    acc = {
      ...acc,
      ...nonScalarFields
    };

    return acc;
  }, {});
};

const upperCaseFirstLetter = function(str) {
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

exports.parseStub = ({ globFile, info, targetPath, log, verbose }) => {
  //Find all the files in glob rules
  const srcPath = info.sourcePath;
  return parseAndConcatGlob({ globFile, srcPath }).then(files => {
    if (verbose) {
      files.ignoredFiles.forEach(currentFile => {
        log(`Analyze : ${currentFile} : in ignore conf`);
      });
    }

    // Init js config parameters
    const configs = info.buildInfo.build.config["stub-config"];
    let jsConfig = null;
    if (configs) {
      const config = configs[0]["stub-struct-js-config"];
      if (config) {
        jsConfig = {
          target: config[0].$.target || "./stubs/fields.js",
          imports: []
        };

        const jsImports = config[0]["stub-js-import"];
        let dirPath = path.dirname(jsConfig.target);
        if (!fs.existsSync(dirPath)) {
          fs.mkdirSync(dirPath);
        }

        if (jsImports) {
          let parentDirs = path
            .dirname(jsConfig.target)
            .split(path.sep)
            .filter(dirPath => {
              return dirPath !== ".";
            })
            .map(() => {
              return "..";
            });

          jsImports.forEach(importItem => {
            let sourceParts = importItem.$.source.split(path.sep);
            let sourcerelativePath = parentDirs.concat(sourceParts).join(path.sep);
            let sourcePath = importItem.$.source;

            if (!fs.existsSync(sourcePath)) {
              let nodePath = require.resolve(sourcePath);
              if (nodePath) {
                sourcerelativePath = sourcePath;
                sourcePath = nodePath;
              } else {
                throw new Error(`Unable to find the stub js import source "${sourcePath}"`);
              }
            }

            jsConfig.imports.push({
              name: importItem.$.name,
              source: sourcePath,
              relativeSource: sourcerelativePath,
              exportedClass: []
            });
          });

          jsConfig.imports.forEach(jsConfigItem => {
            const jsContent = fs.readFileSync(jsConfigItem.source, {
              encoding: "utf8"
            });
            let parse = babelParser.parse(jsContent, {
              sourceType: "module",
              plugins: ["classProperties"]
            });
            parse.program.body.forEach(parseNode => {
              if (parseNode.type === "ExportNamedDeclaration") {
                jsConfigItem.exportedClass.push(parseNode.declaration.id.name);
              }
            });
          });
        }
      }
    }

    //Analyze all the files
    const stripPrefix = xml2js.processors.stripPrefix;
    const cleanDash = str => {
      return str.replace("-", "");
    };
    return Promise.all(
      files.filesToAnalyze.map(currentFilePath => {
        return new Promise((resolve, reject) => {
          if (verbose) {
            log(`Analyze : ${currentFilePath} : ✓`);
          }
          let values = {};
          xml2js.parseString(
            fs.readFileSync(currentFilePath, {
              encoding: "utf8"
            }),
            { tagNameProcessors: [stripPrefix, cleanDash] },
            (err, result) => {
              //Analyze all the enums and organize them
              if (err) {
                reject(err);
                return;
              }
              //Analyze structure configuration
              if (result.config && result.config.structureconfiguration) {
                values = result.config.structureconfiguration.reduce((acc, currentConf) => {
                  const name = currentConf.$.name;
                  if (
                    (!currentConf.fields || !currentConf.fields.length || currentConf.fields.length === 0) &&
                    (!currentConf.parameters || !currentConf.parameters.length || currentConf.parameters.length === 0)
                  ) {
                    return acc;
                  }
                  let elements = {};
                  if (currentConf.fields) {
                    elements = currentConf.fields.reduce((acc, currentField) => {
                      const fields = extractSmartField(currentField, currentFilePath);
                      return {
                        ...acc,
                        ...fields
                      };
                    }, elements);
                  }
                  if (currentConf.parameters) {
                    elements = currentConf.parameters.reduce((acc, currentField) => {
                      const fields = extractSmartField(currentField, currentFilePath, "Parameter");
                      return {
                        ...acc,
                        ...fields
                      };
                    }, elements);
                  }
                  //Enhance elements with missing properties
                  const extend = currentConf.extends ? currentConf.extends[0].$.ref : false;

                  let currentClass = currentConf.class !== undefined ? currentConf.class[0] : false;

                  if (currentClass && currentClass._) {
                    currentClass = currentClass._;
                  }

                  const smartStructure = {
                    name,
                    fields: elements
                  };

                  if (extend) {
                    smartStructure.extend = extend;
                  }

                  if (currentClass) {
                    smartStructure.currentClass = currentClass;
                  }

                  if (acc[name]) {
                    acc[name] = { ...acc[name], ...smartStructure };
                  } else {
                    acc[name] = smartStructure;
                  }
                  return acc;
                }, values);
              }
              resolve(values);
            }
          );
        });
      })
    )
      .then(allElementsFound => {
        //Merge all elements found
        return allElementsFound.reduce((acc, currentEnumsDef) => {
          const currentKeys = Object.keys(currentEnumsDef);
          //Merge elements
          currentKeys.forEach(currentKey => {
            if (acc[currentKey] && "fields" in acc[currentKey]) {
              acc[currentKey].fields = {
                ...acc[currentKey].fields,
                ...currentEnumsDef[currentKey].fields
              };
            } else {
              acc[currentKey] = currentEnumsDef[currentKey];
            }
          });
          return acc;
        }, {});
      })
      .then(allSmartStructures => {
        // Write PHP file stubs
        Object.values(allSmartStructures).map(currentSS => {
          const fieldsString = Object.values(currentSS.fields).reduce((acc, fieldset) => {
            return acc + generateDescription(fieldset);
          }, "");

          //Extends for field part
          const extendsPart = currentSS.extend ? ` extends ${upperCaseFirstLetter(currentSS.extend)}` : "";

          //Extend for class part
          let extendsSSPart = "";

          if (currentSS.currentClass) {
            extendsSSPart = currentSS.currentClass;
            //Add missing \ as first char if it's not already the case
            if (extendsSSPart[0] !== "\\") {
              extendsSSPart = "\\" + extendsSSPart;
            }
          }

          if (!extendsSSPart && currentSS.extend) {
            //the current structure extends another one
            extendsSSPart = currentSS.extend;
          }

          let content = `<?php

namespace SmartStructure {

    class ${upperCaseFirstLetter(currentSS.name)} extends ${extendsSSPart || "\\Anakeen\\SmartElement"}
    {
        const familyName = "${currentSS.name}";
    }
}

namespace SmartStructure\\Fields {

    class ${upperCaseFirstLetter(currentSS.name)}${extendsPart}
    {
${fieldsString}
    }
}`;
          fs.writeFileSync(path.join(targetPath, currentSS.name + "__STUB.php"), content);
          return currentSS;
        });
        return allSmartStructures;
      })
      .then(allSmartStructures => {
        // Write JS file stubs
        const getInheritanceDepth = name => {
          const structs = Object.values(allSmartStructures).filter(struct => {
            return struct.name === name;
          });
          if (structs.length === 1) {
            const fStruct = structs[0];
            if (fStruct.extend) {
              return 1 + getInheritanceDepth(fStruct.extend);
            } else {
              return 0;
            }
          } else {
            return 0;
          }
        };

        Object.values(allSmartStructures).map(currentSS => {
          if (currentSS.extend) {
            currentSS.inheritanceDepth = 1 + getInheritanceDepth(currentSS.extend);
          } else {
            currentSS.inheritanceDepth = 0;
          }
        });

        let smartStubsData = Object.values(allSmartStructures)
          // Need to order by inheritance because of javascript linear interpretation
          .sort((struct1, struct2) => {
            if (struct1.inheritanceDepth === struct2.inheritanceDepth) {
              if (struct1.name > struct2.name) {
                return 1;
              } else if (struct1.name < struct2.name) {
                return -1;
              }
              return 0;
            }
            if (struct1.inheritanceDepth > struct2.inheritanceDepth) {
              return 1;
            }
            if (struct1.inheritanceDepth < struct2.inheritanceDepth) {
              return -1;
            }
            return 0;
          })
          .map(currentSS => {
            let fields = Object.values(currentSS.fields)
              .map(item => {
                return item.field;
              })
              .filter(item => {
                return item.extended !== "true";
              });
            let extendName = currentSS.extend;
            if (jsConfig && jsConfig.imports && currentSS.extend) {
              let findExport = jsConfig.imports.filter(jsConfigItem => {
                return jsConfigItem.exportedClass.indexOf(currentSS.extend) >= 0;
              });
              if (findExport.length > 0) {
                extendName = findExport[0].name + "." + currentSS.extend;
              }
            }

            return {
              structureName: currentSS.name,
              inheritanceDepth: currentSS.inheritanceDepth,
              extend: extendName,
              fields: fields
            };
          });

        const tplJS = fs.readFileSync(__dirname + "/templates/fields.js.mustache", { encoding: "utf8" });

        if (jsConfig) {
          if (verbose) log("Write JS stub file: " + jsConfig.target);
          let jsContent = mustache.render(tplJS, { config: jsConfig, structures: smartStubsData });
          jsContent = prettier.format(jsContent, { semi: true, parser: "babel" });
          fs.writeFileSync(path.join(jsConfig.target), jsContent);
        }
        return Promise.all(smartStubsData);
      });
  });
};
exports.parseWorkflowContants = ({ globFile, info, log, verbose }) => {
  //Find all the files in glob rules
  const srcPath = info.sourcePath;
  return parseAndConcatGlob({ globFile, srcPath }).then(files => {
    if (verbose) {
      files.ignoredFiles.forEach(currentFile => {
        log(`Analyze : ${currentFile} : in ignore conf`);
      });
    }

    // Init js config parameters
    const configs = info.buildInfo.build.config["stub-config"];
    let jsConfig = null;
    if (configs) {
      const config = configs[0]["stub-wfl-js-config"];
      if (config) {
        jsConfig = {
          target: config[0].$.target || "./constants/workflows/",
          imports: []
        };

        const jsImports = config[0]["stub-js-import"];
        let dirPath = jsConfig.target;
        if (!fs.existsSync(dirPath)) {
          fs.mkdirSync(dirPath);
        }

        if (jsImports) {
          let parentDirs = path
            .dirname(jsConfig.target)
            .split(path.sep)
            .filter(dirPath => {
              return dirPath !== ".";
            })
            .map(() => {
              return "..";
            });

          jsImports.forEach(importItem => {
            let sourceParts = importItem.$.source.split(path.sep);
            let sourcerelativePath = parentDirs.concat(sourceParts).join(path.sep);
            let sourcePath = importItem.$.source;

            if (!fs.existsSync(sourcePath)) {
              let nodePath = require.resolve(sourcePath);
              if (nodePath) {
                sourcerelativePath = sourcePath;
                sourcePath = nodePath;
              } else {
                throw new Error(`Unable to find the stub js import source "${sourcePath}"`);
              }
            }

            jsConfig.imports.push({
              name: importItem.$.name,
              source: sourcePath,
              relativeSource: sourcerelativePath,
              exportedClass: []
            });
          });

          jsConfig.imports.forEach(jsConfigItem => {
            const jsContent = fs.readFileSync(jsConfigItem.source, {
              encoding: "utf8"
            });
            let parse = babelParser.parse(jsContent, {
              sourceType: "module",
              plugins: ["classProperties"]
            });
            parse.program.body.forEach(parseNode => {
              if (parseNode.type === "ExportNamedDeclaration") {
                jsConfigItem.exportedClass.push(parseNode.declaration.id.name);
              }
            });
          });
        }
      }
    }

    //Analyze Workflow  files
    return (
      Promise.all(
        files.filesToAnalyze.map(currentFilePath => {
          return new Promise((resolve, reject) => {
            if (verbose) {
              log(`Analyze : ${currentFilePath} : ✓`);
            }
            let values = {};
            let xmlContent = fs.readFileSync(currentFilePath, {
              encoding: "utf8"
            });

            const xmlDOM = libxml.parseXml(xmlContent);
            const wflNs = { wfl: "https://platform.anakeen.com/4/schemas/workflow/1.0" };
            const name = xmlDOM.get("string(//wfl:graph/@name)", wflNs);
            const ns = xmlDOM.get("string(//wfl:graph/@ns)", wflNs);
            const key = ns + "-" + name;

            values[key] = {
              file: currentFilePath,
              label: xmlDOM.get("string(//wfl:graph/@label)", wflNs),
              name: name,
              ns: ns,
              steps: [],
              transitions: []
            };

            if (!name) {
              values[key].error = "No name attribute in workflow file";
              reject(values);
            }

            const attrValue = (nodeObject, attrName) => {
              if (nodeObject.attr(attrName)) {
                return nodeObject.attr(attrName).value();
              }
              return "";
            };
            const steps = xmlDOM.find("//wfl:step", wflNs);
            steps.forEach(step => {
              values[key].steps.push({
                name: attrValue(step, "name"),
                stateLabel: attrValue(step, "state-label"),
                activityLabel: attrValue(step, "activity-label"),
                initial: attrValue(step, "initial") === "true"
              });
            });

            const transitions = xmlDOM.find("//wfl:transition", wflNs);
            transitions.forEach(transition => {
              values[key].transitions.push({
                name: attrValue(transition, "name"),
                label: attrValue(transition, "label"),
                from: attrValue(transition, "from"),
                to: attrValue(transition, "to")
              });
            });

            resolve(values);
          });
        })
      )
        // Set all data to generate workflow files
        .then(allWorkflowFound => {
          //Merge all elements found

          // Write PHP file stubs
          allWorkflowFound.forEach(workflowData => {
            const workflow = Object.values(workflowData)[0];
            let pathParts = path.dirname(workflow.file).split(path.sep);
            let baseName = path.basename(workflow.file);
            workflow.classname = baseName.replace(/\.[^.]+$/, "");

            let vendorIndex = pathParts.indexOf("vendor");
            if (vendorIndex >= 0) {
              workflow.namespace = pathParts.slice(vendorIndex + 1).join("\\");
            }
            let stepLabels = {};
            workflow.steps.forEach(step => {
              step.label = step.stateLabel;
              if (step.activityLabel) {
                step.label += " - " + step.activityLabel;
              }
              step.label.replace("*", "-");
              stepLabels[step.name] = step.label;
            });

            // Need to concatenate multiple use of same transition models
            let transitionModels = {};
            workflow.transitions.forEach(transition => {
              if (transition.to) {
                transition.toLabel = stepLabels[transition.to] || "";
              }
              if (transition.from) {
                transition.fromLabel = stepLabels[transition.from] || "";
              }
              if (!transitionModels[transition.name]) {
                transitionModels[transition.name] = {
                  name: transition.name,
                  label: transition.label,
                  arrows: []
                };
              }
              transitionModels[transition.name].arrows.push({
                to: transition.to,
                toLabel: transition.toLabel,
                from: transition.from,
                fromLabel: transition.fromLabel
              });
            });

            workflow.transitionModels = Object.values(transitionModels);
          });
          return allWorkflowFound;
        })
        // Generate constants workflow PHP file
        .then(allWorkflowData => {
          const tplPHP = fs.readFileSync(__dirname + "/templates/workflow.php.mustache", { encoding: "utf8" });

          // Write PHP file constants
          allWorkflowData.forEach(workflowData => {
            const workflow = Object.values(workflowData)[0];

            let pathParts = path.dirname(workflow.file).split(path.sep);
            let phpBaseName = workflow.classname + "Constant.php";
            let target = pathParts.concat([phpBaseName]).join(path.sep);
            let phpContent = mustache.render(tplPHP, workflow);
            fs.writeFileSync(target, phpContent);
            if (verbose) log("Write PHP workflow constants file: " + target);
          });
          return Promise.all(allWorkflowData);
        })
        // Generate constants workflow JS file
        .then(allWorkflowData => {
          // Write JS file stubs
          const tplJS = fs.readFileSync(__dirname + "/templates/workflow.js.mustache", { encoding: "utf8" });

          allWorkflowData.forEach(workflowData => {
            const workflow = Object.values(workflowData)[0];
            let jsBaseName = workflow.classname + ".js";
            let target = jsConfig.target.concat(jsBaseName);
            let jsContent = mustache.render(tplJS, workflow);
            jsContent = prettier.format(jsContent, { semi: true, parser: "babel" });

            fs.writeFileSync(target, jsContent);
            if (verbose) log("Write JS workflow constants file: " + target);
          });
          return Promise.all(allWorkflowData);
        })
    );
  });
};

exports.parseEnumContants = ({ globFile, info, log, verbose }) => {
  //Find all the files in glob rules
  const srcPath = info.sourcePath;
  return parseAndConcatGlob({ globFile, srcPath }).then(files => {
    if (verbose) {
      files.ignoredFiles.forEach(currentFile => {
        log(`Analyze : ${currentFile} : in ignore conf`);
      });
    }
    // Init js config parameters
    const configs = info.buildInfo.build.config["stub-config"];
    let jsConfig = null;
    if (configs) {
      const config = configs[0]["stub-enum-js-config"];
      if (config) {
        jsConfig = {
          target: config[0].$.target || "./constants/enumerates",
          imports: []
        };

        const jsImports = config[0]["stub-js-import"];
        let dirPath = jsConfig.target;
        if (!fs.existsSync(dirPath)) {
          fs.mkdirSync(dirPath);
        }

        if (jsImports) {
          let parentDirs = path
            .dirname(jsConfig.target)
            .split(path.sep)
            .filter(dirPath => {
              return dirPath !== ".";
            })
            .map(() => {
              return "..";
            });

          jsImports.forEach(importItem => {
            let sourceParts = importItem.$.source.split(path.sep);
            let sourcerelativePath = parentDirs.concat(sourceParts).join(path.sep);
            let sourcePath = importItem.$.source;

            if (!fs.existsSync(sourcePath)) {
              let nodePath = require.resolve(sourcePath);
              if (nodePath) {
                sourcerelativePath = sourcePath;
                sourcePath = nodePath;
              } else {
                throw new Error(`Unable to find the stub js import source "${sourcePath}"`);
              }
            }

            jsConfig.imports.push({
              name: importItem.$.name,
              source: sourcePath,
              relativeSource: sourcerelativePath,
              exportedClass: []
            });
          });

          jsConfig.imports.forEach(jsConfigItem => {
            const jsContent = fs.readFileSync(jsConfigItem.source, {
              encoding: "utf8"
            });
            let parse = babelParser.parse(jsContent, {
              sourceType: "module",
              plugins: ["classProperties"]
            });
            parse.program.body.forEach(parseNode => {
              if (parseNode.type === "ExportNamedDeclaration") {
                jsConfigItem.exportedClass.push(parseNode.declaration.id.name);
              }
            });
          });
        }
      }
    }

    //Analyze Enum  files
    return (
      Promise.all(
        files.filesToAnalyze.map(currentFilePath => {
          return new Promise((resolve, reject) => {
            if (verbose) {
              log(`Analyze : ${currentFilePath} : ✓`);
            }
            let values = {};
            let xmlContent = fs.readFileSync(currentFilePath, {
              encoding: "utf8"
            });

            const xmlDOM = libxml.parseXml(xmlContent);
            const smartNs = { smart: "https://platform.anakeen.com/4/schemas/smart/1.0" };

            const enumconfigNodes = xmlDOM.find("//smart:enum-configuration", smartNs);

            enumconfigNodes.forEach(enumConfigNode => {
              const key = enumConfigNode.attr("name").value();

              if (!key) {
                values.error = "No name attribute in enum file" + currentFilePath;
                reject(values);
              }
              const attrValue = (nodeObject, attrName) => {
                if (nodeObject.attr(attrName)) {
                  return nodeObject.attr(attrName).value();
                }
                return "";
              };
              const isGoodName = name => {
                if (name.match(/^[\p{L}_][\p{L}0-9_]*$/gu)) {
                  if (!name.match(/^(class)$/giu)) {
                    return true;
                  }
                }
                return false;
              };
              const items = enumConfigNode.find(".//smart:enum", smartNs);
              const enumItems = [];
              items.forEach(item => {
                const itemName = attrValue(item, "name");
                const itemLabel = attrValue(item, "label");

                if (itemName.trim() !== "") {
                  const isGood = isGoodName(itemName);
                  enumItems.push({
                    name: itemName,
                    label: itemLabel,
                    isGood: isGood,
                    escapeName: itemName.replace(/"/gu, '\\"'),
                    escapeLabel: itemLabel.replace(/\*\//gu, "*")
                  });
                  if (!isGood) {
                    log(`Skip item "${itemName}" from "${key}" enum set.`, "warning");
                  }
                }
              });

              values[key] = {
                file: currentFilePath,
                name: key,
                items: enumItems
              };
            });

            resolve(values);
          });
        })
      )
        .then(allEnumFound => {
          return allEnumFound.reduce((acc, item) => {
            return acc.concat(Object.values(item));
          }, []);
        })

        // Generate constants enum PHP file
        .then(allEnumData => {
          const tplPHP = fs.readFileSync(__dirname + "/templates/enum.php.mustache", { encoding: "utf8" });

          // Write PHP file constants
          allEnumData.forEach(enumData => {
            let pathParts = path.dirname(enumData.file).split(path.sep);
            enumData.classname = enumData.name.replace(/-/g, "_");
            let targetBaseName = enumData.classname + "Constant.php";
            let target = pathParts.concat([targetBaseName]).join(path.sep);
            let vendorIndex = pathParts.indexOf("vendor");
            if (vendorIndex >= 0) {
              enumData.namespace = pathParts.slice(vendorIndex + 1).join("\\");
            }

            fs.writeFileSync(target, mustache.render(tplPHP, enumData));
            if (verbose) log("Write PHP enum constants file: " + target);
          });
          return Promise.all(allEnumData);
        })
        // Generate constants enum JS file
        .then(allEnumData => {
          // Write JS file stubs
          const tplJS = fs.readFileSync(__dirname + "/templates/enum.js.mustache", { encoding: "utf8" });

          allEnumData.forEach(enumData => {
            let jsBaseName = enumData.classname + ".js";
            let target = jsConfig.target.concat(jsBaseName);
            let jsContent = mustache.render(tplJS, enumData);
            jsContent = prettier.format(jsContent, { semi: true, parser: "babel" });

            fs.writeFileSync(target, jsContent);
            if (verbose) log("Write JS enum constants file: " + target);
          });
          return Promise.all(allEnumData);
        })
    );
  });
};

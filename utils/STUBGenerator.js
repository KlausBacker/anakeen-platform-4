const xml2js = require("xml2js");
const path = require("path");
const fs = require("fs");
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
  if (field.label) {
    desc += `        * ${field.label}\n`;
  }
  desc += `        * <ul>\n`;
  Object.keys(field).forEach(currentKey => {
    if (currentKey !== "name" && currentKey !== "label") {
      desc += `        * <li> <i>${currentKey}</i> ${
        field[currentKey]
      } </li>\n`;
    }
  });
  desc += `        * </ul>\n`;
  desc += "        */ \n";
  desc += `        const ${field.name}='${field.name}';\n`;
  return desc;
};

const extractSmartField = (fields, currentFilePath) => {
  return Object.keys(fields).reduce((acc, currentKey) => {
    if (currentKey === "$") {
      //We are on the properties
      acc[fields[currentKey].name] = {
        field: fields[currentKey],
        fileName: currentFilePath
      };
      return acc;
    }

    //If the current element is a fieldset, we iterate on sub element
    if (currentKey === "fieldset") {
      const subFields = fields[currentKey].reduce((acc, currentSubField) => {
        const subElement = extractSmartField(currentSubField, currentFilePath);
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
          fileName: currentFilePath
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
              if (result.config.structureconfiguration) {
                values = result.config.structureconfiguration.reduce(
                  (acc, currentConf) => {
                    const name = currentConf.$.name;
                    if (
                      !currentConf.fields ||
                      !currentConf.fields.length ||
                      currentConf.fields.length === 0
                    ) {
                      return acc;
                    }
                    const elements = currentConf.fields.reduce(
                      (acc, currentField) => {
                        const fields = extractSmartField(
                          currentField,
                          currentFilePath
                        );
                        return {
                          ...acc,
                          ...fields
                        };
                      },
                      {}
                    );
                    //Enhance elements with missing properties
                    const extend = currentConf.extends
                      ? currentConf.extends[0].$.ref
                      : false;

                    let currentClass =
                      currentConf.class !== undefined
                        ? currentConf.class[0]
                        : false;

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
                  },
                  values
                );
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
            if (acc[currentKey]) {
              acc[currentKey] = {
                ...acc[currentKey],
                ...currentEnumsDef[currentKey]
              };
            } else {
              acc[currentKey] = currentEnumsDef[currentKey];
            }
          });
          return acc;
        }, {});
      })
      .then(allSmartStructures => {
        return Promise.all(
          Object.values(allSmartStructures).map(currentSS => {
            const fieldsString = Object.values(currentSS.fields).reduce(
              (acc, fieldset) => {
                return acc + generateDescription(fieldset);
              },
              ""
            );

            //Extends for field part
            const extendsPart = currentSS.extend
              ? ` extends ${upperCaseFirstLetter(currentSS.extend)}`
              : "";

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

    class ${upperCaseFirstLetter(currentSS.name)} extends ${extendsSSPart ||
              "\\Anakeen\\SmartElement"}
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
            fs.writeFileSync(
              path.join(targetPath, currentSS.name + "__STUB.php"),
              content
            );
          })
        );
      });
  });
};

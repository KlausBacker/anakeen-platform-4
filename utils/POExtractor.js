const xml2js = require("xml2js");
const path = require("path");
const fs = require("fs");

const cp = require("child_process");
const { parseAndConcatGlob } = require("../utils/globAnalyze");

const PO_LANGS = require("./appConst").po_langs;

const attrType = require("./appConst").sfType;

/**
 * Create the string of a po entry
 * @param attr
 * @param info
 * @param currentFilePath
 * @returns {string}
 */
const getPoEntry = (attr, info, currentFilePath) => {
  if (!attr) {
    return "";
  }
  let label = "";
  if (attr.label) {
    label = attr.label.replace(/"/g, '\\"');
  }
  if (!currentFilePath && attr.fileName) {
    currentFilePath = attr.fileName;
  }
  return `\n#: ${currentFilePath}\n#, fuzzy\nmsgctxt "${attr.context ||
    info.name}"\nmsgid "${attr.name}"\nmsgstr "${label}"\n`;
};

/**
 * Extract fields from an xml
 *
 * @param fields
 * @param currentFilePath
 * @returns {{}}
 */
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

/**
 * Convert all the found keys into a pot file
 *
 * @param fieldKey
 * @param filePrefix
 * @param potPath
 * @returns {function(*=): Array}
 */
const convertKeysToPot = ({ fieldKey, filePrefix, potPath }) => {
  return elements => {
    //Generate files from keys
    const filesCreated = [];
    Object.keys(elements).forEach(currentKey => {
      const poEntries = Object.values(elements[currentKey]).reduce(
        (acc, currentEntry) => {
          return (
            acc +
            getPoEntry(
              currentEntry[fieldKey],
              { name: currentKey },
              currentEntry.fileName
            )
          );
        },
        ""
      );
      let now = new Date().toISOString();
      let content = `msgid ""
msgstr ""
"Project-Id-Version: ${filePrefix} ${currentKey} \\n"
"Report-Msgid-Bugs-To: \\n"
"PO-Revision-Date: ${now}\\n"
"Last-Translator: Automatically generated\\n"
"Language-Team: none\\n"
"Language: fr\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"

${poEntries}
`;
      if (poEntries.length > 0) {
        //If there is something, we write the temp pot file
        const potFile = path.join(potPath, `${filePrefix}${currentKey}.pot`);
        fs.writeFileSync(potFile, content);
        filesCreated.push({
          path: potFile,
          smartName: currentKey
        });
      }
    });
    return filesCreated;
  };
};

/**
 * Merge all keys found in the files in one object
 *
 * @param elements
 * @returns {*}
 */
const mergeAllKeysFound = elements => {
  //Merge all enums found
  return elements.reduce((acc, currentElement) => {
    const currentKeys = Object.keys(currentElement);
    //Merge elements
    currentKeys.forEach(currentKey => {
      if (acc[currentKey]) {
        acc[currentKey] = {
          ...acc[currentKey],
          ...currentElement[currentKey]
        };
      } else {
        acc[currentKey] = currentElement[currentKey];
      }
    });
    return acc;
  }, {});
};

/**
 * Parse the glob of the structure and return the temp file to merge
 *
 * @param poGlob
 * @param info
 * @param potPath
 * @returns {Promise<Array | never>}
 */
exports.xmlStructure2Pot = ({ globFile, info, potPath, verbose, log }) => {
  //Find all the files in glob rules
  const srcPath = info.buildInfo.buildPath[0];
  return parseAndConcatGlob({ globFile, srcPath }).then(allFilesFound => {
    if (verbose) {
      allFilesFound.ignoredFiles.forEach(currentFile => {
        log(`Analyze : ${currentFile} : in ignore conf`);
      });
    }
    //Analyze all the files
    const stripPrefix = xml2js.processors.stripPrefix;
    const cleanDash = str => {
      return str.replace("-", "");
    };
    return Promise.all(
      allFilesFound.filesToAnalyze.map(currentFilePath => {
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
                    let elements = {};
                    if (
                      currentConf.fields &&
                      currentConf.fields.length &&
                      currentConf.fields.length > 0
                    ) {
                      elements = currentConf.fields.reduce(
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
                    }
                    if (
                      currentConf.parameters &&
                      currentConf.parameters.length &&
                      currentConf.parameters.length > 0
                    ) {
                      elements = currentConf.parameters.reduce(
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
                        elements
                      );
                    }
                    if (acc[name]) {
                      acc[name] = { ...acc[name], ...elements };
                    } else {
                      acc[name] = elements;
                    }
                    if (!acc[name].title) {
                      acc[name].title = {
                        field: { label: "", name: "title" },
                        fileName: currentFilePath
                      };
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
      .then(mergeAllKeysFound)
      .then(
        convertKeysToPot({ fieldKey: "field", filePrefix: "smart", potPath })
      );
  });
};

/**
 * Parse the glob of the enum and return the temp files to merge
 *
 * @param poGlob
 * @param info
 * @param potPath
 * @returns {Promise<Array | never>}
 */
exports.xmlEnum2Pot = ({ globFile, info, potPath, verbose, log }) => {
  //Find all the files in glob rules
  const srcPath = info.sourcePath;
  return parseAndConcatGlob({ globFile, srcPath }).then(allFilesFound => {
    //Analyze all the files
    const stripPrefix = xml2js.processors.stripPrefix;
    const cleanDash = str => {
      return str.replace("-", "");
    };
    if (verbose) {
      allFilesFound.ignoredFiles.forEach(currentFile => {
        log(`Analyze : ${currentFile} : in ignore conf`);
      });
    }
    return Promise.all(
      allFilesFound.filesToAnalyze.map(currentFilePath => {
        return new Promise((resolve, reject) => {
          if (verbose) {
            log(`Analyze : ${currentFilePath} : ✓`);
          }
          let enums = {};
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
              if (result.config.enumerates) {
                enums = result.config.enumerates.reduce((acc, enumMainTag) => {
                  //If no enum conf, return result and go to the next
                  if (!enumMainTag.enumconfiguration) {
                    return acc;
                  }
                  return enumMainTag.enumconfiguration.reduce(
                    (acc, currentConf) => {
                      const enumName = currentConf.$.name;
                      const enums = currentConf.enum.reduce(
                        (acc, currentField) => {
                          acc[currentField.$.name] = {
                            enumItem: currentField.$,
                            fileName: currentFilePath
                          };
                          return acc;
                        },
                        {}
                      );
                      if (acc[enumName]) {
                        acc[enumName] = { ...acc[enumName], ...enums };
                      } else {
                        acc[enumName] = enums;
                      }
                      return acc;
                    },
                    acc
                  );
                }, enums);
              }

              resolve(enums);
            }
          );
        });
      })
    )
      .then(mergeAllKeysFound)
      .then(
        convertKeysToPot({ fieldKey: "enumItem", filePrefix: "enum", potPath })
      );
  });
};

/**
 * Parse the glob of the CVDOC and return the temp files to merge
 *
 * @param poGlob
 * @param info
 * @param potPath
 * @returns {Promise<Array | never>}
 */
exports.xmlCVDOC2Pot = ({ globFile, info, potPath, verbose, log }) => {
  //Find all the files in glob rules
  const srcPath = info.sourcePath;
  return parseAndConcatGlob({ globFile, srcPath }).then(allFilesFound => {
    if (verbose) {
      allFilesFound.ignoredFiles.forEach(currentFile => {
        log(`Analyze : ${currentFile} : in ignore conf`);
      });
    }
    //Analyze all the files
    const stripPrefix = xml2js.processors.stripPrefix;
    const cleanDash = str => {
      return str.replace("-", "");
    };
    return Promise.all(
      allFilesFound.filesToAnalyze.map(currentFilePath => {
        return new Promise((resolve, reject) => {
          if (verbose) {
            log(`Analyze : ${currentFilePath} : ✓`);
          }
          let views = {};
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
              if (result.config.viewcontrol) {
                views = result.config.viewcontrol.reduce((acc, mainTag) => {
                  const viewName = mainTag.$.name;
                  //If no view list conf, return result and go to the next
                  if (!mainTag.viewlist || !mainTag.viewlist[0].view) {
                    return acc;
                  }
                  //Reduce view list
                  const views = mainTag.viewlist[0].view.reduce(
                    (acc, currentView) => {
                      acc[currentView.$.name] = {
                        view: {
                          name: currentView.$.name,
                          label: currentView.$.label,
                          fileName: currentFilePath
                        }
                      };
                      if (currentView.$["parent-menu-id"]) {
                        acc[`${viewName}${currentView.$["parent-menu-id"]}`] = {
                          view: {
                            name: currentView.$["parent-menu-id"],
                            label: "",
                            fileName: currentFilePath
                          }
                        };
                      }
                      return acc;
                    },
                    {}
                  );
                  if (acc[viewName]) {
                    acc[viewName] = { ...acc[viewName], ...views };
                  } else {
                    acc[viewName] = views;
                  }
                  return acc;
                }, views);
              }
              resolve(views);
            }
          );
        });
      })
    )
      .then(mergeAllKeysFound)
      .then(
        convertKeysToPot({ fieldKey: "view", filePrefix: "cvdoc", potPath })
      );
  });
};

/**
 * Parse the glob of the workflow and return the temp files to merge
 *
 * @param poGlob
 * @param info
 * @param potPath
 * @returns {Promise<Array | never>}
 */
exports.xmlWorkflow2Pot = ({ globFile, info, potPath, verbose, log }) => {
  //Find all the files in glob rules
  const srcPath = info.sourcePath;
  return parseAndConcatGlob({ globFile, srcPath }).then(allFilesFound => {
    if (verbose) {
      allFilesFound.ignoredFiles.forEach(currentFile => {
        log(`Analyze : ${currentFile} : in ignore conf`);
      });
    }
    //Analyze all the files
    const stripPrefix = xml2js.processors.stripPrefix;
    const cleanDash = str => {
      return str.replace("-", "");
    };
    return Promise.all(
      allFilesFound.filesToAnalyze.map(currentFilePath => {
        if (verbose) {
          log(`Analyze : ${currentFilePath} : ✓`);
        }
        return new Promise((resolve, reject) => {
          let views = {};
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
              if (result.config.graph) {
                views = result.config.graph.reduce((acc, mainTag) => {
                  const graphName = mainTag.$.name;
                  //If no view list conf, return result and go to the next
                  if (!mainTag.steps || !mainTag.steps[0].step) {
                    return acc;
                  }
                  //Reduce step list
                  let elements = mainTag.steps[0].step.reduce(
                    (acc, currentView) => {
                      if (currentView.$["state-label"]) {
                        acc[`state-${currentView.$.name}`] = {
                          elements: {
                            name: `${currentView.$.name}`,
                            context: `${graphName}:state`,
                            label: currentView.$["state-label"],
                            fileName: currentFilePath
                          }
                        };
                      }
                      if (currentView.$["activity-label"]) {
                        acc[`activity-${currentView.$.name}`] = {
                          elements: {
                            name: `${currentView.$.name}`,
                            context: `${graphName}:activity`,
                            label: currentView.$["activity-label"],
                            fileName: currentFilePath
                          }
                        };
                      }
                      return acc;
                    },
                    {}
                  );
                  if (
                    !mainTag.transitions ||
                    !mainTag.transitions[0].transition
                  ) {
                    return acc;
                  }
                  //Reduce transition list
                  elements = mainTag.transitions[0].transition.reduce(
                    (acc, currentView) => {
                      acc[currentView.$.name] = {
                        elements: {
                          name: currentView.$.name,
                          label: currentView.$.label,
                          context: `${graphName}:transition`,
                          fileName: currentFilePath
                        }
                      };
                      return acc;
                    },
                    elements
                  );
                  if (acc[graphName]) {
                    acc[graphName] = { ...acc[graphName], ...elements };
                  } else {
                    acc[graphName] = elements;
                  }
                  return acc;
                }, views);
              }
              resolve(views);
            }
          );
        });
      })
    )
      .then(mergeAllKeysFound)
      .then(
        convertKeysToPot({
          fieldKey: "elements",
          filePrefix: "workflow",
          potPath
        })
      );
  });
};
/**
 * Merge the pot and the po files
 *
 * @param element
 * @param srcPath
 * @param potPath
 * @param prefix
 * @returns {Promise<[any , any , any , any , any , any , any , any , any , any]>}
 */
exports.msgmerge = ({ element, srcPath, potPath, prefix }) => {
  return Promise.all(
    PO_LANGS.map(lang => {
      return new Promise((resolve, reject) => {
        const tmpPo = `${potPath}/${element.smartName}_${lang}.po`;
        const basePo = `${srcPath}/locale/${lang}/LC_MESSAGES/src/${prefix}${
          element.smartName
        }_${lang}.po`;
        let command;
        if (fs.existsSync(basePo)) {
          command = `msgmerge  --sort-output -o "${tmpPo}"  "${basePo}" "${
            element.path
          }"`;
        } else {
          command = `msginit  -o "${tmpPo}" -i "${
            element.path
          }" --no-translator --locale=${lang}`;
        }
        cp.exec(command, error => {
          if (error) {
            return reject(error);
          }
          //Copy po to the final place
          fs.copyFileSync(tmpPo, basePo);
          resolve(basePo);
        });
      });
    })
  );
};
/**
 * Merge the pot and po files
 *
 * @param element
 * @param srcPath
 * @returns {Promise<any>}
 */
exports.msgmergeMustache = ({ element, srcPath }) => {
  return new Promise((resolve, reject) => {
    const tmpPot = element.path;
    const basePo = `${srcPath}/locale/${
      element.lang
    }/LC_MESSAGES/src/mustache-${element.targetName}_${element.lang}.po`;
    let command;
    if (fs.existsSync(basePo)) {
      command = `msgmerge  --sort-output -o "${
        element.tmpPo
      }"  "${basePo}" "${tmpPot}"`;
    } else {
      command = `msginit  -o "${
        element.tmpPo
      }" -i "${tmpPot}" --no-translator --locale=${element.lang}`;
    }
    cp.exec(command, error => {
      if (error) {
        return reject(error);
      }
      //Copy po to the final place
      fs.copyFileSync(element.tmpPo, basePo);
      resolve(basePo);
    });
  });
};
/**
 * Extract and merge php files
 *
 * @param info
 * @param phpGlob
 * @param potPath
 * @param target
 * @returns {Promise<[any , any , any , any , any , any , any , any , any , any]>}
 */
exports.php2Po = ({ globFile, targetName, info, potPath, verbose, log }) => {
  const srcPath = info.sourcePath;

  return parseAndConcatGlob({ globFile, srcPath }).then(files => {
    return Promise.all(
      PO_LANGS.map(lang => {
        return new Promise((resolve, reject) => {
          const basePo = path.resolve(
            `${
              info.buildInfo.buildPath[0]
            }/locale/${lang}/LC_MESSAGES/src/${targetName}_${lang}.po`
          );
          const tmpPot = path.resolve(`${potPath}/${targetName}_${lang}.pot`);
          const tmpPo = path.resolve(`${potPath}/${targetName}_${lang}.po`);
          if (verbose) {
            files.ignoredFiles.forEach(currentFile => {
              log(`Analyze : ${currentFile} : in ignore conf`);
            });
            files.filesToAnalyze.forEach(currentFile => {
              log(`Analyze : ${currentFile} : ✓`);
            });
          }
          //join files
          const fileList = files.filesToAnalyze.reduce((acc, currentValue) => {
            return acc + " " + currentValue;
          }, "");
          if (fileList.length === 0) {
            return resolve();
          }
          cp.exec(
            `xgettext --no-location --from-code=utf-8 --language=PHP --keyword=___:1,2c --keyword=n___:1,2,4c -o "${tmpPot}" ${fileList}`,
            err => {
              if (err) {
                return reject(err);
              }
              if (!fs.existsSync(tmpPot)) {
                resolve();
              }
              if (!fs.existsSync(basePo)) {
                cp.exec(
                  `msginit  -o "${basePo}" -i "${tmpPot}" --no-translator --locale=${lang}`,
                  err => {
                    if (err) {
                      return reject(err);
                    }
                    resolve();
                  }
                );
              } else {
                cp.exec(
                  `msgmerge  --sort-output -o "${tmpPo}"  "${basePo}" "${tmpPot}"`,
                  err => {
                    if (err) {
                      return reject(err);
                    }
                    fs.copyFileSync(tmpPo, basePo);
                    resolve();
                  }
                );
              }
            }
          );
        });
      })
    );
  });
};
/**
 * Extract and merge js files
 *
 * @param globInputs
 * @param targetName
 * @param info
 * @param potPath
 * @returns {Promise<[any , any , any , any , any , any , any , any , any , any]>}
 */
exports.js2Po = ({ globFile, targetName, info, potPath, verbose, log }) => {
  const srcPath = info.sourcePath;

  return parseAndConcatGlob({ globFile, srcPath }).then(files => {
    return Promise.all(
      PO_LANGS.map(lang => {
        return new Promise((resolve, reject) => {
          const basePo = `${
            info.buildInfo.buildPath[0]
          }/locale/${lang}/js/src/js_${targetName}_${lang}.po`;
          const tmpPot = `${potPath}/js_${targetName}_${lang}.pot`;
          const tmpPo = `${potPath}/js_${targetName}_${lang}.po`;
          if (verbose) {
            files.ignoredFiles.forEach(currentFile => {
              log(`Analyze : ${currentFile} : in ignore conf`);
            });
            files.filesToAnalyze.forEach(currentFile => {
              log(`Analyze : ${currentFile} : ✓`);
            });
          }
          //join files
          const fileList = files.filesToAnalyze.reduce((acc, currentValue) => {
            return acc + " " + currentValue;
          }, "");
          if (fileList.length === 0) {
            return resolve();
          }
          cp.exec(
            `xgettext --no-location --from-code=utf-8 --language=javascript --keyword=___:1,2c --keyword=n___:1,2,4c -o "${tmpPot}" ${fileList}`,
            err => {
              if (err) {
                return reject(err);
              }
              if (!fs.existsSync(tmpPot)) {
                resolve();
              }
              if (!fs.existsSync(basePo)) {
                cp.exec(
                  `msginit  -o "${basePo}" -i "${tmpPot}" --no-translator --locale=${lang}`,
                  err => {
                    if (err) {
                      return reject(err);
                    }
                    resolve();
                  }
                );
              } else {
                cp.exec(
                  `msgmerge  --sort-output -o "${tmpPo}"  "${basePo}" "${tmpPot}"`,
                  err => {
                    if (err) {
                      return reject(err);
                    }
                    fs.copyFileSync(tmpPo, basePo);
                    resolve();
                  }
                );
              }
            }
          );
        });
      })
    );
  });
};

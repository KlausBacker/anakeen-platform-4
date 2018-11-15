const xml2js = require("xml2js");
const path = require("path");
const fs = require("fs");
const glob = require("glob");

const cp = require("child_process");

const PO_LANGS = require("./appConst").po_langs;

const attrType = require("./appConst").sfType;

const getPoEntry = (attr, info, currentFilePath) => {
  if (!attr) {
    return "";
  }
  let label = "";
  if (attr.label) {
    label = attr.label.replace(/"/g, '\\"');
  }
  return `\n#: ${currentFilePath}\n#, fuzzy\nmsgctxt "${info.name}"\nmsgid "${
    attr.name
  }"\nmsgstr "${label}"\n`;
};

const generatePoEntry = (field, info, currentFilePath) => {
  return Object.keys(field).reduce((accumulator, currentKey) => {
    if (currentKey === "$") {
      return accumulator + getPoEntry(field[currentKey], info, currentFilePath);
    }
    if (currentKey === "fieldset") {
      return (
        accumulator +
        field[currentKey].reduce((accumulator, subAttr) => {
          return accumulator + generatePoEntry(subAttr, info, currentFilePath);
        }, "")
      );
    }
    if (!attrType[currentKey]) {
      return accumulator;
    }
    return (
      accumulator +
      field[currentKey].reduce((accumulator, currentAttr) => {
        if (!currentAttr.$) {
          return accumulator;
        }
        return accumulator + getPoEntry(currentAttr.$, info, currentFilePath);
      }, "")
    );
  }, "");
};

exports.xmlStructure2Pot = ({ poGlob, info, potPath }) => {
  //Find all the files in glob rules
  const srcPath = info.buildInfo.buildPath[0];
  return Promise.all(
    poGlob.map(currentGlob => {
      return new Promise((resolve, reject) => {
        glob(
          currentGlob,
          {
            cwd: srcPath,
            nodir: true
          },
          (err, files) => {
            if (err) {
              return reject(err);
            }
            resolve(files);
          }
        );
      });
    })
  ).then(filesList => {
    const allFilesFound = filesList.reduce((acc, currentFilesList) => {
      return [...acc, ...currentFilesList];
    }, []);
    //Analyze all the files
    const stripPrefix = xml2js.processors.stripPrefix;
    const cleanDash = str => {
      return str.replace("-", "");
    };
    return Promise.all(
      allFilesFound.map(currentFilePath => {
        return new Promise((resolve, reject) => {
          const filesCreated = [];
          xml2js.parseString(
            fs.readFileSync(path.resolve(srcPath, currentFilePath)),
            { tagNameProcessors: [stripPrefix, cleanDash] },
            (err, result) => {
              if (err) {
                reject(err);
                return;
              }
              //Analyze structure configuration
              if (result.config.structureconfiguration) {
                result.config.structureconfiguration.forEach(currentConf => {
                  let infos = currentConf.$;
                  const fields = currentConf.fields;
                  let poEntries = "";

                  if (fields) {
                    fields.forEach(fieldset => {
                      poEntries += generatePoEntry(
                        fieldset,
                        infos,
                        currentFilePath
                      );
                    });
                  }

                  let now = new Date().toISOString();
                  let content = `msgid ""
msgstr ""
"Project-Id-Version: Smart ${infos.name} \\n"
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
                    const potFile = path.join(
                      potPath,
                      `smart${infos.name}.pot`
                    );
                    fs.writeFileSync(potFile, content);
                    filesCreated.push({
                      path: potFile,
                      smartName: infos.name
                    });
                  }
                });
              }
              resolve(filesCreated);
            }
          );
        });
      })
    );
  });
};

exports.xmlEnum2Pot = ({ poGlob, info, potPath }) => {
  //Find all the files in glob rules
  const srcPath = info.buildInfo.buildPath[0];
  return Promise.all(
    poGlob.map(currentGlob => {
      return new Promise((resolve, reject) => {
        glob(
          currentGlob,
          {
            cwd: srcPath,
            nodir: true
          },
          (err, files) => {
            if (err) {
              return reject(err);
            }
            resolve(files);
          }
        );
      });
    })
  ).then(filesList => {
    const allFilesFound = filesList.reduce((acc, currentFilesList) => {
      return [...acc, ...currentFilesList];
    }, []);
    //Analyze all the files
    const stripPrefix = xml2js.processors.stripPrefix;
    const cleanDash = str => {
      return str.replace("-", "");
    };
    return Promise.all(
      allFilesFound.map(currentFilePath => {
        return new Promise((resolve, reject) => {
          let enums = {};
          xml2js.parseString(
            fs.readFileSync(path.resolve(srcPath, currentFilePath), {
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
                      //console.log(acc, currentEnum);
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
      .then(allEnumsFound => {
        //Merge all enums found
        return allEnumsFound.reduce((acc, currentEnumsDef) => {
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
      .then(enums => {
        //Generate files from keys
        const filesCreated = [];
        Object.keys(enums).forEach(currentKey => {
          //console.log(currentKey, enums[currentKey]);
          const poEntries = Object.values(enums[currentKey]).reduce(
            (acc, currentEntry) => {
              return (
                acc +
                getPoEntry(
                  currentEntry.enumItem,
                  {name: currentKey},
                  currentEntry.fileName
                )
              );
            },
            ""
          );
          let now = new Date().toISOString();
          let content = `msgid ""
msgstr ""
"Project-Id-Version: Enum ${currentKey} \\n"
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
            const potFile = path.join(potPath, `enum${currentKey}.pot`);
            fs.writeFileSync(potFile, content);
            filesCreated.push({
              path: potFile,
              smartName: currentKey
            });
          }
        });
        return filesCreated;
      });
  });
};

exports.msgmergeStructure = ({ element, srcPath, potPath }) => {
  return Promise.all(
    PO_LANGS.map(lang => {
      return new Promise((resolve, reject) => {
        const tmpPo = `${potPath}/${element.smartName}_${lang}.po`;
        const basePo = `${srcPath}/locale/${lang}/LC_MESSAGES/src/${
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

exports.msgmergeEnum = ({ element, srcPath, potPath }) => {
  return Promise.all(
    PO_LANGS.map(lang => {
      return new Promise((resolve, reject) => {
        const tmpPo = `${potPath}/${element.smartName}_${lang}.po`;
        const basePo = `${srcPath}/locale/${lang}/LC_MESSAGES/src/enum${
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
exports.php2Po = ({ info, phpGlob, potPath, target }) => {
  const promises = [];

  const srcPath = info.buildInfo.buildPath[0];

  PO_LANGS.forEach(lang => {
    promises.push(
      new Promise((resolve, reject) => {
        try {
          const basePo = path.resolve(
            `${srcPath}/locale/${lang}/LC_MESSAGES/src/${target}_${lang}.po`
          );
          const tmpPot = path.resolve(`${potPath}/${target}_${lang}.pot`);
          const tmpPo = path.resolve(`${potPath}/${target}_${lang}.po`);

          //Find all the php files in glob

          new Promise((resolve, reject) => {
            glob(
              phpGlob,
              {
                cwd: srcPath,
                nodir: true
              },
              (err, files) => {
                if (err) {
                  return reject(err);
                }
                resolve(files);
              }
            );
          })
            .then(filesList => {
              //Convert the file list in string
              const filesString = filesList.reduce((acc, currentValue) => {
                return acc + " " + path.resolve(srcPath, currentValue);
              }, "");
              //Extract all the keys with gettext
              cp.exec(
                `xgettext --no-location --from-code=utf-8 --language=PHP --keyword=___:1,2c --keyword=n___:1,2,4c -o "${tmpPot}" ${filesString}`,
                err => {
                  if (err) {
                    return reject(err);
                  }
                  //Do nothing if nothing is found
                  if (!fs.existsSync(tmpPot)) {
                    //no element found by tmpPot
                    resolve();
                  }
                  //If the file not exist init it
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
                    //Or complete it
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
            })
            .catch(reject);
        } catch (e) {
          console.error(e);
          reject(e);
        }
      })
    );
  });

  return Promise.all(promises);
};
exports.js2Po = (globInputs, targetName, info, potPath) => {
  const promises = [];

  const srcPath = info.buildInfo.buildPath[0];

  PO_LANGS.forEach(lang => {
    promises.push(
      new Promise((resolve, reject) => {
        try {
          const basePo = `${srcPath}/locale/${lang}/js/src/js_${targetName}_${lang}.po`;
          const tmpPot = `${potPath}/js_${targetName}_${lang}.pot`;
          const tmpPo = `${potPath}/js_${targetName}_${lang}.po`;

          //Find all the php files in src
          glob(
            globInputs,
            {
              cwd: srcPath,
              nodir: true
            },
            (err, files) => {
              if (err) {
                return reject(err);
              }
              //join files
              const fileList = files.reduce((acc, currentValue) => {
                return acc + " " + path.resolve(srcPath, currentValue);
              }, "");
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
            }
          );
        } catch (e) {
          console.error(e);
          reject(e);
        }
      })
    );
  });

  return Promise.all(promises);
};

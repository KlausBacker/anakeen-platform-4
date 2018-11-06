const xml2js = require("xml2js");
const vinylFile = require("vinyl-file");
const File = require("vinyl");
const path = require("path");
const fs = require("fs");
const glob = require("glob");

const cp = require("child_process");

const PO_LANGS = ["fr", "en"];

const attrType = {
  fieldtext: "text",
  fieldhtmltext: "htmltext",
  fieldlongtext: "longtext",
  fieldint: "int",
  fieldmoney: "money",
  fieldfloat: "float",
  fieldcolor: "color",
  fieldenum: "enum",
  fielddate: "date",
  fieldtime: "time",
  fieldtimestamp: "timestamp",
  fieldfile: "file",
  fieldimage: "image",
  fielddocid: "docid",
  fieldaccount: "account"
};

const getPoEntry = (attr, info) => {
  if (!attr) {
    return "";
  }
  let label = "";
  if (attr.label) {
    label = attr.label.replace(/"/g, '\\"');
  }

  return `\n#: ${info.file.basename}\n#, fuzzy\nmsgctxt "${
    info.name
  }"\nmsgid "${attr.name}"\nmsgstr "${label}"\n`;
};

const generatePoEntry = (field, info) => {
  return Object.keys(field).reduce((accumulator, currentKey) => {
    if (currentKey === "$") {
      return accumulator + getPoEntry(field[currentKey], info);
    }
    if (currentKey === "fieldset") {
      return (
        accumulator +
        field[currentKey].reduce((accumulator, subAttr) => {
          return accumulator + generatePoEntry(subAttr, info);
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
        return accumulator + getPoEntry(currentAttr.$, info);
      }, "")
    );
  }, "");
};

exports.xmlStructure2Pot = file => {
  return new Promise((resolve, reject) => {
    const files = [];
    const stripPrefix = xml2js.processors.stripPrefix;
    const cleanDash = str => {
      return str.replace("-", "");
    };
    const base = path.join(file.path, "..");
    xml2js.parseString(
      file.contents,
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

            infos.file = file;
            if (fields) {
              fields.forEach(fieldset => {
                poEntries += generatePoEntry(fieldset, infos);
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

            files.push(
              new File({
                smartName: infos.name,
                base: base,
                path: path.join(base, "smart" + infos.name + ".pot"),
                contents: Buffer.from(content)
              })
            );
          });
        }
        resolve(files);
      }
    );
  });
};

exports.xmlEnum2Pot = file => {
  return new Promise((resolve, reject) => {
    const files = [];
    const stripPrefix = xml2js.processors.stripPrefix;
    const cleanDash = str => {
      return str.replace("-", "");
    };
    const base = path.join(file.path, "..");
    xml2js.parseString(
      file.contents,
      { tagNameProcessors: [stripPrefix, cleanDash] },
      (err, result) => {
        if (err) {
          reject(err);
          return;
        }
        //Analyze structure configuration
        if (result.config.enumerates) {
          result.config.enumerates.forEach(enumMainTag => {
            if (enumMainTag.enumconfiguration) {
              enumMainTag.enumconfiguration.forEach(currentConf => {
                let infos = currentConf.$;
                const fields = currentConf.enum;
                let poEntries = "";

                infos.file = file;
                if (fields) {
                  fields.forEach(enumItem => {
                    poEntries += getPoEntry(enumItem.$, infos);
                  });
                }

                let now = new Date().toISOString();
                let content = `msgid ""
msgstr ""
"Project-Id-Version: Enum ${infos.name} \\n"
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

                files.push(
                  new File({
                    enumName: "enum" + infos.name,
                    base: base,
                    path: path.join(base, "enum" + infos.name + ".pot"),
                    contents: Buffer.from(content)
                  })
                );
              });
            }
          });
        }
        resolve(files);
      }
    );
  });
};

/**
 * Merge with locale files
 * @param file
 * @param srcPath
 * @returns {Promise<any>}
 */
exports.msgmergeStructure = (file, srcPath) => {
  return new Promise((resolve, reject) => {
    const tmpDir = file.dirname;
    const files = [];
    let resolvCount = 0;

    PO_LANGS.forEach(lang => {
      const tmpPo = `${tmpDir}/${file.smartName}_${lang}.po`;
      const basePo = `${srcPath}/locale/${lang}/LC_MESSAGES/src/${
        file.smartName
      }_${lang}.po`;

      fs.access(basePo, err => {
        let command;
        if (err !== null) {
          command = `msginit  -o "${tmpPo}" -i "${
            file.path
          }" --no-translator --locale=${lang}`;
        } else {
          command = `msgmerge  --sort-output -o "${tmpPo}"  "${basePo}" "${
            file.path
          }"`;
        }
        cp.exec(command, (error /*, stdout, stderr*/) => {
          //eslint-disable-next-line no-console
          // console.log(command);
          vinylFile.read(tmpPo).then(mergeFile => {
            resolvCount++;
            mergeFile.base = mergeFile.dirname;
            mergeFile.lang = lang;

            files.push(mergeFile);
            if (resolvCount >= PO_LANGS.length) {
              resolve(files);
            }
          });
          if (error) {
            //eslint-disable-next-line no-console
            console.log(`exec error: ${error}`);
            reject(error);
          }
          //   // console.log(command, `stdout: ${stdout}`);
          //   console.log(`stderr: ${stderr}`);
        });
      });
    });
  });
};

exports.msgmergeMustache = (file, info) => {
  return new Promise((resolve, reject) => {
    const tmpDir = file.dirname;
    const files = [];
    const srcPath = info.buildInfo.buildPath[0];
    let resolvCount = 0;

    PO_LANGS.forEach(lang => {
      const tmpPo = `${tmpDir}/mustache-${info.moduleInfo.name}_${lang}.po`;
      const basePo = `${srcPath}/locale/${lang}/LC_MESSAGES/src/mustache-${
        info.moduleInfo.name
      }_${lang}.po`;

      fs.access(basePo, err => {
        let command;
        if (err !== null) {
          command = `msginit  -o "${tmpPo}" -i "${
            file.path
          }" --no-translator --locale=${lang}`;
        } else {
          command = `msgmerge  --sort-output -o "${tmpPo}"  "${basePo}" "${
            file.path
          }"`;
        }

        cp.exec(command, (error /*, stdout, stderr*/) => {
          vinylFile.read(tmpPo).then(mergeFile => {
            resolvCount++;
            mergeFile.base = mergeFile.dirname;
            mergeFile.lang = lang;

            files.push(mergeFile);
            if (resolvCount >= PO_LANGS.length) {
              resolve(files);
            }
          });
          if (error) {
            //eslint-disable-next-line no-console
            console.log(`exec error: ${error}`);
            reject(error);
          }
        });
      });
    });
  });
};

exports.msgmergeEnum = (file, srcPath) => {
  return new Promise((resolve, reject) => {
    const tmpDir = file.dirname;
    const files = [];
    let resolvCount = 0;

    // console.log("merge", file.path, srcPath);

    PO_LANGS.forEach(lang => {
      const tmpPo = path.resolve(`${tmpDir}/${file.enumName}_${lang}.po`);
      const basePo = path.resolve(
        `${srcPath}/locale/${lang}/LC_MESSAGES/src/${file.enumName}_${lang}.po`
      );

      fs.access(basePo, err => {
        let command;
        if (err !== null) {
          command = `msginit  -o "${tmpPo}" -i "${
            file.path
          }" --no-translator --locale=${lang}`;
        } else {
          command = `msgmerge  --sort-output -o "${tmpPo}"  "${basePo}" "${
            file.path
          }"`;
        }

        cp.exec(command, (error /*, stdout, stderr*/) => {
          //eslint-disable-next-line no-console
          // console.log(command);
          vinylFile.read(tmpPo).then(mergeFile => {
            resolvCount++;
            mergeFile.base = mergeFile.dirname;
            mergeFile.lang = lang;

            files.push(mergeFile);
            if (resolvCount >= PO_LANGS.length) {
              resolve(files);
            }
          });
          if (error) {
            //eslint-disable-next-line no-console
            console.log(`exec error: ${error}`);
            reject(error);
          }
        });
      });
    });
  });
};
exports.php2Pot = (info, potdir) => {
  const promises = [];

  const srcPath = info.buildInfo.buildPath[0];
  const moduleName = info.moduleInfo.name;

  PO_LANGS.forEach(lang => {
    promises.push(
      new Promise((resolve, reject) => {
        try {
          const basePo = path.resolve(
            `${srcPath}/locale/${lang}/LC_MESSAGES/src/${moduleName}_${lang}.po`
          );
          const tmpPot = path.resolve(`${potdir}/${moduleName}_${lang}.pot`);
          const tmpPo = path.resolve(`${potdir}/${moduleName}_${lang}.po`);

          //Find all the php files in src
          glob(
            `./**/*.php`,
            {
              cwd: srcPath
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
                `xgettext --no-location --from-code=utf-8 --language=PHP --keyword=___:1,2c --keyword=n___:1,2,4c -o "${tmpPot}" ${fileList}`,
                err => {
                  if (err) {
                    return reject(err);
                  }
                  if (!fs.existsSync(tmpPot)) {
                    //no element found by tmpPot
                    resolve();
                  }
                  if (!fs.existsSync(tmpPo)) {
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

              resolve();
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
exports.js2Po = (globInputs, targetName, info, potdir) => {
  const promises = [];

  const srcPath = info.buildInfo.buildPath[0];

  PO_LANGS.forEach(lang => {
    promises.push(
      new Promise((resolve, reject) => {
        try {
          const basePo = `${srcPath}/locale/${lang}/js/src/js_${targetName}_${lang}.po`;
          const tmpPot = `${potdir}/js_${targetName}_${lang}.pot`;
          const tmpPo = `${potdir}/js_${targetName}_${lang}.po`;

          //Find all the php files in src
          glob(
            globInputs,
            {
              cwd: srcPath
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
                  if (!fs.existsSync(tmpPo)) {
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

              resolve();
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

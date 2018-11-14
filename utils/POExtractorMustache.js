const path = require("path");
const fs = require("fs");
const Mustache = require("mustache");
const glob = require("glob");

const PO_LANGS = require("./appConst").po_langs;

const getPoEntry = attr => {
  if (!attr) {
    return "";
  }
  let poContent = "";
  poContent += `#: ${attr.file}\n`;
  poContent += `#, fuzzy\n`;
  poContent += `msgctxt "${attr.msgctxt}"\n`;
  poContent += `msgid "${attr.msgid}"\n`;
  poContent += `msgstr ""\n\n`;

  return poContent;
};

const getMustachei18n = (token, fileName) => {
  let keys = {};
  if (token.length > 4 && token[0] === "#" && token[1] === "i18n") {
    let type = token[4][0][0];
    if (type === "text") {
      let key = token[4][0][1];
      let data = key.split("::", 2);
      keys[`${data[0]}+${data[1]}`] = {
        msgctxt: data[0],
        msgid: data[1],
        key: token[4][0][1],
        file: fileName
      };
    }
  } else if (token.length > 4 && token[4][0]) {
    keys = { ...keys, ...getMustachei18n(token[4][0], fileName) };
  }
  return keys;
};

module.exports = (globInputs, targetName, info, potPath) => {
  const promises = [];

  const srcPath = info.buildInfo.buildPath[0];

  PO_LANGS.forEach(lang => {
    promises.push(
      new Promise((resolve, reject) => {
        try {
          const tmpPot = `${potPath}/mustache_${targetName}_${lang}.pot`;
          const tmpPo = `${potPath}/mustache_${targetName}_${lang}.po`;

          //Find all the files in src
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

              //Analyze files
              const keys = files.reduce((acc, currentFile) => {
                const currentPath = path.resolve(srcPath, currentFile);
                const tokens = Mustache.parse(
                  fs.readFileSync(currentPath, { encoding: "utf8" }),
                  ["[[", "]]"]
                );
                const concatKeys = tokens.reduce((acc, token) => {
                  const lkeys = getMustachei18n(token, currentFile);
                  return { ...acc, ...lkeys };
                }, {});
                return { ...acc, ...concatKeys };
              }, {});

              const poEntries = Object.values(keys).reduce(
                (acc, currentKey) => {
                  return acc + getPoEntry(currentKey);
                },
                ""
              );

              //console.log(uniqueKeys);
              let now = new Date().toISOString();
              let content = `msgid ""
msgstr ""
"Project-Id-Version: Mustache ${info.moduleInfo.name} \\n"
"Report-Msgid-Bugs-To: \\n"
"PO-Revision-Date: ${now}\\n"
"Last-Translator: Automatically generated\\n"
"Language-Team: none\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"

${poEntries}
`;
              if (poEntries.length > 0) {
                //If there is something, we write the temp pot file
                return fs.writeFile(tmpPot, content, err => {
                  if (err) {
                    return reject(err);
                  }
                  resolve({ path: tmpPot, targetName, lang, tmpPo });
                });
              }
              //Nothing to do here
              resolve(false);
            }
          );
        } catch (e) {
          reject(e);
        }
      })
    );
  });
  return Promise.all(promises);
};

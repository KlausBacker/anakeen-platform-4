const fs = require("fs");
const Mustache = require("mustache");
const { parseAndConcatGlob } = require("../utils/globAnalyze");

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

module.exports = ({ globFile, targetName, info, potPath, verbose, log }) => {
  const srcPath = info.sourcePath;

  return parseAndConcatGlob({ globFile, srcPath }).then(files => {
    if (verbose) {
      log(`Analyze Path: ${globFile.addGlob} `);
    }
    return Promise.all(
      PO_LANGS.map(lang => {
        const tmpPot = `${potPath}/mustache_${targetName}_${lang}.pot`;
        const tmpPo = `${potPath}/mustache_${targetName}_${lang}.po`;
        //Analyze files
        if (verbose) {
          files.ignoredFiles.forEach(currentFile => {
            log(`Analyze : ${currentFile} : in ignore conf`);
          });
        }
        const keys = files.filesToAnalyze.reduce((acc, currentFile) => {
          if (verbose && verbose.length > 1) {
            log(`Analyze : ${currentFile} : ✓`);
          }
          const currentFileContent = fs.readFileSync(currentFile, {
            encoding: "utf8"
          });
          const tokens = [
            ...Mustache.parse(currentFileContent, ["[[", "]]"]),
            ...Mustache.parse(currentFileContent, ["{{", "}}"])
          ];
          const concatKeys = tokens.reduce((acc, token) => {
            const lkeys = getMustachei18n(token, currentFile);
            return { ...acc, ...lkeys };
          }, {});
          return { ...acc, ...concatKeys };
        }, {});

        const poEntries = Object.values(keys).reduce((acc, currentKey) => {
          return acc + getPoEntry(currentKey);
        }, "");

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
          fs.writeFileSync(tmpPot, content);
          return { path: tmpPot, targetName, lang, tmpPo };
        }
        //Nothing to do here
        return false;
      })
    );
  });
};

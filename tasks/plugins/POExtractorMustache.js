const through = require("through2");
const path = require("path");
const File = require("vinyl");
const Mustache = require("mustache");

const getPoEntry = attr => {
  if (!attr) {
    return "";
  }
  let poContent = "";
  attr.files.forEach(file => {
    poContent += `#: ${file}\n`;
  });
  poContent += `#, fuzzy\n`;
  poContent += `msgctxt "${attr.msgctxt}"\n`;
  poContent += `msgid "${attr.msgid}"\n`;
  poContent += `msgstr ""\n\n`;

  return poContent;
};

const getMustachei18n = token => {
  let keys = [];
  if (token.length > 4 && token[0] === "#" && token[1] === "i18n") {
    let type = token[4][0][0];
    if (type === "text") {
      let key = token[4][0][1];
      let data = key.split("::", 2);
      keys.push({
        msgctxt: data[0],
        msgid: data[1],
        key: token[4][0][1]
      });
    }
  } else if (token.length > 4 && token[4][0]) {
    keys = keys.concat(getMustachei18n(token[4][0]));
  }
  return keys;
};

module.exports = function(fileName, info) {
  if (!fileName) {
    throw new Error("gulp-concat: Missing file option");
  }

  let keys = [];

  function bufferContents(file, enc, cb) {
    // ignore empty files
    if (file.isNull()) {
      cb();
      return;
    }
    let tokens = Mustache.parse(file.contents.toString(), ["[[", "]]"]);
    //console.log("Yo", file.path, file.contents.toString(), tokens);
    tokens.forEach(token => {
      let lkeys = getMustachei18n(token);
      lkeys.forEach(lkey => {
        lkey.file = path.basename(file.path);
      });
      keys = keys.concat(lkeys);
    });

    cb();
  }

  function endStream(cb) {
    // no files passed in, no file goes out
    if (!keys) {
      cb();
      return;
    }

    let joinedFile;
    let uniqueKeys = {};
    let poEntries = "";
    keys.forEach(item => {
      if (uniqueKeys[item.key]) {
        uniqueKeys[item.key].files.push(item.file);
      } else {
        uniqueKeys[item.key] = item;
        uniqueKeys[item.key].files = [item.file];
        delete uniqueKeys[item.key].file;
      }
    });
    Object.entries(uniqueKeys).forEach(item => {
      poEntries += getPoEntry(item[1]);
    });

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

    joinedFile = new File({
      base: path.dirname(fileName),
      path: fileName,
      contents: Buffer.from(content)
    });

    joinedFile.contents = Buffer.from(content);

    this.push(joinedFile);
    cb();
  }

  return through.obj(bufferContents, endStream);
};

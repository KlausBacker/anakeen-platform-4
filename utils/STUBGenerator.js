const xml2js = require("xml2js");
const File = require("vinyl");
const path = require("path");

const generateFields = field => {
  var listFields = "";
  if (field.fieldset) {
    field.fieldset.forEach(fs => {
      listFields += generateFields(fs);
    });
  }
  // [frame] or [type]
  var typeList = ["frame", "array"];
  if (field.$ && typeList.indexOf(field.$.type) > 0) {
    listFields += `        /** [${field.$.type}] ${field.$.label} */\r\n`;
    listFields += `        const ${field.$.name}='${field.$.name}';\r\n`;
  }
  // [text]
  if (field.fieldtext) {
    field.fieldtext.forEach(text => {
      listFields += `        /** [text] ${text.$.label} */\r\n`;
      listFields += `        const ${text.$.name}='${text.$.name}';\r\n`;
    });
  }
  // [longtext]
  if (field.fieldlongtext) {
    field.fieldlongtext.forEach(longtext => {
      listFields += `        /** [longtext] ${longtext.$.label} */\r\n`;
      listFields += `        const ${longtext.$.name}='${
        longtext.$.name
      }';\r\n`;
    });
  }
  // [docid]
  if (field.fielddocid) {
    field.fielddocid.forEach(docid => {
      listFields += `        /** [docid("${docid.$.relation}")] ${
        docid.$.label
      } */\r\n`;
      listFields += `        const ${docid.$.name}='${docid.$.name}';\r\n`;
    });
  }
  // [enum]
  if (field.fieldenum) {
    field.fieldenum.forEach(enuma => {
      listFields += `        /** [enum] ${enuma.$.label} */\r\n`;
      listFields += `        const ${enuma.$.name}='${enuma.$.name}';\r\n`;
    });
  }
  // [int]
  if (field.fieldint) {
    field.fieldint.forEach(inta => {
      listFields += `        /** [int] ${inta.$.label} */\r\n`;
      listFields += `        const ${inta.$.name}='${inta.$.name}';\r\n`;
    });
  }
  // [option]
  if (field.fieldoption) {
    field.fieldoption.forEach(option => {
      listFields += `        /** [option] ${option.$.label} */\r\n`;
      listFields += `        const ${option.$.name}='${option.$.name}';\r\n`;
    });
  }
  return listFields;
};

const upperCaseFirstLetter = function(str) {
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

exports.parseStub = file => {
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
            const infos = currentConf.$;
            let currentClass =
              currentConf.class !== undefined ? currentConf.class[0] : false;
            const fields = currentConf.fields;
            let fieldsString = "";
            if (fields) {
              fields.forEach(fieldset => {
                fieldsString += generateFields(fieldset);
              });
            }

            //Extends for field part
            const extendsPart = infos.extends
              ? ` extends ${infos.extends}`
              : "";

            //Extend for class part
            let extendsSSPart = "";

            if (currentClass) {
              if (currentClass._) {
                currentClass = currentClass._;
              }
              extendsSSPart = currentClass;
              //Add missing \ as first char if it's not already the case
              if (extendsSSPart[0] !== "\\") {
                extendsSSPart = "\\" + extendsSSPart;
              }
            }

            if (!extendsSSPart && infos.extends) {
              //the current structure extends another one
              extendsSSPart = infos.extends;
            }

            let content = `<?php

namespace SmartStructure {

    class ${upperCaseFirstLetter(infos.name)} extends ${extendsSSPart ||
              "\\Anakeen\\SmartElement"}
    {
        const familyName = "${infos.name}";
    }
}

namespace SmartStructure\\Fields {

    class ${upperCaseFirstLetter(infos.name)}${extendsPart}
    {
${fieldsString}
    }
}`;

            files.push(
              new File({
                base: base,
                path: path.join(base, infos.name + "__STUB.php"),
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

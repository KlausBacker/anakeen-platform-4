const xml2js = require("xml2js");
const File = require("vinyl");
const path = require("path");

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

const generateDescription = (attr, type = "") => {
  if (!attr) {
    return "";
  }
  let desc = "        /**\n";
  if (type && !attr.type) {
    attr.type = type;
  }
  if (attr.label) {
    desc += `        * ${attr.label}\n`;
  }
  desc += `        * <ul>\n`;
  Object.keys(attr).forEach(currentKey => {
    if (currentKey !== "name" && currentKey !== "label") {
      desc += `        * <li> <i>${currentKey}</i> ${attr[currentKey]} </li>\n`;
    }
  });
  desc += `        * </ul>\n`;
  desc += "        */ \n";
  desc += `        const ${attr.name}='${attr.name}';\n`;
  return desc;
};

const generateFields = field => {
  return Object.keys(field).reduce((accumulator, currentKey) => {
    if (currentKey === "$") {
      return accumulator + generateDescription(field[currentKey]);
    }
    if (currentKey === "fieldset") {
      return (
        accumulator +
        field[currentKey].reduce((accumulator, subAttr) => {
          return accumulator + generateFields(subAttr);
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
        return (
          accumulator + generateDescription(currentAttr.$, attrType[currentKey])
        );
      }, "")
    );
  }, "");
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

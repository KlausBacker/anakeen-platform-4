const fs = require("fs");
const path = require("path");
const xml2js = require("xml2js");
const signale = require("signale");

/*
* Transform XML structure to PHP STUB files
*/
exports.getSTUBgenerator = async (sourcePath, targetPath) => {
  // Check if source and target path exists
  if (fs.existsSync(sourcePath) && fs.existsSync(targetPath)) {
    // Create temporary directory
    var dir = fs.mkdtempSync(path.join(`${targetPath}`, "tmpExt-"));
    signale.info("Temporary directory: " + dir);

    // Get list of XML files
    var files = fs.readdirSync(sourcePath);

    // Parser function
    var parseXML = function parseXML2JS(file) {
      // Return promise
      return new Promise((resolve, reject) => {
        if (!file.endsWith(".struct.xml")) {
          // Control struct XML file format
          reject("Invalid format");
        } else {
          // STUB file path
          var STUBfile =
            dir + "/" + file.substr(0, file.length - 11) + "__STUB.php";

          // Read and parse file
          var xmlContent = fs.readFileSync(sourcePath + "/" + file);
          var stripPrefix = xml2js.processors.stripPrefix;
          var cleanDash = function(str) {
            return str.replace("-", "");
          };
          xml2js.parseString(
            xmlContent,
            { tagNameProcessors: [stripPrefix, cleanDash] },
            function(err, result) {
              // Transform content to STUB data
              if (err) throw err;

              var infos = result.config.structureconfiguration[0].$;
              var smartClass = result.config.structureconfiguration[0].class;
              var fields = result.config.structureconfiguration[0].fields;

              var STUBcontent =
                "<?php\r\n" +
                "namespace SmartStructure {\r\n" +
                "\t/** Contrôle de vues  */\r\n" +
                "\tclass " +
                upperCaseFirstLetter(infos.name) +
                ` extends \\${smartClass} { const familyName="${
                  infos.name
                }"; }\r\n` +
                "}\r\n" +
                "\r\n" +
                "namespace SmartStructure\\Fields {\r\n" +
                "\t/** Contrôle de vues  */\r\n" +
                "\tclass " +
                upperCaseFirstLetter(infos.name) +
                " extends Base {\r\n";

              fields.forEach(function(fieldset) {
                STUBcontent += generateFields(fieldset);
              });

              STUBcontent += "\t}\r\n" + "}\r\n";

              fs.writeFile(STUBfile, STUBcontent, function(err) {
                if (err) {
                  throw err;
                }
                resolve();
              });
            }
          );
        }
      });
    };

    var generateFields = function(field) {
      var listFields = "";
      if (field.fieldset) {
        field.fieldset.forEach(function(fs) {
          listFields += generateFields(fs);
        });
      }
      // [frame] or [type]
      var typeList = ["frame", "array"];
      if (field.$ && typeList.indexOf(field.$.type) > 0) {
        listFields += `\t\t/** [${field.$.type}] ${field.$.label} */\r\n`;
        listFields += `\t\tconst ${field.$.name}='${field.$.name}';\r\n`;
      }
      // [text]
      if (field.fieldtext) {
        field.fieldtext.forEach(function(text) {
          listFields += `\t\t/** [text] ${text.$.label} */\r\n`;
          listFields += `\t\tconst ${text.$.name}='${text.$.name}';\r\n`;
        });
      }
      // [longtext]
      if (field.fieldlongtext) {
        field.fieldlongtext.forEach(function(longtext) {
          listFields += `\t\t/** [longtext] ${longtext.$.label} */\r\n`;
          listFields += `\t\tconst ${longtext.$.name}='${
            longtext.$.name
          }';\r\n`;
        });
      }
      // [docid]
      if (field.fielddocid) {
        field.fielddocid.forEach(function(docid) {
          listFields += `\t\t/** [docid("${docid.$.relation}")] ${
            docid.$.label
          } */\r\n`;
          listFields += `\t\tconst ${docid.$.name}='${docid.$.name}';\r\n`;
        });
      }
      // [enum]
      if (field.fieldenum) {
        field.fieldenum.forEach(function(enuma) {
          listFields += `\t\t/** [enum] ${enuma.$.label} */\r\n`;
          listFields += `\t\tconst ${enuma.$.name}='${enuma.$.name}';\r\n`;
        });
      }
      // [int]
      if (field.fieldint) {
        field.fieldint.forEach(function(inta) {
          listFields += `\t\t/** [int] ${inta.$.label} */\r\n`;
          listFields += `\t\tconst ${inta.$.name}='${inta.$.name}';\r\n`;
        });
      }
      // [option]
      if (field.fieldoption) {
        field.fieldoption.forEach(function(option) {
          listFields += `\t\t/** [option] ${option.$.label} */\r\n`;
          listFields += `\t\tconst ${option.$.name}='${option.$.name}';\r\n`;
        });
      }
      return listFields;
    };

    var upperCaseFirstLetter = function(str) {
      return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    };

    // Parsing list
    var listParsing = files.map(parseXML);

    // Run the parser over all files
    return Promise.all(listParsing).then(() => {
      signale.success("Finished parsing");
      return { extractDir: dir };
    });
  } else if (!fs.existsSync(sourcePath)) {
    signale.error("Source path not found: " + sourcePath);
  } else if (!fs.existsSync(targetPath)) {
    signale.error("Target path not found: " + targetPath);
  }
};

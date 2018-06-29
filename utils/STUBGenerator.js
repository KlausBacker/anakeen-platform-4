const fs = require("fs");
const path = require("path");
const appConst = require("./appConst");
const xml2js = require("xml2js");
const util = require("util");

/*
* Transform XML structure to PHP STUB files
*/
exports.getSTUBgenerator = async (sourcePath, targetPath) => {
    // Check if source and target path exists
    if (fs.existsSync(sourcePath) && fs.existsSync(targetPath)) {
        // Create temporary directory
        var dir = fs.mkdtempSync(path.join(`${targetPath}`, 'tmpExt-'));
        console.log('Temporary directory: ' + dir);

        // Get list of XML files
        var files = fs.readdirSync(sourcePath);

        // Parser function
        var parseXML = function parseXML2JS(file){
            // Return promise
            return new Promise((resolve, reject) => {
                if(!file.endsWith('.struct.xml')){
                // Control struct XML file format
                resolve("Invalid format");
            } else {
                // STUB file path
                var stubFile = dir + "/" + file.substr(0, file.length - 11) + '__STUB.php';

                // Read and parse file
                var xmlContent = fs.readFileSync(sourcePath + '/' + file);
                var stripPrefix = xml2js.processors.stripPrefix;
                var cleanDash = function(str) {
                    return str.replace('-', '');
                };
                xml2js.parseString(xmlContent, { tagNameProcessors: [stripPrefix, cleanDash] }, function (err, result) {
                    // Transform content to STUB data
                    if(err) throw err;

                    var infos = result.config.structureconfiguration[0].$;
                    var smartClass = result.config.structureconfiguration[0].class;
                    var attributes = result.config.structureconfiguration[0].attributes;

                    console.log(smartClass);

                    var STUBcontent = '<?php\r\n' +
                        'namespace SmartStructure {\r\n' +
                        '\t/** Contrôle de vues  */\r\n' +
                        '\tclass ' + upperCaseFirstLetter(infos.name) + ` extends \\${smartClass} { const familyName=\"${infos.name}\"; }\r\n` +
                        '}\r\n' +
                        '\r\n' +
                        'namespace SmartStructure\\Attributes {\r\n' +
                        '\t/** Contrôle de vues  */\r\n' +
                        '\tclass ' + upperCaseFirstLetter(infos.name) + ' extends Base {\r\n';

                    attributes.forEach(function(fieldset){
                        STUBcontent += generateAttributes(fieldset);
                    });

                    STUBcontent += '\t}\r\n' +
                        '}\r\n';

                    fs.writeFile(stubFile, STUBcontent, function(err) {
                        if(err) {
                            throw err;
                        }
                        resolve();
                    });
                });
            }
        });
        };

        var generateAttributes = function(attr) {
            var listAttr = '';
            if(attr.attrfieldset){
                attr.attrfieldset.forEach(function(fieldset){
                    listAttr += generateAttributes(fieldset);
                });
            }
            // [frame] or [type]
            var typeList = ["frame", "array"];
            if(attr.$ && typeList.indexOf(attr.$.type) > 0){
                listAttr += `\t\t/** [${attr.$.type}] ${attr.$.label} */\r\n`;
                listAttr += `\t\tconst ${attr.$.name}='${attr.$.name}';\r\n`;
            }
            // [text]
            if(attr.attrtext){
                attr.attrtext.forEach(function(text){
                    listAttr += `\t\t/** [text] ${text.$.label} */\r\n`;
                    listAttr += `\t\tconst ${text.$.name}='${text.$.name}';\r\n`;
                });
            }
            // [longtext]
            if(attr.attrlongtext){
                attr.attrlongtext.forEach(function(longtext){
                    listAttr += `\t\t/** [longtext] ${longtext.$.label} */\r\n`;
                    listAttr += `\t\tconst ${longtext.$.name}='${longtext.$.name}';\r\n`;
                });
            }
            // [docid]
            if(attr.attrdocid){
                attr.attrdocid.forEach(function(docid){
                    listAttr += `\t\t/** [docid(\"${docid.$.relation}\")] ${docid.$.label} */\r\n`;
                    listAttr += `\t\tconst ${docid.$.name}='${docid.$.name}';\r\n`;
                });
            }
            // [enum]
            if(attr.attrenum){
                attr.attrenum.forEach(function(enuma){
                    listAttr += `\t\t/** [enum] ${enuma.$.label} */\r\n`;
                    listAttr += `\t\tconst ${enuma.$.name}='${enuma.$.name}';\r\n`;
                });
            }
            // [int]
            if(attr.attrint){
                attr.attrint.forEach(function(inta){
                    listAttr += `\t\t/** [int] ${inta.$.label} */\r\n`;
                    listAttr += `\t\tconst ${inta.$.name}='${inta.$.name}';\r\n`;
                });
            }
            // [option]
            if(attr.attroption){
                attr.attroption.forEach(function(option){
                    listAttr += `\t\t/** [option] ${option.$.label} */\r\n`;
                    listAttr += `\t\tconst ${option.$.name}='${option.$.name}';\r\n`;
                });
            }
            return listAttr;
        }

        var upperCaseFirstLetter = function(str) {
            return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
        }

        // Parsing list
        var listParsing = files.map(parseXML);

        // Run the parser over all files
        return Promise.all(listParsing).then(results => {
            console.log('Finished parsing');
        return { "extractDir": dir }
    });
    } else if (!fs.existsSync(sourcePath)) {
        console.log('Source path not found: '+sourcePath);
    } else if (!fs.existsSync(targetPath)) {
        console.log('Target path not found: '+targetPath);
    }
}
const fs = require("fs");
const path = require("path");
const appConst = require("./appConst");
const util = require("util");
const cp = require('child_process');
const mustache = require("mustache");
const getTextOptions = '--language={0} -a --sort-output --from-code=utf-8 --no-location --indent --add-comments=_COMMENT --keyword=___:1 --keyword=___:1,2c --keyword=n___:1,2 --keyword=pgettext:1c,2 --keyword=n___:1,2,4c --keyword=npgettext:1,2,4c --keyword="N_"  --keyword="text" -keyword="Text"';
const fileFormats = {'PHP': '.php', 'JavaScript': '.js', 'Mustache': '.mst', 'Vue': '.vue', 'XML': '.xml'};
const mustacheTags = ['[[', ']]'];

exports.getPOExtractor = async (sourcePath, targetPath, format) => {
    if (fs.existsSync(sourcePath) && fs.existsSync(targetPath)) {
        // Create temporary directory
        var dir = fs.mkdtempSync(path.join(`${targetPath}`, 'tmpExt-'));
        console.log('Temporary directory: ' + dir);

        // Get list of XML files
        var files = fs.readdirSync(sourcePath);

        function xgettext2PO(poFile, sourceFile, format){
            return new Promise((resolve) => {
                var command = "xgettext " + getTextOptions.replace("{0}", format) + " -o " + poFile + ' ' + sourceFile;
                // console.log(command);
                cp.exec(command, (error, stdout, stderr) => {
                    if (error) {
                        console.log(`exec error: ${error}`);
                        throw error;
                    }
                    console.log(`stdout ${sourceFile}: ${stdout}`);
                    console.log(`stderr: ${stderr}`);
                    resolve('Parse done');
                });
            });
        }

        function mst2PHP(poFile, sourceFile){
            return new Promise((resolve) => {
                var keys = [];
                var mstTemplate = fs.readFileSync(sourceFile, "utf8");
                // Search tokens in mustache template
                var tokens = mustache.parse(mstTemplate, mustacheTags);
                // Create content for PHP file
                var PHPfile = poFile.substr(0, poFile.lastIndexOf('.')) + '.php';
                var PHPcontent = '<?php\r\n';
                tokens.forEach(function(token){
                    if(token[0] === 'name') {
                        var matches = token[1].match(/^(?:(.+[^(::)])::)?(.+)$/);
                        PHPcontent += `_('${matches[1]}:${matches[2]}')\r\n`;
                        // keys.push({context: matches[1], key: matches[2], file: sourceFile, position: token[2]});
                    }
                });
                // Write PHP file
                fs.writeFile(PHPfile, PHPcontent, function(err) {
                    if(err) {
                        throw err;
                    }
                    resolve();
                });
                resolve(PHPfile);
            });
        }

        var extractPO = function parseAllFiles(file) {
            return new Promise((resolve) => {
                // Check if file type
                if(fs.lstatSync(path.join(sourcePath, file)).isFile()){
                    var filename = file.substr(0, file.lastIndexOf('.'));
                    var extension = file.substr(file.lastIndexOf('.'));
                    // Check if valid type (not .htaccess, .DS_Store, etc.)
                    if(filename !== '' && (format === 'all' || fileFormats[format].split(',').indexOf(extension) > -1)){
                        // If we treat all format, get the file format from the file extension
                        if(format === 'all'){
                            var BreakException = {};
                            try {
                                fileFormats.forEach(function(extensions) {
                                    if(extensions[1].split(',').indexOf(extension) > -1) {
                                        format = extensions[0];
                                        throw BreakException;
                                    }
                                });
                                resolve('Not parsed, format not found.');
                            } catch (e) {
                                if (e !== BreakException) throw e;
                            }
                        }

                        // Extract file to .po
                        var poFilePath = path.join(dir, filename + '.pot');
                        var sourceFilePath = path.join(sourcePath, file);
                        switch(format) {
                            case 'PHP':
                            case 'JavaScript':
                                xgettext2PO(poFilePath, sourceFilePath, format).then(function(){
                                    resolve('Parse done');
                                });
                                break;
                            case 'Mustache':
                                mst2PHP(poFilePath, sourceFilePath).then(function(tempPHPfile) {
                                    xgettext2PO(poFilePath, tempPHPfile, "PHP").then(function(){
                                        resolve('Parse done');
                                    });
                                });
                                break;
                            default:
                                resolve('Not parsed, format not found.');
                                break;
                        }
                    }
                }
                resolve('Not parsed, not a valid file');
            });
        }

        // Parsing list
        var listParsing = files.map(extractPO);

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
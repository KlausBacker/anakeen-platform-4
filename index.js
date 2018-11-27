const fs = require("fs");
const path = require("path");
const libxml = require("libxmljs");

const allXSD = path.resolve(__dirname, "all.xsd");

module.exports.checkFile = filePath => {
  
  const xsd = libxml.Document.fromXml(fs.readFileSync(allXSD), {
    baseUrl: allXSD
  });

  const buildXML = libxml.Document.fromXml(fs.readFileSync(filePath), {
    baseUrl: filePath,
    pedantic: true
  });

  if (buildXML.validate(xsd) === false) {
    return buildXML.validationErrors.reduce((acc, currentError) => {
      return acc+`${filePath} line :${currentError.line} : ${currentError.toString()}`;
    }, "\n");
  }

  return true
};
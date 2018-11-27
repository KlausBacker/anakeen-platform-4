const fs = require("fs");
const path = require("path");
const libxml = require("libxmljs");

const allXSD = path.resolve(__dirname, "all.xsd");

const cacheXSD = {
  "xsd": null,
  "xsdNamespace": null
};

module.exports.checkFile = filePath => {

  try {
    if (!cacheXSD.xsd) {
      cacheXSD.xsd = libxml.Document.fromXml(fs.readFileSync(allXSD), {
        baseUrl: allXSD
      });
    }

    if (!cacheXSD.xsdNamespace) {
      cacheXSD.xsdNamespace = cacheXSD.xsd.childNodes().filter(currentElement => {
        return currentElement.attr("namespace");
      }).reduce((acc, currentElement) => {
        acc[currentElement.attr("namespace").value()] = true;
        return acc;
      }, {});
    }

    const buildXML = libxml.Document.fromXml(fs.readFileSync(filePath), {
      baseUrl: filePath,
      pedantic: true
    });

    const namespaces = buildXML.namespaces().map(currentNamespace => {
      return currentNamespace.href();
    });
    //No namespaces, no check, unknown namespace no check
    if (buildXML.namespaces().length === 0 || namespaces.find(currentNS => {
      return !cacheXSD.xsdNamespace[currentNS];
    }) !== undefined) {
      return true;
    }

    if (buildXML.validate(cacheXSD.xsd) === false) {
      return buildXML.validationErrors.reduce((acc, currentError) => {
        return acc + `${filePath} line :${currentError.line} : ${currentError.toString()}`;
      }, "\n");
    }
    return true;
  } catch (e) {
    return `Unable to validate : ${filePath} : ${e.toString()}`;
  }
};
/* eslint-disable no-console */
const nameRegExp = /^[a-zA-Z][\w]+/;
const smartStructureName = /^[A-Z][A-Z0-9_]+$/;
const nameSpaceReg = /^[A-Za-z][A-Za-z0-9_]+$/;
const camelCase = require("camelcase");

exports.checkModuleName = moduleName => {
  if (
    moduleName === undefined ||
    (camelCase(moduleName, { pascalCase: true }) === moduleName && nameSpaceReg.test(moduleName))
  ) {
    return nameSpaceReg.test(moduleName);
  } else {
    return false;
  }
  // a suppr
  // return nameSpaceReg.test(moduleName);
};

exports.checkVendorName = vendorName => {
  if (
    vendorName === undefined ||
    (camelCase(vendorName, { pascalCase: true }) === vendorName && nameRegExp.test(vendorName))
  ) {
    return nameRegExp.test(vendorName);
  } else {
    return false;
  }
  // a suppr
  // return nameRegExp.test(vendorName);
};

exports.checkNamespace = namespace => {
  return nameRegExp.test(namespace);
};

exports.checkSmartStructureName = name => {
  return smartStructureName.test(name);
};

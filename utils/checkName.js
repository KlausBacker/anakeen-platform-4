const nameRegExp = /^[a-zA-Z][\w]+/;
const smartStructureName = /^[A-Z][A-Z0-9_]+$/;

exports.checkModuleName = moduleName => {
  return nameRegExp.test(moduleName);
};

exports.checkVendorName = vendorName => {
  return nameRegExp.test(vendorName);
};

exports.checkNamespace = namespace => {
  return nameRegExp.test(namespace);
};

exports.checkSmartStructureName = name => {
  return smartStructureName.test(name);
};

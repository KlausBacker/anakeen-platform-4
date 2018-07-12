const nameRegExp = /[\w]+/;

exports.checkModuleName = moduleName => {
  return nameRegExp.test(moduleName);
};

exports.checkVendorName = vendorName => {
  return nameRegExp.test(vendorName);
};

exports.checkNamespace = namespace => {
  return nameRegExp.test(namespace);
};

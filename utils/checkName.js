const nameRegExp = /[\w]+/;

exports.checkModuleName = (moduleName) => {
    return nameRegExp.test(moduleName);
};

exports.checkVendorName = (vendorName) => {
    return vendorName.test(vendorName);
};

exports.checkNamespace = (namespace) => {
    return namespace.test(namespace);
};
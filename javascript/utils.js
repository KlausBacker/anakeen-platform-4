var WIFF = WIFF || {};

WIFF.Utils = WIFF.Utils || {};

WIFF.Utils.parseModuleLicense = function (value) {
	var license = {
		url: '',
		label: ''
	};
	var elmts = value.match(/^((?:[a-z][a-z0-9+-.]*:\/\/)[^\s]+)(?:\s(.*))?$/i);
	if (elmts === null) {
		license.url = null;
		license.label = value;
		return license;
	}
	license.url = elmts[1];
	if (typeof(elmts[2]) === 'undefined') {
		license.label = license.url;
	} else {
		license.label = elmts[2];
	}
	return license;
};

WIFF.Utils.renderModuleLicense = function (value) {
	var license = WIFF.Utils.parseModuleLicense(value);
	if (license.url === null) {
		return '<span qtip="' + Ext.util.Format.htmlEncode(license.label) + '">' + Ext.util.Format.htmlEncode(license.label) + '</span>';
	}
	var url = Ext.util.Format.htmlEncode(license.url);
	var label = Ext.util.Format.htmlEncode(license.label);
	return '<a style="color: inherit; text-decoration: none" target="_blank" href="' + url + '" qtip="' + label + '">' + label + '</a>';
};

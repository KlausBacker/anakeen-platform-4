var asset = "index.php";
if (window.__karma__) {
    asset = "guest.php";
}

define([
    'text!dcpDocumentTemplate/'+asset+'?app=DOCUMENT&action=TEMPLATE&part=attribute&subPart=default',
    'dcpDocument/widgets/attributes/text/wText'
], function (data, widget) {
    'use strict';
    window.dcp = window.dcp || {};
    window.dcp.templates = window.dcp.templates || {};
    window.dcp.widgets = window.dcp.widgets || {};
    data = JSON.parse(data);
    window.dcp.templates.default = data.content;
    window.dcp.widgets.text = widget;
    return widget;
});
var asset = "index.php";
if (window.__karma__) {
    asset = "guest.php";
}

define([
    'text!dcpDocumentTemplate/' + asset + '?app=DOCUMENT&action=TEMPLATE&part=attribute&subPart=longtext',
    'dcpDocument/widgets/attributes/longtext/wLongtext'
], function (data, widget) {
    'use strict';
    window.dcp = window.dcp || {};
    window.dcp.templates = window.dcp.templates || {};
    window.dcp.widgets = window.dcp.widgets || {};
    data = JSON.parse(data);
    window.dcp.templates.longtext = data.content;
    window.dcp.widgets.longtext = widget;
    return widget;
});
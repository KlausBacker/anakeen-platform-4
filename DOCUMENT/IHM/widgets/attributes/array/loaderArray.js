var asset = "index.php";
if (window.__karma__) {
    asset = "guest.php";
}

define([
    'text!dcpContextRoot/' + asset + '?app=DOCUMENT&action=TEMPLATE&part=attribute&subPart=array',
    'dcpDocument/widgets/attributes/array/wArray'
], function (data, widget) {
    'use strict';
    window.dcp = window.dcp || {};
    window.dcp.templates = window.dcp.templates || {};
    window.dcp.widgets = window.dcp.widgets || {};
    data = JSON.parse(data);
    window.dcp.templates.array = data.content;
    window.dcp.widgets.array = widget;
    return widget;
});
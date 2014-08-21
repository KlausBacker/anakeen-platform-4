var asset = "index.php";
if (window.__karma__) {
    asset = "guest.php";
}

define([
    'text!template/' + asset + '?app=DOCUMENT&action=TEMPLATE&part=attribute&subPart=docid',
    'widgets/attributes/docid/wDocid'
], function (data, widget) {
    'use strict';
    window.dcp = window.dcp || {};
    window.dcp.templates = window.dcp.templates || {};
    window.dcp.widgets = window.dcp.widgets || {};
    data = JSON.parse(data);
    window.dcp.templates.docid = data.content;
    window.dcp.widgets.docid = widget;
    return widget;
});
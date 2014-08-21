var asset = "index.php";
if (window.__karma__) {
    asset = "guest.php";
}

define([
    'text!template/' + asset + '?app=DOCUMENT&action=TEMPLATE&part=attribute&subPart=file',
    'widgets/attributes/file/wFile'
], function (data, widget) {
    'use strict';
    window.dcp = window.dcp || {};
    window.dcp.templates = window.dcp.templates || {};
    window.dcp.widgets = window.dcp.widgets || {};
    data = JSON.parse(data);
    window.dcp.templates.file = data.content;
    window.dcp.widgets.file = widget;
    return widget;
});
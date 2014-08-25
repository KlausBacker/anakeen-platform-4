var asset = "index.php";
if (window.__karma__) {
    asset = "guest.php";
}

define([
    'text!template/' + asset + '?app=DOCUMENT&action=TEMPLATE&part=attribute&subPart=htmltext',
    'widgets/attributes/htmltext/wHtmltext'
], function (data, widget) {
    'use strict';
    window.dcp = window.dcp || {};
    window.dcp.templates = window.dcp.templates || {};
    window.dcp.widgets = window.dcp.widgets || {};
    data = JSON.parse(data);
    window.dcp.templates.htmltext = data.content;
    window.dcp.widgets.htmltext = widget;
    return widget;
});
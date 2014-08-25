define([
    'underscore',
    'widgets/widget',
    'kendo/kendo.notification'
], function (_) {
    'use strict';

    $.widget("dcp.dcpNotification", {


        options :             {
            // animation:false,
            autoHideAfter : 0,
            appendTo :      "#dcpNotificationContainer",
            position :      {
                top :   30,
                right : 100
            },
            templates :     [
                {
                    type :     "error",
                    template : '<div class="dcpNotification--content dcpNotification--error">' +
                                   '<span class="dcpNotification--symbol fa-stack fa-lg"><i class="fa fa-bolt"></i></span>' +
                                   '<div class="dcpNotification--message"><h1>#: title #</h1>' +
                        '<p>#: message #</p><p> #= htmlMessage #</p></div></div>'
                },
                {
                    type :     "warning",
                    template : '<div class="dcpNotification--content dcpNotification--warning">' +
                                   '<span class="dcpNotification--symbol fa-stack fa-lg"><i class="fa fa-warning fa-inverse"></i></span>' +
                                   '<div class="dcpNotification--message"><h1>#: title #</h1>' +
                        '<p>#: message #</p><p> #= htmlMessage #</p></div></div>'
                },
                {
                    type :     "info",
                    template : '<div class="dcpNotification--content dcpNotification--info">' +
                                   '<span class="dcpNotification--symbol fa-stack fa-lg"><i class="fa fa-info fa-inverse fa-flip-horizontal"></i></span>' +
                                   '<div class="dcpNotification--message"><h1>#: title #</h1>' +
                        '<p>#: message #</p><p> #= htmlMessage #</p></div></div>'
                },
                {
                    type :     "success",
                    template : '<div class="dcpNotification--content dcpNotification--info">' +
                                   '<span class="dcpNotification--symbol fa-stack fa-lg"><i class="fa fa-check fa-inverse"></i></span>' +
                                   '<div class="dcpNotification--message"><h1>#: title #</h1>' +
                        '<p>#: message #</p><p> #= htmlMessage #</p></div></div>'
                }
            ]
        },
        notificationElement : null,

        _create : function () {
            var scope = this;
            this.notificationElement = $('<div class="dcpNotification--widget" />');

            this.element.append($('<div id="dcpNotificationContainer" class="dcpNotifications"/>'));
            this.element.append(this.notificationElement);
            this.notificationElement.kendoNotification(this.options);

            this.element.on("notification", function (event, data) {
                scope.show(data.type, data);
            });
        },

        show : function (type, options) {
            options.title = options.title || '';
            options.message = options.message || '';
            options.htmlMessage = options.htmlMessage || '';
            this.notificationElement.data("kendoNotification").show({title : options.title,
                message :                                                    options.message,
                htmlMessage :                                                options.htmlMessage}, type);
        },

        showError :   function (options) {
            this.show("error", options);
        },

        showInfo :    function (options) {
            this.show("info", options);
        },

        showWarning : function (options) {
            this.show("warning", options);
        },

        showSuccess : function (options) {
            this.show("success", options);
        },

        clear : function () {
            this.notificationElement.kendoNotification("hide");
        }


    });
});
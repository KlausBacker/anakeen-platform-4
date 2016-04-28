define([
    'jquery',
    'underscore',
    'dcpDocument/widgets/widget',
    'kendo/kendo.notification'
], function wNotification($, _) {
    'use strict';

    $.widget("dcp.dcpNotification", {
        
        options: {
            // animation:false,
            autoHideAfter: 5000,
            appendTo: "#dcpNotificationContainer",
            position: {
                top: 60, // override by notification.less
                right: 100
            },
            templates: [
                {
                    type: "error",
                    template: '<div class="dcpNotification--content dcpNotification--error">' +
                    '<span class="dcpNotification--symbol fa-stack fa-lg"><span class="fa fa-bolt"></span></span>' +
                    '<div class="dcpNotification--message"><h1>#: title #</h1>' +
                    '<p>#: message #</p><p> #= htmlMessage #</p></div></div>'
                },
                {
                    type: "warning",
                    template: '<div class="dcpNotification--content dcpNotification--warning">' +
                    '<span class="dcpNotification--symbol fa-stack fa-lg"><span class="fa fa-warning fa-inverse"></span></span>' +
                    '<div class="dcpNotification--message"><h1>#: title #</h1>' +
                    '<p>#: message #</p><p> #= htmlMessage #</p></div></div>'
                },
                {
                    type: "info",
                    template: '<div class="dcpNotification--content dcpNotification--info">' +
                    '<span class="dcpNotification--symbol fa-stack fa-lg"><span class="fa fa-info fa-inverse fa-flip-horizontal"></span></span>' +
                    '<div class="dcpNotification--message"><h1>#: title #</h1>' +
                    '<p>#: message #</p><p> #= htmlMessage #</p></div></div>'
                },
                {
                    type: "notice",
                    template: '<div class="dcpNotification--content dcpNotification--notice">' +
                    '<span class="dcpNotification--symbol fa-stack fa-lg"><span class="fa fa-info fa-inverse fa-flip-horizontal"></span></span>' +
                    '<div class="dcpNotification--message"><h1>#: title #</h1>' +
                    '<p>#: message #</p><p> #= htmlMessage #</p></div></div>'
                },
                {
                    type: "success",
                    template: '<div class="dcpNotification--content dcpNotification--info">' +
                    '<span class="dcpNotification--symbol fa-stack fa-lg"><span class="fa fa-check fa-inverse"></span></span>' +
                    '<div class="dcpNotification--message"><h1>#: title #</h1>' +
                    '<p>#: message #</p><p> #= htmlMessage #</p></div></div>'
                }
            ]
        },
        notificationElement: null,

        _create: function wNotificationCreate() {
            var scope = this;
            this.notificationElement = $('<div class="dcpNotification--widget" />');

            this.element.append($('<div id="dcpNotificationContainer" class="dcpNotifications"/>'));
            this.element.append(this.notificationElement);

            this.notificationElement.kendoNotification(this.options);

            this.element.on("notification", function wNotificationOn(event, data) {
                scope.show(data.type, data);
            });
        },
        
        show: function wNotificationShow(type, options) {
            options.title = options.title || '';
            options.message = options.message || '';
            options.htmlMessage = options.htmlMessage || '';
            if ($.inArray(type, ["error","info","warning","success","notice"]) === -1) {
                type="info";
            }
            this.notificationElement.data("kendoNotification").show({
                title: options.title,
                message: options.message,
                htmlMessage: options.htmlMessage // @TODO NEED TO CLEAN HTML TO PREVENT XSS
            }, type);
        },

        showError: function wNotificationShowError(options) {
            this.show("error", options);
        },

        showInfo: function wNotificationShowInfo(options) {
            this.show("info", options);
        },

        showWarning: function wNotificationShowWarning(options) {
            this.show("warning", options);
        },

        showSuccess: function wNotificationShowSuccess(options) {
            this.show("success", options);
        },

        clear: function wNotificationClear() {
            this.notificationElement.kendoNotification("hide");
        }
        
    });
});
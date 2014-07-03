define([
    'underscore',
    'widgets/widget'
], function (_) {
    'use strict';

    $.widget("dcp.dcpNotification", {


        options: {
           // animation:false,
            autoHideAfter: 0,
          //  appendTo : "#dcpNotificationContainer",
            position: {
                top: 30,
                right: 100
            },
            templates: [
                {
                    type: "error",
                    template: '<div class="dcpNotification--content dcpNotification--error">' +
                        '<span class="dcpNotification--symbol fa-stack fa-lg"><i class="fa fa-circle-o fa-stack-2x"></i><i class="fa fa-times fa-stack-1x fa-inverse"></i></span></i>' +
                        '<div class="dcpNotification--title">#: title #</div>'+
                        '<p class="dcpNotification--message">#: message #</p>'+
                        '<p class="dcpNotification--message">#= htmlMessage #</p> </div>'
                },
                {
                    type: "warning",
                    template: '<div class="dcpNotification--content dcpNotification--warning">' +
                        '<span class="dcpNotification--symbol fa-stack fa-lg"><i class="fa fa-warning fa-inverse"></i></span>' +
                        '<div class="dcpNotification--title">#: title #</div>'+
                        '<p class="dcpNotification--message">#: message #</p>'+
                        '<p class="dcpNotification--message">#= htmlMessage #</p> </div>'
                },
                {
                    type: "info",
                    template: '<div class="dcpNotification--content dcpNotification--info">' +
                        '<span class="dcpNotification--symbol fa-stack fa-lg"><i class="fa fa-comment-o fa-inverse fa-flip-horizontal"></i></span>' +
                        '<div class="dcpNotification--title">#: title #</div>'+
                        '<p class="dcpNotification--message">#: message #</p>'+
                        '<p class="dcpNotification--message">#= htmlMessage #</p> </div>'
                },
                {
                    type: "success",
                    template: '<div class="dcpNotification--content dcpNotification--info">' +
                        '<span class="dcpNotification--symbol fa-stack fa-lg"><i class="fa fa-check fa-inverse"></i></span>' +
                        '<div class="dcpNotification--title">#: title #</div>'+
                        '<p class="dcpNotification--message">#: message #</p>'+
                        '<p class="dcpNotification--message">#= htmlMessage #</p> </div>'
                }
            ]
        },
        notificationElement: null,

        _create: function () {
            this.notificationElement = $('<div class="dcpNotification" />');


            this.element.append($('<div id="dcpNotificationContainer" class="dcpNotification"/>'));
            this.element.append(this.notificationElement);
            this.notificationElement.kendoNotification(this.options);
        },

        show: function (type, options) {
            options.title=options.title || '';
            options.message=options.message || '';
            options.htmlMessage=options.htmlMessage || '';
            this.notificationElement.data("kendoNotification").show({title: options.title,
                message: options.message,
                htmlMessage:  options.htmlMessage}, type);
        },


        showError: function (options) {
            this.show("error", options);
        },
        showInfo: function (options) {
            this.show("info", options);
        },
        showWarning: function (options) {
            this.show("warning", options);
        },
        showSuccess: function (options) {
            this.show("success", options);
        },

        clear: function () {
            this.notificationElement.kendoNotification("hide");

        }


    });
});
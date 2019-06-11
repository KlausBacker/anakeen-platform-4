define([
  "jquery",
  "underscore",
  "dcpDocument/widgets/widget"
], function wNotification($, _) {
  "use strict";

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
          template:
            '<div class="dcpNotification--content dcpNotification--error">' +
            '<span class="dcpNotification--symbol fa-stack fa-lg"><span class="fa fa-bolt"></span></span>' +
            '<div class="dcpNotification--message"><h1>#: title #</h1>' +
            "<p>#: message #</p><p> #= htmlMessage #</p></div></div>"
        },
        {
          type: "warning",
          template:
            '<div class="dcpNotification--content dcpNotification--warning">' +
            '<span class="dcpNotification--symbol fa-stack fa-lg"><span class="fa fa-warning fa-inverse"></span></span>' +
            '<div class="dcpNotification--message"><h1>#: title #</h1>' +
            "<p>#: message #</p><p> #= htmlMessage #</p></div></div>"
        },
        {
          type: "info",
          template:
            '<div class="dcpNotification--content dcpNotification--info">' +
            '<span class="dcpNotification--symbol fa-stack fa-lg"><span class="fa fa-info fa-inverse fa-flip-horizontal"></span></span>' +
            '<div class="dcpNotification--message"><h1>#: title #</h1>' +
            "<p>#: message #</p><p> #= htmlMessage #</p></div></div>"
        },
        {
          type: "notice",
          template:
            '<div class="dcpNotification--content dcpNotification--notice">' +
            '<span class="dcpNotification--symbol fa-stack fa-lg"><span class="fa fa-info fa-inverse fa-flip-horizontal"></span></span>' +
            '<div class="dcpNotification--message"><h1>#: title #</h1>' +
            "<p>#: message #</p><p> #= htmlMessage #</p></div></div>"
        },
        {
          type: "success",
          template:
            '<div class="dcpNotification--content dcpNotification--info">' +
            '<span class="dcpNotification--symbol fa-stack fa-lg"><span class="fa fa-check fa-inverse"></span></span>' +
            '<div class="dcpNotification--message"><h1>#: title #</h1>' +
            "<p>#: message #</p><p> #= htmlMessage #</p></div></div>"
        }
      ],

      labels: {
        moreButton: "View more ...",
        moreWindowTitle: "Notification"
      }
    },
    notificationElement: null,

    _create: function wNotificationCreate() {
      var scope = this;
      this.notificationElement = $('<div class="dcpNotification--widget" />');

      this.element.append(
        $('<div id="dcpNotificationContainer" class="dcpNotifications"/>')
      );
      this.element.append(this.notificationElement);

      this.options.show = _.bind(this.showMore, this);
      this.notificationElement.kendoNotification(this.options);

      this.element.on("notification", function wNotificationOn(event, data) {
        scope.show(data.type, data);
      });
    },

    showMore: function wNotificationShowMessage(event) {
      var $boxMessage = event.element;
      var $message = $boxMessage.find(".dcpNotification--message");
      var $more;
      var widgetNotification = this;

      if ($message.prop("scrollHeight") > $message.height()) {
        $more = $('<div class="dcpNotification--more"/>').text(
          this.options.labels.moreButton
        );
        $boxMessage.append($more);
        $more.on("click", function wNotificationClickMode(event) {
          var $clone = $boxMessage.clone();
          var cloneWindow;

          if (widgetNotification.cloneWindow) {
            widgetNotification.cloneWindow.destroy();
            widgetNotification.cloneWindow = null;
          }
          $boxMessage.append($clone);
          $clone.find(".dcpNotification--more").remove();
          $clone
            .find(".dcpNotification--message")
            .prepend($clone.find(".dcpNotification--symbol"));
          event.stopPropagation();
          cloneWindow = $clone
            .kendoWindow({
              title: widgetNotification.options.labels.moreWindowTitle,
              width: "400px"
            })
            .data("kendoWindow");
          cloneWindow.center();
          widgetNotification.cloneWindow = cloneWindow;
        });
      }
    },

    show: function wNotificationShow(type, options) {
      options.title = options.title || "";
      options.message = options.message || "";
      options.htmlMessage = options.htmlMessage || "";
      if (
        $.inArray(type, ["error", "info", "warning", "success", "notice"]) ===
        -1
      ) {
        type = "info";
      }
      this.notificationElement.data("kendoNotification").show(
        {
          title: options.title,
          message: options.message,
          htmlMessage: options.htmlMessage // @TODO NEED TO CLEAN HTML TO PREVENT XSS
        },
        type
      );

      if (type === "error" && console.error) {
        console.error(options.title, options.message, options.htmlMessage);
      }
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
      if (this.cloneWindow) {
        this.cloneWindow.destroy();
        this.cloneWindow = null;
      }
    }
  });
});

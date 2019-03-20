import "@progress/kendo-ui/js/kendo.notification";

export default {
  name: "ank-notifier",
  props: {
    // Position of notification on screen
    position: {
      type: String,
      validator: value =>
        ["top-right", "top-left", "bottom-right", "bottom-left"].indexOf(
          value.toLowerCase()
        ) !== -1
    },

    // Types of notifications hidden to the user
    hideTypes: {
      type: String,
      validator: value => {
        try {
          let object = JSON.parse(value);
          if (Array.isArray(object)) {
            for (let str of object) {
              if (typeof str !== "string") {
                return false;
              }
              if (
                ["error", "warning", "success", "info", "notice"].indexOf(
                  str.toLowerCase()
                ) === -1
              ) {
                return false;
              }
            }
            return true;
          } else {
            return false;
          }
        } catch (Error) {
          return false;
        }
      }
    },

    // Default title for each type of notification
    defaultTitles: {
      type: String,
      validator: value => {
        try {
          let object = JSON.parse(value);
          for (let property in object) {
            if (object.hasOwnProperty(property)) {
              if (
                ["error", "warning", "success", "info", "notice"].indexOf(
                  property.toLowerCase()
                ) !== -1
              ) {
                if (typeof object[property] !== "string") {
                  return false;
                }
              } else {
                return false;
              }
            }
          }
          return true;
        } catch (error) {
          return false;
        }
      }
    },

    // Default display time for each type of notification
    displayTimes: {
      type: String,
      validator: value => {
        try {
          let object = JSON.parse(value);
          for (let property in object) {
            if (object.hasOwnProperty(property)) {
              if (
                ["error", "warning", "success", "info", "notice"].indexOf(
                  property.toLowerCase()
                ) !== -1
              ) {
                if (!Number.isInteger(object[property])) {
                  return false;
                }
              } else {
                return false;
              }
            }
          }
          return true;
        } catch (error) {
          return false;
        }
      }
    },

    // Default type if not defined in the notification event
    defaultType: {
      type: String,
      validator: value =>
        ["error", "warning", "success", "info", "notice"].indexOf(
          value.toLowerCase()
        ) !== -1
    },

    // Default value for property closable, if not defined in the notification event
    defaultClosable: {
      type: String,
      validator: value => {
        try {
          let object = JSON.parse(value);
          for (let property in object) {
            if (object.hasOwnProperty(property)) {
              if (
                ["error", "warning", "success", "info", "notice"].indexOf(
                  property.toLowerCase()
                ) !== -1
              ) {
                if (typeof object[property] !== "boolean") {
                  return false;
                }
              } else {
                return false;
              }
            }
          }
          return true;
        } catch (error) {
          return false;
        }
      }
    }
  },

  data() {
    return {
      // Kendo Notifier
      kendoNotifier: {},

      // Hidden types
      hiddenTypes: [],

      // Default type
      defaultTypeInternal: "info",

      // Default positions
      positionTop: 20,
      positionBottom: null,
      positionLeft: null,
      positionRight: 0,

      // Default display times
      defaultDisplayTimeError: 5000,
      defaultDisplayTimeWarning: 5000,
      defaultDisplayTimeSuccess: 5000,
      defaultDisplayTimeInfo: 5000,
      defaultDisplayTimeNotice: 5000,

      // Default titles
      defaultErrorTitle: "",
      defaultWarningTitle: "",
      defaultSuccessTitle: "",
      defaultInfoTitle: "",
      defaultNoticeTitle: "",

      // Default closable
      defaultClosableError: true,
      defaultClosableWarning: true,
      defaultClosableSuccess: true,
      defaultClosableInfo: true,
      defaultClosableNotice: true
    };
  },

  methods: {
    publishNotification(event) {
      let eventData;
      if (event.detail && event.detail[0]) {
        eventData = event.detail[0];
      }

      // Get type from event or default
      let type = this.defaultTypeInternal;
      if (
        eventData &&
        eventData.type &&
        ["error", "warning", "success", "info", "notice"].indexOf(
          eventData.type.toLowerCase()
        ) !== -1
      ) {
        type = eventData.type.toLowerCase();
      }

      // Get displayTime from event or default
      let displayTime;
      if (
        eventData &&
        eventData.options &&
        eventData.options.displayTime &&
        typeof eventData.options.displayTime === "number"
      ) {
        displayTime = eventData.options.displayTime;
      } else {
        switch (type) {
          case "error":
            displayTime = this.defaultDisplayTimeError;
            break;
          case "warning":
            displayTime = this.defaultDisplayTimeWarning;
            break;
          case "success":
            displayTime = this.defaultDisplayTimeSuccess;
            break;
          case "info":
            displayTime = this.defaultDisplayTimeInfo;
            break;
          case "notice":
            displayTime = this.defaultDisplayTimeNotice;
            break;
          default:
            displayTime = 5000;
            break;
        }
      }

      // Get title from event or default
      let title;
      if (eventData && eventData.content && eventData.content.title) {
        title = eventData.content.title;
      } else {
        switch (type) {
          case "error":
            title = this.defaultErrorTitle;
            break;
          case "warning":
            title = this.defaultWarningTitle;
            break;
          case "success":
            title = this.defaultSuccessTitle;
            break;
          case "info":
            title = this.defaultInfoTitle;
            break;
          case "notice":
            title = this.defaultNoticeTitle;
            break;
          default:
            title = "";
            break;
        }
      }

      // Get closable from event or default
      let closable;
      if (
        eventData &&
        eventData.options &&
        typeof eventData.options.closable !== "undefined" &&
        typeof eventData.options.closable === "boolean"
      ) {
        closable = eventData.options.closable;
      } else {
        switch (type) {
          case "error":
            closable = this.defaultClosableError;
            break;
          case "warning":
            closable = this.defaultClosableWarning;
            break;
          case "success":
            closable = this.defaultClosableSuccess;
            break;
          case "info":
            closable = this.defaultClosableInfo;
            break;
          case "notice":
            closable = this.defaultClosableNotice;
            break;
          default:
            closable = true;
            break;
        }
      }

      // Get message from event or empty message
      // if html content is passed, use it, textContent otherwise
      let message = "";
      let isHtml = false;
      if (eventData && eventData.content && eventData.content.htmlContent) {
        message = eventData.content.htmlContent;
        isHtml = true;
      } else if (
        eventData &&
        eventData.content &&
        eventData.content.textContent
      ) {
        message = eventData.content.textContent;
      }

      // Display notification with parameters if type is not hidden
      let textContent = isHtml ? "" : message;
      let htmlContent = isHtml ? message : "";
      let notCanceled = this.$emit("beforeNotification", {
        content: {
          title: title,
          textContent: textContent,
          htmlContent: htmlContent
        },
        type: type,
        options: {
          displayTime: displayTime,
          closable: closable
        }
      });

      if (notCanceled) {
        if (this.hiddenTypes.indexOf(type) === -1) {
          this.showNotification(
            type,
            title,
            message,
            isHtml,
            displayTime,
            closable
          );
        }
      }
    },

    closeAllNotification() {
      this.kendoNotifier.hide();
    },

    showNotification(type, title, content, isHtml, displayTime, closable) {
      this.kendoNotifier.setOptions({
        autoHideAfter: displayTime
      });
      this.kendoNotifier.show(
        {
          title: title,
          message: content,
          isHtml: isHtml,
          closable: closable
        },
        type
      );

      // To make close buttons work
      this.$(".notification-close")
        .off("click")
        .on("click", event => {
          let _this = this;
          this.$(event.target)
            .closest(".k-notification")
            .fadeOut(200, function() {
              _this.$(this).unwrap();
              this.remove();
            });
        });
    },

    // Init kendo component at mount
    initKendoNotifier() {
      // Define custom template for each type
      let errorTemplate = `
      <div class=" notification error-notification">
        <div class="notification-icon material-icons"></div>
        <div class="notification-content">
          <div class="notification-content-header">
            <div class="notification-title">#= title #</div>
            # if (closable) { #
            <div class="notification-close"><span class="k-icon k-i-close"></span></div>
            # } #          
          </div>
          # if (isHtml) { #
          <div class="notification-message">#= message #</div>
          # } else { #
          <div class="notification-message">#: message #</div>
          # } #
        </div>
      </div>
    `;

      let warningTemplate = `
      <div class=" notification warning-notification">
        <div class="notification-icon material-icons"></div>
        <div class="notification-content">
          <div class="notification-content-header">
            <div class="notification-title">#= title #</div>
            # if (closable) { #
            <div class="notification-close"><span class="k-icon k-i-close"></span></div>
            # } #
          </div>
          # if (isHtml) { #
          <div class="notification-message">#= message #</div>
          # } else { #
          <div class="notification-message">#: message #</div>
          # } #
        </div>
      </div>
    `;

      let successTemplate = `
      <div class=" notification success-notification">
        <div class="notification-icon material-icons"></div>
        <div class="notification-content">
          <div class="notification-content-header">
            <div class="notification-title">#= title #</div>
            # if (closable) { #
            <div class="notification-close"><span class="k-icon k-i-close"></span></div>
            # } #
          </div>
          # if (isHtml) { #
          <div class="notification-message">#= message #</div>
          # } else { #
          <div class="notification-message">#: message #</div>
          # } #
        </div>
      </div>
    `;

      let infoTemplate = `
      <div class=" notification info-notification">
        <div class="notification-icon material-icons"></div>
        <div class="notification-content">
          <div class="notification-content-header">
            <div class="notification-title">#= title #</div>
            # if (closable) { #
            <div class="notification-close"><span class="k-icon k-i-close"></span></div>
            # } #          
          </div>
          # if (isHtml) { #
          <div class="notification-message">#= message #</div>
          # } else { #
          <div class="notification-message">#: message #</div>
          # } #
        </div>
      </div>
    `;

      let noticeTemplate = `
      <div class=" notification notice-notification">
        <div class="notification-icon material-icons"></div>
        <div class="notification-content">
          <div class="notification-content-header">
            <div class="notification-title">#= title #</div>
            # if (closable) { #
            <div class="notification-close"><span class="k-icon k-i-close"></span></div>
            # } #          
          </div>
          # if (isHtml) { #
          <div class="notification-message">#= message #</div>
          # } else { #
          <div class="notification-message">#: message #</div>
          # } #
        </div>
      </div>
    `;

      let animation;
      if (this.positionLeft === null) {
        animation = "slideIn:left";
      } else {
        animation = "slideIn:right";
      }

      // Init notifier, defining static parameters
      this.kendoNotifier = this.$(".ank-notifier")
        .kendoNotification({
          animation: {
            open: {
              effects: animation
            },
            close: {
              effects: animation,
              reverse: true
            }
          },
          hideOnClick: false,
          position: {
            top: this.positionTop,
            bottom: this.positionBottom,
            left: this.positionLeft,
            right: this.positionRight
          },
          templates: [
            {
              type: "error",
              template: errorTemplate
            },
            {
              type: "warning",
              template: warningTemplate
            },
            {
              type: "success",
              template: successTemplate
            },
            {
              type: "info",
              template: infoTemplate
            },
            {
              type: "notice",
              template: noticeTemplate
            }
          ]
        })
        .data("kendoNotification");
    },

    // Override default parameters with redefined ones in props
    parseDefaultParameters() {
      // Type
      if (this.defaultType) {
        this.defaultTypeInternal = this.defaultType.toLowerCase();
      } else {
        this.defaultTypeInternal = "info";
      }

      // Position and animation
      if (this.position) {
        switch (this.position.toLowerCase()) {
          case "top-right":
            this.positionTop = 20;
            this.positionBottom = null;
            this.positionLeft = null;
            this.positionRight = 0;
            break;
          case "top-left":
            this.positionTop = 20;
            this.positionBottom = null;
            this.positionLeft = 0;
            this.positionRight = null;
            break;
          case "bottom-left":
            this.positionTop = null;
            this.positionBottom = 20;
            this.positionLeft = 0;
            this.positionRight = null;
            break;
          case "bottom-right":
            this.positionTop = null;
            this.positionBottom = 20;
            this.positionLeft = null;
            this.positionRight = 0;
            break;
          default:
            break;
        }
      }

      // Display times
      if (this.displayTimes) {
        let displayTimeObject = JSON.parse(this.displayTimes);
        for (let property in displayTimeObject) {
          if (displayTimeObject.hasOwnProperty(property)) {
            switch (property.toLowerCase()) {
              case "error":
                this.defaultDisplayTimeError = displayTimeObject[property];
                break;
              case "warning":
                this.defaultDisplayTimeWarning = displayTimeObject[property];
                break;
              case "success":
                this.defaultDisplayTimeSuccess = displayTimeObject[property];
                break;
              case "info":
                this.defaultDisplayTimeInfo = displayTimeObject[property];
                break;
              case "notice":
                this.defaultDisplayTimeNotice = displayTimeObject[property];
                break;
              default:
                break;
            }
          }
        }
      }

      // Titles
      this.defaultErrorTitle = this.translations.defaultErrorTitle;
      this.defaultWarningTitle = this.translations.defaultWarningTitle;
      this.defaultSuccessTitle = this.translations.defaultSuccessTitle;
      this.defaultInfoTitle = this.translations.defaultInfoTitle;
      this.defaultNoticeTitle = this.translations.defaultNoticeTitle;
      if (this.defaultTitles) {
        let titleObject = JSON.parse(this.defaultTitles);
        for (let property in titleObject) {
          if (titleObject.hasOwnProperty(property)) {
            switch (property.toLowerCase()) {
              case "error":
                this.defaultErrorTitle = titleObject[property];
                break;
              case "warning":
                this.defaultWarningTitle = titleObject[property];
                break;
              case "success":
                this.defaultSuccessTitle = titleObject[property];
                break;
              case "info":
                this.defaultInfoTitle = titleObject[property];
                break;
              case "notice":
                this.defaultNoticeTitle = titleObject[property];
                break;
              default:
                break;
            }
          }
        }
      }

      // Closable
      if (this.defaultClosable) {
        let closableObject = JSON.parse(this.defaultClosable);
        for (let property in closableObject) {
          if (closableObject.hasOwnProperty(property)) {
            switch (property.toLowerCase()) {
              case "error":
                this.defaultClosableError = closableObject[property];
                break;
              case "warning":
                this.defaultClosableWarning = closableObject[property];
                break;
              case "success":
                this.defaultClosableSuccess = closableObject[property];
                break;
              case "info":
                this.defaultClosableInfo = closableObject[property];
                break;
              case "notice":
                this.defaultClosableNotice = closableObject[property];
                break;
              default:
                break;
            }
          }
        }
      }

      // Hidden types
      if (this.hideTypes) {
        for (let type of JSON.parse(this.hideTypes)) {
          this.hiddenTypes.push(type.toLowerCase());
        }
      }
    }
  },

  computed: {
    translations() {
      return {
        // defaultErrorTitle: this.$pgettext("Notifier", "Error"),
        // defaultWarningTitle: this.$pgettext("Notifier", "Warning"),
        // defaultSuccessTitle: this.$pgettext("Notifier", "Success"),
        // defaultInfoTitle: this.$pgettext("Notifier", "Info"),
        // defaultNoticeTitle: this.$pgettext("Notifier", "Notice")
        defaultErrorTitle: "Error",
        defaultWarningTitle: "Warning",
        defaultSuccessTitle: "Success",
        defaultInfoTitle: "Info",
        defaultNoticeTitle: "Notice"
      };
    }
  },

  mounted() {
    this.parseDefaultParameters();
    this.initKendoNotifier();
  }
};

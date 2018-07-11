import successTemplate from "./template/DefaultSuccessTemplate.template.kd";
import errorTemplate from "./template/DefaultErrorTemplate.template.kd";

export default {
  name: "admin-notification",
  data() {
    return {
      kendoNotificationEl: null
    };
  },
  computed: {
    kendoNotification() {
      if (this.kendoNotificationEl) {
        return this.kendoNotificationEl.data("kendoNotification");
      }
      return null;
    }
  },
  mounted() {
    this.kendoNotificationEl = this.$(
      this.$refs.notifications
    ).kendoNotification({
      position: {
        top: 40,
        right: 20
      },
      templates: [
        {
          type: "admin-success",
          template: this.$kendo.template(successTemplate)
        },
        {
          type: "admin-error",
          template: this.$kendo.template(errorTemplate)
        }
      ]
    });
    this.$store.subscribeAction(action => {
      if (action.type === "showMessage") {
        this.showMessage(action.payload.content || {}, action.payload.type);
      }
    });
  },
  methods: {
    showMessage({ title = "", message = "" }, type = "admin-success") {
      if (this.kendoNotification) {
        this.kendoNotification.show({ title, message }, type);
      }
    }
  }
};

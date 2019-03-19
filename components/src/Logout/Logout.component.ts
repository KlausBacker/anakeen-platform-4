import Vue from "vue";
import { Component, Prop } from "vue-property-decorator";
import { _enableReady} from "../../mixins/AnkVueComponentMixin/IeventUtilsMixin";

@Component({
  name: "ank-logout"
})
export default class LogoutComponent extends Vue {
  @Prop({type: String, default: ""}) public title;
  public logout() {
      let eventName = "beforeLogout";
      let options = { cancelable: true, bubbles: false, detail: false };
      let event;
      if (typeof CustomEvent === "function") {
        event = new CustomEvent(eventName, options);
      } else {
        event = document.createEvent("CustomEvent");
        event.initCustomEvent(
          eventName,
          options.bubbles,
          options.cancelable,
          options.detail
        );
      }

      this.$el.parentNode.dispatchEvent(event);
      if (event.defaultPrevented) {
        this.$emit("logoutCanceled");
      } else {
        this.$http
          .delete("/src/logout/session")
          .then(response => {
            this.$emit("afterLogout", response.data);
            document.location.assign(response.data.location || "/");
          })
          .catch(error => {
            if (error.status === 401) {
              this.$emit("afterLogout", error.data);
              document.location.assign(error.data.location || "/");
            } else {
              document.location.reload();
            }
          });
      }
    }
  public get translations() {
      return {
        title: this.$pgettext("Logout", "Logout")
      };
    }
  public mounted() {
    _enableReady();
  }
};

import Vue from "vue";
import { Component, Prop } from "vue-property-decorator";
import { _enableReady } from "../../mixins/AnkVueComponentMixin/IeventUtilsMixin";
import VueSetup from "../setup.js";
Vue.use(VueSetup);
@Component({
  name: "ank-logout"
})
export default class LogoutComponent extends Vue {
  @Prop({ type: String, default: "" }) public title;
  public logout() {
    kendo.ui.progress(kendo.jQuery("body"), true);
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
        .delete("/components/user/session")
        .then(response => {
          this.$emit("afterLogout", response.data);
          document.location.assign(response.data.location || "/");
          kendo.ui.progress(kendo.jQuery("body"), false);
        })
        .catch(error => {
          if (error.status === 401) {
            this.$emit("afterLogout", error.data);
            document.location.assign(error.data.location || "/");
          } else {
            this.$emit("afterLogout", "networkError");
            kendo.ui.progress(kendo.jQuery("body"), false);
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
}
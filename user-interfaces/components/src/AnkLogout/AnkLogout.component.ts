import Vue from "vue";
import { Component, Mixins, Prop } from "vue-property-decorator";
import EventUtilsMixin from "../../mixins/AnkVueComponentMixin/EventUtilsMixin";
import ReadyMixin from "../../mixins/AnkVueComponentMixin/ReadyMixin";
import VueSetup from "../setup.js";
Vue.use(VueSetup);
@Component({
  name: "ank-logout"
})
export default class LogoutComponent extends Mixins(EventUtilsMixin, ReadyMixin) {
  @Prop({ type: String, default: "" }) public title;
  public logout() {
    kendo.ui.progress(kendo.jQuery("body"), true);
    const event = this.$emitCancelableEvent("beforeLogout");
    if (event.isDefaultPrevented()) {
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
    this._enableReady();
  }
}

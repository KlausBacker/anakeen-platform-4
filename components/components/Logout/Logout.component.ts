import Vue from "vue";
import AnkMixins from "../../mixins/AnkVueComponentMixin";
import { Component, Prop, Mixins } from "vue-property-decorator";

@Component({
  name: "ank-logout",
  mixins: [AnkMixins],
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
          .delete("/components/logout/session")
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
    this._enableReady();
  }
};

import "@progress/kendo-ui/js/kendo.progressbar";
import { Component, Mixins, Prop } from "vue-property-decorator";
import EventUtilsMixin from "../../mixins/AnkVueComponentMixin/EventUtilsMixin";
import I18nMixin from "../../mixins/AnkVueComponentMixin/I18nMixin";
import ReadyMixin from "../../mixins/AnkVueComponentMixin/ReadyMixin";
import AnakeenGlobalController from "../AnkController";

@Component({
  name: "ank-logout"
})
export default class LogoutComponent extends Mixins(EventUtilsMixin, ReadyMixin, I18nMixin) {
  @Prop({ type: String, default: "" }) public title;
  @Prop({ type: Boolean, default: true }) public withCloseConfirmation;
  @Prop({ type: Boolean, default: true }) public autoDestroy;

  public logout() {
    const event = this.$emitCancelableEvent("beforeLogout");

    if (event.isDefaultPrevented()) {
      this.$emit("logoutCanceled");
    } else {
      if (this.withCloseConfirmation && !this.checkUnSaveElement()) {
        this.$emit("logoutCanceled");
        return;
      }
      kendo.ui.progress(kendo.jQuery("body"), true);
      //Unmount all the the Smart Element - to release the lock, before disconnection
      let defaultPromise = Promise.resolve();
      if (this.autoDestroy) {
        defaultPromise = AnakeenGlobalController.getControllers().reduce((acc, currentController) => {
          return acc.then(() => {
            return currentController.tryToDestroy({ testDirty: false }).catch(err => {
              console.error("Unable to destroy", err, currentController);
              throw err;
            }) as Promise<void>;
          });
        }, Promise.resolve());
      }

      defaultPromise
        .then(() => {
          kendo.ui.progress(kendo.jQuery("body"), false);
          return this.logoutRequest();
        })
        .catch(err => {
          kendo.ui.progress(kendo.jQuery("body"), false);
          throw err;
        });
    }
  }

  public logoutRequest() {
    return this.$http
      .delete("/components/user/session")
      .then(response => {
        this.$emit("afterLogout", response.data);
        document.location.assign(response.data.data.location || "/");
        kendo.ui.progress(kendo.jQuery("body"), false);
      })
      .catch(error => {
        if (error.status === 401) {
          this.$emit("afterLogout", error.data);
          document.location.assign(error.data.data.location || "/");
        } else {
          this.$emit("afterLogout", "networkError");
          kendo.ui.progress(kendo.jQuery("body"), false);
        }
      });
  }

  public checkUnSaveElement() {
    //Check the state of the current SE
    const checkModified = AnakeenGlobalController.getControllers().reduce((acc, currentController) => {
      if (currentController.getProperty("isModified")) {
        acc += `\n ${currentController.getProperty("title")} ${this.translations.modifications}`;
      }
      return acc;
    }, "");

    //If there are SE with modification ask to the user, before the close
    if (checkModified) {
      return window.confirm(`${checkModified} \n ${this.translations.logout}`);
    }
    return true;
  }

  public get translations() {
    return {
      title: this.$t("Logout.Logout"),
      logout: this.$t("Logout.Confirm"),
      modifications: this.$t("Logout.modifications")
    };
  }

  public mounted() {
    this._enableReady();
  }
}

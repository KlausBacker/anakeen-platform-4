import "@progress/kendo-ui/js/kendo.progressbar";
import { Component, Mixins, Prop } from "vue-property-decorator";
import EventUtilsMixin from "../../mixins/AnkVueComponentMixin/EventUtilsMixin";
import I18nMixin from "../../mixins/AnkVueComponentMixin/I18nMixin";
import ReadyMixin from "../../mixins/AnkVueComponentMixin/ReadyMixin";

@Component({
  name: "ank-logout"
})
export default class LogoutComponent extends Mixins(EventUtilsMixin, ReadyMixin, I18nMixin) {
  @Prop({ type: String, default: "" }) public title;
  @Prop({ type: Boolean, default: true }) public withCloseConfirmation;
  @Prop({ type: Boolean, default: true }) public autoDestroy;

  public logout(): void {
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
      //I do not use the npm controller here because, there is not necesseraly some SE in the page
      if (window.ank && window.ank.smartElement && window.ank.smartElement.globalController && this.autoDestroy) {
        defaultPromise = window.ank.smartElement.globalController.getControllers().reduce((acc, currentController) => {
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

  public logoutRequest(): Promise<void> {
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

  public checkUnSaveElement(): boolean | string {
    //Check the state of the current SE
    //Check if the global controller is here, if not there is no SE so everything is good
    if (!(window.ank && window.ank.smartElement && window.ank.smartElement.globalController)) {
      return true;
    }
    const checkModified = window.ank.smartElement.globalController.getControllers().reduce((acc, currentController) => {
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

  public get translations(): { title: string; logout: string; modifications: string } {
    return {
      title: this.$t("Logout.Logout") as string,
      logout: this.$t("Logout.Confirm") as string,
      modifications: this.$t("Logout.modifications") as string
    };
  }

  public mounted(): void {
    this._enableReady();
  }
}

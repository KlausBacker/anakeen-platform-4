/* tslint:disable:object-literal-sort-keys */
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.core";
import "@progress/kendo-ui/js/kendo.dropdownlist";
import "@progress/kendo-ui/js/kendo.window";

import axios from "axios";
import { Component, Mixins, Prop } from "vue-property-decorator";
import EventUtilsMixin from "../../mixins/AnkVueComponentMixin/EventUtilsMixin";
import I18nMixin from "../../mixins/AnkVueComponentMixin/I18nMixin";
import ReadyMixin from "../../mixins/AnkVueComponentMixin/ReadyMixin";

// eslint-disable-next-line no-unused-vars
import { IAuthent } from "./IAuthent";

// noinspection JSUnusedGlobalSymbols
@Component({
  components: {
    "ank-password": () => import("./AnkAuthentPassword/AnkAuthentPassword.vue")
  },
  name: "ank-authent"
})
export default class AuthentComponent extends Mixins(EventUtilsMixin, ReadyMixin, I18nMixin) {
  @Prop({ type: String, default: "" })
  public nsSde;
  @Prop({ type: String, default: "fr-FR, en-US" })
  public authentLanguages;
  @Prop({ type: String, default: "fr-FR" })
  public defaultLanguage;
  public login = "";
  public authentError = "Error";
  public forgetError = "Error";
  public forgetSuccess = "";
  public forgetStatusFailed = false;
  public resetError = "";
  public resetSuccess = "";
  public resetStatusFailed = true;
  public wrongPassword = false;
  public resetPassword = false;
  public pwd = "";
  public resetPwd1 = "";
  public resetPwd2 = "";
  public authent: IAuthent;
  public availableLanguagesLabel: object = {
    "en-US": "English",
    "fr-FR": "Français"
  };

  public $refs!: {
    authentForgetForm: HTMLElement;
    authentForm: HTMLElement;
    authentResetPasswordForm: HTMLElement;
    authentForgetButton: HTMLElement;
    authentForgetSubmit: HTMLElement;
    authentResetSubmit: HTMLElement;
    authentHelpButton: HTMLElement;
    loginButton: HTMLElement;
    authentLocale: HTMLElement;
    authentHelpContent: HTMLElement;
    authentGoHome: HTMLElement;
  };

  public get translations(): {
    loginPlaceHolder: string;
    passwordPlaceHolder: string;
    validationMessagePassword: string;
    validationMessageIdentifier: string;
    helpContentTitle: string;
    authentError: string;
    unexpectedError: string;
    forgetContentTitle: string;
    forgetPlaceHolder: string;
    passwordLabel: string;
    resetPasswordLabel: string;
    confirmPasswordLabel: string;
    confirmPasswordError: string;
  } {
    return {
      loginPlaceHolder: this.$t("authent.Enter your identifier") as string,
      passwordPlaceHolder: this.$t("authent.Enter your password") as string,
      validationMessagePassword: this.$t("authent.You must enter your password") as string,
      validationMessageIdentifier: this.$t("authent.You must enter your identifier") as string,
      helpContentTitle: this.$t("authent.Help to sign in") as string,
      authentError: this.$t("authent.Authentication error") as string,
      unexpectedError: this.$t("authent.Unexpected error") as string,
      forgetContentTitle: this.$t("authent.Form to reset password") as string,
      forgetPlaceHolder: this.$t("authent.Identifier or email address") as string,
      passwordLabel: this.$t("authent.password :") as string,
      resetPasswordLabel: this.$t("authent.New password :") as string,
      confirmPasswordLabel: this.$t("authent.Confirm password :") as string,
      confirmPasswordError: this.$t("authent.Confirm password are not same as new password") as string
    };
  }

  // noinspection JSUnusedGlobalSymbols
  public get availableLanguages(): { key: string; label: string } {
    const languages = this.authentLanguages.split(",");
    return languages.map(lang => {
      // jscs:ignore requireShorthandArrowFunctions
      return {
        key: lang.trim(),
        label: this.availableLanguagesLabel[lang.trim()]
      };
    });
  }

  public get redirectUri(): string {
    let uri = this.authent.getSearchArg("redirect_uri");
    if (!uri) {
      uri = "/";
    }
    return uri.replace(/(https?:\/\/)|(\/)+/g, "$1$2");
  }

  public beforeMount(): void {
    const passKey = this.authent.getSearchArg("passkey");
    let currentLanguage = this.defaultLanguage;
    if (this.defaultLanguage === "auto") {
      const navLanguage = navigator.language || "fr";
      if (navLanguage.substr(0, 2) === "fr") {
        currentLanguage = "fr-FR";
      } else {
        currentLanguage = "en-US";
      }
    }

    this.$_globalI18n.setLocale(currentLanguage);

    if (passKey) {
      this.resetPassword = true;
      this.login = this.authent.getSearchArg("uid");
      this.authent.authToken = passKey;
    }
  }

  public created(): void {
    this.authent = {
      authToken: null,
      getSearchArg: (key): string => {
        let result = "";
        let tmp = [];
        location.search
          .substr(1)
          .split("&")
          .forEach(item => {
            tmp = item.split("=");
            if (tmp[0] === key) {
              result = decodeURIComponent(tmp[1]);
            }
          });

        return result;
      },

      initForgetElements: (): void => {
        const $ = kendo.jQuery;
        const $forgetForm = $(this.$refs.authentForgetForm);
        const forgetWindow = $(this.$refs.authentForgetForm)
          .kendoWindow({
            visible: false,
            actions: ["Maximize", "Close"]
          })
          .data("kendoWindow");

        $(this.$refs.authentForgetButton).kendoButton({
          click: () => {
            forgetWindow
              .title(this.translations.forgetContentTitle)
              .center()
              .open();
          }
        });

        $(this.$refs.authentForgetSubmit).kendoButton();
        $forgetForm.on("submit", this.forgetPassword);
      },

      initResetPassword: (): void => {
        const $ = kendo.jQuery;
        const $resetForm = $(this.$refs.authentResetPasswordForm);

        $(this.$refs.authentResetSubmit).kendoButton();

        $resetForm.on("submit", this.applyResetPassword);
      }
    };
  }

  public mounted(): void {
    const $ = kendo.jQuery;
    const $connectForm = $(this.$refs.authentForm);
    const helpWindow = $(this.$refs.authentHelpContent)
      .kendoWindow({
        visible: false,
        actions: ["Maximize", "Close"]
      })
      .data("kendoWindow");

    $(this.$refs.authentHelpButton).kendoButton({
      click: () => {
        helpWindow
          .title(this.translations.helpContentTitle)
          .center()
          .open();
      }
    });

    $(this.$refs.loginButton).kendoButton();
    $connectForm.on("submit", this.createSession);

    $(this.$refs.authentLocale).kendoDropDownList({
      change: (e: kendo.ui.DropDownListChangeEvent) => {
        this.$_globalI18n.setLocale(e.sender.value());
      }
    });

    this.$on("localeChanged", lang => {
      $(this.$refs.authentLocale)
        .data("kendoDropDownList")
        .value(lang);
    });

    /**
     * Special custom warning if required fields are empty
     */

    if (this.resetPassword) {
      this.authent.initResetPassword();
    } else {
      this.authent.initForgetElements();
    }
    this._enableReady();
  }

  public createSession(event): void {
    const $ = kendo.jQuery;
    $(this.$refs.authentForm);
    kendo.ui.progress($(this.$refs.authentForm), true);

    const login = encodeURIComponent(this.login);
    event.preventDefault();

    const beforeEvent = this.$emitCancelableEvent("beforeLogin", {
      language: this.$i18n.locale,
      login: this.login,
      redirect: this.redirectUri
    });

    if (!beforeEvent.isDefaultPrevented()) {
      const data = beforeEvent.detail[0];
      this.$_globalI18n.setLocale(data.language);
      this.login = data.login;
      const redirectURI = data.redirect === this.redirectUri ? this.redirectUri : data.redirect;
      this.$http
        .post(`/api/v2/authent/sessions/${login}`, {
          language: this.$i18n.locale.replace("-", "_"),
          password: this.pwd
        })
        .then(() => {
          const afterEvent = this.$createEvent("afterLogin", {
            cancelable: false,
            data: [
              {
                language: this.$i18n.locale,
                login: this.login,
                redirect: redirectURI
              }
            ]
          });
          this.$emit("afterLogin", afterEvent);

          window.location.href = redirectURI;
          this.wrongPassword = false;
        })
        .catch(e => {
          console.error("Error", e);
          if (e.response && e.response.data && e.response.data.exceptionMessage) {
            const info = e.response.data;
            if (info.code === "AUTH0001") {
              // Normal authentication error
              this.authentError = this.translations.authentError as string;
            } else {
              this.authentError = info.userMessage || info.exceptionMessage;
            }
          } else {
            this.authentError = this.translations.authentError as string;
          }

          this.wrongPassword = true;

          kendo.ui.progress($(this.$refs.authentForm), false);
          $(this.$refs.loginButton).prop("disabled", false);
        });

      $(this.$refs.loginButton).prop("disabled", true);
    } else {
      kendo.ui.progress($(this.$refs.authentForm), false);
    }
  }

  public forgetPassword(event): void {
    const $ = kendo.jQuery;

    kendo.ui.progress($(this.$refs.authentForgetForm), true);

    const login = encodeURIComponent(this.login);
    event.preventDefault();

    const beforeEvent = this.$emitCancelableEvent("beforeRequestResetPassword", {
      language: this.$i18n.locale,
      login: this.login
    });

    if (!beforeEvent.isDefaultPrevented()) {
      const data = beforeEvent.detail[0];
      this.$_globalI18n.setLocale(data.language);
      this.login = data.login;

      this.$http
        .post(`/api/v2/authent/mailPassword/${login}`, {
          language: this.$i18n.locale.replace("-", "_"),
          password: this.pwd
        })
        .then(response => {
          const afterEvent = this.$createEvent("afterRequestResetPassword", {
            cancelable: false,
            data: [
              {
                language: this.$i18n.locale,
                login: this.login
              }
            ]
          });
          this.$emit("afterRequestResetPassword", afterEvent);

          this.forgetStatusFailed = false;
          kendo.ui.progress($(this.$refs.authentForgetForm), false);
          this.forgetSuccess = response.data.data.message;
          $(this.$refs.authentForgetSubmit)
            .prop("disabled", true)
            .hide();
        })
        .catch(e => {
          console.error("Error", e);
          if (e.response && e.response.data && e.response.data.exceptionMessage) {
            const info = e.response.data;

            if (info.messages && info.messages.length > 0) {
              this.forgetError = info.messages[0].contentText;
            } else {
              this.forgetError = info.userMessage || info.exceptionMessage;
            }
          } else {
            this.forgetError = this.translations.unexpectedError as string;
          }

          this.forgetStatusFailed = true;

          kendo.ui.progress($(this.$refs.authentForgetForm), false);
          $(this.$refs.authentForgetSubmit).prop("disabled", false);
        });
    } else {
      this.forgetStatusFailed = false;
      kendo.ui.progress($(this.$refs.authentForgetForm), false);
    }
  }

  public applyResetPassword(event): void {
    const $ = kendo.jQuery;

    event.preventDefault();

    if (!this.resetPwd1 || this.resetPwd1 !== this.resetPwd2) {
      this.resetStatusFailed = true;
      this.resetError = this.translations.confirmPasswordError as string;
      return;
    }

    const beforeEvent = this.$emitCancelableEvent("beforeApplyResetPassword", {
      language: this.$i18n.locale,
      login: this.login
    });

    if (!beforeEvent.isDefaultPrevented()) {
      const httpAuth = axios.create({
        baseURL: "/api/v2",
        headers: {
          Authorization: "Token " + this.authent.authToken
        }
      });

      kendo.ui.progress($(this.$refs.authentResetPasswordForm), true);

      const login = encodeURIComponent(this.login);
      httpAuth
        .put(`/authent/password/${login}`, {
          password: this.resetPwd1,
          language: this.$i18n.locale.replace("-", "_")
        })
        .then(response => {
          const afterEvent = this.$createEvent("afterApplyResetPassword", {
            cancelable: false,
            data: [
              {
                language: this.$i18n.locale,
                login: this.login
              }
            ]
          });
          this.$emit("afterApplyResetPassword", afterEvent);
          this.resetStatusFailed = false;
          kendo.ui.progress($(this.$refs.authentResetPasswordForm), false);
          this.resetSuccess = response.data.data.message;
          window.setTimeout(() => {
            $(this.$refs.authentGoHome).kendoButton({
              click: () => {
                window.location.href = "../";
              }
            });
          }, 10);
        })
        .catch(e => {
          if (e.response && e.response.data && e.response.data.exceptionMessage) {
            const info = e.response.data;

            if (info.messages && info.messages.length > 0) {
              this.resetError = info.messages[0].contentText;
            } else {
              this.resetError = e.response.data.userMessage || e.response.data.exceptionMessage;
            }
          } else {
            this.resetError = this.translations.unexpectedError as string;
          }

          this.resetStatusFailed = true;

          kendo.ui.progress($(this.$refs.authentResetPasswordForm), false);
          $(this.$refs.authentForgetSubmit).prop("disabled", false);
        });

      $(this.$refs.authentForgetSubmit)
        .prop("disabled", true)
        .hide();
    }
  }
}

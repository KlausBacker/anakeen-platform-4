/* tslint:disable:object-literal-sort-keys */
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.core";
import "@progress/kendo-ui/js/kendo.dropdownlist";
import "@progress/kendo-ui/js/kendo.window";

import axios from "axios";
import { Component, Mixins, Prop } from "vue-property-decorator";
const a4Password = () => import("./AnkAuthentPassword/AnkAuthentPassword.vue");
import EventUtilsMixin from "../../mixins/AnkVueComponentMixin/EventUtilsMixin";
import I18nMixin from "../../mixins/AnkVueComponentMixin/I18nMixin";
import ReadyMixin from "../../mixins/AnkVueComponentMixin/ReadyMixin";

// eslint-disable-next-line no-unused-vars
import { IAuthent } from "./IAuthent";

// noinspection JSUnusedGlobalSymbols
@Component({
  components: {
    "ank-password": a4Password
  },
  name: "ank-authent"
})
export default class AuthentComponent extends Mixins(EventUtilsMixin, ReadyMixin, I18nMixin) {
  @Prop({ type: String, default: "" }) public nsSde;
  @Prop({ type: String, default: "fr-FR, en-US" }) public authentLanguages;
  @Prop({ type: String, default: "fr-FR" }) public defaultLanguage;
  public login: string = "";
  public authentError: string = "Error";
  public forgetError: string = "Error";
  public forgetSuccess: string = "";
  public forgetStatusFailed: boolean = false;
  public resetError: string = "";
  public resetSuccess: string = "";
  public resetStatusFailed: boolean = true;
  public wrongPassword: boolean = false;
  public resetPassword: boolean = false;
  public pwd: string = "";
  public resetPwd1: string = "";
  public resetPwd2: string = "";
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

  public get translations() {
    return {
      loginPlaceHolder: this.$t("authent.Enter your identifier"),
      passwordPlaceHolder: this.$t("authent.Enter your password"),
      validationMessagePassword: this.$t("authent.You must enter your password"),
      validationMessageIdentifier: this.$t("authent.You must enter your identifier"),
      helpContentTitle: this.$t("authent.Help to sign in"),
      authentError: this.$t("authent.Authentication error"),
      unexpectedError: this.$t("authent.Unexpected error"),
      forgetContentTitle: this.$t("authent.Form to reset password"),
      forgetPlaceHolder: this.$t("authent.Identifier or email address"),
      passwordLabel: this.$t("authent.password :"),
      resetPasswordLabel: this.$t("authent.New password :"),
      confirmPasswordLabel: this.$t("authent.Confirm password :"),
      confirmPasswordError: this.$t("authent.Confirm password are not same as new password")
    };
  }

  // noinspection JSUnusedGlobalSymbols
  public get availableLanguages() {
    const languages = this.authentLanguages.split(",");
    return languages.map(lang => {
      // jscs:ignore requireShorthandArrowFunctions
      return {
        key: lang.trim(),
        label: this.availableLanguagesLabel[lang.trim()]
      };
    });
  }

  public get redirectUri() {
    let uri = this.authent.getSearchArg("redirect_uri");
    if (!uri) {
      uri = "/";
    }
    if (window.location.hash) {
      uri += window.location.hash;
    }
    return uri.replace(/(https?:\/\/)|(\/)+/g, "$1$2");
  }

  public beforeMount() {
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

  public created() {
    this.authent = {
      authToken: null,
      getSearchArg: key => {
        let result = null;
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

      initForgetElements: () => {
        const $ = kendo.jQuery;
        const $forgetForm = $(this.$refs.authentForgetForm);
        let forgetWindow;

        forgetWindow = $(this.$refs.authentForgetForm)
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

      initResetPassword: () => {
        const $ = kendo.jQuery;
        const $resetForm = $(this.$refs.authentResetPasswordForm);

        $(this.$refs.authentResetSubmit).kendoButton();

        $resetForm.on("submit", this.applyResetPassword);
      }
    };
  }

  public mounted() {
    const $ = kendo.jQuery;
    const $connectForm = $(this.$refs.authentForm);
    let helpWindow;

    // this.$refs.authentForm.input.focus();

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

    helpWindow = $(this.$refs.authentHelpContent)
      .kendoWindow({
        visible: false,
        actions: ["Maximize", "Close"]
      })
      .data("kendoWindow");

    /**
     * Special custom warning if required fields are empty
     */

    if (this.resetPassword) {
      this.authent.initResetPassword();
    } else {
      this.authent.initForgetElements();
    }
    this.$refs.authentForm.getElementsByTagName("input")[0].focus();
    this._enableReady();
  }

  public createSession(event) {
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

  public forgetPassword(event) {
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

  public applyResetPassword(event) {
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

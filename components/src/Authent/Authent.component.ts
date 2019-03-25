import Vue from "vue";
import { Component, Prop } from "vue-property-decorator";
const a4Password = () => import("./AuthentPassword/AuthentPassword.vue");
import { IAuthent } from "./IAuthent";
import { _enableReady } from "../../mixins/AnkVueComponentMixin/IeventUtilsMixin";
import VueSetup from "../setup.js";
Vue.use(VueSetup);

// noinspection JSUnusedGlobalSymbols
@Component({
  components: {
    "ank-password": a4Password
  },
  name: "ank-authent"
})
export default class AuthentComponent extends Vue {
  @Prop({ type: String, default: "authent/{login3}/?lang={lang}" })
  public loginUrl;
  @Prop({ type: String, default: "fr_FR, en_US" }) public authentLanguages;
  @Prop({ type: String, default: "fr_FR" }) public defaultLanguage;
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
      loginPlaceHolder: this.$pgettext("Authent", "Enter your identifier"),
      passwordPlaceHolder: this.$pgettext("Authent", "Enter your password"),
      validationMessagePassword: this.$pgettext(
        "Authent",
        "You must enter your password"
      ),
      validationMessageIdentifier: this.$pgettext(
        "Authent",
        "You must enter your identifier"
      ),
      helpContentTitle: this.$pgettext("Authent", "Help to sign in"),
      authentError: this.$pgettext("Authent", "Authentication error"),
      unexpectedError: this.$pgettext("Authent", "Unexpected error"),
      forgetContentTitle: this.$pgettext("Authent", "Form to reset password"),
      forgetPlaceHolder: this.$pgettext(
        "Authent",
        "Identifier or email address"
      ),
      passwordLabel: this.$pgettext("Authent", "Password :"),
      resetPasswordLabel: this.$pgettext("Authent", "New password :"),
      confirmPasswordLabel: this.$pgettext("Authent", "Confirm password :"),
      confirmPasswordError: this.$pgettext(
        "Authent",
        "Confirm password are not same as new password"
      )
    };
  }

  public get availableLanguages() {
    let languages = this.authentLanguages.split(",");
    return languages.map(lang => {
      // jscs:ignore requireShorthandArrowFunctions
      return {
        key: lang.trim(),
        label: this.$language.available[lang.trim()]
      };
    });
  }

  public get redirectUri() {
    let uri = this.authent.getSearchArg("redirect_uri");
    if (!uri) {
      uri = "/";
    }
    return uri.replace(/(https?:\/\/)|(\/)+/g, "$1$2");
  }

  public beforeMount() {
    let passKey = this.authent.getSearchArg("passkey");
    let currentLanguage = this.defaultLanguage;
    if (this.defaultLanguage === "auto") {
      let navLanguage = navigator.language || window.navigator["userLanguage"];
      if (navLanguage.substr(0, 2) === "fr") {
        currentLanguage = "fr_FR";
      } else {
        currentLanguage = "en_US";
      }
    }

    Vue.config.language = currentLanguage;

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
            if (tmp[0] === key) result = decodeURIComponent(tmp[1]);
          });

        return result;
      },

      initForgetElements: () => {
        let $ = kendo.jQuery;
        let $forgetForm = $(this.$refs.authentForgetForm);
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
        let $ = kendo.jQuery;
        let $resetForm = $(this.$refs.authentResetPasswordForm);

        $(this.$refs.authentResetSubmit).kendoButton();

        $resetForm.on("submit", this.applyResetPassword);
      }
    };
  }

  public mounted() {
    let $ = kendo.jQuery;
    let $connectForm = $(this.$refs.authentForm);
    let helpWindow;

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
      change(e: kendo.ui.DropDownListChangeEvent) {
        Vue.config.language = e.sender.value();
      }
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
    _enableReady();
  }

  public createSession(event) {
    let $ = kendo.jQuery;
    $(this.$refs.authentForm);
    kendo.ui.progress($(this.$refs.authentForm), true);

    let login = encodeURIComponent(this.login);
    event.preventDefault();
    this.$http
      .post(`/api/v2/authent/sessions/${login}`, {
        password: this.pwd,
        language: this.$language.current
      })
      .then(() => {
        window.location.href = this.redirectUri;
        this.wrongPassword = false;
      })
      .catch(e => {
        console.error("Error", e);
        if (e.response && e.response.data && e.response.data.exceptionMessage) {
          let info = e.response.data;
          if (info.code === "AUTH0001") {
            // Normal authentication error
            this.authentError = this.translations.authentError;
          } else {
            this.authentError = info.userMessage || info.exceptionMessage;
          }
        } else {
          this.authentError = this.translations.authentError;
        }

        this.wrongPassword = true;

        kendo.ui.progress($(this.$refs.authentForm), false);
        $(this.$refs.loginButton).prop("disabled", false);
      });

    $(this.$refs.loginButton).prop("disabled", true);
  }

  public forgetPassword(event) {
    let $ = kendo.jQuery;

    kendo.ui.progress($(this.$refs.authentForgetForm), true);

    let login = encodeURIComponent(this.login);
    event.preventDefault();
    this.$http
      .post(`/api/v2/authent/mailPassword/${login}`, {
        password: this.pwd,
        language: this.$language.current
      })
      .then(response => {
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
          let info = e.response.data;

          if (info.messages && info.messages.length > 0) {
            this.forgetError = info.messages[0].contentText;
          } else {
            this.forgetError = info.userMessage || info.exceptionMessage;
          }
        } else {
          this.forgetError = this.translations.unexpectedError;
        }

        this.forgetStatusFailed = true;

        kendo.ui.progress($(this.$refs.authentForgetForm), false);
        $(this.$refs.authentForgetSubmit).prop("disabled", false);
      });
  }

  public applyResetPassword(event) {
    let $ = kendo.jQuery;

    event.preventDefault();

    if (!this.resetPwd1 || this.resetPwd1 !== this.resetPwd2) {
      this.resetStatusFailed = true;
      this.resetError = this.translations.confirmPasswordError;
      return;
    }

    let httpAuth = this.$axios.create({
      baseURL: "/api/v2",
      headers: {
        Authorization: "Token " + this.authent.authToken
      }
    });

    kendo.ui.progress($(this.$refs.authentResetPasswordForm), true);

    let login = encodeURIComponent(this.login);
    httpAuth
      .put(`/authent/password/${login}`, {
        password: this.resetPwd1,
        language: this.$language.current
      })
      .then(response => {
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
          let info = e.response.data;

          if (info.messages && info.messages.length > 0) {
            this.resetError = info.messages[0].contentText;
          } else {
            this.resetError =
              e.response.data.userMessage || e.response.data.exceptionMessage;
          }
        } else {
          this.resetError = this.translations.unexpectedError;
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

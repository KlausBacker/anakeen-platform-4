import "@progress/kendo-ui/js/kendo.popup";
import "@progress/kendo-ui/js/kendo.window";
import $ from "jquery";
import { Component, Mixins, Prop } from "vue-property-decorator";
import EventUtilsMixin from "../../mixins/AnkVueComponentMixin/EventUtilsMixin";
import I18nMixin from "../../mixins/AnkVueComponentMixin/I18nMixin";
import ReadyMixin from "../../mixins/AnkVueComponentMixin/ReadyMixin";

@Component({
  components: {
    "ank-authent-password": () => import("../AnkAuthent/AnkAuthentPassword/AnkAuthentPassword.vue")
  },
  name: "ank-identity"
})
export default class IdentityComponent extends Mixins(EventUtilsMixin, ReadyMixin, I18nMixin) {
  // Compact badge (just the initials), or large badge (with name and email)
  @Prop({ type: Boolean, default: false }) public large;
  // Allow the user to change his email, or not
  @Prop({ type: Boolean, default: false }) public emailAlterable;
  // Allow the user to change his password, or not
  @Prop({ type: Boolean, default: false }) public passwordAlterable;
  // User information
  public login = "";
  public initials = "";
  public firstName = "";
  public lastName = "";
  public email = "";
  public $refs!: {
    [key: string]: any;
  };
  // New information to modify on the server
  public oldPassword = "";
  public newEmail = "";
  public newPassword = "";
  public newPasswordConfirmation = "";

  // Warning messages during email/password change
  public emailWarningMessage = "";
  public passwordWarningMessage = "";

  public getBaseComponent(): JQuery {
    return kendo.jQuery(this.$refs["identity-component"]);
  }
  // Fetch user's information from the server
  public fetchUser(): Promise<void> {
    return this.$http.get("/api/v2/ui/users/current").then(response => {
      const customEvent = this.$emitCancelableEvent("beforeUserLoaded", {
        ...response.data.data
      });
      if (!customEvent.isDefaultPrevented()) {
        this.login = customEvent.detail[0].login;
        this.initials = customEvent.detail[0].initials;
        this.firstName = customEvent.detail[0].firstName;
        this.lastName = customEvent.detail[0].lastName;
        this.email = customEvent.detail[0].email;
        const afterEvent = this.$createEvent("afterUserLoaded", {
          cancelable: false,
          data: [
            {
              ...customEvent.detail[0]
            }
          ]
        });
        this.$emit("afterUserLoaded", afterEvent);
      }
    });
  }

  // Catch the input event of the ank-authent-password component to update the old password
  public updateOldPassword(data): void {
    this.oldPassword = data;
  }

  // Catch the input event of the ank-authent-password component to update the new password
  public updateNewPassword(data): void {
    this.newPassword = data;
  }

  // Catch the input event of the ank-authent-password component to update the new password confirmation
  public updateNewPasswordConfirmation(data): void {
    this.newPasswordConfirmation = data;
  }

  // Send a request to change the password on the server
  public modifyUserPassword(): void {
    const customEvent = this.$emitCancelableEvent("beforePasswordChange");

    if (!customEvent.isDefaultPrevented()) {
      // Verify if password matches confirmation
      if (this.newPassword === this.newPasswordConfirmation && this.newPassword !== "") {
        kendo.ui.progress(kendo.jQuery(this.$refs.passwordModifier), true);
        this.$http
          .put("/components/identity/password", {
            newPassword: this.newPassword,
            oldPassword: this.oldPassword
          })
          .then(() => {
            const afterEvent = this.$createEvent("afterPasswordChange", {
              cancelable: false,
              data: [
                {
                  email: this.email,
                  firstName: this.firstName,
                  initials: this.initials,
                  lastName: this.lastName,
                  login: this.login
                }
              ]
            });
            this.$emit("afterPasswordChange", afterEvent);

            // Remove loader + close dialog
            kendo.ui.progress(kendo.jQuery(this.$refs.passwordModifier), false);
            this.openPasswordModifiedWindow();
          })
          .catch(error => {
            // Show a warning message and remove the loader
            this.passwordWarningMessage = error.response.data.userMessage;
            kendo.ui.progress(kendo.jQuery(this.$refs.passwordModifier), false);
          });
      } else {
        // Show a warning message
        this.passwordWarningMessage = this.translations.passwordsMismatchMessage;
      }
    } else {
      this.closePasswordModifierWindow();
    }
  }

  // Send a request to change the email on the server
  public modifyUserEmail(): void {
    const customEvent = this.$emitCancelableEvent("beforeMailAddressChange", {
      newEmail: this.newEmail
    });

    if (!customEvent.isDefaultPrevented()) {
      // Verify if the input is an email ( [string]@[string].[string] )
      if (this.newEmail.match(/\S+@\S+\.\S+/)) {
        kendo.ui.progress($(this.$refs.emailModifier), true);
        this.$http
          .put("/components/identity/email", {
            email: customEvent.detail[0].newEmail,
            password: this.oldPassword
          })
          .then(response => {
            this.email = response.data.email;

            const afterCustomEvent = this.$createEvent("afterMailAddressChange", {
              cancelable: false,
              data: [
                {
                  email: this.email
                }
              ]
            });

            this.$emit("afterMailAddressChange", afterCustomEvent);

            // Remove loader and close dialog
            kendo.ui.progress(kendo.jQuery(this.$refs.emailModifier), false);
            this.openEmailModifiedWindow();
          })
          .catch(error => {
            // Show a warning message and remove the loader
            if (error && error.response && error.response.data && error.response.data.userMessage) {
              this.emailWarningMessage = error.response.data.userMessage;
            } else {
              this.emailWarningMessage = this.translations.unkownError;
            }
            kendo.ui.progress(kendo.jQuery(this.$refs.emailModifier), false);
          });
      } else {
        // Show a warning message
        this.emailWarningMessage = this.translations.emailFormatMessage;
      }
    } else {
      this.closeEmailModifierWindow();
    }
  }

  // Open or close the popup that allow the user to change his email and/or password
  public toggleSettingsPopup(): void {
    if (this.emailAlterable || this.passwordAlterable) {
      kendo
        .jQuery(this.$refs.modificationPopup)
        .data("kendoPopup")
        .toggle();
    }
  }
  // Close the popup that allow the user to change his email and/or password
  public closeSettingsPopup(): void {
    if (this.emailAlterable || this.passwordAlterable) {
      kendo
        .jQuery(this.$refs.modificationPopup)
        .data("kendoPopup")
        .close();
    }
  }

  // Open dialog window to change user's email
  public openEmailModifierWindow(): void {
    this.closeSettingsPopup();

    // Init window to change the user's email (if allowed in the props)
    if (this.emailAlterable) {
      // Function called when th dialog is open and closed
      const resetEmailChangeData = (): void => {
        this.oldPassword = "";
        this.$refs.oldPasswordInputEmail.setValue("");
        this.newEmail = "";
        this.emailWarningMessage = "";
      };

      const $emailWindow = kendo.jQuery(this.$refs.emailModifier);
      $emailWindow
        .kendoWindow({
          actions: ["Close"],
          activate: () => {
            $(this.$refs.emailModifier).focus();
          },
          close: resetEmailChangeData,
          maxWidth: 500,
          minWidth: 100,
          modal: true,
          open: resetEmailChangeData,
          title: this.translations.emailChangeAction,
          visible: false,
          width: "80%"
        })
        .data("kendoWindow")
        .center()
        .open()
        .wrapper.addClass("identity-email-window");
    }
  }

  // Close dialog window to change user's email
  public closeEmailModifierWindow(): void {
    kendo
      .jQuery(this.$refs.emailModifier)
      .data("kendoWindow")
      .close();
  }

  // Open dialog to confirm the modification of the email
  public openEmailModifiedWindow(): void {
    const dialog = kendo.jQuery(this.$refs.emailModifiedWindow);
    dialog
      .kendoWindow({
        actions: ["Close"],
        close: this.closeEmailModifierWindow,
        maxWidth: 500,
        minWidth: 100,
        modal: true,
        title: this.translations.emailChangeSuccessTitle,
        visible: true,
        width: "80%"
      })
      .data("kendoWindow")
      .center()
      .open()
      .wrapper.addClass("identity-emailModified-window");
  }
  // Close dialog to confirm the modification of the email
  public closeEmailModifiedWindow(): void {
    kendo
      .jQuery(this.$refs.emailModifiedWindow)
      .data("kendoWindow")
      .close();
  }
  // Open dialog window to change user's password
  public openPasswordModifierWindow(): void {
    this.closeSettingsPopup();

    // Init window to change the user's password (if allowed in the props)
    if (this.passwordAlterable) {
      // Function called when the dialog is open and closed
      const resetPasswordChangeData = (): void => {
        this.oldPassword = "";
        this.$refs.oldPasswordInput.setValue("");
        this.newPassword = "";
        this.$refs.passwordInput.setValue("");
        this.newPasswordConfirmation = "";
        this.$refs.passwordConfirmationInput.setValue("");
        this.passwordWarningMessage = "";
        this.reColorInputs();
      };

      const passwordWindow = kendo.jQuery(this.$refs.passwordModifier);
      passwordWindow
        .kendoWindow({
          actions: ["Close"],
          activate: () => {
            $(this.$refs.passwordInput).focus();
          },
          close: resetPasswordChangeData,
          maxWidth: 500,
          minWidth: 100,
          modal: true,
          open: resetPasswordChangeData,
          title: this.translations.passwordChangeAction,
          visible: false,
          width: "80%"
        })
        .data("kendoWindow")
        .center()
        .open()
        .wrapper.addClass("identity-password-window");
    }
  }

  // Close dialog window to change user's password
  public closePasswordModifierWindow(): void {
    $(this.$refs.passwordModifier)
      .data("kendoWindow")
      .close();
  }

  // Open dialog to confirm the modification of the password
  public openPasswordModifiedWindow(): void {
    const dialog = kendo.jQuery(this.$refs.passwordModifiedWindow);
    dialog
      .kendoWindow({
        actions: ["Close"],
        close: this.closePasswordModifierWindow,
        maxWidth: 500,
        minWidth: 100,
        modal: true,
        title: this.translations.passwordChangeSuccessTitle,
        visible: true,
        width: "80%"
      })
      .data("kendoWindow")
      .center()
      .open()
      .wrapper.addClass("identity-passwordModified-window");
  }

  // Close dialog to confirm the modification of the password
  public closePasswordModifiedWindow(): void {
    $(this.$refs.passwordModifiedWindow)
      .data("kendoWindow")
      .close();
  }

  // Reset email modification warning message when a key is pressed, if this key is not enter
  // (Enter shouldn't remove the message because if the user validate a wrong email with enter,
  // the message should appear)
  public removeEmailWarningMessage(event): void {
    if (event.key !== "Enter") {
      this.emailWarningMessage = "";
    }
  }

  // Reset password modification warning message when a key is pressed, if this key is not enter
  // (Enter shouldn't remove the message because if the user validate a wrong password with enter,
  // the message should appear)
  public updatePasswordChangeForm(event): void {
    if (event.key !== "Enter") {
      this.passwordWarningMessage = "";
    }

    this.reColorInputs();
  }

  // Re color input borders to show if password and confirmation are different
  public reColorInputs(): void {
    if (this.newPassword && this.newPasswordConfirmation) {
      if (this.newPassword === this.newPasswordConfirmation) {
        $(this.$refs.passwordModifier)
          .find(".password-input")
          .find(":input")
          .css("border-color", "green");
        $(this.$refs.passwordModifier)
          .find(".password-confirmation-input")
          .find(":input")
          .css("border-color", "green");
      } else {
        $(this.$refs.passwordModifier)
          .find(".password-input")
          .find(":input")
          .css("border-color", "red");
        $(this.$refs.passwordModifier)
          .find(".password-confirmation-input")
          .find(":input")
          .css("border-color", "red");
      }
    } else {
      $(this.$refs.passwordModifier)
        .find(".password-input")
        .find(":input")
        .css("border-color", "");
      $(this.$refs.passwordModifier)
        .find(".password-confirmation-input")
        .find(":input")
        .css("border-color", "");
    }
  }
  public get translations() {
    return {
      cancelEmailButtonLabel: this.$t("identity.Cancel email modification") as string,
      cancelPasswordButtonLabel: this.$t("identity.Cancel password modification") as string,
      closeButtonLabel: this.$t("identity.Close") as string,
      currentEmailLabel: this.$t("identity.Current email") as string,
      emailChangeAction: this.$t("identity.Change email") as string,
      emailChangeSuccess: this.$t("identity.Email successfully changed") as string,
      emailChangeSuccessTitle: this.$t("identity.Email changed") as string,
      emailFormatMessage: this.$t("identity.Wrong email format") as string,
      newEmailLabel: this.$t("identity.New email") as string,
      newEmailPlaceholder: this.$t("identity.Your new email address") as string,
      newPasswordConfirmationLabel: this.$t("identity.New password confirmation") as string,
      newPasswordConfirmationPlaceholder: this.$t("identity.Confirmation of your new password") as string,
      newPasswordLabel: this.$t("identity.New password") as string,
      newPasswordPlaceholder: this.$t("identity.Your new password") as string,
      noEmail: this.$t("identity.No email yet") as string,
      oldPasswordLabel: this.$t("identity.Current password") as string,
      oldPasswordPlaceholder: this.$t("identity.Your current password") as string,
      passwordChangeAction: this.$t("identity.Change password") as string,
      passwordChangeSuccess: this.$t("identity.Password successfully changed") as string,
      passwordChangeSuccessTitle: this.$t("identity.Password changed") as string,
      passwordsMismatchMessage: this.$t("identity.Confirmation doesn't match with the password") as string,
      serverError: this.$t("identity.Server error") as string,
      unkownError: this.$t("identity.unkown erroras string, try again") as string,
      validateEmailButtonLabel: this.$t("identity.Confirm email modification") as string,
      validatePasswordButtonLabel: this.$t("identity.Confirm password modification") as string
    };
  }

  public get displayName(): string {
    return this.firstName + " " + this.lastName;
  }

  // Email change validation button enabled only if the input is a correct email adress
  public get emailChangeButtonDisabled(): boolean {
    return !this.newEmail.match(/\S+@\S+\.\S+/) || !this.oldPassword;
  }

  // Password change validation button enabled only if the password matches the confirmation
  public get passwordChangeButtonDisabled(): boolean {
    return this.newPassword !== this.newPasswordConfirmation || this.newPassword === "" || this.oldPassword === "";
  }

  public mounted(): void {
    // Init popup to allow email and/or password modification (if allowed in the props)
    if (this.emailAlterable || this.passwordAlterable) {
      kendo.jQuery(this.$refs.modificationPopup).kendoPopup({
        anchor: this.getBaseComponent().find(".identity-badge"),
        animation: false,
        collision: "flip fit",
        origin: "bottom left",
        position: "top left"
      });
    }
    this.fetchUser().then(() => {
      this._enableReady();
    });
  }
}

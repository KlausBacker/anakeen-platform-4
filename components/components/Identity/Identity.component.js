import AuthentPassword from "../Authent/AuthentPassword";
import AnkMixins from "../../mixins/AnkVueComponentMixin";

export default {
  name: "ank-identity",
  mixins: AnkMixins,
  components: {
    "ank-authent-password": AuthentPassword
  },

  props: {
    // Compact badge (just the initials), or large badge (with name and email)
    large: {
      type: Boolean,
      default: false
    },

    // Allow the user to change his email, or not
    emailAlterable: {
      type: Boolean,
      default: false
    },

    // Allow the user to change his password, or not
    passwordAlterable: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      // User information
      login: "",
      initials: "",
      firstName: "",
      lastName: "",
      email: "",

      // New information to modify on the server
      oldPassword: "",
      newEmail: "",
      newPassword: "",
      newPasswordConfirmation: "",

      // Warning messages during email/password change
      emailWarningMessage: "",
      passwordWarningMessage: ""
    };
  },

  methods: {
    getBaseComponent() {
      return this.$(this.$refs["identity-component"]);
    },
    // Fetch user's information from the server
    fetchUser() {
      return this.$http.get("/api/v2/ui/users/current").then(response => {
        const customEvent = this.$createComponentEvent("beforeUserLoaded", {
          cancelable: true,
          detail: [
            {
              ...response.data.data
            }
          ]
        });

        if (this.$emitAnkEvent("beforeUserLoaded", customEvent)) {
          this.login = customEvent.detail[0].login;
          this.initials = customEvent.detail[0].initials;
          this.firstName = customEvent.detail[0].firstName;
          this.lastName = customEvent.detail[0].lastName;
          this.email = customEvent.detail[0].email;
          const afterEvent = this.$createComponentEvent("afterUserLoaded", {
            detail: [
              {
                ...customEvent.detail[0]
              }
            ]
          });
          this.$emitAnkEvent("afterUserLoaded", afterEvent);
        }
      });
    },

    // Catch the input event of the ank-authent-password component to update the old password
    updateOldPassword(data) {
      this.oldPassword = data;
    },

    // Catch the input event of the ank-authent-password component to update the new password
    updateNewPassword(data) {
      this.newPassword = data;
    },

    // Catch the input event of the ank-authent-password component to update the new password confirmation
    updateNewPasswordConfirmation(data) {
      this.newPasswordConfirmation = data;
    },

    // Send a request to change the password on the server
    modifyUserPassword() {
      const customEvent = this.$createComponentEvent("beforePasswordChange", {
        cancelable: true
      });

      this.$emitAnkEvent("beforePasswordChange", customEvent);

      if (!customEvent.defaultPrevented) {
        // Verify if password matches confirmation
        if (
          this.newPassword === this.newPasswordConfirmation &&
          this.newPassword !== ""
        ) {
          kendo.ui.progress(this.$(this.$refs.passwordModifier), true);
          this.$http
            .put("/components/identity/password", {
              oldPassword: this.oldPassword,
              newPassword: this.newPassword
            })
            .then(() => {
              const afterEvent = this.$createComponentEvent(
                "afterPasswordChange",
                {
                  detail: [
                    {
                      email: this.email,
                      login: this.login,
                      initials: this.initials,
                      firstName: this.firstName,
                      lastName: this.lastName
                    }
                  ]
                }
              );
              this.$emitAnkEvent("afterPasswordChange", afterEvent);

              // Remove loader + close dialog
              kendo.ui.progress(this.$(this.$refs.passwordModifier), false);
              this.openPasswordModifiedWindow();
            })
            .catch(error => {
              // Show a warning message and remove the loader
              this.passwordWarningMessage = error.response.data.userMessage;
              kendo.ui.progress(this.$(this.$refs.passwordModifier), false);
            });
        } else {
          // Show a warning message
          this.passwordWarningMessage = this.translations.passwordsMismatchMessage;
        }
      } else {
        this.closePasswordModifierWindow();
      }
    },

    // Send a request to change the email on the server
    modifyUserEmail() {
      const customEvent = this.$createComponentEvent(
        "beforeMailAddressChange",
        {
          cancelable: true,
          detail: [
            {
              newEmail: this.newEmail
            }
          ]
        }
      );

      this.$emitAnkEvent("beforeMailAddressChange", customEvent);

      if (!customEvent.defaultPrevented) {
        // Verify if the input is an email ( [string]@[string].[string] )
        if (this.newEmail.match(/\S+@\S+\.\S+/)) {
          kendo.ui.progress(this.$(this.$refs.emailModifier), true);
          this.$http
            .put("/components/identity/email", {
              email: customEvent.detail[0].newEmail,
              password: this.oldPassword
            })
            .then(response => {
              this.email = response.data.email;

              const customEvent = this.$createComponentEvent(
                "afterMailAddressChange",
                {
                  detail: [
                    {
                      email: this.email
                    }
                  ]
                }
              );

              this.$emitAnkEvent("afterMailAddressChange", customEvent);

              // Remove loader and close dialog
              this.$kendo.ui.progress(this.$(this.$refs.emailModifier), false);
              this.openEmailModifiedWindow();
            })
            .catch(error => {
              // Show a warning message and remove the loader
              if (
                error &&
                error.response &&
                error.response.data &&
                error.response.data.userMessage
              ) {
                this.emailWarningMessage = error.response.data.userMessage;
              } else {
                this.emailWarningMessage = this.translations.unkownError;
              }
              this.$kendo.ui.progress(this.$(this.$refs.emailModifier), false);
            });
        } else {
          // Show a warning message
          this.emailWarningMessage = this.translations.emailFormatMessage;
        }
      } else {
        this.closeEmailModifierWindow();
      }
    },

    // Open or close the popup that allow the user to change his email and/or password
    toggleSettingsPopup() {
      if (this.emailAlterable || this.passwordAlterable) {
        this.$(this.$refs.modificationPopup)
          .data("kendoPopup")
          .toggle();
      }
    },

    // Close the popup that allow the user to change his email and/or password
    closeSettingsPopup() {
      if (this.emailAlterable || this.passwordAlterable) {
        this.$(this.$refs.modificationPopup)
          .data("kendoPopup")
          .close();
      }
    },

    // Open dialog window to change user's email
    openEmailModifierWindow() {
      this.closeSettingsPopup();

      // Init window to change the user's email (if allowed in the props)
      if (this.emailAlterable) {
        // Function called when th dialog is open and closed
        let resetEmailChangeData = () => {
          this.oldPassword = "";
          this.$refs.oldPasswordInputEmail.setValue("");
          this.newEmail = "";
          this.emailWarningMessage = "";
        };

        let $emailWindow = this.$(this.$refs.emailModifier);
        $emailWindow
          .kendoWindow({
            minWidth: "100px",
            width: "80%",
            maxWidth: "500px",
            modal: true,
            title: this.translations.emailChangeAction,
            visible: false,
            actions: ["Close"],
            open: resetEmailChangeData,
            close: resetEmailChangeData,
            activate: () => {
              this.$(this.$refs.emailModifier).focus();
            }
          })
          .data("kendoWindow")
          .center()
          .open()
          .wrapper.addClass("identity-email-window");
      }
    },

    // Close dialog window to change user's email
    closeEmailModifierWindow() {
      this.$(this.$refs.emailModifier)
        .data("kendoWindow")
        .close();
    },

    // Open dialog to confirm the modification of the email
    openEmailModifiedWindow() {
      let dialog = this.$(this.$refs.emailModifiedWindow);
      dialog
        .kendoWindow({
          minWidth: "100px",
          width: "80%",
          maxWidth: "500px",
          title: this.translations.emailChangeSuccessTitle,
          visible: true,
          modal: true,
          action: ["Close"],
          close: this.closeEmailModifierWindow
        })
        .data("kendoWindow")
        .center()
        .open()
        .wrapper.addClass("identity-emailModified-window");
    },

    // Close dialog to confirm the modification of the email
    closeEmailModifiedWindow() {
      this.$(this.$refs.emailModifiedWindow)
        .data("kendoWindow")
        .close();
    },

    // Open dialog window to change user's password
    openPasswordModifierWindow() {
      this.closeSettingsPopup();

      // Init window to change the user's password (if allowed in the props)
      if (this.passwordAlterable) {
        // Function called when the dialog is open and closed
        let resetPasswordChangeData = () => {
          this.oldPassword = "";
          this.$refs.oldPasswordInput.setValue("");
          this.newPassword = "";
          this.$refs.passwordInput.setValue("");
          this.newPasswordConfirmation = "";
          this.$refs.passwordConfirmationInput.setValue("");
          this.passwordWarningMessage = "";
          this.reColorInputs();
        };

        let passwordWindow = this.$(this.$refs.passwordModifier);
        passwordWindow
          .kendoWindow({
            minWidth: "100px",
            width: "80%",
            maxWidth: "500px",
            modal: true,
            title: this.translations.passwordChangeAction,
            visible: false,
            actions: ["Close"],
            open: resetPasswordChangeData,
            close: resetPasswordChangeData,
            activate: () => {
              this.$(this.$refs.passwordInput).focus();
            }
          })
          .data("kendoWindow")
          .center()
          .open()
          .wrapper.addClass("identity-password-window");
      }
    },

    // Close dialog window to change user's password
    closePasswordModifierWindow() {
      this.$(this.$refs.passwordModifier)
        .data("kendoWindow")
        .close();
    },

    // Open dialog to confirm the modification of the password
    openPasswordModifiedWindow() {
      let dialog = this.$(this.$refs.passwordModifiedWindow);
      dialog
        .kendoWindow({
          minWidth: "100px",
          width: "80%",
          maxWidth: "500px",
          title: this.translations.passwordChangeSuccessTitle,
          visible: true,
          modal: true,
          action: ["Close"],
          close: this.closePasswordModifierWindow
        })
        .data("kendoWindow")
        .center()
        .open()
        .wrapper.addClass("identity-passwordModified-window");
    },

    // Close dialog to confirm the modification of the password
    closePasswordModifiedWindow() {
      this.$(this.$refs.passwordModifiedWindow)
        .data("kendoWindow")
        .close();
    },

    // Reset email modification warning message when a key is pressed, if this key is not enter
    // (Enter shouldn't remove the message because if the user validate a wrong email with enter,
    // the message should appear)
    removeEmailWarningMessage(event) {
      if (event.key !== "Enter") {
        this.emailWarningMessage = "";
      }
    },

    // Reset password modification warning message when a key is pressed, if this key is not enter
    // (Enter shouldn't remove the message because if the user validate a wrong password with enter,
    // the message should appear)
    updatePasswordChangeForm(event) {
      if (event.key !== "Enter") {
        this.passwordWarningMessage = "";
      }

      this.reColorInputs();
    },

    // Re color input borders to show if password and confirmation are different
    reColorInputs() {
      if (this.newPassword && this.newPasswordConfirmation) {
        if (this.newPassword === this.newPasswordConfirmation) {
          this.$(this.$refs.passwordModifier)
            .find(".password-input")
            .find(":input")
            .css("border-color", "green");
          this.$(this.$refs.passwordModifier)
            .find(".password-confirmation-input")
            .find(":input")
            .css("border-color", "green");
        } else {
          this.$(this.$refs.passwordModifier)
            .find(".password-input")
            .find(":input")
            .css("border-color", "red");
          this.$(this.$refs.passwordModifier)
            .find(".password-confirmation-input")
            .find(":input")
            .css("border-color", "red");
        }
      } else {
        this.$(this.$refs.passwordModifier)
          .find(".password-input")
          .find(":input")
          .css("border-color", "");
        this.$(this.$refs.passwordModifier)
          .find(".password-confirmation-input")
          .find(":input")
          .css("border-color", "");
      }
    }
  },

  computed: {
    translations() {
      return {
        unkownError: this.$pgettext("Identity", "unkown error, try again"),
        emailChangeAction: this.$pgettext("Identity", "Change email"),
        passwordChangeAction: this.$pgettext("Identity", "Change password"),
        currentEmailLabel: this.$pgettext("Identity", "Current email"),
        noEmail: this.$pgettext("Identity", "No email yet"),
        newEmailLabel: this.$pgettext("Identity", "New email"),
        newEmailPlaceholder: this.$pgettext(
          "Identity",
          "Your new email address"
        ),
        validateEmailButtonLabel: this.$pgettext(
          "Identity",
          "Confirm email modification"
        ),
        cancelEmailButtonLabel: this.$pgettext(
          "Identity",
          "Cancel email modification"
        ),
        validatePasswordButtonLabel: this.$pgettext(
          "Identity",
          "Confirm password modification"
        ),
        cancelPasswordButtonLabel: this.$pgettext(
          "Identity",
          "Cancel password modification"
        ),
        oldPasswordLabel: this.$pgettext("Identity", "Current password"),
        oldPasswordPlaceholder: this.$pgettext(
          "Identity",
          "Your current password"
        ),
        newPasswordLabel: this.$pgettext("Identity", "New password"),
        newPasswordPlaceholder: this.$pgettext("Identity", "Your new password"),
        newPasswordConfirmationLabel: this.$pgettext(
          "Identity",
          "New password confirmation"
        ),
        newPasswordConfirmationPlaceholder: this.$pgettext(
          "Identity",
          "Confirmation of your new password"
        ),
        serverError: this.$pgettext("Identity", "Server error"),
        passwordsMismatchMessage: this.$pgettext(
          "Identity",
          "Confirmation doesn't match with the password"
        ),
        emailFormatMessage: this.$pgettext("Identity", "Wrong email format"),
        emailChangeSuccess: this.$pgettext(
          "Identity",
          "Email successfully changed"
        ),
        emailChangeSuccessTitle: this.$pgettext("Identity", "Email changed"),
        passwordChangeSuccess: this.$pgettext(
          "Identity",
          "Password successfully changed"
        ),
        passwordChangeSuccessTitle: this.$pgettext(
          "Identity",
          "Password changed"
        ),
        closeButtonLabel: this.$pgettext("Identity", "Close")
      };
    },

    displayName() {
      return this.firstName + " " + this.lastName;
    },

    // Email change validation button enabled only if the input is a correct email adress
    emailChangeButtonDisabled() {
      return !this.newEmail.match(/\S+@\S+\.\S+/) || !this.oldPassword;
    },

    // Password change validation button enabled only if the password matches the confirmation
    passwordChangeButtonDisabled() {
      return (
        this.newPassword !== this.newPasswordConfirmation ||
        this.newPassword === "" ||
        this.oldPassword === ""
      );
    }
  },

  mounted() {
    // Init popup to allow email and/or password modification (if allowed in the props)
    if (this.emailAlterable || this.passwordAlterable) {
      this.$(this.$refs.modificationPopup).kendoPopup({
        anchor: this.getBaseComponent().find(".identity-badge", this.$el),
        origin: "bottom left",
        position: "top left",
        animation: false,
        collision: "flip fit"
      });
    }
    this.fetchUser().then(() => {
      this._enableReady();
    });
  }
};

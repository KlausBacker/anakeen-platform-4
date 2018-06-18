export default {
    name: 'ank-identity',

    props: {
        // Compact badge (just the initials), or large badge (with name and email)
        large: {
            type: Boolean,
            default: false,
        },

        // Allow the user to change his email, or not
        emailAlterable: {
            type: Boolean,
            default: false,
        },

        // Allow the user to change his password, or not
        passwordAlterable: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            // User information
            login: '',
            initials: '',
            firstName: '',
            lastName: '',
            email: '',

            // New information to modify on the server
            oldPassword: '',
            newEmail: '',
            newPassword: '',
            newPasswordConfirmation: '',

            // Warning messages during email/password change
            emailWarningMessage: '',
            passwordWarningMessage: '',
        };
    },

    methods: {
        // Fetch user's information from the server
        fetchUser() {
            this.$http.get('/api/v2/ui/users/current')
                .then(response => {
                    this.$emit('userLoaded', response.data.data);

                    this.login = response.data.data.login;
                    this.initials = response.data.data.initials;
                    this.firstName = response.data.data.firstName;
                    this.lastName = response.data.data.lastName;
                    this.email = response.data.data.email;
                });
        },

        // Catch the input event of the ank-authent-password component to update the old password
        updateOldPassword(event) {
            this.oldPassword = event.detail[0];
        },

        // Catch the input event of the ank-authent-password component to update the new password
        updateNewPassword(event) {
            this.newPassword = event.detail[0];
        },

        // Catch the input event of the ank-authent-password component to update the new password confirmation
        updateNewPasswordConfirmation(event) {
            this.newPasswordConfirmation = event.detail[0];
        },

        // Send a request to change the password on the server
        modifyUserPassword() {
            let eventName = 'beforeLogout';
            let options = {
                cancelable: true,
                detail: [
                    {
                        email: this.email,
                        login: this.login,
                        initials: this.initials,
                        firstName: this.firstName,
                        lastName: this.lastName,
                    },
                ],
            };
            let event;
            if (typeof window.CustomEvent === 'function') {
                event = new CustomEvent(eventName, options);
            } else {
                event = document.createEvent('CustomEvent');
                event.initCustomEvent(eventName, options.bubbles, options.cancelable, options.detail);
            }

            this.$el.parentNode.dispatchEvent(event);

            if (!event.defaultPrevented) {
                // Verify if password matches confirmation
                if ((this.newPassword === this.newPasswordConfirmation) && (this.newPassword !== '')) {
                    kendo.ui.progress(this.$('#epasswordModifier'), true);
                    this.$http.put('/components/identity/password',
                        {
                            oldPassword: this.oldPassword,
                            newPassword: this.newPassword,
                        })
                        .then(() => {
                            this.$emit('passwordModified');

                            // Remove loader + close dialog
                            kendo.ui.progress(this.$('#passwordModifier'), false);
                            this.openPasswordModifiedWindow();
                        })
                        .catch((error) => {
                            // Show a warning message and remove the loader
                            this.passwordWarningMessage = error.response.data.userMessage;
                            kendo.ui.progress(this.$('#passwordModifier'), false);
                        });
                } else {
                    // Show a warning message
                    this.passwordWarningMessage = this.translations.passwordsMismatchMessage;
                }
            } else {
                this.$emit('passwordChangeCanceled');
            }
        },

        // Send a request to change the email on the server
        modifyUserEmail() {
            let eventName = 'beforeLogout';
            let options = {
                cancelable: true,
                detail: [
                    {
                        currentEmail: this.email,
                        newEmail: this.newEmail,
                        login: this.login,
                        initials: this.initials,
                        firstName: this.firstName,
                        lastName: this.lastName,
                    },
                ],
            };
            let event;
            if (typeof window.CustomEvent === 'function') {
                event = new CustomEvent(eventName, options);
            } else {
                event = document.createEvent('CustomEvent');
                event.initCustomEvent(eventName, options.bubbles, options.cancelable, options.detail);
            }

            this.$el.parentNode.dispatchEvent(event);

            if (!event.defaultPrevented) {
                // Verify if the input is an email ( [string]@[string].[string] )
                if (this.newEmail.match(/\S+@\S+\.\S+/)) {
                    kendo.ui.progress(this.$('#emailModifier'), true);
                    this.$http.put('/components/identity/email',
                        {
                            email: this.newEmail,
                            password: this.oldPassword,
                        })
                        .then(response => {
                            this.$emit('emailModified', { email: response.data.email });

                            this.email = response.data.email;

                            // Remove loader and close dialog
                            kendo.ui.progress(this.$('#emailModifier'), false);
                            this.openEmailModifiedWindow();
                        })
                        .catch((error) => {
                            // Show a warning message and remove the loader
                            this.emailWarningMessage = error.response.data.userMessage;
                            kendo.ui.progress(this.$('#emailModifier'), false);
                        });
                } else {
                    // Show a warning message
                    this.emailWarningMessage = this.translations.emailFormatMessage;
                }
            } else {
                this.$emit('emailChangeCanceled');
            }
        },

        // Open or close the popup that allow the user to change his email and/or password
        toggleSettingsPopup() {
            if (this.emailAlterable || this.passwordAlterable) {
                this.$('#popup').data('kendoPopup').toggle();
            }
        },

        // Close the popup that allow the user to change his email and/or password
        closeSettingsPopup() {
            if (this.emailAlterable || this.passwordAlterable) {
                this.$('#popup').data('kendoPopup').close();
            }
        },

        // Open dialog window to change user's email
        openEmailModifierWindow() {
            this.closeSettingsPopup();

            // Init window to change the user's email (if allowed in the props)
            if (this.emailAlterable) {
                // Function called when th dialog is open and closed
                let resetEmailChangeData = () => {
                    this.oldPassword = '';
                    document.getElementById('oldPasswordInputEmail').publicMethods.setValue('');
                    this.newEmail = '';
                    this.emailWarningMessage = '';
                };

                let $emailWindow = $('#emailModifier');
                $emailWindow.kendoWindow({
                    draggable: false,
                    resizable: false,
                    modal: true,
                    width: '600px',
                    title: this.translations.emailChangeAction,
                    visible: false,
                    actions: ['Close'],
                    open: resetEmailChangeData,
                    close: resetEmailChangeData,
                    activate: () => { this.$('#emailInput').focus(); },
                }).data('kendoWindow').center().open();
            }
        },

        // Close dialog window to change user's email
        closeEmailModifierWindow() {
            this.$('#emailModifier').data('kendoWindow').close();
        },

        // Open dialog to confirm the modification of the email
        openEmailModifiedWindow() {
            let dialog = this.$('#emailModifiedWindow');
            dialog.kendoWindow({
                width: '250px',
                title: this.translations.emailChangeSuccessTitle,
                visible: true,
                modal: true,
                action: ['Close'],
                close: this.closeEmailModifierWindow,
            }).data('kendoWindow').center().open();
        },

        // Close dialog to confirm the modification of the email
        closeEmailModifiedWindow() {
            this.$('#emailModifiedWindow').data('kendoWindow').close();
        },

        // Open dialog window to change user's password
        openPasswordModifierWindow() {
            this.closeSettingsPopup();

            // Init window to change the user's password (if allowed in the props)
            if (this.passwordAlterable) {
                // Function called when the dialog is open and closed
                let resetPasswordChangeData = () => {
                    this.oldPassword = '';
                    document.getElementById('oldPasswordInput').publicMethods.setValue('');
                    this.newPassword = '';
                    document.getElementById('passwordInput').publicMethods.setValue('');
                    this.newPasswordConfirmation = '';
                    document.getElementById('passwordConfirmationInput').publicMethods.setValue('');
                    this.passwordWarningMessage = '';
                    this.reColorInputs();
                };

                let passwordWindow = this.$('#passwordModifier');
                passwordWindow.kendoWindow({
                    draggable: false,
                    resizable: false,
                    modal: true,
                    width: '600px',
                    title: this.translations.passwordChangeAction,
                    visible: false,
                    actions: ['Close'],
                    open: resetPasswordChangeData,
                    close: resetPasswordChangeData,
                    activate: () => { this.$('#passwordInput').focus(); },
                }).data('kendoWindow').center().open();
            }
        },

        // Close dialog window to change user's password
        closePasswordModifierWindow() {
            this.$('#passwordModifier').data('kendoWindow').close();
        },

        // Open dialog to confirm the modification of the password
        openPasswordModifiedWindow() {
            let dialog = this.$('#passwordModifiedWindow');
            dialog.kendoWindow({
                width: '250px',
                title: this.translations.passwordChangeSuccessTitle,
                visible: true,
                modal: true,
                action: ['Close'],
                close: this.closePasswordModifierWindow,
            }).data('kendoWindow').center().open();
        },

        // Close dialog to confirm the modification of the password
        closePasswordModifiedWindow() {
            this.$('#passwordModifiedWindow').data('kendoWindow').close();
        },

        // Reset email modification warning message when a key is pressed, if this key is not enter
        // (Enter shouldn't remove the message because if the user validate a wrong email with enter,
        // the message should appear)
        removeEmailWarningMessage(event) {
            if (event.key !== 'Enter') {
                this.emailWarningMessage = '';
            }
        },

        // Reset password modification warning message when a key is pressed, if this key is not enter
        // (Enter shouldn't remove the message because if the user validate a wrong password with enter,
        // the message should appear)
        updatePasswordChangeForm(event) {
            if (event.key !== 'Enter') {
                this.passwordWarningMessage = '';
            }

            this.reColorInputs();
        },

        // Re color input borders to show if password and confirmation are different
        reColorInputs() {
            if (this.newPassword && this.newPasswordConfirmation) {
                if (this.newPassword === this.newPasswordConfirmation) {
                    $('#passwordInput').find(':input').css('border-color', 'green');
                    $('#passwordConfirmationInput').find(':input').css('border-color', 'green');
                } else {
                    $('#passwordInput').find(':input').css('border-color', 'red');
                    $('#passwordConfirmationInput').find(':input').css('border-color', 'red');
                }
            } else {
                $('#passwordInput').find(':input').css('border-color', '');
                $('#passwordConfirmationInput').find(':input').css('border-color', '');
            }
        },
    },

    computed: {
        translations() {
            return {
                emailChangeAction: this.$pgettext('Identity', 'Change email'),
                passwordChangeAction: this.$pgettext('Identity', 'Change password'),
                currentEmailLabel: this.$pgettext('Identity', 'Current email'),
                noEmail: this.$pgettext('Identity', 'No email yet'),
                newEmailLabel: this.$pgettext('Identity', 'New email'),
                newEmailPlaceholder: this.$pgettext('Identity', 'Your new email address'),
                validateEmailButtonLabel: this.$pgettext('Identity', 'Confirm email modification'),
                cancelEmailButtonLabel: this.$pgettext('Identity', 'Cancel email modification'),
                validatePasswordButtonLabel: this.$pgettext('Identity', 'Confirm password modification'),
                cancelPasswordButtonLabel: this.$pgettext('Identity', 'Cancel password modification'),
                oldPasswordLabel: this.$pgettext('Identity', 'Current password'),
                oldPasswordPlaceholder: this.$pgettext('Identity', 'Your current password'),
                newPasswordLabel: this.$pgettext('Identity', 'New password'),
                newPasswordPlaceholder: this.$pgettext('Identity', 'Your new password'),
                newPasswordConfirmationLabel: this.$pgettext('Identity', 'New password confirmation'),
                newPasswordConfirmationPlaceholder: this.$pgettext('Identity', 'Confirmation of your new password'),
                serverError: this.$pgettext('Identity', 'Server error'),
                passwordsMismatchMessage: this.$pgettext('Identity', 'Confirmation doesn\'t match with the password'),
                emailFormatMessage: this.$pgettext('Identity', 'Wrong email format'),
                emailChangeSuccess: this.$pgettext('Identity', 'Email successfully changed'),
                emailChangeSuccessTitle: this.$pgettext('Identity', 'Email changed'),
                passwordChangeSuccess: this.$pgettext('Identity', 'Password successfully changed'),
                passwordChangeSuccessTitle: this.$pgettext('Identity', 'Password changed'),
                closeButtonLabel: this.$pgettext('Identity', 'Close'),
            };
        },

        displayName() {
            return this.firstName + ' ' + this.lastName;
        },

        // Email change validation button enabled only if the input is a correct email adress
        emailChangeButtonDisabled() {
            return (!this.newEmail.match(/\S+@\S+\.\S+/) || (!this.oldPassword));
        },

        // Password change validation button enabled only if the password matches the confirmation
        passwordChangeButtonDisabled() {
            return (
                (this.newPassword !== this.newPasswordConfirmation)
                || (this.newPassword === '')
                || (this.oldPassword === '')
            );
        },
    },

    mounted() {
        this.fetchUser();

        // Init popup to allow email and/or password modification (if allowed in the props)
        if (this.emailAlterable || this.passwordAlterable) {
            $('#popup').kendoPopup({
                anchor: this.$('#identity'),
                origin: 'bottom left',
                position: 'top left',
                animation: false,
                collision: 'flip fit',
            });
        }
    },
};

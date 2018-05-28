export default {
    name: 'ank-identity',

    props: {
        // Compact badge (just the initials), or large badge (with name and email)
        large: {
            type: Boolean,
            default: false
        },
        // Allow the user to change his email, or not
        emailalterable: {
            type: Boolean,
            default: false
        },
        // Allow the user to change his password, or not
        passwordalterable: {
            type: Boolean,
            default: false
        }
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
            newEmail: '',
            newPassword: '',
            newPasswordConfirmation: '',

            // Warning messages
            emailWarningMessage: '',
            passwordWarningMessage: ''
        }
    },

    methods: {
        // Fetch user's informations from the server
        fetchUser() {
            this.$http.get('/components/identity/user')
                .then(response => {
                    this.$emit('userLoaded', response.data);

                    this.login = response.data.login;
                    this.initials = response.data.initials;
                    this.firstName = response.data.firstName;
                    this.lastName = response.data.lastName;
                    this.email = response.data.email;
                });
        },

        // Send a request to change the password on the server
        modifyUserPassword() {
            let event = new CustomEvent('beforePasswordChange', {
                cancelable: true,
                detail: {
                    email: this.email,
                    login: this.login,
                    initials: this.initials,
                    firstName: this.firstName,
                    lastName: this.lastName
                }
            });
            this.$el.parentNode.dispatchEvent(event);

            if (!event.defaultPrevented) {
                // Verify if password matches confirmation
                if (this.newPassword === this.newPasswordConfirmation) {
                    kendo.ui.progress(this.$("#epasswordModifier"), true);
                    this.$http.put('/api/v2/authent/password/' + this.login,
                        {
                            password: this.newPassword
                        })
                        .then(() => {
                            this.$emit('passwordModified');

                            // Remove loader + close dialog
                            kendo.ui.progress(this.$("#passwordModifier"), false);
                            this.closePasswordModifierWindow();
                        })
                        .catch(() => {
                            // Show an error message and remove the loader
                            this.passwordWarningMessage = this.translations.serverError;
                            kendo.ui.progress(this.$("#passwordModifier"), false);
                        });
                } else {
                    this.passwordWarningMessage = this.translations.passwordsMismatchMessage;
                }
            } else {
                this.$emit('passwordChangeCanceled');
            }
        },

        // Send a request to change the email on the server
        modifyUserEmail() {
            let event = new CustomEvent('beforeEmailChange', {
                cancelable: true,
                detail: {
                    currentEmail: this.email,
                    newEmail: this.newEmail,
                    login: this.login,
                    initials: this.initials,
                    firstName: this.firstName,
                    lastName: this.lastName
                }
            });
            this.$el.parentNode.dispatchEvent(event);

            if (!event.defaultPrevented) {
                // Verify if the input is an email ( [string]@[string].[string] )
                if (this.newEmail.match(/\S+@\S+\.\S+/)) {
                    kendo.ui.progress(this.$("#emailModifier"), true);
                    this.$http.put('/components/identity/email', {
                        email: this.newEmail
                    })
                        .then(response => {
                            this.$emit('emailModified', { email: response.data.email });

                            this.email = response.data.email;

                            // Remove loader and close dialog
                            kendo.ui.progress(this.$("#emailModifier"), false);
                            this.closeEmailModifierWindow();
                        })
                        .catch(() => {
                            this.emailWarningMessage = this.translations.serverError;
                            kendo.ui.progress(this.$("#emailModifier"), false);
                        });
                } else {
                    this.emailWarningMessage = this.translations.emailFormatMessage;
                }
            } else {
                this.$emit('emailChangeCanceled');
            }
        },

        // Open or close the popup that allow the user to change his email and/or password
        toggleSettingsPopup() {
            if (this.emailalterable || this.passwordalterable) {
                this.$("#popup").data("kendoPopup").toggle();
            }
        },

        // Close the popup that allow the user to change his email and/or password
        closeSettingsPopup() {
            if (this.emailalterable || this.passwordalterable) {
                this.$("#popup").data("kendoPopup").close();
            }
        },

        // Open dialog window to change user's email
        openEmailModifierWindow() {
            this.closeSettingsPopup();
            this.$("#emailModifier").data("kendoWindow").open();
        },

        // Close dialog window to change user's email
        closeEmailModifierWindow() {
            this.$("#emailModifier").data("kendoWindow").close();
        },

        // Open dialog window to change user's password
        openPasswordModifierWindow() {
            this.closeSettingsPopup();
            this.$("#passwordModifier").data("kendoWindow").open();
            this.$("#passwordInput").focus();
            document.getElementById("passwordInput").focus();
        },

        // Close dialog window to change user's password
        closePasswordModifierWindow() {
            this.$("#passwordModifier").data("kendoWindow").close();
        },

        // Reset email modification warning message when a key is pressed, if this key is not enter
        // (Enter shouldn't remove the message because if the user validate a wrong email with enter, the message should appear)
        removeEmailWarningMessage(event) {
            if (event.key !== 'Enter') {
                this.emailWarningMessage = '';
            }
        },

        // Reset password modification warning message when a key is pressed, if this key is not enter
        // (Enter shouldn't remove the message because if the user validate a wrong password with enter, the message should appear)
        removePasswordWarningMessage(event) {
            if (event.key !== 'Enter') {
                this.passwordWarningMessage = '';
            }
        }
    },

    computed: {
        translations() {
            return {
                emailChangeAction: this.$pgettext('Identity', 'Change email'),
                passwordChangeAction: this.$pgettext('Identity', 'Change password'),
                currentEmailLabel: this.$pgettext('Identity', 'Current email'),
                noEmail: this.$pgettext('Identity', 'No email yet'),
                newEmailLabel: this.$pgettext('Identity', 'New email'),
                validateButtonLabel: this.$pgettext('Identity', 'Confirm'),
                cancelButtonLabel: this.$pgettext('Identity', 'Cancel'),
                newPasswordLabel: this.$pgettext('Identity', 'New password'),
                newPasswordConfirmationLabel: this.$pgettext('Identity', 'New password confirmation'),
                serverError: this.$pgettext('Identity', 'Server error'),
                passwordsMismatchMessage: this.$pgettext('Identity', 'Confirmation doesn\'t match with the password'),
                emailFormatMessage: this.$pgettext('Identity', 'Wrong email format'),
                emailWindowTitle: this.$pgettext('Identity', 'Change email'),
                passwordWindowTitle: this.$pgettext('Identity', 'Change password'),
            };
        },

        displayName() {
            return this.firstName + ' ' + this.lastName;
        }
    },

    mounted() {
        this.fetchUser();

        // Init popup to allow email and/or password modification (if allowed in the props)
        if (this.emailalterable || this.passwordalterable) {
            $('#popup').kendoPopup({
                anchor: this.$('#identity'),
                origin: "bottom left",
                position: "top left",
                animation: false,
                collision: 'flip'
            });
        }

        // Init window to change the user's email (if allowed in the props)
        if (this.emailalterable) {
            let resetEmailChangeData = () => {
                this.newEmail = '';
                this.emailWarningMessage = '';
            };

            let emailWindow = $("#emailModifier");
            emailWindow.kendoWindow({
                draggable: false,
                resizable: false,
                modal: true,
                width: '600px',
                title: this.translations.emailWindowTitle,
                visible: false,
                actions: ['Close'],
                open: resetEmailChangeData,
                close: resetEmailChangeData,
                activate: () => { this.$("#emailInput").focus(); }
            }).data('kendoWindow').center();
        }

        // Init window to change the user's password (if allowed in the props)
        if (this.passwordalterable) {
            let resetPasswordChangeData = () => {
                this.newPassword = '';
                this.newPasswordConfirmation = '';
                this.passwordWarningMessage = '';
            };

            let passwordWindow = this.$("#passwordModifier");
            passwordWindow.kendoWindow({
                draggable: false,
                resizable: false,
                modal: true,
                width: "600px",
                title: this.translations.passwordWindowTitle,
                visible: false,
                actions: ['Close'],
                open: resetPasswordChangeData,
                close: resetPasswordChangeData,
                activate: () => { this.$("#passwordInput").focus(); }
            }).data("kendoWindow").center();
        }
    }
}
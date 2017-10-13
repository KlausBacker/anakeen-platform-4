import Vue from 'vue';
import axios from 'axios';

import a4Password from './AuthentPassword.vue';

// noinspection JSUnusedGlobalSymbols
export default {
    name: 'Authent',
    props: {
        loginUrl: {
            type: String,
            default: 'authent/{login3}/?lang={lang}',
        },
        authentLanguages: {
            type: String,
            default: 'fr_FR, en_US',
        },
        defaultLanguage: {
            type: String,
            default: 'fr_FR',
        },
    },
    data() {
        return {
            authentError: 'Error',
            forgetError: 'Error',
            forgetSuccess: '',
            forgetStatusFailed: false,
            resetError: '',
            resetSuccess: '',
            resetStatusFailed: true,
            wrongPassword: false,
            resetPassword: false,
            pwd: '',
            resetPwd1: '',
            resetPwd2: '',
        };
    },

    components: {
        'a4-password': a4Password,
    },
    computed: {
        translations() {
            return {
                loginPlaceHolder: this.$pgettext('Authent', 'Enter your identifier'),
                passwordPlaceHolder: this.$pgettext('Authent', 'Enter your password'),
                validationMessagePassword: this.$pgettext('Authent', 'You must enter your password'),
                validationMessageIdentifier: this.$pgettext('Authent', 'You must enter your identifier'),
                helpContentTitle: this.$pgettext('Authent', 'Help to sign in'),
                authentError: this.$pgettext('Authent', 'Authentication error'),
                unexpectedError: this.$pgettext('Authent', 'Unexpected error'),
                forgetContentTitle: this.$pgettext('Authent', 'Form to reset password'),
                forgetPlaceHolder: this.$pgettext('Authent', 'Identifier or email address'),
                passwordLabel: this.$pgettext('Authent', 'Password :'),
                resetPasswordLabel: this.$pgettext('Authent', 'New password :'),
                confirmPasswordLabel: this.$pgettext('Authent', 'Confirm password :'),
                confirmPasswordError: this.$pgettext('Authent', 'Confirm password are not same as new password'),
            };
        },

        availableLanguages() {

            let languages = this.authentLanguages.split(',');

            return languages.map((lang) => {// jscs:ignore requireShorthandArrowFunctions
                return {
                    key: lang.trim(),
                    label: this.$language.available[lang.trim()],
                };
            });
        },

        redirectUri() {
            let uri = this._protected.getSearchArg('redirect_uri');
            if (!uri) {
                uri = '/';
            }

            return uri;
        },
    },

    beforeMount() {
        let passKey = this._protected.getSearchArg('passkey');
        let currentLanguage = this.defaultLanguage;
        if (this.defaultLanguage === 'auto') {
            let navLanguage = navigator.language || navigator.userLanguage;
            if (navLanguage === 'fr') {
                currentLanguage = 'fr_FR';
            } else {
                currentLanguage = 'en_US';
            }
        }

        Vue.config.language = currentLanguage;

        if (passKey) {
            this.resetPassword = true;
            this.login = this._protected.getSearchArg('uid');
            this.authToken = passKey;
        }
    },

    created() {
        this._protected = {};

        this._protected.getSearchArg = (key) => {
            let result = null;
            let tmp = [];
            location.search
                .substr(1)
                .split('&')
                .forEach((item) => {
                    tmp = item.split('=');
                    if (tmp[0] === key) result = decodeURIComponent(tmp[1]);
                });

            return result;
        };

        this._protected.initForgetElements = () => {

            let $ = this.$kendo.jQuery;
            let $forgetForm = $(this.$refs.authentForgetForm);
            let forgetWindow;
            console.log('protected', this);

            forgetWindow = $(this.$refs.authentForgetForm).kendoWindow({
                visible: false,
                actions: [
                    'Maximize',
                    'Close',
                ],
            }).data('kendoWindow');

            $(this.$refs.authentForgetButton).kendoButton({
                click: () => {
                    forgetWindow.title(this.translations.forgetContentTitle).center().open();
                },
            });

            $(this.$refs.authentForgetSubmit).kendoButton();
            $forgetForm.on('submit', this.forgetPassword);
        };

        this._protected.initResetPassword = () => {

            let $ = this.$kendo.jQuery;
            let $resetForm = $(this.$refs.authentResetPasswordForm);

            $(this.$refs.authentResetSubmit).kendoButton();

            $resetForm.on('submit', this.applyResetPassword);
        };
    },

    mounted() {
        let $ = this.$kendo.jQuery;
        let $connectForm = $(this.$refs.authentForm);
        let helpWindow;

        $(this.$refs.authentHelpButton).kendoButton({
            click: () => {
                helpWindow.title(this.translations.helpContentTitle).center().open();
            },
        });

        $(this.$refs.loginButton).kendoButton();
        $connectForm.on('submit', this.createSession);
        $(this.$refs.authentComponent).find('.btn-reveal').on('click', function revealPass() {
            let $pwd = $(this).closest('.input-group-btn').find('input');
            if ($pwd.attr('type') === 'password') {
                $pwd.attr('type', 'text');
                $(this).find('.fa').removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                if ($pwd.attr('type') === 'text') {
                    $pwd.attr('type', 'password');
                    $(this).find('.fa').addClass('fa-eye').removeClass('fa-eye-slash');
                }
            }
        });

        $(this.$refs.authentLocale).kendoDropDownList({
            change: function changeLocale() {
                Vue.config.language = this.value();
            },
        });

        helpWindow = $(this.$refs.authentHelpContent).kendoWindow({
            visible: false,
            actions: [
                'Maximize',
                'Close',
            ],
        }).data('kendoWindow');

        /**
         * Special custom warning if required fields are empty
         */

        if (this.resetPassword) {
            this._protected.initResetPassword();
        } else {
            this._protected.initForgetElements();
        }

    },

    methods: {

        createSession(event) {
            let $ = this.$kendo.jQuery;

            kendo.ui.progress($(this.$refs.authentForm), true);

            let login = encodeURIComponent(this.login);
            event.preventDefault();
            this.$http.post(`/authent/sessions/${login}`, {
                password: this.pwd,
                language: this.$language.current,
            }).then(() => {
                window.location.href = this.redirectUri;
                this.wrongPassword = false;

            }).catch((e) => {
                console.log('Error', e);
                if (e.response && e.response.data && e.response.data.exceptionMessage) {
                    let info = e.response.data;
                    if (info.messages && info.messages.length > 0 && info.messages[0].code === 'AUTH0001') {
                        // Normal authentication error
                        this.authentError = this.translations.authentError;
                    } else {
                        this.authentError = e.response.data.exceptionMessage;
                    }
                } else {
                    this.authentError = this.translations.authentError;
                }

                this.wrongPassword = true;

                kendo.ui.progress($(this.$refs.authentForm), false);
                $(this.$refs.loginButton).prop('disabled', false);
            });

            $(this.$refs.loginButton).prop('disabled', true);
        },

        forgetPassword(event) {
            let $ = this.$kendo.jQuery;

            kendo.ui.progress($(this.$refs.authentForgetForm), true);

            let login = encodeURIComponent(this.login);
            event.preventDefault();
            this.$http.post(`/authent/mailPassword/${login}`, {
                password: this.pwd,
                language: this.$language.current,
            }).then((response) => {
                console.log('Success', response);
                this.forgetStatusFailed = false;
                kendo.ui.progress($(this.$refs.authentForgetForm), false);
                this.forgetSuccess = response.data.data.message;
                $(this.$refs.authentForgetSubmit).prop('disabled', true).hide();
            }).catch((e) => {
                console.log('Error', e);
                if (e.response && e.response.data && e.response.data.exceptionMessage) {
                    let info = e.response.data;

                    if (info.messages && info.messages.length > 0) {
                        this.forgetError = info.messages[0].contentText;
                    } else {
                        this.forgetError = e.response.data.exceptionMessage;
                    }

                } else {
                    this.forgetError = this.translations.unexpectedError;
                }

                this.forgetStatusFailed = true;

                kendo.ui.progress($(this.$refs.authentForgetForm), false);
                $(this.$refs.authentForgetSubmit).prop('disabled', false);
            });

        },

        applyResetPassword(event) {
            let $ = this.$kendo.jQuery;

            event.preventDefault();

            if (!this.resetPwd1 || this.resetPwd1 !== this.resetPwd2) {
                this.resetStatusFailed = true;
                this.resetError = this.translations.confirmPasswordError;
                return;
            }

            let httpAuth = axios.create({
                baseURL: '/api/v1',
                headers: {
                    Authorization: 'DcpOpen ' + this.authToken,
                },
            });

            kendo.ui.progress($(this.$refs.authentResetPasswordForm), true);

            let login = encodeURIComponent(this.login);
            httpAuth.put(`/authent/password/${login}`, {
                password: this.resetPwd1,
                language: this.$language.current,

            }).then((response) => {
                console.log('Success', response);
                this.resetStatusFailed = false;
                kendo.ui.progress($(this.$refs.authentResetPasswordForm), false);
                this.resetSuccess = response.data.data.message;
                window.setTimeout(() => {
                    $(this.$refs.authentGoHome).kendoButton({
                        click: () => {
                            window.location.href = '../';
                        },
                    });
                }, 10);
            }).catch((e) => {
                if (e.response && e.response.data && e.response.data.exceptionMessage) {
                    let info = e.response.data;

                    if (info.messages && info.messages.length > 0) {
                        this.resetError = info.messages[0].contentText;
                    } else {
                        this.resetError = e.response.data.exceptionMessage;
                    }
                } else {
                    this.resetError = this.translations.unexpectedError;
                }

                this.resetStatusFailed = true;

                kendo.ui.progress($(this.$refs.authentResetPasswordForm), false);
                $(this.$refs.authentForgetSubmit).prop('disabled', false);
            });

            $(this.$refs.authentForgetSubmit).prop('disabled', true).hide();
        },
    },
};

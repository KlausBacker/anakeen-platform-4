import Vue from 'vue';

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
            forgetStatusFailed: false,
            wrongPassword: false,
            hidePassword: true,
        };
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
                forgetContentTitle: this.$pgettext('Authent', 'Form to reset password'),
                forgetPlaceHolder: this.$pgettext('Authent', 'Identifier or email address'),
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
            let getSearchArg = (key) => {
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

            let uri = getSearchArg('redirect_uri');
            if (!uri) {
                uri = '/';
            }

            return uri;
        },
    },

    beforeMount() {
        Vue.config.language = this.defaultLanguage;
    },

    created() {
        this._protected = {};

        this._protected.initForgetElements = () => {

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
            $forgetForm.kendoButton();
            $forgetForm.on('submit', this.askResetPassword);
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
        $connectForm.find('.btn-reveal').on('click', () => {
            let $pwd = $(this.$refs.authentPassword);
            if ($pwd.attr('type') === 'password') {
                this.hidePassword = false;
                $pwd.attr('type', 'text');
            } else {
                if ($pwd.attr('type') === 'text') {
                    this.hidePassword = true;
                    $pwd.attr('type', 'password');
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
        $connectForm.find('input').on('change', function requireMessage() {
            let msg = $(this).data('validationmessage');
            if (this.value === '' && msg) {
                this.setCustomValidity(msg);
            } else {
                this.setCustomValidity('');
            }
        });

        this._protected.initForgetElements();

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

        askResetPassword(event) {
            let $ = this.$kendo.jQuery;

            kendo.ui.progress($(this.$refs.authentForm), true);

            let login = encodeURIComponent(this.login);
            event.preventDefault();
            this.$http.post(`/authent/mailPassword/${login}`, {
                password: this.pwd,
                language: this.$language.current,
            }).then(() => {
                window.location.href = this.redirectUri;
                this.forgetStatusFailed = false;
            }).catch((e) => {
                console.log('Error', e);
                if (e.response && e.response.data && e.response.data.exceptionMessage) {
                    let info = e.response.data;
                    if (info.messages && info.messages.length > 0 && info.messages[0].code === 'AUTH0001') {
                        // Normal authentication error
                        this.forgetError = this.translations.authentError;
                    } else {
                        this.forgetError = e.response.data.exceptionMessage;
                    }
                } else {
                    this.forgetError = this.translations.authentError;
                }

                this.forgetStatusFailed = true;

                kendo.ui.progress($(this.$refs.authentForm), false);
                $(this.$refs.authentForgetSubmit).prop('disabled', false);
            });

            $(this.$refs.authentForgetSubmit).prop('disabled', true);
        },
    },
};

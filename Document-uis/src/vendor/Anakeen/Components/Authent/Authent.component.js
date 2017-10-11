import Vue from 'vue';

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
            wrongPassword: false,
            hidePassword: true,
            translations__: {
                loginPlaceHolder: this.$pgettext('Authent', 'Enter your identifier'),
                passwordPlaceHolder: this.$pgettext('Authent', 'Enter your password'),
                validationMessagePassword: this.$pgettext('Authent', 'You must enter your password'),
                validationMessageIdentifier: this.$pgettext('Authent', 'You must enter your identifier'),
                helpContentTitle: this.$pgettext('Authent', 'Help to sign in'),
            },
        };
    },

    computed: {
        translations: function translations() {
            return {
                loginPlaceHolder: this.$pgettext('Authent', 'Enter your identifier'),
                passwordPlaceHolder: this.$pgettext('Authent', 'Enter your password'),
                validationMessagePassword: this.$pgettext('Authent', 'You must enter your password'),
                validationMessageIdentifier: this.$pgettext('Authent', 'You must enter your identifier'),
                helpContentTitle: this.$pgettext('Authent', 'Help to sign in'),
            };
        },

        availableLanguages: function availableLanguages()  {

            let languages = this.authentLanguages.split(',');
            return languages.map((lang) => {
                return {
                    key: lang.trim(),
                    label: this.$language.available[lang.trim()],
                };
            });
        },

        redirectUri: () => {
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

    mounted() {
        let $ = this.$kendo.jQuery;
        let $form = $(this.$refs.authentForm);
        let helpWindow;

        console.log('FT3', this.availableLanguages, this.defaultLanguage);

        //Vue.config.language = this.defaultLanguage;
        $(this.$refs.authentHelpButton).kendoButton({
            click: () => {
                console.log('open', this.translations);
                helpWindow.title(this.translations.helpContentTitle).center().open();
            },
        });
        $(this.$refs.authentForgetButton).kendoButton({
            click: () => {
                window.alert('No Yet');
            },
        });
        $(this.$refs.loginButton).kendoButton();
        $form.on('submit', this.createSession);
        $form.find('.btn-reveal').on('click', () => {
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
            change: function changeLocale(e) {
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

        $form.find('input').on('change', function requireMessage() {
            console.log('Change', this);
            let msg = $(this).data('validationmessage');
            if (this.value === '' && msg) {
                this.setCustomValidity(msg);
            } else {
                this.setCustomValidity('');
            }
        });

    },

    methods: {

        createSession(event) {
            let $ = this.$kendo.jQuery;

            kendo.ui.progress($(this.$refs.authentForm), true);

            let login = encodeURIComponent(this.login);
            event.preventDefault();
            this.$http.post(`/authent/${login}`, {
                password: this.pwd,
                language: this.$language.current,
            }).then(() => {
                window.location.href = this.redirectUri;
                this.wrongPassword = false;

            }).catch(() => {
                this.wrongPassword = true;

                kendo.ui.progress($(this.$refs.authentForm), false);
                $(this.$refs.loginButton).prop('disabled', false);
            });

            $(this.$refs.loginButton).prop('disabled', true);
        },
    },
};

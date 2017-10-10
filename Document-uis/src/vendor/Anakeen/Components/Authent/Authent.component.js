import Vue from 'vue';

export default {
    name: 'Authent',
    props: {
        appUrl: {
            type: String, default: '',
          },
      },
    data()
    {
      return {
          login: '',
          pwd: '',
          wrongPassword: false,
          hidePassword: true,
          translations: {
              loginPlaceHolder: this.$pgettext('Authent', 'Enter your identifier'),
              passwordPlaceHolder: this.$pgettext('Authent', 'Enter your password'),
              validationMessagePassword: this.$pgettext('Authent', 'You must enter your password'),
              validationMessageIdentifier: this.$pgettext('Authent', 'You must enter your identifier'),
            },
        };
    },

    computed: {
        redirectUri: () =>
        {
            let getSearchArg = (key) =>
            {
                let result = null;
                let tmp = [];
                location.search
                    .substr(1)
                    .split('&')
                    .forEach((item) =>
                    {
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

    mounted()
    {
      let $ = this.$kendo.jQuery;
      let $form = $(this.$refs.authentForm);

      $(this.$refs.authentHelpButton).kendoButton();
      $(this.$refs.loginButton).kendoButton();
      $form.on('submit', this.createSession);
      $form.find('.btn-reveal').on('click', () =>
    {
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
              Vue.config.language =  this.value();
            },
        });

      $form.find('input').on('change', function requireMessage()
    {
        let msg = $(this).data('validationmessage');
        if (this.value === '' && msg) {
          this.setCustomValidity(msg);
        } else {
          this.setCustomValidity('');
        }
      });
    },

    methods: {

        createSession(event)
        {
          event.preventDefault();
          this.$http.post(`/authent/${this.login}`, {
              password: this.pwd,
            }).then(() =>
          {
              window.console.log('red', this.redirectUri);
              window.location.href = this.redirectUri;
              this.wrongPassword = false;

            }).catch(() =>
          {
              this.wrongPassword = true;
            });
        },
      },
  };

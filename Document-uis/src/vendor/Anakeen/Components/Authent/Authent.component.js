export default {
    name: 'Authent', props: {
        appUrl: {
            type: String, default: '',
          },
      }, data()
    {
      return {
          login: '', pwd: '', wrongPassword: false, hidePassword: false,
        };
    },

    computed: {
        redirectUri: () =>
        {
            let getSearchArg = (key) => {
                let result = null;
                let    tmp = [];
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

    mounted()
    {
      let $ = this.$kendo.jQuery;
      $('.authent-login-button').kendoButton();
      $('.authent-form').on('submit', this.createSession);
      $('.btn-reveal').on('click', function revealPassword()
        {
            let $pwd = $('.authent-pwd');
            if ($pwd.attr('type') === 'password') {
              this.hidePassword = false;
              $pwd.attr('type', 'text');
            } else
                if ($pwd.attr('type') === 'text') {
              this.hidePassword = true;
              $pwd.attr('type', 'password');
            }
          });

      $('.form-control').on('change', function requireMessage() {
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

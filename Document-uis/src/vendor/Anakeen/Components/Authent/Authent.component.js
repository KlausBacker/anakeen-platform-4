
export default {
  name: 'Authent',
  props: {
    appUrl: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      login: '',
      pwd: '',
    };
  },
  mounted() {
    "use strict";
    $('.AuthentForm').submit(createSession);
    $('.AuthentLogin').kendoMaskedTextBox({
      mask: 'LOL'
    });
  },
  methods: {
    createSession() {
      "use strict";
      this.$http.post(`/authent/${this.login}`, {
        password: this.pwd
      }).then(function (response) {
        if (response.status === 201) {
          // get home page or previous page
        } else {
          // display login or password is incorrect
        }
      });
    }
  }
};

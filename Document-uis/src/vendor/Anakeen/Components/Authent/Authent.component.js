
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
      showPasswordIsChecked: false,
    };
  },
  mounted() {
    "use strict";
    this.$kendo.jQuery('.AuthentButton').kendoButton(
      {
        click: this.createSession
      }
    );
  },
  methods: {
    createSession(event) {
      event.preventDefault();
      window.console.log('Im creating a session here');
      "use strict";
      this.$http.post(`/authent/${this.login}`, {
        password: this.pwd
      });
    }
  }
};

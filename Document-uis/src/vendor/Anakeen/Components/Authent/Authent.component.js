
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
      wrongPassword: false,
    };
  },
  mounted() {
    "use strict";
    this.$kendo.jQuery('.AuthentButton').kendoButton(
      {
        click: this.createSession
      }
    );
    $('.reveal').on('click', function() {
      var $pwd = $('.AuthentPwd');
      ($pwd.attr('type') === 'password') ? $pwd.attr('type', 'text') : $pwd.attr('type', 'password');
    });
  }
  ,
  methods: {
    createSession(event) {
      event.preventDefault();
      window.console.log('Im creating a session here');
      "use strict";
      this.$http.post(`/authent/${this.login}`, {
        password: this.pwd
      }).then( ()=> {
        this.wrongPassword = false;
      }).catch( () => {
        this.wrongPassword = true;
      });
    },
  }
};

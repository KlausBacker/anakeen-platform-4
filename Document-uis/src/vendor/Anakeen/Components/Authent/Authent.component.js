
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
      hidePassword: false,
    };
  },
  mounted() {
    "use strict";
    this.$kendo.jQuery('.AuthentButton').kendoButton();
    $('.AuthentForm').on('submit', this.createSession);
    $('.btn-reveal').on('click', function() {
      if($('.AuthentPwd').attr('type') === 'password'){
        this.hidePassword = false;
        $('.AuthentPwd').attr('type', 'text');
      } else if ($('.AuthentPwd').attr('type') === 'text') {
        this.hidePassword = true;
        $('.AuthentPwd').attr('type', 'password');
      }
    });
  }
  ,
  methods: {
    createSession(event) {
      event.preventDefault();
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

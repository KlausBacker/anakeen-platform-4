import Vue from "vue";
import Login from "./LoginPage.vue";
import LoadScript from "vue-m-loader";

Vue.use(LoadScript);

new Vue({
  el: "#login",
  components: {
    Login
  },
  data: {
    nsSde: ""
  },
  mounted() {
    this.nsSde = window.nsSde;
  },
  template: "<login :nsSde='nsSde'></login>"
});

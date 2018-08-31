require("../SCSS/hub.scss");
//Some js code here !

import Vue from "vue";
import AnkComponents from "@anakeen/ank-components";
import axios from "axios";
import ankHub from "../Components/Hub.vue";

Vue.use(AnkComponents, { globalVueComponent: true });
Vue.axios = Vue.prototype.$axios = Vue.prototype.$http = axios.create({
  timeout: 10000,
  withCredentials: false
});

new Vue({
  el: "#hub",
  template: "<ank-hub/>",
  components: {
    ankHub
  }
});

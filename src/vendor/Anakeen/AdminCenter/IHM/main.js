import Vue from "vue";

// import router from "./router";
import { HubStation } from "@anakeen/hub-components";
import axios from "axios";
import VueRouter from "vue-router";

Vue.use(VueRouter);
const router = new VueRouter({
  mode: "history"
});
new Vue({
  el: "#admin-center",
  template: '<hub-station :config="config" baseUrl="/admin"></hub-station>',
  components: {
    HubStation
  },
  data() {
    return {
      config: []
    };
  },
  created: function() {
    axios.get("/hub/config/ADMINCENTER").then(response => {
      this.config = response.data.data;
    });
  },
  router
});

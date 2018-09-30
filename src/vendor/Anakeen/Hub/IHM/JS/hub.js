require("../SCSS/hub.scss");

import Vue from "vue";

import ankHub from "../Components/Hub";
import AnkComponents from "@anakeen/ank-components";
import AnkAxios from "axios";

Vue.prototype.$http = AnkAxios.create();
Vue.use(AnkAxios);
Vue.use(AnkComponents);

new Vue(
    {
        el: "#ank-hub",
        template: "<ank-hub/>",
        components: {
            ankHub
        }
    }
);

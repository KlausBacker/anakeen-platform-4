// Entry point to javascript file for webcomponents
import Vue from "vue";
import AnkComponents from "@anakeen/ank-components/lib/ank-components.umd.js";

// Register web components
Vue.use(AnkComponents, {
  webComponents: true
});

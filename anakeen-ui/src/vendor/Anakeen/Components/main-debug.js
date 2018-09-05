// Entry point to javascript file for webcomponents
import Vue from "vue";
import AnkComponents from "@anakeen/ank-components";

// Register web components
Vue.use(AnkComponents, {
  webComponents: true
});

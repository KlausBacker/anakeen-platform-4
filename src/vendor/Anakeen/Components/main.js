// Entry point to javascript file for webcomponents
import Vue from "vue";
import AnkComponents from "../../../../components/components/index";

// Register web components
Vue.use(AnkComponents, {
  webComponents: true
});

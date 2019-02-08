import HelloWorld from "./HelloWorld.vue";
import MyComponent from "./MyComponent.vue";

export default function install(Vue) {
  Vue.component("hello-world", HelloWorld);
  Vue.component("hello-icon", MyComponent);
}

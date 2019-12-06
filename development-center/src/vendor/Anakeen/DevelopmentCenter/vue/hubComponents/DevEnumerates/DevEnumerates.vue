<template>
  <hub-element-layout>
    <nav>
      <span>Enumerates</span>
    </nav>
    <template v-slot:hubContent>
      <div class="dev-enumerates">
        <dev-enumerates
          @filter="onFilter"
          :name="name"
          :localeKey="key"
          :label="label"
          :parentkey="parentkey"
          :disabled="disabled"
        ></dev-enumerates>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/AnkHubElement.esm";
import { setupVue } from "../../setup.js";
import enumStore from "./storeModule.js";

export default {
  name: "ank-dev-enumerates",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "dev-enumerates": () =>
      new Promise(resolve => {
        import("../../sections/Enum/EnumDevCenter.vue").then(Component => {
          resolve(Component.default);
        });
      })
  },
  beforeCreate() {
    this.$parent.$parent.collapsable = false;
    this.$parent.$parent.collapsed = false;
  },
  computed: {
    name() {
      return this.$store.getters["enumerates/name"];
    },
    label() {
      return this.$store.getters["enumerates/label"];
    },
    key() {
      return this.$store.getters["enumerates/key"];
    },
    parentkey() {
      return this.$store.getters["enumerates/parentkey"];
    },
    disabled() {
      return this.$store.getters["enumerates/disabled"];
    }
  },
  created() {
    setupVue(this);
    if (this.$store) {
      this.$store.registerModule(["enumerates"], enumStore);
    }
    const pattern = `/${this.entryOptions.route}`;
    this.getRouter()
      .on(new RegExp(pattern), (...params) => {
        const queries = window.location.search.replace(/^\?/, "").split("&");
        const filters = queries.reduce((acc, curr) => {
          const entry = curr.split("=");
          acc[entry[0]] = entry[1];
          return acc;
        }, {});
        ["name", "key", "label", "parentkey", "disabled"].forEach(key => {
          this.$store.commit(
            `enumerates/SET_${key.toUpperCase()}`,
            filters[key] || ""
          );
        });
      })
      .resolve();
  },
  methods: {
    onFilter(filter) {
      let search = "";
      if (filter && Object.keys(filter).length) {
        search = `?${kendo.jQuery.param(filter)}`;
      }
      this.navigate(`/devel/${this.entryOptions.route}/${search}`);
    }
  }
};
</script>
<style>
.dev-enumerates {
  height: 100%;
  width: 100%;
}
</style>

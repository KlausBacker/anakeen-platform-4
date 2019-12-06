<template>
  <hub-element-layout>
    <nav>
      <span>Localization</span>
    </nav>
    <template v-slot:hubContent>
      <div class="dev-localization">
        <dev-localization
          @filter="onFilter"
          :context="context"
          :msgid="msgid"
          :fr="fr"
          :en="en"
          :files="files"
        ></dev-localization>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/AnkHubElement.esm";
import { setupVue, syncRouter } from "../../setup.js";
import localizationStore from "./storeModule.js";
export default {
  name: "ank-dev-localization",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "dev-localization": () =>
      new Promise(resolve => {
        import(
          "../../sections/Localization/Localization/Localization.vue"
        ).then(Component => {
          resolve(Component.default);
        });
      })
  },
  beforeCreate() {
    this.$parent.$parent.collapsable = false;
    this.$parent.$parent.collapsed = false;
  },
  computed: {
    context() {
      return this.$store.getters["localization/context"];
    },
    msgid() {
      return this.$store.getters["localization/msgid"];
    },
    en() {
      return this.$store.getters["localization/en"];
    },
    fr() {
      return this.$store.getters["localization/fr"];
    },
    files() {
      return this.$store.getters["localization/files"];
    }
  },
  created() {
    setupVue(this);
    if (this.$store) {
      this.$store.registerModule(["localization"], localizationStore);
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
        ["context", "msgid", "en", "fr", "files"].forEach(key => {
          this.$store.commit(
            `localization/SET_${key.toUpperCase()}`,
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
.dev-localization {
  height: 100%;
  min-height: 0;
  flex: 1;
  display: flex;
}
</style>

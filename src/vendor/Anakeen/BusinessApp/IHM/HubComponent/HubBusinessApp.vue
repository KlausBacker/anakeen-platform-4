<template>
  <div>
    <nav v-if="isDockCollapsed">
      <i class="material-icons hub-icon">group</i>
    </nav>
    <nav v-else-if="isDockExpanded" class="business-app-label-expanded">
      <i class="material-icons hub-icon">group</i> <span>Business App</span>
    </nav>
    <div v-else-if="isHubContent" class="business-app-entry">
      <business-app
        :collections="collections"
        :welcomeTab="welcomeTab"
      ></business-app>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue } from "vue-property-decorator";
import HubElement from "@anakeen/hub-components/components/lib/HubElement";
import BusinessApp from "../BusinessApp/BusinessApp.vue";

@Component({
  name: "HubBusinessApp",
  extends: HubElement,
  components: {
    BusinessApp
  }
})
export default class HubBusinessApp extends Vue {
  @Prop({ default: () => [], type: Array }) public collections!: Array<object>;
  @Prop({ default: false, type: [Boolean, Object] }) public welcomeTab!:
    | boolean
    | object;
}
</script>

<style scoped>
.business-app-entry {
  width: 100%;
  height: 100%;
}
.business-app-label-expanded {
  display: flex;
  align-items: center;
}
</style>

<template>
  <div>
    <nav v-if="isDockCollapsed">
      <span class="hub-icon" v-html="iconTemplate"></span>
    </nav>
    <nav v-else-if="isDockExpanded" class="business-app-label-expanded">
      <span class="hub-icon" v-html="iconTemplate"></span>
      <span class="hub-label">{{ hubLabel }}</span>
    </nav>
    <div v-else-if="isHubContent" class="business-app-entry">
      <business-app
        :collections="collections"
        :welcomeTab="welcomeTab"
        :selectedElement="selectedElement"
        @selectedElement="onElementOpened"
        @displayMessage="onDisplayMessage"
        @displayError="onDisplayError"
      ></business-app>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue, Watch } from "vue-property-decorator";
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
  @Prop({ default: "", type: String }) public iconTemplate!: string;
  @Prop({ default: "Business App", type: String }) public hubLabel!: string;

  public selectedElement: string = "welcome";

  @Watch("selectedElement")
  onSelectedElementDataChange(newVal, oldVal) {
    // @ts-ignore
    this.navigate(this.routeUrl + "/" + newVal);
  }

  protected onElementOpened(elementId) {
    if (elementId) {
      this.selectedElement = elementId;
    }
  }

  public created() {
    if (this["isHubContent"]) {
      this.subRouting();
    }
  }

  public get routeUrl() {
    // @ts-ignore
    return this.entryOptions.completeRoute;
  }

  protected subRouting() {
    const url = (this.routeUrl + "/:elementId").replace(/\/\/+/g, "/");

    // @ts-ignore
    this.registerRoute(url, params => {
      this.selectedElement = params.elementId;
    }).resolve(window.location.pathname);
  }

  protected onDisplayMessage(message) {
    // @ts-ignore
    this.hubNotify({
      type: "info", // Type de notification parmi: "info", "notice", "success", "warning", "error"
      content: {
        textContent: message.message, // ou htmlContent: "<em>Un message d'information important</em>"
        title: message.title
      }
    });
  }

  protected onDisplayError(message) {
    // @ts-ignore
    this.hubNotify({
      type: "error",
      content: {
        textContent: message.message, // ou htmlContent: "<em>Un message d'information important</em>"
        title: message.title
      }
    });
  }
}
</script>

<style scoped lang="scss">
.business-app-entry {
  width: 100%;
  height: 100%;
}

.hub-icon {
  /deep/ i {
    font-size: 1.5rem;
  }
}
.hub-icon /deep/ p {
  margin: 0;

  i {
    font-size: 1.5rem;
  }
}

.business-app-label-expanded {
  display: flex;
  align-items: center;

  .hub-label,
  .hub-icon {
    margin-left: 1rem;
  }
}
</style>

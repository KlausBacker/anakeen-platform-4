<template>
  <hub-element-layout>
    <nav>
      <span class="hub-icon" v-html="iconTemplate"></span>
      <span v-if="!isDockCollapsed" class="hub-label">{{ hubLabel }}</span>
    </nav>
    <template v-slot:hubContent>
      <div class="business-app-entry">
        <business-app
          :businessAppName="businessAppName"
          :collections="collections"
          :welcomeTab="welcomeTab"
          :selectedElement="selectedElement"
          @selectedElement="onElementOpened"
          :collection="selectedCollection"
          @selectedCollection="onCollectionChanged"
          :page="selectedPage"
          @pageChanged="onPageChanged"
          :filter="currentFilter"
          @filterChanged="onFilterChanged"
          @displayMessage="onDisplayMessage"
          @displayError="onDisplayError"
        ></business-app>
      </div>
    </template>
  </hub-element-layout>
</template>

<script lang="ts">
import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import HubElement from "@anakeen/hub-components/components/lib/AnkHub.esm";
import BusinessApp from "../BusinessApp/BusinessApp.vue";
import BusinessAppModule from "./businessAppModule.js";

@Component({
  name: "ank-business-app",
  extends: HubElement,
  components: {
    "business-app": BusinessApp
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
  public selectedCollection: string = "";
  public selectedPage: number = 1;
  public currentFilter: string = "";

  @Watch("selectedElement")
  onSelectedElementDataChange(newVal) {
    // @ts-ignore
    this.navigate(this.getComputedRoute({ selectedElement: newVal }));
  }

  @Watch("selectedCollection")
  onSelectedCollectionDataChange(newVal) {
    // @ts-ignore
    this.navigate(this.getComputedRoute({ collection: newVal }));
  }

  @Watch("selectedPage")
  onSelectedPageDataChange(newVal) {
    // @ts-ignore
    this.navigate(this.getComputedRoute({ page: newVal }));
  }

  @Watch("currentFilter")
  onCurrentFilterDataChange(newVal) {
    // @ts-ignore
    this.navigate(this.getComputedRoute({ filter: newVal }));
  }

  protected onElementOpened(elementId) {
    if (elementId) {
      this.selectedElement = elementId;
    }
  }

  protected onCollectionChanged(collection) {
    if (collection) {
      this.selectedCollection = collection;
    }
  }

  protected onPageChanged(page) {
    if (page) {
      this.selectedPage = page;
    }
  }

  protected onFilterChanged(filterValue) {
    this.currentFilter = filterValue;
  }
  public created() {
    // @ts-ignore
    const store = this.getStore() || window.hub.store;
    store.registerModule(this.businessAppName, BusinessAppModule());
    this.subRouting();
  }

  public get businessAppName() {
    // @ts-ignore
    let name = this.entryOptions.name || this.entryOptions.route;
    return HubBusinessApp.formatString(name, { separator: "_", uppercase: true});
  }

  public get routeUrl() {
    // @ts-ignore
    return this.entryOptions.completeRoute;
  }

  private getComputedRoute({ collection, selectedElement, page, filter }) {
    const collectionVal = collection || this.selectedCollection;
    const selectedElVal = selectedElement || this.selectedElement;
    const pageVal = page || this.selectedPage;
    const filterVal = filter || this.currentFilter;
    return (
      this.routeUrl +
      "/" +
      collectionVal +
      "/" +
      selectedElVal +
      "?page=" +
      pageVal +
      (filterVal ? "&filter=" + encodeURI(filterVal) : "")
    );
  }

  protected subRouting() {
    const url = (this.routeUrl + "/:collection/:elementId").replace(
      /\/\/+/g,
      "/"
    );

    // @ts-ignore
    this.registerRoute(url, (params, ...args) => {
      // @ts-ignore
      this.selectedCollection = params.collection;
      this.selectedElement = params.elementId;
      const page = window.location.search.match(/page=(\d+)/);
      if (page && page.length > 1) {
        this.selectedPage = parseInt(page[1]);
      }
      const filter = window.location.search.match(/filter=([^&]+)/);
      if (filter && filter.length > 1) {
        this.currentFilter = decodeURI(filter[1]);
      }
    }).resolve(window.location.pathname);
  }

  protected onDisplayMessage(message) {
    let type = "info";
    if (message.type) {
      type = message.type;
    }
    let contentHtml = "";
    if (message.message) {
      contentHtml += `<p>${escape(message.message)}</p>`;
    }
    if (message.htmlMessage) {
      contentHtml += `<p>${message.htmlMessage}</p>`;
    }
    // @ts-ignore
    this.hubNotify({
      type,
      content: {
        htmlContent: contentHtml, // ou htmlContent: "<em>Un message d'information important</em>"
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

  private static formatString(str: string, opts: any = { uppercase: false, capitalize: true, lowercase: false, separator: ""}) {
    const DEFAULT_OPTIONS = { uppercase: false, capitalize: true, lowercase: false, separator: ""};
    const options = Object.assign({}, DEFAULT_OPTIONS, opts);
    const capitalize = (msg) => msg.charAt(0).toUpperCase() + msg.slice(1);
    const finalResult = str.replace(/[/ \-_']+[\w]?/g, (capture, index, input) => {
      const lastCapture = capture.charAt(capture.length - 1);
      if (/\w/.test(lastCapture)) {
        let result = lastCapture;
        if (options.capitalize) {
          result = result.toUpperCase();
        }
        if (options.separator) {
          result = options.separator + result;
        }
        return result;
      }
      return ""
    });
    if (options.uppercase) {
      return finalResult.toUpperCase();
    }
    if (options.lowercase) {
      return finalResult.toLowerCase();
    }
    if (options.capitalize) {
      return capitalize(finalResult);
    }
    return finalResult;
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

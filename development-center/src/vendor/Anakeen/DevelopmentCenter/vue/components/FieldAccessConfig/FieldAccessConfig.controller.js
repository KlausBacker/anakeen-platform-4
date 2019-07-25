import AnkTreeList from "devComponents/SSTreeList/SSTreeList.vue";
import { getTreeListData } from "./utils/treeUtils";

export default {
  props: {
    url: {
      type: String,
      default: `/api/v2/devel/security/fall/<identifier>`
    },
    fallid: {
      type: [Number, String],
      default: 0
    },
    detachable: {
      type: Boolean,
      default: false
    },
    detachUrl: {
      type: String,
      default: "/api/v2/devels/security/workflow/<identifier>.html"
    },
    displayField: {
      type: String,
      default: "name"
    }
  },
  components: {
    AnkTreeList
  },
  watch: {
    fallid() {
      this.treeConfigReady = false;
      this.privateMethods.initTreeList();
    }
  },
  devCenterRefreshData() {
    this.treeConfigReady = false;
    this.privateMethods.initTreeList();
  },
  computed: {
    fallProperties() {
      if (this.fallData) {
        return this.fallData.properties;
      }
      return null;
    },
    fallContent() {
      if (this.fallContent) {
        return this.fallData.fields;
      }
      return [];
    },
    resolvedUrl() {
      let baseUrl = this.url;
      if (this.appliedLayers.length) {
        baseUrl = `${baseUrl}?${this.$.param({ layers: this.appliedLayers })}`;
      }
      if (baseUrl.indexOf("<identifier>") === -1) {
        return baseUrl;
      }
      return baseUrl.replace("<identifier>", this.fallid);
    },
    resolveDetachUrl() {
      const baseUrl = this.detachUrl;
      if (baseUrl.indexOf("<identifier>") === -1) {
        return baseUrl;
      }
      return baseUrl.replace("<identifier>", this.fallid);
    },
    baseColumns() {
      return [
        {
          name: "fieldId",
          label: "Field Id"
        },
        {
          name: "_original_",
          label: "Original access"
        },
        {
          name: "_final_",
          label: "Result access"
        }
      ];
    }
  },
  beforeCreate() {
    this.privateMethods = {
      getHeaderTemplate: column => {
        const defaultColumnsIds = this.baseColumns.map(c => c.name);
        if (defaultColumnsIds.indexOf(column.name) === -1) {
          let checked = "";
          if (this.appliedLayers.indexOf(column.id) > -1) {
            checked = "checked";
          }
          return `
            <div class="fall-layer-header">
                <span class="fall-layer-header-aclName">${column.label}</span>
                <a data-role="develRouterLink"
                href="/devel/smartElements/${column.refName || column.id}/view/?name=${column.refName}"
                class="fall-layer-header-label">${column.refName}</a>
                <div class="show-all-switch switch-container">
                    <label class="switch">
                        <input class="switch-button" ${checked} type="checkbox" data-layer="${column.id}">
                        <span class="slider round"></span>
                    </label>
                    <label class="switch-label" for="extendedView">
                        <span>Apply layer</span>
                    </label>
                </div>
            </div>
          `;
        }
        return column.label;
      },
      getCellTemplate: columnId => item => {
        if (columnId === "_final_") {
          if (item["_original_"] !== item["_final_"]) {
            return `<b>${item[columnId] || ""}</b>`;
          }
        }
        return item[columnId] || "";
      },
      getColumns: layers => {
        let columns = this.baseColumns.slice(0, 2);
        columns = columns.concat(
          layers.map(l => {
            return {
              name: l.name || l.id,
              label: l.aclName,
              id: l.id,
              refName: l.name
            };
          })
        );
        columns.push(this.baseColumns[this.baseColumns.length - 1]);
        return columns;
      },
      initTreeList: () => {
        kendo.ui.progress(this.$(this.$refs.fallConfigTree), true);
        this.$http
          .get(this.resolvedUrl)
          .then(response => {
            if (response && response.data && response.data.data) {
              const data = response.data.data;
              if (data.request) {
                this.appliedLayers = data.request.layers.map(l => l.id);
              }
              this.fallData = data;
              this.treeColumns = this.privateMethods.getColumns(this.fallData.layers);
              this.$emit("fieldaccess-config-ready");
              kendo.ui.progress(this.$(this.$refs.fallConfigTree), false);
            }
          })
          .catch(err => {
            kendo.ui.progress(this.$(this.$refs.fallConfigTree), false);
            console.error(err);
            throw err;
          });
      },
      parseData: data => {
        return getTreeListData(data.fields, this.privateMethods.onInvalidFieldFound);
      },
      onInvalidFieldFound: fieldId => {
        const errorMessage = `Field with field id : ${fieldId} is unknown`;
        if (!this.warnings[fieldId]) {
          this.$store.dispatch("displayError", {
            title: "Warning",
            textContent: errorMessage,
            type: "warning"
          });
          this.warnings[fieldId] = errorMessage;
        }
        console.warn(errorMessage);
      },
      customizeTree: () => {
        const treeList = this.$refs.ankTreeList.$refs.ssTreelist.kendoWidget();
        this.$(treeList.thead).on("change", "input.switch-button[type=checkbox]", event => {
          const layer = this.$(event.currentTarget).data("layer");
          const indexOf = this.appliedLayers.indexOf(layer);
          if (event.currentTarget.checked) {
            if (indexOf === -1) {
              this.appliedLayers.push(layer);
            }
          } else {
            if (indexOf >= -1) {
              this.appliedLayers.splice(indexOf, 1);
            }
          }
        });
      }
    };
  },
  created() {
    this.$on("fieldaccess-config-ready", () => {
      this.treeConfigReady = true;
    });
  },
  mounted() {
    this.privateMethods.initTreeList();
  },
  methods: {
    onDetachComponent() {
      window.open(this.resolveDetachUrl);
    },
    getLabel(element, capitalize = false, firstBold = false) {
      let label = "";
      if (element) {
        if (typeof element === "object") {
          if (this.displayField) {
            label = element[this.displayField] || element.name;
          } else {
            label = element.name;
          }
        } else {
          label = element;
        }
      }
      if (label && capitalize) {
        label = `${label.charAt(0).toUpperCase()}${label.substring(1)}`;
      }
      if (label && firstBold) {
        label = `${label.charAt(0).bold()}${label.substring(1)}`;
      }
      return label;
    }
  },
  data() {
    return {
      model: {
        id: "virtualId",
        parentId: "parentId",
        expanded: true
      },
      completeList: false,
      treeColumns: [],
      appliedLayers: [],
      treeConfigReady: false,
      warnings: {},
      fallData: null
    };
  }
};

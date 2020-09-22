import "@progress/kendo-ui/js/kendo.popup";
import "@progress/kendo-ui/js/kendo.grid";
import { Vue } from "vue-property-decorator";

import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import { DropdownsInstaller } from "@progress/kendo-dropdowns-vue-wrapper";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";

const urlJoin = require("url-join");

import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import AnkHubMockup from "./HubAdminMockUp.vue";
import AnkSplitter from "@anakeen/internal-components/lib/Splitter";

Vue.use(ButtonsInstaller);
Vue.use(DropdownsInstaller);
Vue.use(DataSourceInstaller);

//noinspection JSUnusedGlobalSymbols
export default {
  name: "ank-hub-admin",
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-hub-mockup": AnkHubMockup,
    "smart-element": () => AnkSmartElement,
    "ank-splitter": AnkSplitter
  },
  props: ["hubId", "hubComponentSelected"],
  data() {
    return {
      // eslint-disable-next-line no-undef
      collection: "",
      hubElement: { properties: {} },
      hubIcon: "",
      mockData: {},
      hubConfig: [],
      selectedComponent: this.hubComponentSelected,
      isSmartElementMounted: false,
      panes: [
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size: "50%"
        },
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size: "50%"
        }
      ]
    };
  },
  watch: {
    hubId: function(val, oldVal) {
      if (val !== oldVal) {
        this.initHub(val);
        Vue.nextTick(() => {
          this.$refs.hubGrid._loadGridContent();
        });
      }
    },
    selectedComponent: function(val) {
      if (val > 0) {
        this.openDetailConfig(val);
      }
      this.selectTr(val);
      this.$emit("hubComponentSelected", val);
    }
  },

  created() {
    this.initHub(this.hubId);
  },

  mounted() {
    $(this.$el).on("click", ".smart-element-grid-cell--key  > .smart-element-grid-cell-content", e => {
      //noinspection JSUnusedGlobalSymbols
      this.selectedComponent = this.$refs.hubGrid.dataItems[e.currentTarget.innerText - 1].properties.id;
    });
    this.openConfigInfo();
  },
  methods: {
    initHub(id) {
      return this.$http.get(`/api/v2/smart-elements/${id}.json`).then(response => {
        this.hubElement = response.data.data.document;
      });
    },

    openConfigInfo() {
      this.openDetailConfig(this.hubId);
    },
    openElement() {
      this.selectedComponent = this.hubComponentSelected;
      if (this.selectedComponent) {
        this.openDetailConfig(this.selectedComponent);
      }
    },
    exportConfiguration() {
      window.open(`/hub/config/${this.hubId}.zip`, "_self");
    },
    openInterface() {
      let routeEntry = `/hub/station/${this.hubId}/`;
      if (this.hubElement) {
        const routerEntry = this.hubElement.attributes
          ? this.hubElement.attributes.hub_instanciation_router_entry
          : null;
        if (routerEntry && routerEntry.value) {
          routeEntry = urlJoin("/", routerEntry.value);
        } else {
          const instanceLogicalName = this.hubElement.properties.name;
          if (instanceLogicalName) {
            routeEntry = `/hub/station/${instanceLogicalName}/`;
          }
        }
      }
      window.open(routeEntry);
    },
    selectTr(seId) {
      let $trs = $(this.$el).find("tr[data-seid]");
      let $tr = $(this.$el).find("tr[data-seid=" + seId + "]");

      $trs.removeClass("row--selected");
      $tr.addClass("row--selected");
    },

    selectCreateConfig(e) {
      this.collection = e.dataItem.value;
      this.$refs.hubAdminSplitter.disableEmptyContent();
      this.$nextTick(() => {
        this.createConfig(this.collection);
      });
    },
    addClassOnSelectorContainer(e) {
      e.sender.popup.element.addClass("hub-select-container");
    },
    createConfig(e) {
      this.$refs.hubAdminSplitter.disableEmptyContent();
      this.$refs.smartConfig
        .fetchSmartElement({
          initid: e,
          viewId: "!defaultCreation"
        })
        .then(() => {
          this.$refs.smartConfig.setValue("hub_station_id", {
            value: this.hubElement.properties.initid,
            displayValue: this.hubElement.properties.title,
            icon: this.hubElement.properties.icon
          });
        });
    },

    displayMockUp: function(e) {
      let data = e.data.content.content;
      const positionKey = [
        "TOP_LEFT",
        "TOP_CENTER",
        "TOP_RIGHT",
        "LEFT_TOP",
        "LEFT_CENTER",
        "LEFT_BOTTOM",
        "RIGHT_TOP",
        "RIGHT_CENTER",
        "RIGHT_BOTTOM",
        "BOTTOM_LEFT",
        "BOTTOM_CENTER",
        "BOTTOM_RIGHT"
      ];

      this.mockData = {};
      positionKey.forEach(pos => {
        this.mockData[pos] = [];
      });

      data.sort((a, b) => {
        const idxa = positionKey.indexOf(a.attributes.hub_docker_position.value);
        const idxb = positionKey.indexOf(a.attributes.hub_docker_position.value);
        const posa = a.attributes.hub_order.value || 0;
        const posb = b.attributes.hub_order.value || 0;

        const pa = idxa * 100 + posa;
        const pb = idxb * 100 + posb;

        if (pa > pb) {
          return 1;
        } else if (pa < pb) {
          return -1;
        } else if (pa === pb) {
          const sortTitle = a.properties.title.localeCompare(b.properties.title);
          if (sortTitle > 0) {
            return 1;
          } else if (sortTitle < 0) {
            return -1;
          } else {
            return 0;
          }
        }
        return 0;
      });

      data.forEach((datum, k) => {
        datum.abstract.key = { value: k + 1, displayValue: k + 1 };
        this.mockData[datum.attributes.hub_docker_position.value].push({
          key: datum.abstract.key,
          title: datum.properties.title,
          initid: datum.properties.initid
        });
      });
    },
    openDetailConfig(seid) {
      this.$refs.hubAdminSplitter.disableEmptyContent();
      if (this.isSmartElementMounted) {
        this.openConfig(seid);
      } else {
        this.$watch("isSmartElementMounted", (val, oldVal) => {
          if (!oldVal && !!val) {
            this.openConfig(seid);
          }
        });
      }
    },

    openConfig(eid) {
      if (this.$refs.smartConfig) {
        this.$refs.smartConfig.fetchSmartElement({
          initid: eid,
          viewId: "!defaultConsultation"
        });
        // this.$refs.hubGrid.privateScope.initGrid();
      }
    },

    changeSelectComponent(seid) {
      //noinspection JSUnusedGlobalSymbols
      this.selectedComponent = seid;
    },

    listenSmartElement() {
      if (!this.isSmartElementMounted) {
        this.$refs.smartConfig.addEventListener("afterSave", (e, d) => {
          const seId = d.initid;
          if (d.family.name === "HUBINSTANCIATION") {
            this.initHub(seId);
          }
          if (this.$refs.hubGrid && this.$refs.hubGrid.dataSource) {
            this.selectedComponent = 0;
            this.$refs.hubGrid.kendoGrid.dataSource.read().then(() => {
              this.selectedComponent = seId;
            });
          }
        });
        this.$refs.smartConfig.addEventListener("afterDelete", () => {
          if (this.$refs.hubGrid && this.$refs.hubGrid.dataSource) {
            this.$refs.hubGrid.kendoGrid.dataSource.read();
            this.selectedComponent = 0;
          }
        });
        this.isSmartElementMounted = true;
      }
    }
  }
};

import "@progress/kendo-ui/js/kendo.popup";
import "@progress/kendo-ui/js/kendo.grid";
import Vue from "vue";

import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";

import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSEGrid";
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement";
import AnkHubMockup from "./HubAdminMockUp.vue";
import AnkSplitter from "@anakeen/internal-components/lib/Splitter";

Vue.use(ButtonsInstaller);

export default {
  name: "ank-hub-admin",
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-hub-mockup": AnkHubMockup,
    "smart-element": AnkSmartElement,
    "ank-splitter": AnkSplitter
  },
  data() {
    return {
      // eslint-disable-next-line no-undef
      childFam: window.ankChildFam,
      collection: "",
      hubId: "",
      hubTitle: "",
      hubIcon: "",
      mockData: {},
      hubConfig: [],
      selectedComponent: 0,
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
    selectedComponent: function(val) {
      if (val > 0) {
        this.openDetailConfig(val);
      }
      this.selectTr(val);
    }
  },

  created() {
    let route = window.location.href;
    this.hubId = route.match(/\/hub\/admin\/(\w+)/)[1];
    this.$http
      .get(`/api/v2/smart-elements/${this.hubId}.json`)
      .then(response => {
        this.hubTitle = response.data.data.document.properties.title;
      });
  },

  mounted() {
    Object.keys(this.childFam).forEach(key => {
      const elt = this.childFam[key];
      this.hubConfig.push({ text: elt.title, value: elt.name });
    });
  },
  methods: {
    selectTr(seId) {
      let $trs = $(this.$el).find("tr[data-seid]");
      let $tr = $(this.$el).find("tr[data-seid=" + seId + "]");

      $trs.removeClass("row--selected");
      $tr.addClass("row--selected");
    },
    toolbarConfig() {
      $(".grid-toolbar-create-action").kendoDropDownList({
        dataTextField: "text",
        dataValueField: "value",
        dataSource: this.hubConfig,
        valueTemplate: "Create",
        select: e => {
          this.selectCreateConfig(e);
        }
      });
    },
    selectCreateConfig(e) {
      this.collection = e.dataItem.value;
      this.$refs.hubAdminSplitter.disableEmptyContent();
      this.$nextTick(() => {
        if (this.$refs.smartConfig.isLoaded()) {
          this.createConfig(this.collection);
        } else {
          this.$refs.smartConfig.$once("documentLoaded", () => {
            this.createConfig(this.collection);
          });
        }
      });
    },
    createConfig(e) {
      this.$refs.hubAdminSplitter.disableEmptyContent();
      this.$refs.smartConfig.fetchSmartElement({
        initid: e,
        viewId: "!defaultCreation"
      });
      this.$refs.smartConfig.addEventListener("ready", () => {
        this.$refs.smartConfig.addCustomClientData({
          hubId: this.hubId,
          hubTitle: this.hubTitle
        });
      });
    },

    displayMockUp(e) {
      let data = e.data.content.smartElements;
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
        const idxa = positionKey.indexOf(
          a.attributes.hub_docker_position.value
        );
        const idxb = positionKey.indexOf(
          b.attributes.hub_docker_position.value
        );
        const posa = a.attributes.hub_order.value || 0;
        const posb = b.attributes.hub_order.value || 0;

        const pa = idxa * 100 + posa;
        const pb = idxb * 100 + posb;

        if (pa > pb) {
          return 1;
        } else if (pa < pb) {
          return -1;
        }

        return 0;
      });

      data.forEach((datum, k) => {
        datum.attributes.key = { value: k + 1, displayValue: k + 1 };
        this.mockData[datum.attributes.hub_docker_position.value].push({
          key: datum.attributes.key.value,
          title: datum.properties.title.displayValue,
          initid: datum.properties.initid
        });
      });
      window.console.log(data, this.mockData);
      this.addDataOnRow();
    },

    addDataOnRow() {
      this.$nextTick(() => {
        const kgrid = this.$refs.hubGrid.kendoGrid;
        const items = kgrid.items();

        items.each(function addTypeClass() {
          const dataItem = kgrid.dataItem(this);
          if (dataItem.initid) {
            $(this).attr("data-seid", dataItem.initid);
          }
        });

        this.selectTr(this.selectedComponent);
      });
    },

    openDetailConfig(seid) {
      let e = new Event("click");
      e.data = {
        type: "detail",
        row: {
          id: seid
        }
      };

      this.$refs.hubAdminSplitter.disableEmptyContent();
      this.$nextTick(() => {
        if (this.$refs.smartConfig && this.$refs.smartConfig.isLoaded()) {
          this.listenSmartElement(seid);
        } else {
          this.$refs.smartConfig.$once("documentLoaded", () => {
            this.listenSmartElement(seid);
          });
        }
      });
    },

    openConfig(eid) {
      this.$refs.smartConfig.fetchSmartElement({
        initid: eid,
        viewId: "!defaultConsultation"
      });
    },
    toolbarActionClick(e) {
      switch (e.data.type) {
        case "consult":
          window.open(`/hub/station/${this.hubId}/`);
          break;
      }
    },
    changeSelectComponent(seid) {
      //noinspection JSUnusedGlobalSymbols
      this.selectedComponent = seid;
    },

    actionClick(e) {
      e.preventDefault();
      switch (e.data.type) {
        case "detail":
          this.selectedComponent = e.data.row.id;
          break;

        default:
          break;
      }
    },
    listenSmartElement(eid) {
      this.$refs.smartConfig.addEventListener("afterSave", (e, d) => {
        const seId = d.initid;
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

      this.openConfig(eid);
    }
  }
};

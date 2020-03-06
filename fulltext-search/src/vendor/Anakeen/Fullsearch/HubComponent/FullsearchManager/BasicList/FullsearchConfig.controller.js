import Vue from "vue";
import { Grid } from "@progress/kendo-vue-grid";

const detailInstance = Vue.component("template-component", {
  props: {
    dataItem: Object
  },
  template: `
      <section>
          <p><strong>Stemmer:</strong> {{dataItem.domainStem}}</p>
          <div>
              <strong>Analyzed structures:</strong>
              <ul class="structure-info">
                  <li v-for="structure in dataItem.structures"  >
                      <span :class="{ success: (structure.stats.totalToIndex === structure.stats.totalIndexed && structure.stats.totalDirty === 0) }">{{ structure.structure }}</span>
                      <ul>
                          <li>Total to index : {{structure.stats.totalToIndex}}</li>
                          <li :class="{ warning: (structure.stats.totalToIndex > structure.stats.totalIndexed) }">Total indexed : {{structure.stats.totalIndexed}}</li>
                          <li :class="{ warning: (structure.stats.totalDirty > 0) }">Total not up to date : {{structure.stats.totalDirty}}</li>
                      
                      </ul>
                      
                  </li>
              </ul>
              <strong>Files indexing statuses:</strong>
              <ul  class="file-info"> 
                  <li  v-for="fileStatus in dataItem.database.files"><span class="status">{{fileStatus.label}}</span> : <b>{{fileStatus.count}}</b> </li>
              </ul>
          </div>
          <p><strong>Database table size:</strong> {{dataItem.database.size.prettySize}}</p>
      </section>`
});

const componentHeaderInstance = Vue.component("template-component", {
  props: {
    field: String,
    grid: Object,
    title: String,
    column: Object,
    sortable: [Boolean, Object]
  },
  data() {
    return {
      autoReloadTimer: false
    };
  },
  template: `<div class="full-header"  ><b>{{title}}</b> 
      <span>File cache size: <b>{{getFileTableSize()}}</b></span>
      <span>Next update: <b>{{getUpdateDate()}}</b></span>
      <button @click="clickHandler" @dblclick="autoRefresh" class="btn"  :class="{ 'btn-primary':this.autoReloadTimer}"><span class="material-icons">refresh</span></button></div>`,
  methods: {
    getFileTableSize() {
      if (this.grid && this.grid.fileCacheSize) {
        return this.grid.fileCacheSize.prettySize;
      }
    },
    getUpdateDate() {
      if (this.grid && this.grid.nextUpdateDate) {
        return this.grid.nextUpdateDate.toDateString() + " " + this.grid.nextUpdateDate.toTimeString().substr(0, 5);
      }
      return "No update date";
    },
    clickHandler: function(e) {
      if (this.autoReloadTimer) {
        this.autoReloadTimer = false;
        this.$emit("autoReload", this.autoReloadTimer);
      }
      this.$emit("reload", e);
    },
    autoRefresh: function() {
      this.autoReloadTimer = !this.autoReloadTimer;
      this.$emit("autoReload", this.autoReloadTimer);
    }
  }
});


// noinspection JSUnusedGlobalSymbols
export default {
  name: "ank-fullsearch-list",
  components: {
    "kendo-grid": Grid
  },
  data() {
    return {
      selectedField: "selected",
      selectedID: "",
      detailTemplate: detailInstance,
      domains: [],
      fileCacheSize: {},
      gridData: null,
      nextUpdateDate: "",
      configInfo: [],
      expandedRows: {},
      autoReloadTimer: null,
      columns: [
        {
          field: "title",
          title: "Search domains",
          headerCell: this.headerCellRenderFunction
        }
      ]
    };
  },

  created() {
    this.fetchConfigs();
  },

  mounted() {},
  computed: {},
  methods: {
    headerCellRenderFunction(createElement, defaultRendering, props) {
      return createElement(componentHeaderInstance, {
        props: { ...props, grid: this },
        on: {
          reload: this.fetchConfigs,
          autoReload: this.autoReload
        }
      });
    },
    fetchConfigs: function() {
      return this.$http.get("/api/admin/fullsearch/domains/").then(response => {
        this.configInfo = [];
        this.fileCacheSize = response.data.data.fileCacheSize;
        if (response.data.data.nextUpdateDate) {
          this.nextUpdateDate = new Date(response.data.data.nextUpdateDate);
        }
        this.domains = response.data.data.config;
        this.domains.forEach(domain => {
          const domainName = domain.name;
          const domainStem = domain.stem;

          for (let [structName, dbStats] of Object.entries(domain.database.structures)) {
            domain.configs.forEach(struc => {
              if (struc.structure === structName) {
                struc.stats = dbStats;
              }
            });
          }

          this.configInfo.push({
            domainName: domainName,
            domainStem: domainStem,
            description: domain.description,
            title: domain.description ? `${domain.description} (${domainName})` : domainName,
            database: domain.database,
            structures: domain.configs
          });
        });
        if (!this.selectedID && this.configInfo && this.configInfo.length > 0) {
          this.selectedID = this.configInfo[0].domainName;
          this.$emit("selected", this.selectedID);
        }

        this.updateData();
      });
    },

    autoReload(reload) {
      if (reload) {
        this.autoReloadTimer = window.setTimeout(() => {
          this.fetchConfigs().then(() => {
            this.autoReloadTimer = null;
            this.autoReload(true);
          });
        }, 1000);
      } else {
        if (this.autoReloadTimer) {
          window.clearTimeout(this.autoReloadTimer);
        }
      }
    },
    updateData() {
      this.gridData = this.configInfo.map(item => ({ ...item, selected: item.domainName === this.selectedID }));
      // Reaffect expanded rows
      for (let [domainName, expanded] of Object.entries(this.expandedRows)) {
        this.gridData.forEach(item => {
          if (item.domainName === domainName) {
            Vue.set(item, "expanded", expanded);
          }
        });
      }
    },
    onRowClick(event) {
      this.selectedID = event.dataItem.domainName;
      this.gridData.map(item => {
        // Need to unselect before select new one
        if (item.selected === true) {
          Vue.set(item, event.target.$props.selectedField, false);
        }
      });
      Vue.set(event.dataItem, event.target.$props.selectedField, true);
      this.$emit("selected", this.selectedID);
    },
    expandChange(event) {
      Vue.set(event.dataItem, event.target.$props.expandField, event.value);
      this.expandedRows[event.dataItem.domainName] = event.value;
    }
  }
};

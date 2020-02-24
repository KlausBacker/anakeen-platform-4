import Vue from "vue";
import { Grid } from "@progress/kendo-vue-grid";

const componentInstance = Vue.component("template-component", {
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
          </div>
          <p><strong>Database size:</strong> {{dataItem.database.size.prettySize}}</p>
      </section>`
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
      detailTemplate: componentInstance,
      domains: [],
      gridData: null,
      configInfo: [],
      columns: [{ field: "domainName", title: "Search domains" }]
    };
  },

  created() {
    // this.getData();
  },

  mounted() {
    this.fetchConfigs();
  },
  computed: {},
  methods: {
    fetchConfigs() {
      this.$http.get("/api/admin/fullsearch/domains/").then(response => {
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

          /*  domain.database.structures.forEach((db, structName) => {
            domain.configs.forEach((struc) => {
              if (struc.name === )
            })
          });*/

          this.configInfo.push({
            domainName: domainName,
            domainStem: domainStem,
            database: domain.database,
            structures: domain.configs
          });
        });
        if (!this.selectedID && this.configInfo && this.configInfo.length > 0) {
          this.selectedID = this.configInfo[0].domainName;
          this.$emit("selected", this.selectedID);
        }
        this.getData();
      });
    },

    getData() {
      this.gridData = this.configInfo.map(item => ({ ...item, selected: item.domainName === this.selectedID }));
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
    }
  }
};

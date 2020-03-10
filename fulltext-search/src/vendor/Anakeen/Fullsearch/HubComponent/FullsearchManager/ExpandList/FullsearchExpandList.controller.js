import Vue from "vue";
import { process } from "@progress/kendo-data-query";
import { Grid } from "@progress/kendo-vue-grid";

const componentInstance = {
  props: {
    field: String,
    dataItem: Object,
    format: String,
    className: String,
    columnIndex: Number,
    columnsCount: Number,
    rowType: String,
    level: Number,
    expanded: Boolean,
    editor: String
  },
  computed: {
    renderArrow() {
      var returnValue =
        this.columnIndex === undefined ||
        this.level === undefined ||
        this.columnIndex < this.level ||
        this.columnsCount === undefined ||
        this.rowType !== "groupHeader" ||
        this.dataItem[this.field] === undefined;
      return returnValue && this.dataItem[this.field];
    },
    renderCell() {
      return this.field !== undefined && this.rowType !== "groupHeader";
    },
    computedAggregates: function() {
      let renderedString = "";

      if (this.dataItem.field === "type") {
        renderedString = " (" + this.dataItem.items.length + ")";
      }

      return renderedString;
    }
  },
  template: `
    <td v-if="renderCell" :class="className">
      {{ getNestedValue(field, dataItem)}}
    </td>
    <td v-else-if="renderArrow" key="'g' + columnIndex" :class="'k-group-cell'" ></td>
    <td v-else-if="columnIndex <= level" key='g-colspan' 
        :colSpan="columnsCount - columnIndex">
      <p class="k-reset" @click="onClick">
        <a tabIndex="-1"
           href="#"
           :class="expanded ? 'k-i-collapse k-icon' : 'k-i-expand k-icon'"/>
        <b>{{dataItem[field]}}  </b>  {{computedAggregates}}
          <span class="search-button" v-if="(dataItem['field'] === 'domainName')"><button  @click="onSelect" class="btn btn-primary">Search</button></span>
      </p>
    </td>`,
  methods: {
    onClick(e) {
      this.$emit("click", e, this.dataItem, this.expanded);
    },

    onSelect(e) {
      e.preventDefault();
      e.stopPropagation();
      this.$emit("selected", this.dataItem.value);
    },
    getNestedValue(fieldName, dataItem) {
      const path = fieldName.split(".");
      let data = dataItem;
      path.forEach(p => {
        data = data ? data[p] : undefined;
      });

      return data;
    },
    getGroupValue(fieldName, dataItem) {
      const path = fieldName.split(".");
      let data = dataItem;
      path.forEach(p => {
        data = data ? data[p] : undefined;
      });

      return data;
    }
  }
};

// noinspection JSUnusedGlobalSymbols
export default {
  name: "ank-fullsearch-expand",
  components: {
    "kendo-grid": Grid
  },
  data() {
    return {
      cellTemplate: componentInstance,
      selectedField: "selected",
      domains: [],
      gridData: null,
      group: [{ field: "domainName" }, { field: "structure" }, { field: "type" }],
      configInfo: [],
      columns: [
        { field: "field", title: "Field" },
        { field: "weight", title: "Weight" }
      ]
    };
  },

  created() {},

  mounted() {
    this.fetchConfigs();
  },

  methods: {
    fetchConfigs() {
      this.$http.get("/api/admin/fullsearch/domains/").then(response => {
        this.domains = response.data.data.config;
        this.domains.forEach(domain => {
          const domainName = domain.name;
          const domainStem = domain.stem;
          domain.configs.forEach(config => {
            const structure = config.structure;
            config.fields.forEach(field => {
              this.configInfo.push({
                domainName: domainName,
                domainStem: domainStem,
                structure: structure,
                type: "field",
                field: field.field,
                weight: field.weight
              });
            });
            config.files.forEach(field => {
              this.configInfo.push({
                domainName: domainName,
                domainStem: domainStem,
                structure: structure,
                type: "file",
                field: field.field,
                weight: field.weight
              });
            });
          });
        });
        this.updateData();
      });
    },
    updateData() {
      this.gridData = process(this.configInfo, { group: this.group });
      this.gridData.data.forEach(p => {
        // Collapse 2 main groups
        Vue.set(p, "expanded", false);
        if (p.items) {
          p.items.forEach(struct => {
            Vue.set(struct, "expanded", false);
          });
        }
      });
    },

    onRowClick(event) {
      this.$emit("selected", event);
    },
    expandChange(event) {
      Vue.set(event.dataItem, event.target.$props.expandField, event.value);
    }
  }
};

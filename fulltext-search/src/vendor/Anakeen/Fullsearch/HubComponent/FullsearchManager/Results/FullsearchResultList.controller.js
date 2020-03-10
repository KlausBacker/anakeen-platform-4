import Vue from "vue";
import kendo from "@progress/kendo-ui/js/kendo.core";
import { Grid } from "@progress/kendo-vue-grid";

const componentInstance = Vue.component("template-component", {
  props: {
    dataItem: Object
  },
  template: `
      <td  @click="clickHandler">
          <div v-if="dataItem.properties" class="fullresult-element">
              <p><img alt="icon" :src="dataItem.properties.icon"/><b>{{dataItem.properties.title}}</b> </p>
              <p v-html="getHighValue()" class="highlight"></p>
          </div>

          <div v-if="dataItem.message" class="fullresult-message">
              <p v-if="dataItem.pattern">{{dataItem.message}} <em>{{dataItem.pattern}}</em></p>
              <p v-else><b>{{dataItem.message}}</b></p>
          </div>
      </td>
  `,
  methods: {
    clickHandler: function(e) {
      e.dataItem = this.dataItem;
      this.$emit("custom", e);
    },
    getHighValue() {
      let encode = kendo.htmlEncode(this.dataItem.highlights);
      encode = encode.replace(/\[\[\[/g, "<em>");
      encode = encode.replace(/]]]/g, "</em>");
      return encode;
    }
  }
});

export default {
  name: "ank-fullresult-list",
  components: {
    "kendo-grid": Grid
  },
  props: {
    dataItems: Array,
    pattern: String
  },
  computed: {
    gridItems() {
      if (this.dataItems && this.dataItems.length > 0) {
        return this.dataItems;
      }
      if (this.pattern) {
        return [{ message: "No find matching ", pattern: this.pattern }];
      }
      return [{ message: "Type words to send a search" }];
    }
  },
  data() {
    return {
      selectedField: "selected",
      selectedID: "",
      cellTemplate: componentInstance,
      columns: [{ field: "element", title: "Elements" }]
    };
  },

  created() {},
  mounted() {},
  methods: {
    onRowClick(event) {
      this.selectedID = event.dataItem.properties.initid;
      this.dataItems.map(item => {
        // Need to unselect before select new one
        if (item.selected === true) {
          Vue.set(item, this.selectedField, false);
        }
      });
      Vue.set(event.dataItem, this.selectedField, true);
      this.$emit("selected", this.selectedID);
    }
  }
};

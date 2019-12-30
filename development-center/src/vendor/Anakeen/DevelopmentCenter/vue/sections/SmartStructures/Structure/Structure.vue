<template>
  <ss-treelist
    ref="structList"
    :items="getItems(this.columnSizeTab)"
    :url="url"
    :get-values="getValues"
    :column-template="columnTemplate"
    :ss-name="ssName"
    :inline-filters="true"
    column-list-key="fields"
  />
</template>
<script>
import { Vue } from "vue-property-decorator";
import SsTreelist from "../../../components/SSTreeList/SSTreeList.vue";

Vue.use(SsTreelist.name, SsTreelist);

export default {
  components: { SsTreelist },
  props: ["ssName"],
  data() {
    return {
      columnSizeTab: window.localStorage.getItem("ss-list-column-size-conf-" + this.ssName)
        ? JSON.parse(window.localStorage.getItem("ss-list-column-size-conf-" + this.ssName))
        : [],
      items: [
        { name: "id", label: "Identification", hidden: false },
        { name: "structure", label: "Structure", hidden: true },
        { name: "type", label: "Type", hidden: false },
        { name: "labeltext", label: "Label", hidden: false },
        { name: "accessibility", label: "Access", hidden: false, width: "9rem" },
        { name: "ordered", label: "Order", hidden: true, width: "8rem" },
        { name: "isTitle", label: "is Title", hidden: true },
        { name: "isAbstract", label: "is Abstract", hidden: true },
        { name: "isNeeded", label: "is Needed", hidden: true },
        { name: "phpconstraint", label: "Constraint", hidden: false },
        { name: "computed", label: "Computed Method", hidden: false },
        { name: "autocomplete", label: "Auto Complete Method", hidden: false },
        { name: "link", label: "Link", hidden: true },
        { name: "optionValues", label: "Options", hidden: false },
        { name: "properties", label: "Properties", hidden: true },
        { name: "overrides", label: "Overrides", hidden: true },
        { name: "declaration", label: "Declaration", hidden: true }
      ],
      url: `/api/v2/devel/smart/structures/${this.ssName}/fields/`,
      getValues(response) {
        return response.fields;
      },
      getItems(tab) {
        Object.keys(this.items).forEach(item => {
          if (tab) {
            tab.forEach(it => {
              if (this.items[item].name === it.field && it.width) {
                this.items[item]["width"] = it.width + "px";
              } else if (!this.items[item]["width"]) {
                this.items[item]["width"] = "10rem";
              }
            });
          }
        });
        return this.items;
      },
      columnTemplate(colId) {
        return dataItem => {
          console.log(dataItem["id"], colId, dataItem["declaration"], dataItem[colId]);
          if (dataItem[colId] === null || dataItem[colId] === undefined) {
            return "";
          }
          if (dataItem[colId] && (colId === "optionValues" || colId === "properties")) {
            let str = "";
            Object.keys(dataItem[colId].toJSON()).forEach(item => {
              str += `<li><span><b>${item}</b></span> : <span>${dataItem[colId][item]}</span></li>`;
            });
            return str;
          }
          let className = "";
          let cellData = kendo.htmlEncode(dataItem[colId]);
          if (dataItem["declaration"] === "overrided" && dataItem["overrides"]) {
            Object.keys(dataItem["overrides"].toJSON()).forEach(item => {
              console.log(dataItem["id"], "over", item, colId);
              if (item === colId) {
                className = " overrided";
              }
            });
          }
          if (cellData && colId === "overrides") {
            let cellDataOver = [];
            let overrides = dataItem["overrides"].toJSON();
            Object.keys(overrides).forEach(item => {
              cellDataOver.push( `<span>${kendo.htmlEncode(item)}: (before : <i>${kendo.htmlEncode(overrides[item].before)}</i>) => (after: <i>${kendo.htmlEncode(overrides[item].after)})</i></span>`);
              console.log(dataItem["id"], "over", item, colId);

              cellData = cellDataOver.join("<hr/>");
            });

            cellData += "";
          }
          if (className) {
            return `<div class="${className}">${cellData}</div>`;
          }
          return cellData;
        };
      }
    };
  },
  watch: {
    ssName(newValue) {
      this.url = `/api/v2/devel/smart/structures/${newValue}/fields/`;
    }
  },
  mounted() {
    if (this.$refs.structList) {
      this.$refs.structList.$refs.ssTreelist.kendoWidget().bind("columnResize", e => {
        window.localStorage.setItem(
          "ss-list-column-size-conf-" + this.ssName,
          JSON.stringify(this.$refs.structList.onColumnResize(e))
        );
      });
    }
  }
};
</script>

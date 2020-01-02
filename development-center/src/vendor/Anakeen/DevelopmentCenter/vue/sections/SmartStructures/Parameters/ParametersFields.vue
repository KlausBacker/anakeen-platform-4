<template>
  <ss-treelist
    ref="paramList"
    :items="getItems(this.columnSizeTab)"
    :url="url"
    :get-values="getValues"
    :column-template="columnTemplate"
    :messages="messages"
    column-list-key="paramfields"
    :ss-name="ssName"
    :inline-filters="true"
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
      columnSizeTab: window.localStorage.getItem("param-list-column-size-conf-" + this.ssName)
        ? JSON.parse(window.localStorage.getItem("param-list-column-size-conf-" + this.ssName))
        : [],
      items: [
        { name: "id", label: "Identification", hidden: false },
        { name: "structure", label: "Structure", hidden: true },
        { name: "type", label: "Type", hidden: false, width: "8rem" },
        { name: "labeltext", label: "Label", hidden: false },
        {
          name: "accessibility",
          label: "Access",
          hidden: false,
          width: "9rem"
        },
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
      url: `/api/v2/devel/smart/structures/${this.ssName}/parameters/`,
      getValues(response) {
        return response.parameterFields;
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
          if (dataItem[colId] instanceof Object && colId === "value") {
            if (dataItem[colId].length > 1) {
              let str = "";
              Object.keys(dataItem[colId].toJSON()).forEach(item => {
                str += "<li>" + dataItem[colId][item] + "</li>";
              });
              return str;
            } else {
              return dataItem[colId][0] ? dataItem[colId][0] : "";
            }
          }
          return dataItem[colId];
        };
      },
      messages: "There are no parameters for this Smart Structure..."
    };
  },
  mounted() {
    if (this.$refs.paramList) {
      this.$refs.paramList.$refs.ssTreelist.kendoWidget().bind("columnResize", e => {
        window.localStorage.setItem(
          "param-list-column-size-conf-" + this.ssName,
          JSON.stringify(this.$refs.paramList.onColumnResize(e))
        );
      });
    }
  }
};
</script>

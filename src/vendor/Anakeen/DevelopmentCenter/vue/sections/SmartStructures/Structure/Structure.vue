<template>
    <ss-treelist :items="items" :url="url" :getValues="getValues" :columnTemplate="columnTemplate" :ssName="ssName"></ss-treelist>
</template>
<script>
  import Vue from "vue";
  import SsTreelist from "../../../components/SSTreeList/SSTreeList.vue";
  Vue.use(SsTreelist.name,SsTreelist);

  export default {
    components: {SsTreelist},
    props: ["ssName"],
    data() {
      return {
        items: [
          { name: "id", label: "Identification", hidden: false, width: "20rem"},
          { name: "structure", label: "Structure", hidden: false},
          { name: "type", label: "Type", hidden: false, width: "8rem"},
          { name: "labeltext", label: "Label", hidden: false},
          { name: "accessibility", label: "Access", hidden: false ,width: "9rem"},
          { name: "ordered", label: "Order", hidden: false ,width: "8rem"},
          { name: "isTitle", label: "is Title", hidden: true},
          { name: "isAbstract", label: "is Abstract", hidden: true},
          { name: "isNeeded", label: "is Needed", hidden: true},
          { name: "phpconstraint", label: "Constraint", hidden: false},
          { name: "phpfunc", label: "Computed Methods", hidden: false},
          { name: "phpfile", label: "Auto Complete Methods", hidden: true},
          { name: "link", label: "Link", hidden: false},
          { name: "optionValues", label: "Options", hidden: false},
          { name: "properties", label: "Properties", hidden: true},
          { name: "overrides", label: "Overrides", hidden: true},
          { name: "declaration", label: "Declaration", hidden: true},
        ],
        url: `/api/v2/devel/smart/structures/${this.ssName}/fields/`,
        getValues(response) {
          return response.fields;
        },
        columnTemplate(colId) {
          return dataItem => {
            if (dataItem[colId] === null || dataItem[colId] === undefined) {
              return "";
            }
            if (
              dataItem[colId] &&
              (colId === "optionValues" || colId === "properties")
            ) {
              let str = "";
              Object.keys(dataItem[colId].toJSON()).forEach(item => {
                str += `<li><span><b>${item}</b></span> : <span>${
                  dataItem[colId][item]
                  }</span></li>`;
              });
              return str;
            }
            let className = "";
            if (dataItem["declaration"] === "overrided" &&
              dataItem["overrides"]
            ) {
              Object.keys(dataItem["overrides"].toJSON()).forEach(item => {
                if (item === colId) {
                  className = " overrided";
                }
              });
            }
            if (className) {
              return `<div class="${className}">${dataItem[colId]}</div>`;
            }
            return dataItem[colId];
          };
        }
      }
    }
  }
</script>

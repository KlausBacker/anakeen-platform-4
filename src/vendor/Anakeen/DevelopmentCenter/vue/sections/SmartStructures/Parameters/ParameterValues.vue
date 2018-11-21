<template>
    <ss-treelist :items="items" :url="url" :getValues="getValues" :columnTemplate="columnTemplate" :messages="messages"></ss-treelist>
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
          { name: "id", label: "Identification", hidden: false},
          { name: "structure", label: "Structure", hidden: false},
          { name: "type", label: "Type", hidden: false},
          { name: "labeltext", label: "Label", hidden: false},
          { name: "accessibility", label: "Access", hidden: false},
          { name: "ordered", label: "Order", hidden: true},
          { name: "isTitle", label: "is Title", hidden: true},
          { name: "isAbstract", label: "is Abstract", hidden: true},
          { name: "isNeeded", label: "is Needed", hidden: true},
          { name: "phpconstraint", label: "Constraint", hidden: true},
          { name: "phpfunc", label: "Function", hidden: true},
          { name: "phpfile", label: "PHP File", hidden: true},
          { name: "link", label: "Link", hidden: true},
          { name: "optionValues", label: "Options", hidden: true},
          { name: "properties", label: "Properties", hidden: true},
          { name: "overrides", label: "Overrides", hidden: true},
          { name: "declaration", label: "Declaration", hidden: true},
        ],
        url: `/api/v2/devel/smart/structures/${this.ssName}/parameters/`,
        getValues(response) {
          return response.parameterFields;
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
      }
    }
  }
</script>

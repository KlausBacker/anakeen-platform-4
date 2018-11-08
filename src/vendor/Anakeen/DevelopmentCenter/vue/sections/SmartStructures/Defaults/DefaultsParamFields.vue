<template>
    <ss-treelist :items="items" :url="url" :getValues="getValues" :columnTemplate="columnTemplate"></ss-treelist>
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
          { name: "type", label: "Type", hidden: false},
          { name: "labeltext", label: "Label", hidden: false},
          { name: "config", label: "Configuration", hidden: false},
          { name: "value", label: "Value", hidden: false},
        ],
        url: `/api/v2/devel/smart/structures/${this.ssName}/defaults/`,
        getValues(response) {
          const items = response.defaultValues;
          const fields = Object.keys(items).map(item => {
            return {
              idVal: item,
              config: items[item].config,
              type: items[item].type,
              value: items[item].value
            };
          });
          fields.forEach(items2 => {
            Object.keys(response.parameterFields).forEach(items => {
              if (items2.type === "parameter") {
                if (items2.idVal === response.parameterFields[items].id) {
                  response.parameterFields[items].config = items2.config;
                  response.parameterFields[items].value = items2.value;
                }
              }
            });
          });
          return response.parameterFields;
        },
        columnTemplate(colId) {
          return dataItem => {
            if (dataItem[colId] === null || dataItem[colId] === undefined) {
              return "";
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
        }
      }
    }
  }
</script>

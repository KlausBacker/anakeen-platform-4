<template>
    <ss-treelist :items="items" :url="url" :getValues="getValues" :columnTemplate="columnTemplate"
                 :messages="messages"></ss-treelist>
</template>
<script>
  import Vue from "vue";
  import SsTreelist from "../../../components/SSTreeList/SSTreeList.vue";

  Vue.use(SsTreelist.name, SsTreelist);

  export default {
    components: {SsTreelist},
    props: ["ssName"],
    data() {
      return {
        items: [
          {name: "id", label: "Identification", hidden: false},
          {name: "type", label: "Type", hidden: false},
          {name: "labeltext", label: "Label", hidden: false},
          {name: "config", label: "Configuration", hidden: false},
          {name: "value", label: "Value", hidden: false},
        ],
        url: `/api/v2/devel/smart/structures/${this.ssName}/defaults/`,
        messages: "There are no default values for this Smart Structure..."
      }
    },
    methods: {
      recursiveData(items, str) {
        if (items instanceof Object) {
          Object.keys(items.toJSON()).forEach(item => {
            if (items[item] instanceof Object) {
              console.log(items[item]);
              this.recursiveData(items[item], str);
            } else {
              str += "<li>" + items[item] + "</li>";
            }
          });
        }
        return str;
      },
      columnTemplate(colId) {
        return dataItem => {
          if (dataItem[colId] === null || dataItem[colId] === undefined) {
            return "";
          }
          if (dataItem[colId] instanceof Object) {
            if (colId === "value" || colId === "config") {
              if (dataItem[colId].length > 1) {
                let str = "";
                return this.recursiveData(dataItem[colId], str);
              } else {
                return dataItem[colId][0] ? dataItem[colId][0] : "";
              }
            }
          }
          return dataItem[colId];
        };
      },
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
          Object.keys(response.fields).forEach(items => {
            if (items2.type === "field") {
              if (items2.idVal === response.fields[items].id) {
                response.fields[items].config = items2.config;
                response.fields[items].value = items2.value;
              }
            }
          });
        });
        return response.fields;
      }
    }
  }
</script>

<template>
    <ss-treelist ref="paramDefList" :items="getItems(this.columnSizeTab)" :url="url" :getValues="getValues" :columnTemplate="columnTemplate" :messages="messages" :inlineFilters="true"></ss-treelist>
</template>
<script>
  import { Vue } from "vue-property-decorator";
  import SsTreelist from "../../../components/SSTreeList/SSTreeList.vue";
  Vue.use(SsTreelist.name,SsTreelist);

  export default {
    components: {SsTreelist},
    props: ["ssName"],
    data() {
      return {
        columnSizeTab: window.localStorage.getItem(
          "param-list-values-column-size-conf-" + this.ssName
        ) ? JSON.parse(window.localStorage.getItem(
          "param-list-values-column-size-conf-" + this.ssName)) : [],
        items: [
          { name: "id", label: "Identification", hidden: false},
          { name: "type", label: "Type", hidden: false},
          { name: "labeltext", label: "Label", hidden: false},
          { name: "config", label: "Configuration", hidden: false, width: "15rem"},
          { name: "value", label: "Resulting Values", hidden: false, width: "15rem"},
        ],
        url: `/api/v2/devel/smart/structures/${this.ssName}/parameters/`,
        getValues(response) {
          const items = response.parameterValues;
          const fields = Object.keys(items).map(item => {
            return {
              idVal: item,
              config: items[item].config,
              value: items[item].value
            };
          });
          fields.forEach(items2 => {
            Object.keys(response.parameterFields).forEach(items => {
              if (items2.idVal === response.parameterFields[items].id) {
                response.parameterFields[items].config = items2.config;
                response.parameterFields[items].value = items2.value;
              }
            });
          });
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
        messages: "There are no default parameter values for this Smart Structure..."
      }
    },
    mounted() {
      if (this.$refs.paramDefList) {
        this.$refs.paramDefList.$refs.ssTreelist.kendoWidget().bind("columnResize", (e) => {
          window.localStorage.setItem(
            "param-list-values-column-size-conf-" + this.ssName,
            JSON.stringify(this.$refs.paramDefList.onColumnResize(e))
          );
        });
      }
    }
  }
</script>

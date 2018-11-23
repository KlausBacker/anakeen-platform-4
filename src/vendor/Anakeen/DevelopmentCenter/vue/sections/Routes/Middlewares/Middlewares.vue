<template>
    <ss-treelist ref="middlewaresList" :items="items" :url="url" :getValues="getValues" :columnTemplate="columnTemplate"
                 :ssName="''" :sort="sort"></ss-treelist>
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
        columnSizeTab: [],
        items: [
          {name: "name", label: "Name", hidden: false},
          {name: "method", label: "Method", hidden: false, width: "8rem"},
          {name: "pattern", label: "Pattern", hidden: false},
          {name: "description", label: "Description", hidden: false},
          {name: "priority", label: "Priority", hidden: false, width: "7rem"},
          {name: "rowLevel", label: "rowLevel", hidden: true}
        ],
        sort: [{ field: "rowLevel", dir: "asc" },{ field: "name", dir: "asc"}],
        url: `/api/v2/devel/routes/middlewares/`,
        getValues(response) {
          return response;
        },
        columnTemplate(colId) {
          return dataItem => {
            if (dataItem[colId]) {
              return dataItem[colId];
            } else {
              return '';
            }
          }
        }
      }
    }
  }
</script>
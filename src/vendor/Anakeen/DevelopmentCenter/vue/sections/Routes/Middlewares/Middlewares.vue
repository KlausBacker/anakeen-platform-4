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
    beforeRouteEnter(to, from, next) {
      next(function (vueInstance) {
        console.log(from);
        if (from.name === "routes" && to.query.filter) {
          vueInstance.url = `/api/v2/devel/routes/middlewares/applicable/${to.query.name}`;
        } else {
          vueInstance.url = `/api/v2/devel/routes/middlewares/`;
        }
      });
    },
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
        sort: [{field: "rowLevel", dir: "asc"}, {field: "name", dir: "asc"}],
        url: `/api/v2/devel/routes/middlewares/`,
        getValues(response) {
          return response;
        },
        columnTemplate(colId) {
          return dataItem => {
            if (dataItem[colId] === null || dataItem[colId] === undefined) {
              return "";
            }
            switch (colId) {
              case "pattern":
                const data = dataItem[colId].split(",");
                let str = "";
                if (data.length > 1) {
                  data.forEach(d => {
                    str += `<li><a data-role="develRouterLink" href="/devel/routes/routes/?filter=${d}">${d}</a></li>`;
                  });
                } else {
                  str = `<a data-role="develRouterLink" href="/devel/routes/routes/?filter=${dataItem[colId]}">${dataItem[colId]}</a>`;
                }
                return str;
              default:
                return dataItem[colId];
            }
          };
        },
      }
    }
  }
</script>
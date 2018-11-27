<template>
    <ss-treelist ref="routesList" :items="items" :url="url" :getValues="getValues" :columnTemplate="columnTemplate"
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
          {name: "override", label: "Overrided", hidden: false, width: "8rem"},
          {name: "rowLevel", label: "rowLevel", hidden: true}
        ],
        sort: [{field: "rowLevel", dir: "asc"}, {field: "name", dir: "asc"}],
        url: `/api/v2/devel/routes/all/`,
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
                let name;
                if (dataItem["rowLevel"] === 2) {
                  if (data.length > 1) {
                    name = `${dataItem["parentName"]}::${dataItem["name"]}`;
                    data.forEach(d => {
                      str += `<li><a data-role="develRouterLink" href="/devel/routes/middlewares/?filter=${d}&name=${name}">${d}</a></li>`;
                    });
                  } else {
                    name = `${dataItem["parentName"]}::${dataItem["name"]}`;
                    str = `<a data-role="develRouterLink" href="/devel/routes/middlewares/?filter=${dataItem[colId]}&name=${name}">${dataItem[colId]}</a>`;
                  }
                } else {
                  if (data.length > 1) {
                    data.forEach(d => {
                      str += `<li><a data-role="develRouterLink" href="/devel/routes/middlewares/?filter=${d}&name=${dataItem["name"]}">${d}</a></li>`;
                    });
                  } else {
                    str = `<a data-role="develRouterLink" href="/devel/routes/middlewares/?filter=${dataItem[colId]}&name=${dataItem["name"]}">${dataItem[colId]}</a>`;
                  }
                }
                return str;
              default:
                return dataItem[colId];
            }
          };
        }
      };
    }
  }
</script>
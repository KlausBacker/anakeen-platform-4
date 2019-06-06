<template>
    <ss-treelist ref="routesList" :items="items" :url="url" :getValues="getValues" :columnTemplate="columnTemplate"
                 :ssName="''" :sort="sort" :inlineFilters="true" @filter="onFilter"></ss-treelist>
</template>
<script>
  import Vue from "vue";
  import SsTreelist from "../../../components/SSTreeList/SSTreeList.vue";

  Vue.use(SsTreelist.name, SsTreelist);

  export default {
    components: {SsTreelist},
    props: ["ssName", "routeFilter"],
    beforeRouteEnter(to, from, next) {
      next(function (vueInstance) {
        if (to.name === "routes" && to.query.name) {
          vueInstance.url = `/api/v2/devel/routes/all/${to.query.name}`;
          vueInstance.$nextTick(() => {
            vueInstance.$refs.routesList.$refs.ssTreelist.$(".pattern-filter", vueInstance.$refs.routesList.$refs.ssTreelist.$el)[0].value = to.query.pattern;
          });
        } else {
          vueInstance.url = `/api/v2/devel/routes/all/`;
        }
      });
    },
    beforeRouteUpdate(to, from, next) {
      if (!to.query.name) {
          this.url = `/api/v2/devel/routes/all/`;
      } else {
          this.url = `/api/v2/devel/routes/all/${to.query.name}`;
      }
      next();
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
                const name = `${dataItem["parentName"]}::${dataItem["name"]}`;
                if (dataItem["rowLevel"] === 2) {
                  if (data.length > 1) {
                    data.forEach(d => {
                      str += `<li><a data-role="develRouterLink" href="/devel/routes/middlewares/?pattern=${d}&name=${name}">${d}</a></li>`;
                    });
                  } else {
                    str = `<a data-role="develRouterLink" href="/devel/routes/middlewares/?pattern=${dataItem[colId]}&name=${name}">${dataItem[colId]}</a>`;
                  }
                } else {
                  if (data.length > 1) {
                    data.forEach(d => {
                      str += `<li><a data-role="develRouterLink" href="/devel/routes/middlewares/?pattern=${d}&name=${name}">${d}</a></li>`;
                    });
                  } else {
                    str = `<a data-role="develRouterLink" href="/devel/routes/middlewares/?pattern=${dataItem[colId]}&name=${name}">${dataItem[colId]}</a>`;
                  }
                }
                return str;
              default:
                return dataItem[colId];
            }
          };
        }
      };
    },
    methods: {
      onFilter(event) {
        const filter = event.filter;
        if (filter) {
          const currentFilter = event.sender.dataSource.filter();
          let nextFilter = {};
          if (currentFilter) {
            nextFilter = currentFilter.filters.reduce((acc, curr) => {
              acc[curr.field] = curr.value;
              return acc;
            }, {});
          }
          this.$emit(
            "filter",
            Object.assign({}, nextFilter, { [filter.field]: filter.value })
          );
        } else {
          const currentFilter = event.sender.dataSource.filter();
          let nextFilter = {};
          if (currentFilter) {
            nextFilter = currentFilter.filters.reduce((acc, curr) => {
              if (curr.field !== event.field) {
                acc[curr.field] = curr.value;
              }
              return acc;
            }, {});
          }
          this.$emit("filter", Object.assign({}, nextFilter));
        }
      }
    }
  }
</script>
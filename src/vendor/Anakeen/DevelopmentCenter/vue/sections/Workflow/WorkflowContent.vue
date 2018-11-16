<template>
    <div ref="wflSplitter" class="wfl-splitter">
        <div class="wfl-list-grid">
            <div>
                <kendo-toolbar class="wfl-list-grid-toolbar">
                    <kendo-toolbar-item type="button" icon="refresh" @click="refreshWflList"></kendo-toolbar-item>
                </kendo-toolbar>
            </div>
            <kendo-datasource ref="wflListGrid"
                              :transport-read="getWflList"
                              :schema-data="parseWflData">
            </kendo-datasource>
            <kendo-grid ref="wflListContent" class="wfl-list-grid-content"
                        :data-source-ref="'wflListGrid'"
                        :sortable="true">
                <kendo-grid-column :field="'baTitle'" :title="'<b>Name</b>'"></kendo-grid-column>
                <kendo-grid-column :command="{text: 'Consult', click: actionClick}" :title="' '" :width="'10rem'"></kendo-grid-column>
            </kendo-grid>
        </div>
        <div class="wfl-tabs">
            <router-tabs v-if="contentVisible" class="wfl-section" :items="items"></router-tabs>
        </div>
    </div>
</template>

<script>
  export default {
    name: "WflContent",
    props: ["wflIdentifier"],
    data() {
      return {
        contentVisible: false,
        items: [
          {
            name: "Wfl::infos",
            label: "Informations",
            props: true
          },
          {
            name: "Wfl::steps",
            label: "Steps",
            props: true
          },
          {
            name: "Wfl::transitions",
            label: "Transitions",
            props: true
          },
          {
            name: "Wfl::permissions",
            label: "Permissions",
            props: true
          }
        ]
      }
    },
    mounted() {
      this.$(this.$refs.wflSplitter).kendoSplitter({
        orientation: "horizontal",
        panes: [
          {
            scrollable: false,
            collapsible: true,
            resizable: false,
            size: "20%"
          },
          {
            scrollable: false,
            collapsible: false,
            resizable: false,
            size: "80%"
          }
        ]
      });
    },
    methods: {
      refreshWflList() {
        this.$refs.wflListContent.kendoWidget().dataSource.filter({});
        this.$refs.wflListContent.kendoWidget().dataSource.read();
      },
      getWflList(options) {
        this.$http
          .get(`/api/v2/devel/workflow/structures/${this.$store.getters.vendorCategory}/`, {
            params: options.data,
            paramsSerializer: kendo.jQuery.param
          })
          .then(response => {
            options.success(response);
          })
          .catch(response => {
            options.error(response);
          });
      },
      parseWflData(response) {
        let result = [];
        if (response && response.data && response.data.data) {
          response.data.data.forEach(item => {
            if (item.name === this.$route.params.ssName) {
              result.push(item.wfl);
            }
          });
          return result;
        }
        return [];
      },
      actionClick(e) {
        e.preventDefault();
        const dataItem = this.$(e.currentTarget).closest("tr")[0].firstElementChild.textContent;
        this.contentVisible = true;
        this.$router.push({
          name: "Wfl::infos",
          params: {
            wflIdentifier: dataItem
          }
        });
      }
    }
  }
</script>

<style lang="scss" scoped>
    .wfl-section {
        display: flex;
        flex-direction: column;
        height: 100%;

        .wfl-content {
            display: flex;
            flex: 1;
            padding: 1rem;
            border: 1px solid rgba(33, 37, 41, .125);
            border-radius: .25rem;
        }
    }

    .wfl-splitter {
        width: 100%;
        height: 100%;
    }

    .wfl-list-grid-content {
        height: 95%;
    }

    .wfl-list-grid-toolbar {
        height: 5%;
    }
</style>
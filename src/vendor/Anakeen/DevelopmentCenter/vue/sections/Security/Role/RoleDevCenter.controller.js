import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSEGrid";
import Splitter from "@anakeen/internal-components/lib/Splitter.js";
import ElementView from "../../SmartElements/ElementView/ElementView.vue";

export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-splitter": Splitter,
    "element-view": ElementView
  },
  props: ["role"],
  watch: {
    role(newValue) {
      this.$refs.rolesSplitter.disableEmptyContent();
      this.initFilters(window.location.search);
      this.selectedRole = newValue;
    }
  },
  data() {
    return {
      selectedRole: this.role,
      panes: [
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size: "50%"
        },
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size: "50%"
        }
      ]
    };
  },
  mounted() {
    if (this.selectedRole) {
      this.$refs.rolesSplitter.disableEmptyContent();
    }
    this.initFilters(window.location.search);
  },
  devCenterRefreshData() {
    if (this.$refs.roleContent && this.$refs.roleContent.dataSource) {
      this.$refs.roleContent.dataSource.read();
    }
  },
  methods: {
    initFilters(searchUrl) {
      const computeFilters = () => {
        const re = /(name|title|role_login)=([^&]+)/g;
        let match;
        const filters = [];
        while ((match = re.exec(searchUrl))) {
          if (match && match.length >= 3) {
            const field = match[1];
            const value = decodeURIComponent(match[2]);
            filters.push({
              field,
              operator: "contains",
              value
            });
          }
        }
        if (filters.length) {
          this.$refs.roleContent.dataSource.filter(filters);
        }
      };
      if (this.$refs.roleContent.kendoGrid) {
        computeFilters();
      } else {
        this.$refs.roleContent.$once("grid-ready", () => {
          computeFilters();
        });
      }
    },
    onGridDataBound() {
      this.getRoute().then(route => {
        this.$emit("navigate", route);
      });
    },
    getFilter() {
      if (this.$refs.roleContent && this.$refs.roleContent.kendoGrid) {
        const currentFilter = this.$refs.roleContent.kendoGrid.dataSource.filter();
        if (currentFilter) {
          const filters = currentFilter.filters;
          return filters.reduce((acc, curr) => {
            acc[curr.field] = curr.value;
            return acc;
          }, {});
        }
      }
      return {};
    },
    getRoute() {
      const filter = this.getFilter();
      const filterUrl = Object.keys(filter).length ? `?${$.param(filter)}` : "";
      if (this.selectRole) {
        return Promise.resolve([
          {
            url: this.selectedRole + filterUrl,
            name: this.selectedRole,
            label: this.selectedRole
          }
        ]);
      }
      return Promise.resolve([{ url: filterUrl }]);
    },
    selectRole(e) {
      let profileId;
      switch (e.data.type) {
        case "consultRole":
          e.preventDefault();
          profileId = e.data.row.name || e.data.row.id.toString();
          this.$refs.rolesSplitter.disableEmptyContent();
          this.selectedRole = profileId;
          this.getRoute().then(route => {
            this.$emit("navigate", route);
            this.getSelected(e.data.row.id, "id");
          });
          break;
      }
    },
    onGridError(event) {
      this.$store.dispatch("displayError", {
        title: "Error",
        textContent: event.data.message
      });
    },
    getSelected(e) {
      if (e !== "") {
        if (this.$refs.roleContent.kendoGrid) {
          this.$("tr[role=row]", this.$el).removeClass(
            "control-view-is-opened"
          );
          this.$(
            "tr[data-uid=" +
              this.$refs.roleContent.kendoGrid.dataSource
                .view()
                .find(d => d.rowData.id === e).uid +
              "]",
            this.$el
          ).addClass("control-view-is-opened");
        }
      }
    }
  }
};

import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import Splitter from "@anakeen/internal-components/lib/Splitter.js";
import ElementView from "../../SmartElements/ElementView/ElementView.vue";

export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-splitter": Splitter,
    "element-view": ElementView
  },
  props: ["ssName", "mask"],
  watch: {
    mask(newValue) {
      this.$refs.masksSplitter.disableEmptyContent();
      this.initFilters(window.location.search);
      this.selectedMask = newValue;
    }
  },
  mounted() {
    if (this.selectedMask) {
      this.$refs.masksSplitter.disableEmptyContent();
    }
    this.initFilters(window.location.search);
  },
  data() {
    return {
      selectedMask: this.mask,
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
  devCenterRefreshData() {
    if (this.$refs.masksGrid && this.$refs.masksGrid.dataSource) {
      this.$refs.masksGrid.dataSource.read();
    }
  },
  methods: {
    initFilters(searchUrl) {
      const computeFilters = () => {
        const re = /(name|title)=([^&]+)/g;
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
          this.$refs.masksGrid.dataSource.filter(filters);
        }
      };
      if (this.$refs.masksGrid.kendoGrid) {
        computeFilters();
      } else {
        this.$refs.masksGrid.$once("grid-ready", () => {
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
      if (this.$refs.masksGrid && this.$refs.masksGrid.kendoGrid) {
        const currentFilter = this.$refs.masksGrid.kendoGrid.dataSource.filter();
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
    getSelected(e, col) {
      if (e !== "") {
        if (this.$refs.masksGrid.kendoGrid) {
          if (col === "id") {
            this.$("[role=row]", this.$el).removeClass("control-view-is-opened");
            this.$(
              "[data-uid=" + this.$refs.masksGrid.kendoGrid.dataSource.view().find(d => d.rowData.id === e).uid + "]",
              this.$el
            ).addClass("control-view-is-opened");
          }
        } else if (col === "name") {
          this.$("[role=row]", this.$el).removeClass("control-view-is-opened");
          this.$(
            "[data-uid=" + this.$refs.masksGrid.kendoGrid.dataSource.view().find(d => d.rowData.name === e).uid + "]",
            this.$el
          ).addClass("control-view-is-opened");
        }
      }
    },
    getRoute() {
      const filter = this.getFilter();
      const filterUrl = Object.keys(filter).length ? `?${$.param(filter)}` : "";
      if (this.selectedMask) {
        return Promise.resolve([
          {
            url: this.selectedMask + filterUrl,
            name: this.selectedMask,
            label: this.selectedMask
          }
        ]);
      }
      return Promise.resolve([{ url: filterUrl }]);
    },
    actionClick(event) {
      event.preventDefault();
      switch (event.data.type) {
        case "consult": {
          this.$refs.masksSplitter.disableEmptyContent();
          this.selectedMask = event.data.row.name || event.data.row.id;
          this.getRoute().then(route => {
            this.$emit("navigate", route);
            this.getSelected(event.data.row.id, "id");
          });
          break;
        }
      }
    }
  }
};

import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import { Component, Prop, Vue, Watch } from "vue-property-decorator";

@Component({
  components: {
    "ank-se-grid": AnkSEGrid
  }
})
export default class Welcome extends Vue {
  @Prop({ default: () => [], type: Array }) public creation!: object[];
  @Prop({ default: () => [], type: Array }) public gridCollections!: object[];

  public mounted() {
    this.$nextTick(() => {
      $(window).trigger("resize");
    });
  }

  public refresh() {
    this.refreshGrids();
  }

  protected onCreateClick(createInfo, event) {
    this.$emit("tabWelcomeCreate", createInfo);
  }

  protected onActionClick(event) {
    switch (event.data.type) {
      case "consult":
        event.preventDefault();
        this.$emit("tabWelcomeGridConsult", event.data.row);
    }
  }

  private refreshGrids() {
    if (Array.isArray(this.$refs.grids)) {
      this.$refs.grids.forEach(grid => {
        grid._loadGridContent();
      });
    } else if (this.$refs.grids) {
      // @ts-ignore
      this.$refs.grids._loadGridContent();
    }
  }

  private reloadGrid(index) {
    if (Array.isArray(this.$refs.grids)) {
      // @ts-ignore
      this.$refs.grids[index]._loadGridContent();
    } else if (index === 0) {
      // @ts-ignore
      this.$refs.grids.dataSource._loadGridContent();
    }
  }
}

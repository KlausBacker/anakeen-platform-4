import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSEGrid";
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
        grid.dataSource.read();
      });
    } else {
      // @ts-ignore
      this.$refs.grids.dataSource.read();
    }
  }

  private reloadGrid(index) {
    if (Array.isArray(this.$refs.grids)) {
      // @ts-ignore
      this.$refs.grids[index].dataSource.read();
    } else if (index === 0) {
      // @ts-ignore
      this.$refs.grids.dataSource.read();
    }
  }
}

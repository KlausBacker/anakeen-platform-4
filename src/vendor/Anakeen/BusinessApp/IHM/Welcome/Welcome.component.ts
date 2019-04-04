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
}

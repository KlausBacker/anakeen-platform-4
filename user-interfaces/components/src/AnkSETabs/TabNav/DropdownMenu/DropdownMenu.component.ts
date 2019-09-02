import { Component, Prop, Vue } from "vue-property-decorator";

@Component
export default class DropdownMenu extends Vue {
  @Prop({ default: () => [], type: Array }) public items!: object[];
  public showMenu: boolean = false;

  public toggleShow() {
    this.showMenu = !this.showMenu;
  }

  protected itemClicked(item) {
    this.toggleShow();
    this.$emit("dropdownMenuSelected", item);
  }
}

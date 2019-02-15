import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.treelist";
import "@progress/kendo-ui/js/kendo.window";
import Vue from "vue";
import Component from "vue-class-component";

declare var $;

// noinspection JSUnusedGlobalSymbols
@Component({})
export default class AuthenticationTokensController extends Vue {
  protected static initTokenGrid(divDom) {
    $(divDom).kendoGrid({
      columns: [
        {
          field: "label",
          title: "Description",
          width: 240
        },
        {
          field: "token",
          title: "Token"
        },
        {
          field: "login",
          title: "User"
        },
        {
          field: "date",
          title: "Expiration"
        }
      ],
      height: 550,
      pageable: {
        buttonCount: 5,
        pageSizes: true,
        refresh: true
      },
      sortable: true
    });
  }

  public mounted() {
    console.log("Hello", this.$refs.tokenGrid);
    AuthenticationTokensController.initTokenGrid(this.$refs.tokenGrid);
  }
}

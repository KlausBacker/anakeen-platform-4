import { LayoutInstaller } from "@progress/kendo-layout-vue-wrapper";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.splitter";
import "@progress/kendo-ui/js/kendo.treelist";
import "@progress/kendo-ui/js/kendo.window";
import { Component, Vue } from "vue-property-decorator";
import AuthenticationTokenInfo from "./AuthenticationTokenInfo.vue";
import IAuthenticationToken from "./IAuthenticationToken";

declare var $;

Vue.use(LayoutInstaller);

// noinspection JSUnusedGlobalSymbols
@Component({
  components: {
    "ank-token-info": AuthenticationTokenInfo
  },
  name: "ank-authentication-tokens"
})
export default class AuthenticationTokensController extends Vue {
  public tokenInfo: IAuthenticationToken = {
    token: "Hello"
  };

  public mounted() {
    console.log("Hello", this.$refs.tokenGrid);
    this.initTokenGrid(this.$refs.tokenGrid);
  }
  protected initTokenGrid(divDom) {
    $(divDom).kendoGrid({
      columns: [
        {
          attributes: {
            class: "cell--description"
          },
          field: "description",
          title: "Description"
        },
        {
          attributes: {
            class: "cell--token"
          },
          field: "token",
          title: "Token",
          width: "30rem"
        },
        {
          attributes: {
            class: "cell--user"
          },
          field: "user",
          title: "User"
        },
        {
          attributes: {
            class: "cell--expire"
          },
          field: "expire",
          template:
            '#= kendo.toString(expire,"D") # #= kendo.toString(expire,"HH:mm:ss") #',
          title: "Expiration",
          type: "date"
        },
        {
          command: {
            click: e => {
              const widget = $(e.delegateTarget).data("kendo-grid");
              const dataItem = widget.dataItem(
                $(e.currentTarget).closest("tr")
              );
              console.log("Click", dataItem);
              this.tokenInfo.token = dataItem.token;
              this.tokenInfo.user = dataItem.user;
              this.tokenInfo.expirationDate = new Date(dataItem.expire);
            },
            text: "View Details",
            title: " ",
            width: "180px"
          }
        }
      ],

      dataSource: {
        schema: {
          data: response => {
            return response.data;
          },
          model: {
            fields: {
              description: {
                type: "string"
              },
              expire: {
                type: "date"
              }
            },
            id: "token"
          }
        },
        transport: {
          read: {
            url: `/api/v2/admin/tokens/`
          }
        }
      },
      filterable: {
        extra: false,
        operators: {
          string: {
            contains: "Contains...",
            startswith: "Starts with..."
          }
        }
      },
      pageable: {
        buttonCount: 5,
        pageSizes: true,
        refresh: true
      },
      sortable: true
    });
  }
}

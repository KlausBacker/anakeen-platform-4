import AnkSplitter from "@anakeen/internal-components/lib/Splitter";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.filtercell";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.treelist";
import "@progress/kendo-ui/js/kendo.window";
import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import AnkTokenInfo from "./AuthenticationTokenInfo.vue";
import { IAuthenticationToken } from "./IAuthenticationToken";
import IsoDates from "./IsoDates";

declare var kendo;

// noinspection JSUnusedGlobalSymbols
@Component({
  components: {
    "ank-splitter": AnkSplitter,
    "ank-token-info": AnkTokenInfo
  },
  name: "ank-authentication-tokens"
})
export default class AuthenticationTokensController extends Vue {
  // noinspection JSMethodCanBeStatic
  public get panes() {
    return [
      {
        collapsible: false,
        resizable: true,
        scrollable: false
      },
      {
        collapsible: false,
        max: "500px",
        min: "250px",
        resizable: true,
        scrollable: false,
        size: "500px"
      }
    ];
  }

  public get viewToken() {
    return this.tokenInfo.token !== "";
  }
  public $refs!: {
    [key: string]: any;
  };
  public tokenInfo: IAuthenticationToken = {
    token: ""
  };
  public showExpire: boolean = false;

  @Prop({ default: "", type: String })
  public value!: string;
  protected kTokenGrid: any;

  @Watch("value")
  public onSelectedTokenChanged(newValue, oldValue) {
    if (newValue !== oldValue) {
      // TODO Select the token info matching newValue token id
    }
  }

  public mounted() {
    this.initTokenGrid(this.$refs.tokenGrid);
  }

  public updated() {
    if (!$(this.$refs.tokenGrid).data("kendoGrid")) {
      this.initTokenGrid(this.$refs.tokenGrid);
    }
  }

  public flipFiltering() {
    this.showExpire = !this.showExpire;
    this.refreshList();
  }

  public displayCreateForm() {
    Vue.component("ank-token-info", resolve => {
      import("./AuthenticationTokenInfo.vue").then(AuthenticationTokenInfo => {
        resolve(AuthenticationTokenInfo.default);
      });
    });
    this.$refs.splitter.disableEmptyContent();
    const tomorrow = new Date();

    tomorrow.setDate(tomorrow.getDate() + 1);
    this.tokenInfo = {
      description: "",
      expendable: true,
      expirationDate: tomorrow,
      routes: [
        {
          method: "GET",
          pattern: "/"
        }
      ],
      token: "new",
      user: ""
    };
  }

  public refreshList() {
    this.tokenInfo = {
      token: ""
    };
    $(this.$refs.tokenGrid)
      .data("kendoGrid")
      .dataSource.read();
  }

  protected selectTokenRow(tokenId) {
    const $viewButtons = $(this.$el).find(
      "tr[data-token=" + tokenId + "] .k-button.k-grid-Info"
    );
    $($viewButtons.get(0)).trigger("click");
  }
  /**
   * add token in tr tag to easily select tr
   * @param grid
   */
  protected addRowClassName(grid) {
    const items = grid.items();

    const nowIsTime = IsoDates.getIsoData(new Date());
    items.each(function addTypeClass(this: any) {
      const dataItem = grid.dataItem(this);
      if (dataItem.token) {
        $(this).attr("data-token", dataItem.token);
      }
      if (dataItem.expire < nowIsTime) {
        $(this).addClass("token--expired");
      }
    });
  }

  protected initTokenGrid(divDom) {
    $(divDom)
      .kendoGrid({
        columns: [
          {
            attributes: {
              class: "cell--description"
            },
            field: "description",
            filterable: {
              cell: {
                delay: 1,
                operator: "contains",
                showOperators: false,
                suggestionOperator: "contains"
              }
            },
            title: "Description"
          },
          {
            attributes: {
              class: "cell--token"
            },
            field: "token",
            filterable: {
              cell: {
                delay: 1,
                operator: "contains",
                showOperators: false,
                suggestionOperator: "contains"
              }
            },
            title: "Token",
            width: "30rem"
          },
          {
            attributes: {
              class: "cell--user"
            },
            field: "user",
            filterable: {
              cell: {
                delay: 1,
                operator: "contains",
                showOperators: false,
                suggestionOperator: "contains"
              }
            },
            title: "User"
          },
          {
            attributes: {
              class: "cell--expire"
            },
            field: "expire",
            filterable: {
              cell: {
                delay: 9999,
                operator: "contains",
                showOperators: false,
                suggestionOperator: "contains"
              }
            },
            template:
              '# if (expire != "infinity") { #  #= kendo.toString(new Date(expire),"yyyy-MM-dd HH:mm:ss") # # } else { # Never # } #',
            title: "Expire at",
            width: "13rem"
          },
          {
            command: {
              click: e => {
                e.preventDefault();
                this.$refs.splitter.disableEmptyContent();
                Vue.component("ank-token-info", resolve => {
                  import("./AuthenticationTokenInfo.vue").then(
                    AuthenticationTokenInfo => {
                      resolve(AuthenticationTokenInfo.default);
                    }
                  );
                });
                const widget = kendo
                  .jQuery(e.delegateTarget)
                  .data("kendo-grid");
                const $tr = kendo.jQuery(e.currentTarget).closest("tr");
                const dataItem = widget.dataItem($tr).toJSON(); // Need to use JSON to has the raw data
                this.tokenInfo = {
                  author: dataItem.author,
                  creationDate: new Date(dataItem.cdate),
                  description: dataItem.description,
                  expendable: dataItem.expendable,
                  expirationDate:
                    dataItem.expire === "infinity"
                      ? null
                      : new Date(dataItem.expire),
                  routes: [],
                  token: dataItem.token,
                  user: dataItem.user
                };
                this.$emit("input", dataItem.token.toString());
                dataItem.routes.forEach(route => {
                  route.methods.forEach(method => {
                    this.tokenInfo.routes.push({
                      method,
                      pattern: route.pattern
                    });
                  });
                });
                $tr
                  .closest("tbody")
                  .find("tr")
                  .removeClass("token--selected");
                $tr.addClass("token--selected");
              },
              text: "Display"
            },
            width: "10rem"
          }
        ],

        dataBound: e => {
          const grid = e.sender;
          this.addRowClassName(grid);
          if (this.value) {
            this.selectTokenRow(this.value);
          }
        },
        dataSource: {
          filter: this.value
            ? {
                field: "token",
                operator: "contains",
                value: this.value
              }
            : {},

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
                  type: "string"
                }
              },
              id: "token"
            }
          },
          transport: {
            read: {
              data: () => {
                return {
                  showExpired: this.showExpire
                };
              },
              url: `/api/v2/admin/tokens/`
            }
          }
        },

        filterable: {
          extra: false,
          operators: {
            string: {
              contains: "Contains"
            }
          }

          // mode: "row"
        },
        pageable: {
          alwaysVisible: true,
          info: false,
          messages: {
            display: "Showing {0}-{1} from {2} data items"
          },
          pageSizes: false,
          refresh: true
        },
        sortable: true
      })
      .data("kendoGrid");
  }
}

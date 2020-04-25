import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import { LayoutInstaller } from "@progress/kendo-layout-vue-wrapper";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.datetimepicker";
import "@progress/kendo-ui/js/kendo.window";
import {
  IAuthenticationToken,
  IAuthenticationTokenDescription,
  IAuthenticationTokenRoute
} from "./IAuthenticationToken";
import IsoDates from "./IsoDates";

import { Component, Prop, Vue, Watch } from "vue-property-decorator";

// declare var $;
// declare var kendo;

Vue.use(LayoutInstaller);
Vue.use(ButtonsInstaller);
// noinspection JSUnusedGlobalSymbols
@Component({
  name: "ank-token-info"
})
export default class AuthenticationTokenInfoController extends Vue {
  public get creationDateFormatted() {
    if (this.info.creationDate) {
      return kendo.toString(this.info.creationDate, "yyyy, dd MMM HH:mm:ss");
    }
    return "-";
  }

  public get neverExpire() {
    return this.tokenValues.expirationDate === null;
  }
  public get fullInfo(): boolean {
    return (
      this.info.description !== "" &&
      this.info.user !== "" &&
      this.info.routes.length > 0 &&
      this.info.routes[0].pattern !== ""
    );
  }
  public get isExpired() {
    const now = new Date();
    if (this.tokenValues.expirationDate) {
      return this.tokenValues.expirationDate < now;
    }
    return false;
  }

  public get isInfo() {
    return this.info.token && this.info.token !== "new";
  }

  public get isNew() {
    return this.info.token && this.info.token === "new";
  }

  @Prop() public info: IAuthenticationToken;

  public expendable: "multiple" | "unique" = "multiple";

  public tokenValues: IAuthenticationToken = {};
  protected prevDate: Date;

  @Watch("info")
  public onConfigPropChanged(val: IAuthenticationToken) {
    this.expendable = val.expendable === true ? "unique" : "multiple";
    this.tokenValues = val;
  }

  @Watch("expendable")
  public onExpendChanged(val: string) {
    this.tokenValues.expendable = val === "unique";
  }

  public onInfinity() {
    if (this.tokenValues.expirationDate === null) {
      this.tokenValues.expirationDate = this.prevDate;
    } else {
      this.prevDate = this.tokenValues.expirationDate;
      this.tokenValues.expirationDate = null;
    }
  }
  public onAddRouteRow() {
    const line: IAuthenticationTokenRoute = {
      method: "GET",
      pattern: "/"
    };
    this.tokenValues.routes.push(line);
  }
  public onCreate() {
    const tokenData: IAuthenticationTokenDescription = {
      description: this.tokenValues.description,
      expendable: this.tokenValues.expendable,
      expirationDate:
        this.tokenValues.expirationDate === null
          ? "infinity"
          : IsoDates.getIsoData(this.tokenValues.expirationDate),
      routes: this.tokenValues.routes.filter(route => route.pattern !== ""),
      user: this.tokenValues.user
    };

    this.$http.post("/api/v2/admin/tokens/", tokenData).then(response => {
      const data = response.data.data;
      this.$emit("token-deleted", data);
    });
  }
  // noinspection JSMethodCanBeStatic
  public onCloseConfirm(e) {
    e.sender.element
      .closest("[data-role=window]")
      .data("kendoWindow")
      .close();
  }
  public onConfirmDelete() {
    $(this.$refs.confirmDelete)
      .kendoWindow({
        actions: ["Close"],
        modal: true,
        title: `${this.$t("AdminCenterAuthentication token.Confirm Token Deletion")}`,
        visible: false
      })
      .data("kendoWindow")
      .center()
      .open();
  }
  public onDelete(e) {
    e.sender.element
      .closest("[data-role=window]")
      .data("kendoWindow")
      .close();
    this.$http
      .delete("/api/v2/admin/tokens/" + this.info.token)
      .then(response => {
        const data = response.data.data;
        $(this.$refs.infoDelete)
          .kendoWindow({
            actions: ["Close"],
            close: () => {
              this.$emit("token-deleted", data);
            },
            modal: true,
            title: `${this.$t("AdminCenterAuthentication token.Token Deleted")}`,
            visible: false
          })
          .data("kendoWindow")
          .center()
          .open();
      });
  }

  public mounted() {
    this.initForm();
    // this.tokenValues=this.info;
    this.onConfigPropChanged(this.info);
  }

  public updated() {
    this.initForm();
  }

  protected initForm() {
    const $expireDate = $(this.$refs.expireDate);

    $expireDate.kendoDateTimePicker({
      change: (e: any) => {
        this.tokenValues.expirationDate = e.sender.value();
      },
      value: this.info.expirationDate
    });

    $(this.$refs.form)
      .find("select")
      .each((k, item) => {
        const $item = $(item);
        const index = $item.data("index");
        $item.kendoDropDownList({
          change: (e: any) => {
            this.tokenValues.routes[index].method = e.sender.value();
          }
        });
      });
    // .kendoDropDownList({});
  }
}

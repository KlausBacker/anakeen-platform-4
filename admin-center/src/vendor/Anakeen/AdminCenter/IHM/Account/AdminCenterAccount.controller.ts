/* eslint-disable @typescript-eslint/explicit-function-return-type,@typescript-eslint/no-explicit-any */
import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { DropdownsInstaller } from "@progress/kendo-dropdowns-vue-wrapper";
import { GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import { InputsInstaller } from "@progress/kendo-inputs-vue-wrapper";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.toolbar";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import { Component, Vue, Watch, Mixins } from "vue-property-decorator";

Vue.use(ButtonsInstaller);
Vue.use(GridInstaller);
Vue.use(DropdownsInstaller);
Vue.use(DataSourceInstaller);
Vue.use(InputsInstaller);
declare const $;
declare const kendo;

@Component({
  components: {
    AnkSmartElement: () => AnkSmartElement,
    "ank-split-panes": AnkPaneSplitter
  }
})
export default class AdminCenterAccountController extends Mixins(AnkI18NMixin) {
  public $refs!: {
    [key: string]: any;
  };

  public get translations() {
    return {
      CreateUser: this.$t("AdminCenterAccount.Create User"),
      Login: this.$t("AdminCenterAccount.Login"),
      FirstName: this.$t("AdminCenterAccount.First name"),
      LastName: this.$t("AdminCenterAccount.Last name"),
      Email: this.$t("AdminCenterAccount.E-mail"),
      Groups: this.$t("AdminCenterAccount.Groups"),
      CreateSubGroup: this.$t("AdminCenterAccount.Create sub group"),
      Display: this.$t("AdminCenterAccount.Display"),
      ItemsPerPage: this.$t("AdminCenterKendoGridTranslation.items per page"),
      Items: this.$t("AdminCenterKendoGridTranslation.{0}-{1}of{2}items"),
      Refresh: this.$t("AdminCenterKendoGridTranslation.Refresh"),
      NoData: this.$t("AdminCenterKendoGridTranslation.No data"),
      FilterBy: this.$t("AdminCenterKendoGridTranslation.Filter by"),
      ChooseOperator: this.$t("AdminCenterKendoGridTranslation.Choose operator"),
      ClearFilter: this.$t("AdminCenterKendoGridTranslation.Clear"),
      ApplyFilter: this.$t("AdminCenterKendoGridTranslation.Apply"),
      ChooseValue: this.$t("AdminCenterKendoGridTranslation.Choose value"),
      AditionalValue: this.$t("AdminCenterKendoGridTranslation.Aditional value"),
      AditionalFilterBy: this.$t("AdminCenterKendoGridTranslation.Aditional filter by"),
      contains: this.$t("AdminCenterKendoGridTranslation.contains")
    };
  }

  public gridGroupContent = new kendo.data.DataSource({
    pageSize: 50,
    schema: {
      data: response => {
        for (let i = 0; i < response.data.length; i++) {
          response.data[i].currentDepth = this.getDepth();
          response.data[i].openPathIds = this.getOpenPathIds();
        }
        return response.data;
      },
      model: {
        fields: {
          currentDepth: { type: "number" },
          lastname: { type: "string" },
          login: { type: "string" }
        },
        id: "id"
      },
      total: "total"
    },
    serverFiltering: true,
    serverPaging: true,
    serverSorting: true,
    transport: {
      read: {
        url: "/api/v2/admin/account/groups/",
        data: filter => {
          filter.openPathIds = this.getOpenPathIds();
          filter.depth = this.getDepth();
          return filter;
        }
      }
    },
    requestEnd: e => {
      if (e && e.response) {
        const newDataDepth = [];
        for (let i = 1; i <= e.response.maxDepth; i++) {
          newDataDepth.push({ id: i });
        }

        this.updateDepth(newDataDepth);
      }
    }
  });

  public gridUserContent = new kendo.data.DataSource({
    pageSize: 50,
    schema: {
      data: "data",
      model: {
        fields: {
          firstname: { type: "string" },
          lastname: { type: "string" },
          login: { type: "string" },
          mail: { type: "string" }
        },
        id: "id"
      },
      total: "total"
    },
    serverFiltering: true,
    serverPaging: true,
    serverSorting: true,
    transport: {
      read: {
        url: "/api/v2/admin/account/users/"
      }
    }
  });
  public displayGroupDocument = false;
  public selectedGroupDocumentId = false;
  public selectedGroupLogin = "@users";
  public selectedGroup = null;
  public openPathIds: string[] = [];
  public selectedUser = "";
  public options: object = {};
  public groupId = "";
  public groupTitle: string | boolean = false;
  public dataDepth = [{ id: 1 }];
  public selectedDepth = 1;
  private smartTriggerActivated = false;

  @Watch("groupId")
  public watchGroupId(value) {
    if (this.$refs.groupList) {
      const createGrpBtn = this.$refs.groupList.kendoWidget();
      if (value === "@users") {
        createGrpBtn.setOptions({ optionLabel: this.$t("AdminCenterAccount.Create Group") });
        this.selectedGroup = null;
      } else {
        createGrpBtn.setOptions({ optionLabel: this.$t("AdminCenterAccount.Create sub group") });

        this.selectedGroup = this.gridGroupContent.get(value);
      }
    }
  }

  public mounted(): void {
    this.$nextTick(() => {
      this.groupId = window.localStorage.getItem("admin.account.groupSelected.id");
      this.gridGroupContent.read();

      this.$refs.groupGrid
        .kendoWidget()
        .element.on("mousedown", '.groupinfo[data-expanded="false"] .group-expand', e => {
          const grid = this.$refs.groupGrid.kendoWidget();
          const $tr = $(e.currentTarget).closest("tr");
          const dataItem = grid.dataItem($tr);

          e.preventDefault();
          e.stopPropagation();

          this.openPathIds.push(dataItem.pathid + ":" + dataItem.accountId);
          this.gridGroupContent.read();
        });
      this.viewAllUsers();
    });
  }

  public updateDepth(newDataDepth): void {
    this.dataDepth = newDataDepth;
  }
  public getDepth(): number {
    return this.selectedDepth;
  }
  public getOpenPathIds(): string[] {
    return this.openPathIds;
  }

  public parseCreateUser(data): object[] {
    return data.user;
  }

  public parseCreateGroup(data): object[] {
    return data.group;
  }

  // Bind the grid events (click to open an user)
  public openUser(event): void {
    event.preventDefault();
    const grid = this.$refs.grid.kendoWidget();
    const $tr = $(event.currentTarget).closest("tr");
    const dataItem = grid.dataItem($tr);
    const userId = dataItem.id;

    this.selectedUser = userId;
    this.$nextTick(() => {
      if (!this.$refs.grid.kendoWidget()._data) {
        this.gridUserContent.read();
      }
      if (userId) {
        const openDoc = this.$refs.openDoc;
        if (openDoc) {
          openDoc.fetchSmartElement({
            initid: userId,
            viewId: "!defaultConsultation"
          });
          this.refreshData(openDoc);
        }
      }
    });
  }

  public onGroupFilter(): void {
    this.selectedDepth = this.dataDepth.length;
  }
  public groupRowTemplate = this.generateRowTemplate();

  public generateRowTemplate(): object {
    const template =
      '<tr data-uid="#: uid #">' +
      '<td class="grouprow" >' +
      '<div class="groupinfo" style="margin-left: #= data.path.length #rem"' +
      ' #if (data.path.length < (data.currentDepth -1) || data.openPathIds.indexOf(data.pathid+":"+data.accountId) >= 0 || subgroupCount == 0) {# data-expanded="true" #} else {# data-expanded="false" #}# >' +
      '<div class="path"># for (var i = 0; i < data.path.length; i++)  { # &gt;&nbsp; #= (data.path[i]) ## } # </div>' +
      '<div class="groupname"><div class="lastname"><div class="group-expand"  > </div> <span>#: lastname#</span> </div> ' +
      '# if (subgroupCount > 0) { # <div class="account-badge group-count"  > #: subgroupCount# </div> #}#' +
      '<div class="account-badge user-count"> #: userCount# </div></div></div>' +
      "</td>" +
      "</tr>";

    return kendo.template(template);
  }

  // Display the selected group in the ank-document
  public updateGroupSelected(selectedGroupId): void {
    this.selectedGroupLogin = selectedGroupId || this.selectedGroupLogin;
    if (selectedGroupId && selectedGroupId !== "@users") {
      this.selectedGroupDocumentId = selectedGroupId;
      this.displayGroupDocument = true;
      return;
    }
    this.displayGroupDocument = false;
  }

  // Refresh the with the new selected group
  public updateGridData(selectedGroupLogin?): void {
    if (selectedGroupLogin === "@users") {
      this.gridUserContent.filter({});
    } else {
      this.gridUserContent.filter({
        field: "group",
        operator: "equal",
        value: selectedGroupLogin
      });
    }
  }

  public viewAllUsers(): void {
    const grid = this.$refs.groupGrid.kendoWidget();
    this.groupId = "@users";
    this.updateGridData(this.groupId);
    this.updateGroupSelected(this.groupId);
    grid.clearSelection();
  }

  public openGroup(): void {
    this.selectedUser = this.groupId;
    this.$nextTick(() => {
      const openDoc = this.$refs.openDoc;
      if (openDoc) {
        openDoc.fetchSmartElement({
          initid: this.groupId,
          viewId: "!defaultConsultation"
        });
        this.refreshData(openDoc);
      }
    });
  }
  public selectCreateUserConfig(e): void {
    if (e.dataItem.canCreate) {
      this.selectedUser = e.dataItem.id;
      this.$nextTick(() => {
        e.sender.value(""); // reset dropdownn
        const openDoc = this.$refs.openDoc;
        if (openDoc) {
          this.refreshData(openDoc);
          openDoc.fetchSmartElement({
            customClientData: { defaultGroup: this.selectedGroupDocumentId },
            initid: this.selectedUser,
            viewId: "!defaultCreation"
          });
        }
      });
    }
  }

  public selectDepth(): void {
    this.$nextTick(() => {
      this.openPathIds = [];
      this.gridGroupContent.page(1);
    });
  }

  public selectMaxDepth(e): void {
    console.log("selectMaxDepth", e);
    if (e.checked) {
      this.selectedDepth = this.dataDepth.length;
    } else {
      this.selectedDepth = 1;
    }
    this.selectDepth();
  }

  public addClassOnSelectorContainer(e): void {
    e.sender.popup.element.addClass("select-container");
  }
  public selectCreateGroupConfig(e): void {
    if (e.dataItem.canCreate) {
      this.selectedUser = e.dataItem.id;
      this.$nextTick(() => {
        e.sender.value(""); // reset dropdownn
        const openDoc = this.$refs.openDoc;
        if (openDoc) {
          this.refreshData(openDoc);
          openDoc.fetchSmartElement({
            customClientData: { defaultGroup: this.selectedGroupDocumentId },
            initid: this.selectedUser,
            viewId: "!defaultCreation"
          });
        }
      });
    }
  }

  // Show users the selected group
  public onGroupSelect(event): void {
    event.preventDefault();
    const grid = this.$refs.groupGrid.kendoWidget();
    const $tr = event.sender.select();
    const dataItem = grid.dataItem($tr);
    if (dataItem) {
      window.localStorage.setItem("admin.account.groupSelected.id", dataItem.id);
      this.updateGridData(dataItem.login);
      this.updateGroupSelected(dataItem.id);
      this.groupTitle = dataItem.title;
      this.groupId = dataItem.id;
    }
  }

  public refreshData(openDoc): void {
    if (!this.smartTriggerActivated) {
      openDoc.addEventListener("afterSave", () => {
        this.gridUserContent.read();
        this.gridGroupContent.read();
      });
      openDoc.addEventListener("afterDelete", () => {
        this.updateGridData();
        this.gridGroupContent.read();
      });
      this.smartTriggerActivated = true;
    }
  }
}

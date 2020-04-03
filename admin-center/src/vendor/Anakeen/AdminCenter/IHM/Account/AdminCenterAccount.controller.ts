import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { DropdownsInstaller } from "@progress/kendo-dropdowns-vue-wrapper";
import { GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.toolbar";
import { Component, Vue, Watch } from "vue-property-decorator";

Vue.use(ButtonsInstaller);
Vue.use(GridInstaller);
Vue.use(DropdownsInstaller);
Vue.use(DataSourceInstaller);
declare const $;
declare const kendo;

@Component({
  components: {
    AnkSmartElement: () => AnkSmartElement,
    "ank-split-panes": AnkPaneSplitter
  }
})
export default class AdminCenterAccountController extends Vue {
  public $refs!: {
    [key: string]: any;
  };

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
  public openPathIds: string[] = [];
  public selectedUser = "";
  public options: object = {};
  public groupId = "";
  public groupTitle: string | boolean = false;
  public dataDepth = [{ id: 1 }];
  public selectedDepth = 1;
  private smartTriggerActivated = false;
  private refreshNeeded = false;
  @Watch("refreshNeeded")
  public watchRefreshNeeded(value) {
    if (value) {
      setTimeout(() => {
        console.log("NEED REFERSH GROUP GRID");
      }, 300);
    }
    this.refreshNeeded = false;
  }

  @Watch("groupId")
  public watchGroupId(value) {
    if (this.$refs.groupList) {
      const createGrpBtn = this.$refs.groupList.kendoWidget();
      if (value === "@users") {
        createGrpBtn.setOptions({ optionLabel: "Create group" });
      } else {
        createGrpBtn.setOptions({ optionLabel: "Create sub group" });
      }
    }
  }

  public mounted(): void {
    this.$nextTick(() => {
      this.groupId = window.localStorage.getItem("admin.account.groupSelected.id");
      this.gridGroupContent.read();

      this.$refs.groupGrid.kendoWidget().element.on("mousedown", ".account-badge.group-count", e => {
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

  public parseCreateUser(data) {
    return data.user;
  }

  public parseCreateGroup(data) {
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

  public onGroupFilter() {
    this.selectedDepth = this.dataDepth.length;
    console.log("onGroupFilter");
  }
  public groupRowTemplate = this.generateRowTemplate();

  public generateRowTemplate() {
    const template =
      '<tr data-uid="#: uid #">' +
      '<td class="grouprow" >' +
      '<div class="groupinfo" style="margin-left: #= data.path.length #rem"' +
      '#if (data.path.length < (data.currentDepth -1) || data.openPathIds.indexOf(data.pathid+":"+data.accountId) >= 0) {# data-expanded="true" #}# >' +
      '<div class="path"># for (var i = 0; i < data.path.length; i++)  { #&gt;&nbsp; #= (data.path[i]) ## } # </div>' +
      '<div class="groupname"><div class="lastname">#: lastname# </div> ' +
      '# if (subgroupCount > 0) { # <div class="account-badge group-count"  > #: subgroupCount# </div> #}#' +
      '<div class="account-badge user-count"> #: userCount# </div></div></div>' +
      "</td>" +
      "</tr>";

    return kendo.template(template);
  }

  // Display the selected group in the ank-document
  public updateGroupSelected(selectedGroupId) {
    this.selectedGroupLogin = selectedGroupId || this.selectedGroupLogin;
    if (selectedGroupId && selectedGroupId !== "@users") {
      this.selectedGroupDocumentId = selectedGroupId;
      this.displayGroupDocument = true;
      return;
    }
    this.displayGroupDocument = false;
  }

  // Refresh the with the new selected group
  public updateGridData(selectedGroupLogin?) {
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

  public viewAllUsers() {
    this.groupId = "@users";
    this.updateGridData(this.groupId);
    this.updateGroupSelected(this.groupId);
  }

  public openGroup() {
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
  public selectCreateUserConfig(e) {
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
  public addClassOnSelectorContainer(e) {
    e.sender.popup.element.addClass("select-container");
  }
  public selectCreateGroupConfig(e) {
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

  // Open group selected in group change mode
  public openChangeGroup() {
    this.selectedUser = this.groupId;
    this.$nextTick(() => {
      const openDoc = this.$refs.openDoc;
      if (openDoc) {
        openDoc.fetchSmartElement({
          initid: this.groupId,
          viewId: "changeGroup"
        });
      }
    });
  }

  // Show users the selected group
  public onGroupSelect(event) {
    event.preventDefault();
    const grid = this.$refs.groupGrid.kendoWidget();
    const $tr = event.sender.select();
    const dataItem = grid.dataItem($tr);
    window.localStorage.setItem("admin.account.groupSelected.id", dataItem.id);
    this.updateGridData(dataItem.login);
    this.updateGroupSelected(dataItem.id);
    this.groupTitle = dataItem.title;
    this.groupId = dataItem.id;
  }

  public refreshData(openDoc) {
    if (!this.smartTriggerActivated) {
      openDoc.addEventListener("afterSave", () => {
        this.gridUserContent.read();
        this.gridGroupContent.read();
        this.refreshNeeded = true;
      });
      openDoc.addEventListener("afterDelete", () => {
        this.updateGridData();
        this.gridGroupContent.read();
        this.refreshNeeded = true;
      });
      this.smartTriggerActivated = true;
      this.refreshNeeded = false;
    }
  }
}

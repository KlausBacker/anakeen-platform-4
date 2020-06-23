import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm.esm";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.switch";
import { Component, Mixins, Prop, Vue, Watch } from "vue-property-decorator";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import * as $ from "jquery";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import { LayoutInstaller } from "@progress/kendo-layout-vue-wrapper";

Vue.use(ButtonsInstaller);
Vue.use(LayoutInstaller);

@Component({
  components: {
    "ank-smart-form": () => {
      return AnkSmartForm;
    },
    "ank-split-panes": AnkPaneSplitter
  }
})
export default class AdminCenterAllParam extends Mixins(AnkI18NMixin) {
  @Prop({
    default: true,
    type: Boolean
  })
  public force;

  @Prop({ type: Boolean, default: true })
  public isUserTab!: boolean;

  @Prop({ type: Boolean, default: true })
  public isGlobalTab!: boolean;

  @Prop({ type: Array, default: [] })
  public namespace!: Array<string>;

  @Prop({ type: String, default: "" })
  public specificUser!: string;

  @Prop({ type: String, default: "" })
  public selectedTab!: string;

  @Prop({ type: String, default: "" })
  public userId!: string;

  @Prop({ type: String, default: "" })
  public paramId!: string;

  @Prop({ type: Boolean, required: true })
  public userTab!: boolean;

  @Watch("selectedTab")
  protected watchSelectedTab(newValue): void {
    // Condition to pass only once time
    if (this.userTab === (newValue === "userTab")) {
      this.paramNameSpace = "";
      this.selectedParamObj = {
        selectedParamName: "",
        selectedParamNameSpace: "",
        selectedParamDesc: "",
        selectedParamInitialValue: "",
        selectedParamValue: ""
      };
      this.selectedParam = false;
      this.kendoGrid.dataSource.read();
      this.changeUrl();
    }
    $(window).trigger("resize");
  }

  public $refs!: {
    [key: string]: any;
  };
  public kendoGrid: any = null;

  public selectedParam = false;
  public selectedParamObj = {
    selectedParamName: "",
    selectedParamNameSpace: "",
    selectedParamDesc: "",
    selectedParamInitialValue: "",
    selectedParamValue: ""
  };

  public element: any;
  public modifications: any;
  public account = "";
  public accountDisplayValue = "";
  public paramNameSpace = "";

  // funtion to set the dataSource on the kendoGrid
  public paramGridData: kendo.data.DataSource = new kendo.data.DataSource({
    schema: {
      data: response => {
        return response.data.data.gridData;
      },
      total: response => {
        if (response.data.data.gridData) {
          return response.data.data.gridData.filter(d => d.nameSpace).length;
        } else {
          return 0;
        }
      }
    },
    serverFiltering: false,
    serverPaging: false,
    serverSorting: false,
    transport: {
      read: options => {
        this.$http
          .get(this.urlParameters(), {
            params: options.data,
            paramsSerializer: kendo.jQuery.param
          })
          .then(result => {
            const gridData = result.data.data.gridData || [];
            if ((this.userTab && result.data.data.user) || !this.userTab) {
              // filter to remove if nameSpace in the response data not exist and filter by an nameSpace array
              result.data.data.gridData = gridData.filter(data => {
                if (data.nameSpace) {
                  if (this.namespace.length > 0 && !this.namespace.includes(data.nameSpace)) {
                    return false;
                  }
                  return data;
                } else {
                  return false;
                }
              });
            } else {
              this.$emit("notify", "warning", this.$t("AdminCenterAllParameter.Invalid user id"));
            }
            // notify the data source that the request succeeded
            options.success(result);
            // To display number of results : the first time
            $(window).trigger("resize");
          })
          .catch(options.error);
        return options;
      }
    }
  });

  get smartFormData() {
    // eslint-disable-next-line prefer-const
    let smartFormDataParameter = {
      renderOptions: {
        types: {
          menu: {
            labelPosition: "right"
          }
        }
      },
      menu: [
        {
          beforeContent: '<div class="fa fa-close" />',
          iconUrl: "",
          id: "close",
          important: false,
          label: this.$t("AdminCenterAllParameter.Close"),
          target: "_self",
          type: "itemMenu",
          url: "#action/param.close"
        }
      ],
      structure: [],
      title: this.selectedParamObj.selectedParamName,
      type: "",
      force: false,
      values: {
        description: this.selectedParamObj.selectedParamDesc,
        default_value: this.selectedParamObj.selectedParamInitialValue,
        value: this.selectedParamObj.selectedParamValue
      }
    };
    if (this.element && this.element.isReadOnly === false) {
      const configSaveParamMenu = {
        beforeContent: '<div class="fa fa-save" />',
        iconUrl: "",
        id: "submit",
        important: false,
        label: this.$t("AdminCenterAllParameter.Save the modifications"),
        target: "_self",
        type: "itemMenu",
        url: "#action/param.save"
      };
      smartFormDataParameter.menu.unshift(configSaveParamMenu);
      if (this.userTab) {
        const configRestoreParamMenu = {
          beforeContent: '<div class="fa fa-undo" />',
          visibility: this.selectedParamObj.selectedParamValue === "" ? "disabled" : "visible",
          iconUrl: "",
          id: "reset",
          important: false,
          label: this.$t("AdminCenterAllParameter.Reset default value"),
          target: "_self",
          type: "itemMenu",
          url: "#action/param.restore"
        };
        smartFormDataParameter.menu.push(configRestoreParamMenu);
      }
    }
    return smartFormDataParameter;
  }

  get selectUserForm() {
    // eslint-disable-next-line prefer-const
    let configUserForm = {
      renderOptions: {
        types: {
          account: {
            labelPosition: "none"
          }
        }
      },
      structure: [
        {
          label: this.$t("AdminCenterAllParameter.Select account"),
          name: "my_fr_ident",
          type: "frame",
          icon: "/api/v2/images/assets/sizes/24x24c/se-iuser.png",
          url: "#action/param.cancel",
          content: [
            {
              label: this.$t("AdminCenterAllParameter.User"),
              name: "my_user",
              type: "account",
              display: "write"
            }
          ]
        }
      ]
    };
    if (this.specificUser) {
      configUserForm.structure[0].label = this.$t("AdminCenterAllParameter.User parameters");
      configUserForm.structure[0].content[0].type = "text";
      configUserForm.structure[0].content[0].display = "read";
    }
    return configUserForm;
  }

  // change url to emit to the router
  public changeUrl(): void {
    let url = "/";
    // if (this.userTab === false) {
    if (this.selectedTab === "globalTab") {
      url += "global/";
    } else {
      url += "user/";
      if (this.account) {
        url += this.account + "/";
      }
    }
    if (this.selectedParam === true) {
      url += this.selectedParamObj.selectedParamNameSpace + ":" + this.selectedParamObj.selectedParamName + "/";
    }
    this.$emit("navigate", url);
  }

  // Input type to use in template
  public parameterInputType(): string {
    const parameterType = this.element.type.toLowerCase();

    let eachLine = [];
    if (this.element && this.element.value) {
      eachLine = this.element.value.split("\n");
    }
    if (parameterType === "text" && eachLine.length > 1) {
      return "longtext";
    }
    if (parameterType === "number" || parameterType === "integer" || parameterType === "double") {
      return "int";
    } else if (parameterType.startsWith("enum")) {
      return "enum";
    } else if (parameterType === "json") {
      return "longtext";
    } else if (parameterType === "") {
      return "longtext";
    } else {
      return parameterType;
    }
  }

  // Return the possible values of an enum parameter
  public enumPossibleValues(): string[] {
    if (this.parameterInputType() === "enum") {
      let rawEnum = this.element.type;
      rawEnum = rawEnum.slice(5);
      rawEnum = rawEnum.slice(0, -1);
      return rawEnum.split("|");
    }
  }

  // Get entries from an Parameters
  public loadParameterFromClick(e): void {
    this.selectedParamObj.selectedParamName = this.kendoGrid.dataItem($(e.currentTarget).closest("tr")).name;
    this.selectedParamObj.selectedParamNameSpace = this.kendoGrid.dataItem($(e.currentTarget).closest("tr")).nameSpace;
    this.selectedParamObj.selectedParamDesc = this.kendoGrid.dataItem($(e.currentTarget).closest("tr")).description;
    this.selectedParamObj.selectedParamInitialValue = this.kendoGrid.dataItem(
      $(e.currentTarget).closest("tr")
    ).initialValue;
    this.selectedParamObj.selectedParamValue = this.kendoGrid.dataItem($(e.currentTarget).closest("tr")).value;
    this.element = this.kendoGrid.dataItem($(e.currentTarget).closest("tr"));
    this.displayParameter();
  }

  // Get the parameters value from an request (in the request we get the id and displayValue user)
  public loadParameterFromRouter(nameParameter): void {
    this.$http
      .get(this.urlParameters())
      .then(result => {
        if ((this.userTab && result.data.data.user) || !this.userTab) {
          if (this.userTab && this.selectedTab === "userTab" && this.account !== "") {
            this.fromRouter = true;
            this.accountDisplayValue = result.data.data.user.displayValue;
            this.$refs.userSmartForm.setValue("my_user", {
              value: this.account,
              displayValue: this.accountDisplayValue
            });
          }
          if (this.namespace && this.namespace.length > 0) {
            result.data.data.gridData = result.data.data.gridData.filter(data => {
              if (data.nameSpace) {
                if (this.namespace.length > 0 && !this.namespace.includes(data.nameSpace)) {
                  return false;
                }
                return data;
              } else {
                return false;
              }
            });
          }

          const param = result.data.data.gridData.find(d => d.nameSpace + ":" + d.name === nameParameter);
          if (param) {
            this.selectedParamObj.selectedParamName = param.name;
            this.selectedParamObj.selectedParamNameSpace = param.nameSpace;
            this.selectedParamObj.selectedParamDesc = param.description;
            this.selectedParamObj.selectedParamInitialValue = param.initialValue;
            this.selectedParamObj.selectedParamValue = param.value;
            this.element = param;
            this.smartFormData.structure = [];
            this.selectedParam = true;
            this.displayParameter();
          } else {
            if (nameParameter && nameParameter !== "") {
              this.$emit(
                "notify",
                "warning",
                this.$t("AdminCenterAllParameter.Invalid name parameter") + "\n(" + nameParameter + ")"
              );
              this.changeUrl();
            }
          }
        } else {
          this.$emit("notify", "warning", this.$t("AdminCenterAllParameter.Invalid user id"));
          return;
        }
      })
      .catch(err => {
        console.error(err);
      });
  }

  public displayParameter(): void {
    this.smartFormData.values.description = this.selectedParamObj.selectedParamDesc;
    this.smartFormData.values.default_value = this.selectedParamObj.selectedParamInitialValue;
    const type = this.parameterInputType();
    let item = {};

    const value = this.selectedParamObj.selectedParamValue;
    if (
      this.element.type.toLowerCase() === "json" ||
      (type === "longtext" && value.charAt(value.length - 1) === "}" && value.charAt(0) === "{") ||
      (type === "longtext" && value.charAt(value.length - 1) === "]" && value.charAt(0) === "[")
    ) {
      try {
        this.smartFormData.values.value = JSON.stringify(JSON.parse(value), undefined, 2);
      } catch {
        this.smartFormData.values.value = value;
        console.warn("Bad json");
      }
    }
    if (type === "enum") {
      item = {
        label: this.$t("AdminCenterAllParameter.Value"),
        name: "value",
        type: type,
        enumItems: [],
        display: "read"
      };
      const possibleEnum = this.enumPossibleValues();
      for (let i = 0; i < possibleEnum.length; i++) {
        const val = { key: possibleEnum[i], label: possibleEnum[i] };
        item["enumItems"].push(val);
      }
    } else {
      item = {
        label: this.$t("AdminCenterAllParameter.Value"),
        name: "value",
        type: type,
        display: "read"
      };
    }
    if (this.element.isReadOnly === false) {
      item["display"] = "write";
    }
    const structureSmartForm = {
      label: this.$t("AdminCenterAllParameter.Parameters"),
      name: "param_frame",
      type: "frame",
      content: [
        {
          label: this.$t("AdminCenterAllParameter.Description"),
          name: "description",
          type: "text",
          display: "read"
        },
        item
      ],
      renderOptions: {
        fields: {
          param_frame: {
            stickyTabs: "auto"
          }
        }
      }
    };
    if (this.userTab === true && this.account) {
      const defaultValue = {
        label: this.$t("AdminCenterAllParameter.Default value"),
        name: "default_value",
        type: "text",
        display: "read"
      };
      structureSmartForm.content.splice(1, 0, defaultValue);
      this.smartFormData.title = `${this.$t("AdminCenterAllParameter.Parameter from")} " ${
        this.accountDisplayValue
      } " : ${this.selectedParamObj.selectedParamName}`;
    }
    this.smartFormData.structure.push(structureSmartForm);
    this.changeUrl();
    kendo.ui.progress($(".param-form-wrapper", this.$el), false);
  }

  public fromRouter = false;

  public userChange(event, smartElement, smartField, params): void {
    this.smartFormData.structure = [];
    if (this.fromRouter) {
      this.fromRouter = false;
    } else {
      this.selectedParam = false;
    }
    this.account = params.current.value;
    this.accountDisplayValue = params.current.displayValue;
    this.kendoGrid.dataSource.read();
    this.changeUrl();
  }

  public menuClick(event, smartElement, params): void {
    const smartForm = this.$refs.formParameters;
    if (params.eventId === "param.save") {
      this.modifications = smartForm.getValue("value").value;
      this.$http
        .put(this.urlParameters(true) + this.element.nameSpace + "/" + this.element.name + "/", {
          value: this.modifications.toString()
        })
        .then(() => {
          for (let i = 0; i < document.getElementsByClassName("dcpDocument__header__modified").length; i++) {
            // @ts-ignore
            document.getElementsByClassName("dcpDocument__header__modified")[i].style.display = "none";
          }
          this.$emit(
            "notify",
            "success",
            this.element.name +
              " = " +
              this.modifications.toString() +
              "\n" +
              this.$t("AdminCenterAllParameter.Modification saved")
          );
          this.selectedParamObj.selectedParamValue = this.modifications;
          this.displayParameter();
          this.kendoGrid.dataSource.read();
        })
        .catch(err => {
          console.error(err);
        });
    } else if (params.eventId === "param.restore") {
      this.$http
        .delete(this.urlParameters(true) + this.element.nameSpace + "/" + this.element.name + "/")
        .then(() => {
          for (let i = 0; i < document.getElementsByClassName("dcpDocument__header__modified").length; i++) {
            // @ts-ignore
            document.getElementsByClassName("dcpDocument__header__modified")[i].style.display = "none";
          }
          this.$emit(
            "notify",
            "success",
            this.element.name + "\n" + this.$t("AdminCenterAllParameter.Restore parameter")
          );
          this.loadParameterFromRouter(this.element.nameSpace + ":" + this.element.name);
          this.kendoGrid.dataSource.read();
        })
        .catch(err => {
          console.error(err);
        });
    } else if (params.eventId === "param.close") {
      this.smartFormData.structure = [];
      this.selectedParam = false;
    }
  }

  public updateModifications(event, smartElement, smartField, values): void {
    this.modifications = values.current.value;
  }

  public urlParameters(saveFormUser = false): string {
    const urlParam = "/api/v2/admin/parameters/";

    if (this.userTab === false) {
      return urlParam;
    } else if (this.userTab === true && this.account && saveFormUser === false) {
      return urlParam + "users/" + this.account + "/";
    } else if (this.userTab === true && saveFormUser === true) {
      return urlParam + this.account + "/";
    }
    return urlParam + "users/";
  }

  public loadParamFromVu(): void {
    this.paramNameSpace = this.paramId;
    if (this.userTab === (this.selectedTab === "userTab")) {
      if (this.paramNameSpace !== "" || this.account !== "") {
        this.loadParameterFromRouter(this.paramNameSpace);
      }
    }
  }

  // action click to last column of kendoGrid
  public onClickAction(e) {
    this.smartFormData.structure = [];
    this.selectedParam = true;
    if (this.userTab === (this.selectedTab === "userTab")) {
      this.loadParameterFromClick(e);
    }
  }

  public mounted() {
    if (this.paramId && this.paramId !== "") {
      this.paramNameSpace = this.paramId;
    }
    if (this.userId && this.userId !== "") {
      this.account = this.userId;
    }
    // @ts-ignore
    this.kendoGrid = $(this.$refs.gridWrapper)
      .kendoGrid({
        columns: [
          {
            field: "nameSpace",
            title: this.$t("AdminCenterAllParameter.NameSpace")
          },
          {
            field: "category",
            title: this.$t("AdminCenterAllParameter.Sub-category")
          },
          {
            field: "name",
            title: this.$t("AdminCenterAllParameter.Name")
          },
          {
            field: "description",
            title: this.$t("AdminCenterAllParameter.Description")
          },
          {
            field: "value",
            title: this.$t("AdminCenterAllParameter.Value"),
            template: data => {
              if (data.initialValue && data.value && data.initialValue !== data.value) {
                return `<div title="${this.$t(
                  "AdminCenterAllParameter.Element modified"
                )}" class="param-value-modified"> ${data.value}</div>`;
              } else if (data.initialValue && !data.value) {
                return `<div> ${data.initialValue}</div>`;
              } else if (!data.initialValue && !data.value) {
                return "";
              }
              return `<div> ${data.value}</div>`;
            }
          },
          {
            command: [
              {
                text: this.$t("AdminCenterAllParameter.Consult"),
                click: this.onClickAction,
                name: "Consult",
                visible: function(dataItem): boolean {
                  return dataItem.isReadOnly === true;
                }
              },
              {
                name: "Modify",
                click: this.onClickAction,
                text: this.$t("AdminCenterAllParameter.Modify"),
                visible: function(dataItem): boolean {
                  return dataItem.isReadOnly === false;
                }
              }
            ],
            filterable: false
          }
        ],
        dataSource: this.paramGridData,
        pageable: {
          info: true,
          input: false,
          numeric: false,
          pageSizes: false,
          previousNext: false,
          refresh: true,
          messages: {
            itemsPerPage: this.$t("AdminCenterKendoGridTranslation.items per page"),
            display: this.$t("AdminCenterKendoGridTranslation.{2}items"),
            refresh: this.$t("AdminCenterKendoGridTranslation.Refresh"),
            NoData: this.$t("AdminCenterKendoGridTranslation.No data"),
            empty: this.$t("AdminCenterKendoGridTranslation.No data")
          }
        },
        scrollable: true,
        sortable: true,
        filterable: {
          extra: false,
          operators: {
            string: {
              contains: this.$t("AdminCenterKendoGridTranslation.contains")
            }
          },
          messages: {
            info: this.$t("AdminCenterKendoGridTranslation.Filter by") + ": ",
            operator: this.$t("AdminCenterKendoGridTranslation.Choose operator"),
            clear: this.$t("AdminCenterKendoGridTranslation.Clear"),
            filter: this.$t("AdminCenterKendoGridTranslation.Apply"),
            value: this.$t("AdminCenterKendoGridTranslation.Choose value"),
            additionalValue: this.$t("AdminCenterKendoGridTranslation.Aditional value"),
            title: this.$t("AdminCenterKendoGridTranslation.Aditional filter by")
          }
        },
        dataBound: () => {
          if (this.userTab === false && this.selectedTab === "globalTab") {
            if (this.paramNameSpace !== "" || this.account !== "") {
              this.loadParameterFromRouter(this.paramNameSpace);
            }
          }
        }
      })
      .data("kendoGrid");
  }
}

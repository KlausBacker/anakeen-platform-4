import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import Vue from "vue";
import VModal from "vue-js-modal";
import { Component, Prop, Watch } from "vue-property-decorator";

Vue.use(VModal);
Vue.use(GridInstaller);
Vue.use(DataSourceInstaller);
@Component({
  components: {
    "smart-form": AnkSmartForm
  }
})
export default class SmartStructureManagerDefaultValuesController extends Vue {
  public smartForm: object = {};
  public unsupportedType = ["frame", "tab", "array"];
  public $refs!: {
    [key: string]: any;
  };
  @Prop({
    default: "",
    type: String
  })
  public ssName;
  public editWindow = {
    title: "",
    width: "50%"
  };

  @Watch("ssName")
  public watchSsName(newValue) {
    if (newValue) {
      this.$refs.defaultGridContent.kendoWidget().dataSource.read();
    }
  }
  public onEditClick(e) {
    const row = $(e.target).closest("tr")[0];
    const value = row.children[2].textContent;
    console.log(value);
    const type = row.children[1].textContent;
    this.$modal.show("hello-world", {
      config: {
        menu: [
          {
            beforeContent: '<div class="fa fa-times" />',
            htmlLabel: "",
            iconUrl: "",
            id: "cancel",
            important: false,
            label: "Cancel",
            target: "_self",
            type: "itemMenu",
            url: "#action/document.cancel",
            visibility: "visible"
          },
          {
            beforeContent: '<div class="fa fa-save" />',
            htmlLabel: "",
            iconUrl: "",
            id: "submit",
            important: false,
            label: "Submit",
            target: "_self",
            type: "itemMenu",
            url: "#action/document.save",
            visibility: "visible"
          }
        ],
        structure: [
          {
            content: [
              {
                enumItems: [
                  {
                    key: "inherited",
                    label: "Inherited"
                  },
                  {
                    key: "value",
                    label: "Value"
                  },
                  {
                    key: "advanced_value",
                    label: "Advanced Value"
                  },
                  {
                    key: "no_value",
                    label: "No value"
                  }
                ],
                label: "Type",
                name: "my_type",
                type: "enum"
              },
              {
                label: "Inherited",
                name: "my_inherited_value",
                type: "text"
              },
              {
                label: "Value",
                name: "my_value",
                type: `${type}`
              },
              {
                label: "Advanced value",
                name: "my_advanced_value",
                type: "longtext"
              }
            ],
            label: "Default value",
            name: "my_default_value",
            type: "frame"
          }
        ],
        title: "Edit value form",
        values: {
          my_inherited_value: `${value}`
        }
      }
    });
  }
  public beforeEdit(data) {
    this.smartForm = data.params.config;
  }
  public displayData(colId) {
    return dataItem => {
      switch (colId) {
        case "type":
          if (dataItem[colId]) {
            return dataItem[colId];
          }
          break;
        case "value":
          return this.displayMultiple(dataItem[colId]);
      }
    };
  }
  public displayMultiple(data) {
    if (data instanceof Object) {
      const str = "";
      return this.recursiveData(data, str);
    } else if (data !== null && data !== undefined) {
      return data;
    } else {
      return "None".fontcolor("ced4da");
    }
  }
  public recursiveData(items, str) {
    if (items instanceof Object) {
      Object.keys(items.toJSON()).forEach(item => {
        if (items[item] instanceof Object) {
          this.recursiveData(items[item], str);
        } else {
          if (items[item]) {
            str += `<li>${items[item]}</li>`;
          }
        }
      });
    }
    if (str === "") {
      return "";
    }
    return `<ul>${str}</ul>`;
  }
  protected parseDefaultValuesData(response) {
    const result = [];
    if (response && response.data && response.data.data) {
      const items = response.data.data.defaultValues;
      Object.keys(items).map(item => {
        if (!this.unsupportedType.includes(items[item].type)) {
          result.push({
            config: items[item].config,
            id: item,
            type: items[item].type,
            value: items[item].value
          });
        }
      });
      return result;
    }
    return [];
  }

  protected getDefaultValues(options) {
    this.$http
      .get(`/api/v2/admin/smart-structures/${this.ssName}/defaults/`, {
        params: options.data,
        paramsSerializer: kendo.jQuery.param
      })
      .then(response => {
        options.success(response);
      })
      .catch(response => {
        options.error(response);
      });
    return [];
  }
}

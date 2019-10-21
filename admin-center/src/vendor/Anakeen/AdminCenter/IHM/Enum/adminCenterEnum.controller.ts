import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.switch";
import * as _ from "underscore";
import Vue from "vue";
import { Component } from "vue-property-decorator";

@Component({
  components: {
    "ank-smart-form": AnkSmartForm,
    "ank-split-panes": AnkPaneSplitter
  }
})
export default class AdminCenterEnumController extends Vue {
  get smartFormData() {
    return {
      menu: [
        {
          beforeContent: '<div class="fa fa-save" />',
          iconUrl: "",
          id: "submit",
          important: false,
          label: "Sauver les modifications",
          target: "_self",
          type: "itemMenu",
          url: "#action/enum.save",
          visibility: "visible"
        }
      ],
      renderOptions: {
        fields: {
          enum_array_active: {
            editDisplay: "bool"
          },
          enum_array_translation: {
            template: '<a href="#">Translate</a>'
          },
        }
      },
      structure: [
        {
          content: [
            {
              content: [
                {
                  display: "write",
                  label: "Key",
                  name: "enum_array_key",
                  type: "text"
                },
                {
                  label: "Label",
                  name: "enum_array_label",
                  type: "text"
                },
                {
                  label: "Translation",
                  name: "enum_array_translation",
                  type: "text"
                },
                {
                  label: "Active",
                  name: "enum_array_active",
                  type: "enum",
                  "enumItems": [
                    {
                      "key": "disable",
                      "label": "Disable"
                    },
                    {
                      "key": "enable",
                      "label": "Enable"
                    }
                  ]
                },
              ],
              label: "Entries",
              name: "enum_array",
              type: "array"
            }
          ],
          label: "Enumerate " + this.selectedEnum,
          name: "enum_frame",
          type: "frame"
        }
      ],
      title: "Enumerate " + this.selectedEnum,
      type: "",
      values: {
        enum_array_key: this.keysArray,
        enum_array_label: this.labelArray,
        enum_array_active: this.activeArray
      }
    };
  }

  public selectedEnum: string = "";
  public kendoGrid: any = null;
  // Store temporarily data from a specific line update/add
  public tempModifications: any = {};
  // Store data to send to the server
  public modifications: any = {};
  // Initial enum entries data
  public smartFormModel: any = {};
  // SmartForm's filling data
  public keysArray: any = [];
  public labelArray: any = [];
  public activeArray: any = [];

  private smartFormDataCounter: number = 0;

  // Get entries from an Enum
  public loadEnumerate(e) {
    this.keysArray = [];
    this.labelArray = [];
    this.activeArray = [];
    this.selectedEnum = this.kendoGrid.dataItem($(e.currentTarget).closest("tr")).enumerate;
    const that = this;
    this.$http.get(`/api/v2/admin/enumdata/${this.selectedEnum}`).then(response => {
      const enumData = response.data.data;
      enumData.forEach((value, index) => {
        that.smartFormModel[index] = _.defaults(value, { key: "", label: "", active: "", eorder: "" });
        that.modifications[index] = that.smartFormModel[index];
        that.smartFormDataCounter++;
      });
      that.smartFormModel.size = that.smartFormDataCounter;
      that.buildInitialFormData();
    });
  }

  // Manage how SmartForm's lines are added and act accordingly
  public manageRows(event, smartElement, smartField, type, index) {
    if (smartField.id === "enum_array") {
      switch (type) {
        case "addLine": {
          // If user's clicking on the "+" button
          if (this.smartFormDataCounter <= 0) {
            //@ts-ignore
            this.modifications[index] = { key: "", label: "", active: "enable", eorder: index + 1};
          }
          // If lines are added by the SmartForm's initial build
          else {
            this.smartFormDataCounter--;
          }
          break;
        }
        case "removeLine": {
          delete this.modifications[index];
          break;
        }
        case "moveLine": {
          // '+1' because array's index start at 0 but eorder column in db starts at 1
          const fromLine = index["fromLine"] + 1;
          const toLine = index["toLine"] + 1;
          this.changeEnumOrder(fromLine, toLine);
          console.log(this.modifications);
          break;
        }
      }
    }
  }

  public updateModifications(event, smartElement, smartField, values, index) {
    if (values.current[index] !== undefined) {
      switch (smartField.id) {
        case "enum_array_key": {
          this.modifications[index].key = values.current[index].value;
          break;
        }
        case "enum_array_label": {
          this.modifications[index].label = values.current[index].value;
          break;
        }
        case "enum_array_active": {
          this.modifications[index].active = values.current[index].value;
          break;
        }
        default:
          break;
      }
    }
  }

  public setRowMode(edit, rowIndex) {



    
   /*  const mode = edit ? "edit" : "view";
    const row = this.getRow(rowIndex);
    $(row)
      .find(".enum-key-wrapper")
      .attr("mode", mode);
    $(row)
      .find(".enum-validate-wrapper")
      .attr("mode", mode);

    if (edit) {
    } else {
    } */
  }

  public saveModifications(event, smartElement, params) {
    if (params.eventId === "enum.save") {
      // ToDo : Check validity of data

      const data = {
        data: this.modifications,
        enumName: this.selectedEnum
      };
      this.$http.post(`/api/v2/admin/enumupdate/${this.selectedEnum}`, data).then(() => {
        // @ts-ignore
        this.kendoGrid.dataSource.read();
      });
    }
  }
  public mounted() {
    this.kendoGrid = $(this.$refs.gridWrapper)
      .kendoGrid({
        toolbar: kendo.template('<input type="button" id="clearFilterButton" class="k-button" value="Clear Filter" />'),
        columns: [
          {
            field: "enumerate",
            title: "Enumerate",
          },
          {
            field: "label",
            title: "Label",
          },
          {
            field: "structures",
            title: "Structure",
          },
          {
            field: "fields",
            title: "Fields"
          },
          {
            field: "modifiable",
            title: "Modifiable",
            filterable: false
          },
          {
            command: {
              click: this.loadEnumerate,
              text: "Modify"
            },
            title: "Actions"
          }
        ],
        dataSource: {
          schema: {
            data: "data.data",
            model: {
              fields: {
                enumerate: { type: "string" },
                fields: { type: "string" },
                label: { type: "string" },
                structures: { type: "string" }
              }
            },
            total: "data.total"
          },
          serverFiltering: true,
          serverPaging: true,
          serverSorting: true,
          transport: {
            read: {
              dataType: "json",
              url: "/api/v2/admin/enum"
            }
          }
        },
        pageable: {
          pageSize: 50,
          pageSizes: [50, 100, 200]
        },
        scrollable: true,
        sortable: true,
        filterable: {
          extra: false,
          operators: {
            string: {
              contains: "Contains"
            }
          },
        },
        filterMenuInit:function(e){
          let that=this;
          $(e.container).find('.k-primary').click(function(event){
            let val = $(e.container).find('[title="Value"]').val()
            if(val == ""){
              // @ts-ignore
              that.kendoGrid.dataSource.filter({});
            }

          })
        },
      })
      .data("kendoGrid");

  }

  private getRow(rowIndex) {
    return $(`tr[data-line=${rowIndex}]`)[0];
  }

  // Fill SmartForm's initial data arrays
  private buildInitialFormData() {
    for (let i = 0; i < this.smartFormModel.size; i++) {
      this.keysArray.push(this.smartFormModel[i].key);
      this.labelArray.push(this.smartFormModel[i].label);
      this.activeArray.push(this.smartFormModel[i].active)
    }
  }

  // Add data in SmartForm's data arrays and complete the "add" process
  private insertFormData(index) {
    this.keysArray[index] = this.smartFormModel[index].key;
    this.labelArray[index] = this.smartFormModel[index].label;
    this.activeArray[index] = this.smartFormModel[index].active;
  }

  private changeEnumOrder(fromLine, toLine) {
    for (let i in this.modifications) {
      if (this.modifications.hasOwnProperty(i)) {
        if (fromLine > toLine) {
          console.log("from > to")
          if (this.modifications[i].eorder < fromLine && this.modifications[i].eorder >= toLine) {
            Number(this.modifications[i].eorder++);
          }
          else if (this.modifications[i].eorder == fromLine) {
            this.modifications[i].eorder = toLine;
          }
        }
        else if (fromLine < toLine) {
          console.log("from < to")
          if (this.modifications[i].eorder > fromLine && this.modifications[i].eorder <= toLine) {
            Number(this.modifications[i].eorder--);
          }
          else if (this.modifications[i].eorder == fromLine) {
            this.modifications[i].eorder = toLine;
          }
        }
      }
    }
  }
}

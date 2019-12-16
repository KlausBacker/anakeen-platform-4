import ankSmartController from "@anakeen/user-interfaces/components/lib/AnkController.esm";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm.esm";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import { Component, Prop, Vue, Watch} from "vue-property-decorator";
 
Vue.use(GridInstaller);
Vue.use(DataSourceInstaller);

@Component({
  components: {
    "smart-form": () => AnkSmartForm,
  }
})
export default class SmartStructureManagerDefaultValuesController extends Vue {
  public smartForm: object = {};
  public smartFormModal = this.$refs.smartFormModal;
  public showModal = false;

  public unsupportedType = ["frame", "tab", "array"];
  public $refs!: {
    [key: string]: any;
  };
 
  @Prop({
    default: "",
    type: String
  })
  public ssName;
  public finalData = {
    fieldId: "",
    structureId: this.ssName,
    value: "",
    valueType: "value",
  }
  public smartFormArrayStructure = {};
  public smartFormArrayValues = {};
  public smartFormDisplayManager = {
    ssm_array: "none",
    ssm_value: "write"
  }
  public editWindow = {
    title: "",
    width: "50%"
  };

  protected rawValue;
  protected displayValue;
  protected parentValue;
  protected type; 

  @Watch("ssName")
  public watchSsName(newValue) {
    if (newValue) {
      this.smartFormArrayStructure = {};
      this.smartFormArrayValues = {};
      this.$refs.defaultGridContent.kendoWidget().dataSource.read();
      this.finalData.structureId = newValue;
    }
  }
  public onEditClick(e) { 
    // Fetch data from grid
    const row = $(e.target).closest("tr")[0];
    this.rawValue = row.children[2].innerText;
    this.displayValue = row.children[3].innerText;
    this.parentValue = row.children[1].textContent;
    // this.type = {type: 'abcd', typeFormat: 'efgh'}
    this.type = JSON.parse(row.children[4].textContent);
    this.finalData.fieldId = row.children[5].innerText;
    // * LAST
    this.finalData.value = this.getArrayDefaultValue(this.finalData.fieldId);
    this.showModal = true;
    this.showSmartForm();
  }
  /**
   * Prepare last SmartForm's data and create it
   */
  public showSmartForm() {
    // SmartForm preparation
    Object.assign(this.smartFormArrayValues, {
      "ssm_advanced_value": "",
      "ssm_inherited_value": `${this.parentValue}`,
      "ssm_type": "value",
      "ssm_value": `${this.displayValue}`,
    });

    // In case of enumerate, fetch his data
    let enumData = [];
    if(this.type.type === "enum") {
      enumData = this.getEnum(this.type.typeFormat);
    }

    // In case of array default value, manage array/field display
    if(this.smartFormArrayValues.hasOwnProperty(this.finalData.fieldId)) {
      this.smartFormDisplayManager.ssm_array = "write";
      this.smartFormDisplayManager.ssm_value = "none";
    } else {
      this.smartFormDisplayManager.ssm_array = "none";
      this.smartFormDisplayManager.ssm_value = "write";
    }

    // SmartForm Generation
    this.smartForm = {
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
            url: "#action/document.delete",
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
            url: "#action/ssmanager.save",
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
                    label: "Erase field"
                  }
                ],
                label: "Type",
                name: "ssm_type",
                type: "enum"
              },
              {
                display: "read",
                label: "Inherited",
                name: "ssm_inherited_value",
                type: "text",
              },
              {
                display: this.smartFormDisplayManager.ssm_value,
                enumItems: enumData,
                label: "Value",
                name: "ssm_value",
                type: `${this.type.type}`,
                typeFormat: `${this.type.typeFormat}`
              },
              {
                label: "Advanced value",
                name: "ssm_advanced_value",
                type: "longtext",
              },
              {
                // ToDo : Change index by parent's id name
                content: this.smartFormArrayStructure[Object.keys(this.smartFormArrayStructure)[0]],
                display: this.smartFormDisplayManager.ssm_array,
                label: "Array",
                name: "ssm_array",
                type: "array",
              }
            ],
            label: "Default value",
            name: "ssm_default_value",
            type: "frame"
          }
        ],
        title: "Edit value form",
        values: this.smartFormArrayValues
    }
    // console.log("ArrayStructure:", this.smartFormArrayStructure);
    // console.log("ArrayValue:", this.smartFormArrayValues);
    // console.log("===", this.smartFormArrayStructure[Object.keys(this.smartFormArrayStructure)[0]])
    // console.log(JSON.stringify(this.smartForm, null, 2))
  }
  public ssmFormReady() {
      this.$refs.ssmForm.hideSmartField("ssm_inherited_value");
      this.$refs.ssmForm.hideSmartField("ssm_advanced_value");
  }
  public ssmFormChange(e, smartStructure, smartField, values, index) {
    const smartForm = this.$refs.ssmForm;
    
    switch (smartForm.getValue("ssm_type").value) {
      case "inherited":
        smartForm.hideSmartField("ssm_advanced_value");
        smartForm.hideSmartField("ssm_value");
        smartForm.hideSmartField("ssm_array");
        smartForm.showSmartField("ssm_inherited_value");
        this.finalData.valueType =smartForm.getValue("ssm_type").value
        this.finalData.value = smartForm.getValue("ssm_inherited_value").value;
        break;
      case "value":
        smartForm.hideSmartField("ssm_advanced_value");
        smartForm.showSmartField("ssm_value");
        smartForm.showSmartField("ssm_array");
        smartForm.hideSmartField("ssm_inherited_value");
        this.finalData.value = this.getArrayDefaultValue(this.finalData.fieldId);
        this.finalData.valueType = smartForm.getValue("ssm_type").value;
        break;
      case "advanced_value":
        smartForm.showSmartField("ssm_advanced_value");
        smartForm.hideSmartField("ssm_value");
        smartForm.hideSmartField("ssm_array");
        smartForm.hideSmartField("ssm_inherited_value");
        this.finalData.value = smartForm.getValue("ssm_advanced_value").value;
        this.finalData.valueType = smartForm.getValue("ssm_type").value;
        break;
      case "no_value":
        smartForm.hideSmartField("ssm_advanced_value");
        smartForm.hideSmartField("ssm_value");
        smartForm.hideSmartField("ssm_array");
        smartForm.hideSmartField("ssm_inherited_value");
        this.finalData.valueType = smartForm.getValue("ssm_type").value;
        this.finalData.value = "";
        break;
    }
    if(this.finalData.fieldId === smartField.id){
      let unformattedValue = smartForm.getValue(this.finalData.fieldId);
      let formattedValue = []
      unformattedValue.forEach(element => {
        console.log(element)
        formattedValue.push(element.value);
      });
      this.finalData.value = JSON.parse(JSON.stringify(formattedValue));
    }
    console.log(this.finalData.value)
  }
  public formClickMenu(e, se, params) {
    switch (params.eventId) {
      case "document.delete":
        this.showModal = false;
        break;
      case "ssmanager.save":
        this.updateData(this.finalData);
        break;
    }
  }
  public displayData(colId) {
    return dataItem => {
      switch (colId) {
        case "type":
          if (dataItem[colId]) {
            return dataItem[colId];
          }
          break;
        default:
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
      return ""/* "None".fontcolor("ced4da") */;
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
      const defaultValues = response.data.data.defaultValues;
      const fields = response.data.data.fields;
      Object.keys(defaultValues).map(item => {
        const defaultVal = defaultValues[item];
        const field = fields.find(element => element.id === item);
        const parentField = fields.find(element => element.id === field.parentId);
        if (!this.unsupportedType.includes(field.simpletype)) {
          const {type, typeFormat} = this.formatType(field.simpletype, field.type);
          
          // Manage multiple values
          if(parentField.type === "array"){
            this.prepareSmartFormArray(defaultVal, field, parentField);
          }
          if (field) {
            // ToDo : Refactor as a function
            let rawValue: object = {};
            let displayValue: object = {};
            if(Array.isArray(defaultValues[item].result)) {
              for (let i = 0; i < defaultValues[item].result.length; i++) {
                const element = defaultValues[item].result[i];
                rawValue[i] = element.value;
                displayValue[i] = element.displayValue;
              }
            } else if (defaultValues[item].result instanceof Object) {
              if(defaultValues[item].result.value && defaultValues[item].result.displayValue)
              {
                rawValue = defaultValues[item].result.value;
                displayValue = defaultValues[item].result.displayValue;
              }
              else {
                rawValue = null;
                displayValue = null;
              }
            }
            result.push({
              displayValue,
              fieldId: item,
              label: this.formatLabel(field, fields),
              parentValue: defaultValues[item].parentConfigurationValue ? defaultValues[item].parentConfigurationValue : null,
              rawValue,
              type: JSON.stringify({type, typeFormat})
            });
          }
        }
      });
      return result;
    }
    return [];
  }
  protected prepareSmartFormArray(defaultVal, field, parentField) {
    // console.log("Field:", field);
    // console.log("ParentField:", parentField);
    // console.log("DefaultVal:", defaultVal.result);
    // console.log("Column", column);

    // Prepare data and structure
    const {type, typeFormat} = this.formatType(field.simpletype, field.type);
    const column = {
      "enumItems": [],
      "label": field.id,
      "name": field.id,
      "type": type,
      "typeFormat": typeFormat,
    };
    const values = {};
    // Define array if not already done
    if(!this.smartFormArrayStructure[parentField.id]){
      this.smartFormArrayStructure[parentField.id] = [];
    }
    if(!this.smartFormArrayValues[parentField.id]){
      this.smartFormArrayValues[parentField.id] = [];
    }
    // In case of enum type, fetch associate data
    if(field.simpletype === "enum") {
      column.enumItems = this.getEnum(typeFormat);
      column.typeFormat = "";
    }

    if(defaultVal && defaultVal.result && defaultVal.result.length > 0) {
      if(!values[field.id]) {
        values[field.id] = [];
      }
      defaultVal.result.forEach(element => {
        if(field.simpleType !== "enum"){
          if(element.value && element.displayValue){
            values[field.id].push(element.value)
          } else {
            values[field.id].push("")
          }
        }
      });
      // console.log("ArrayStructure", this.smartFormArrayStructure);
      // console.log("ArrayValue", this.smartFormArrayValues);
      this.smartFormArrayStructure[parentField.id].push(column);
      Object.assign(this.smartFormArrayValues, values);
    }
  }
  /**
   * Create the 'label' with parents architecture
   * @param field 
   * @param fieldsList 
   */
  protected formatLabel(field, fieldsList) {
    // TODO : Revoir avec la nouvelle structure de données
    // Construct label /w parent architecture
    let constructingLabel = [field.labeltext]
    if (field.parentId) {
      const parentField = fieldsList.find(element => element.id === field.parentId)
      constructingLabel.push(parentField.labeltext)
      this.formatLabel(parentField, fieldsList)
    }
    constructingLabel = constructingLabel.reverse();
    if(constructingLabel[constructingLabel.length -1] !== null)
    {
      return constructingLabel.join(" / ");
    }
    return constructingLabel[0];
  }
  protected formatType(simpleType, longType) {
    const type = simpleType;
    let typeFormat = '';
    if(type === 'docid' || type === 'enum'){
      typeFormat = longType.match(/"([^"]*)"/)[1];
    }
    return {type, typeFormat}
  }
  protected getDefaultValues(options) {
    this.$http
      .get(`/api/v2/admin/smart-structures/${this.ssName}/defaults/`, {
        params: options.data,
        paramsSerializer: kendo.jQuery.param
      })
      .then(response => {
        this.smartFormArrayStructure = {};
        this.smartFormArrayValues = {};
        options.success(response);
      })
      .catch(response => {
        options.error(response);
      });
    return [];
  }
  protected getEnum(enumerate){
    const returnVal = [];
    this.$http
    .get(`/api/v2/admin/enumdata/${enumerate}`)
    .then(response => {
      if (response.status === 200 && response.statusText === "OK") {
        response.data.data.forEach(element => {
          returnVal.push({
            key: element.key,
            label: element.label
          })
        })
      } else {
        throw new Error(response.data);
      }
    })
    .catch(response => {
      console.error(response);
    });

    return returnVal;
  }
  protected autoFilterCol(e) {
    e.element.addClass("k-textbox filter-input");
  }
  private getArrayDefaultValue(fieldId){
    const value = this.smartFormArrayValues.hasOwnProperty(fieldId)
    ? this.smartFormArrayValues[fieldId]
    : this.rawValue

    return value;
  }
  private updateData(data){
    console.log("BeforeSendData", JSON.stringify(data));
    const url = `/api/v2/admin/smart-structures/${data.structureId}/update/default/`;
    this.$http
      .put(url, {params: JSON.stringify(data)})
      .then(response => {
        this.$refs.defaultGridData.kendoDataSource.read();
        this.showModal = false;
      })
      .catch(response => {
        console.error("UpdateDataResError", response);
      })
  }
}

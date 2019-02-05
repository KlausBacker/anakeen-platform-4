import Vue from "vue";
import JSONEditor from "jsoneditor";
import "jsoneditor/dist/jsoneditor.min.css";
import {Prop} from "vue-property-decorator";
import "./ParameterEditor.types.ts";

declare var $;
declare var kendo;

export default class ParameterEditorController extends Vue {
  name: string = "admin-center-parameter-editor";
  @Prop(Object) editedItem = { value: "", name: "", type:"", initialValue: ""};
  @Prop(String) editRoute = "";
  jsonEditor: jsonEditor;
  jsonValue: Object = {};

  // Saved value sent by the server in response
  responseValue: String = "";

  // Memorize kendo widgets
  editionWindow: any = null;
  confirmationWindow: any = null;
  errorWindow: any = null;
  // Open the parameter editor with corresponding fields
    openEditor() {
      if (this.editedItem) {
        let kendoDropdown = null;
        // Init kendoDropDown if edited item is an enum
        if (this.parameterInputType === "enum") {
          kendoDropdown = $(".enum-drop-down", this.$el)
            .kendoDropDownList()
            .data("kendoDropDownList");
        }
        // Init kendoButtons of the parameter editor
        $(".modify-btn", this.$el)
          .kendoButton({
            icon: "check"
          })
          .data("kendoButton");

        $(".cancel-btn", this.$el)
          .kendoButton({
            icon: "close"
          })
          .data("kendoButton");

        // Init Json editor if edited item is a json
        if (
          this.parameterInputType === "json" &&
          ParameterEditorController.isJson(this.editedItem.value)
        ) {
          this.jsonValue = JSON.parse(this.editedItem.value);
          let divContainer = $(".json-editor", this.$el)[0]; // [0] to get DOM element
          this.jsonEditor = new JSONEditor(
            divContainer,
            {
              search: false,
              navigationBar: false,
              statusBar: false,
              history: false,
              modes: ["tree", "code"]
            },
            this.jsonValue
          );
        }

        this.editionWindow = $(".edition-window")
          .kendoWindow({
            modal: true,
            autoFocus: false,
            draggable: false,
            resizable: false,
            width: "60%",
            title: this.editedItem.name,
            visible: false,
            actions: ["Close"],

            activate: () => {
              if (this.parameterInputType === "enum") {
                if (kendoDropdown) {
                  kendoDropdown.focus();
                }
              } else {
                $(".parameter-new-value", this.$el).focus();
              }
              $(".edition-window")
                .data("kendoWindow")
                .title(this.editedItem.name);
            },

            close: () => {
              if (
                this.parameterInputType === "json" &&
                ParameterEditorController.isJson(this.editedItem.value)
              ) {
                this.jsonEditor.destroy();
              } else if (this.parameterInputType === "enum") {
                kendoDropdown.destroy();
              }

              this.$emit("closeEditor", this.responseValue);
            }
          })
          .data("kendoWindow");

        // Reset border color of fields
        $(".parameter-new-value", this.$el).css("border-color", "");

        this.editionWindow.center().open();
      }
    }
    // Close the parameter editor
    closeEditor() {
      this.editionWindow.close();
    }
    // Send request to modify parameter in server
    modifyParameter() {
      // Get new value to save depending on the parameter type
      let newValue;
      if (
        this.parameterInputType === "json" &&
        ParameterEditorController.isJson(this.editedItem.value)
      ) {
        newValue = JSON.stringify(this.jsonEditor.get());
      } else if (
        this.parameterInputType === "json" &&
        !ParameterEditorController.isJson($(".parameter-new-value", this.$el).val())
      ) {
        $(".parameter-new-value", this.$el).css("border-color", "red");
      } else if (this.parameterInputType === "enum") {
        newValue = $("select.enum-drop-down", this.$el).val();
      } else {
        $(".parameter-new-value", this.$el).css("border-color", "");
        newValue = $(".parameter-new-value", this.$el).val();
      }

      if (newValue) {
        // Send the request at edition route passed as a prop of the component
        this.$http
          .put(this.editRoute, {
            value: newValue
          })
          .then(response => {
            // Save the modified value sent by the server, and open a confirmation window
            this.responseValue = response.data.data.value;
            this.confirmationWindow = $(".confirmation-window")
              .kendoWindow({
                modal: true,
                draggable: false,
                resizable: false,
                title: "Parameter modified",
                width: "30%",
                visible: false,
                actions: []
              })
              .data("kendoWindow");

            this.confirmationWindow.center().open();

            // Init confirmation window close kendoButton
            $(".close-confirmation-btn").kendoButton({
              icon: "arrow-chevron-left"
            });
          })
          .catch(() => {
            // Open an error window to notify the user
            this.errorWindow = $(".error-window")
              .kendoWindow({
                modal: true,
                draggable: false,
                resizable: false,
                title: "Error",
                width: "30%",
                visible: false,
                actions: []
              })
              .data("kendoWindow");

            this.errorWindow.center().open();

            // Init error window close kendoButton
            $(".close-error-btn").kendoButton({
              icon: "arrow-chevron-left"
            });
          });
      }
    }

    // Close both confirmation and editor windows
    closeConfirmationAndEditor() {
      this.confirmationWindow.close();
      this.editionWindow.close();
    }

    // Close both error and editor windows
    closeErrorAndEditor() {
      this.errorWindow.close();
      this.editionWindow.close();
    }

    // Check if a string is a correct Json
    static isJson(stringValue) {
      try {
        JSON.parse(stringValue);
        return true;
      } catch (e) {
        return false;
      }
    }
    // Input type to use in template
    get parameterInputType() {
      let parameterType = this.editedItem.type.toLowerCase();
      if (
        parameterType === "number" ||
        parameterType === "integer" ||
        parameterType === "double"
      ) {
        return "number";
      } else if (parameterType.startsWith("enum")) {
        return "enum";
      } else {
        return parameterType;
      }
    }

    // Return the possible values of an enum parameter
    get enumPossibleValues() {
      if (this.parameterInputType === "enum") {
        let rawEnum = this.editedItem.type;
        rawEnum = rawEnum.slice(5);
        rawEnum = rawEnum.slice(0, -1);
        return rawEnum.split("|");
      }
    }

    // Value to display in the editor. If the parameter has no value, display initial system value (if possible)
    get inputSelectedValue() {
      if (this.editedItem.value) {
        return this.editedItem.value;
      } else if (this.editedItem.initialValue) {
        return this.editedItem.initialValue;
      } else {
        return "";
      }
    }
  updated() {
    // When updated (editedItem and editionRoute modified), open editor
    this.openEditor();
  }
  beforeDestroy() {
    if (this.confirmationWindow) {
      this.confirmationWindow.destroy();
    }
    if (this.errorWindow) {
      this.errorWindow.destroy();
    }
    if (this.editionWindow) {
      this.editionWindow.destroy();
    }
  }
  mounted() {
    this.openEditor();

    // When resizing the browser window, resize and center the edition window
    window.addEventListener("resize", () => {
      if (this.editionWindow) {
        this.editionWindow.setOptions({
          width: "60%"
        });
        this.editionWindow.center();
      }
    });
  }
};

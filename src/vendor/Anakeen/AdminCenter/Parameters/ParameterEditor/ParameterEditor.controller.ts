import Vue from "vue";
import Component from "vue-class-component";
import { Prop } from "vue-property-decorator";

declare var $;
declare var kendo;

@Component({
  name: "admin-center-parameter-editor"
})
export default class ParameterEditorController extends Vue {
  // Input type to use in template
  get parameterInputType() {
    const parameterType = this.editedItem.type.toLowerCase();
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
      this.inputIsJson = ParameterEditorController.isJson(
        this.editedItem.value
      );
      return this.editedItem.value;
    } else if (this.editedItem.initialValue) {
      return this.editedItem.initialValue;
    } else {
      return "";
    }
  }

  // Check if a string is a correct Json
  public static isJson(stringValue) {
    try {
      JSON.parse(stringValue);
      return true;
    } catch (e) {
      return false;
    }
  }
  @Prop({
    default: () => ({ value: "", name: "", type: "", initialValue: "" }),
    type: Object
  })
  public editedItem;
  @Prop({ type: String, default: "" })
  public editRoute;
  public jsonValue: object = {};
  // Saved value sent by the server in response
  public responseValue: string = "";
  // Memorize kendo widgets
  public editionWindow: any = null;
  public confirmationWindow: any = null;
  public errorWindow: any = null;
  public inputIsJson: boolean = false;
  public isNotJson: boolean = false;
  // Open the parameter editor with corresponding fields
  public openEditor() {
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

      this.editionWindow = $(".edition-window")
        .kendoWindow({
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
          autoFocus: false,
          close: () => {
            if (
              this.parameterInputType === "json" &&
              ParameterEditorController.isJson(this.editedItem.value)
            ) {
              // this.jsonEditor.destroy();
            } else if (this.parameterInputType === "enum") {
              kendoDropdown.destroy();
            }

            this.$emit("closeEditor", this.responseValue);
          },
          draggable: false,
          modal: true,
          resizable: false,
          title: this.editedItem.name,
          visible: false,
          width: "60%"
        })
        .data("kendoWindow");

      // Reset border color of fields
      $(".parameter-new-value", this.$el).css("border-color", "");

      this.editionWindow.center().open();
    }
  }
  // Close the parameter editor
  public closeEditor() {
    this.editionWindow.close();
  }
  // Send request to modify parameter in server
  public modifyParameter() {
    // Get new value to save depending on the parameter type
    let newValue;
    if (
      this.parameterInputType === "json" &&
      ParameterEditorController.isJson(this.editedItem.value)
    ) {
      newValue = $(".parameter-new-value", this.$el).val();
    } else if (this.parameterInputType === "enum") {
      newValue = $("select.enum-drop-down", this.$el).val();
    } else {
      $(".parameter-new-value", this.$el).css("border-color", "");
      newValue = $(".parameter-new-value", this.$el).val();
    }

    if (newValue) {
      if (
        this.parameterInputType === "json" &&
        !ParameterEditorController.isJson(newValue)
      ) {
        $(".parameter-new-value", this.$el).css("border-color", "red");
        this.isNotJson = true;
        return false;
      }
      this.isNotJson = false;
      // Send the request at edition route passed as a prop of the component
      this.$http
        .put(this.editRoute, {
          value: newValue
        })
        .then(response => {
          // Save the modified value sent by the server, and open a confirmation window
          console.log(response);
          this.responseValue = response.data.data.value;
          this.confirmationWindow = $(".confirmation-window")
            .kendoWindow({
              actions: [],
              draggable: false,
              modal: true,
              resizable: false,
              title: "Parameter modified",
              visible: false,
              width: "30%"
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
              actions: [],
              draggable: false,
              modal: true,
              resizable: false,
              title: "Error",
              visible: false,
              width: "30%"
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
  public closeConfirmationAndEditor() {
    this.confirmationWindow.close();
    this.editionWindow.close();
  }

  // Close both error and editor windows
  public closeErrorAndEditor() {
    this.errorWindow.close();
    this.editionWindow.close();
  }
  public updated() {
    // When updated (editedItem and editionRoute modified), open editor
    this.openEditor();
  }
  public beforeDestroy() {
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
  public mounted() {
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
}

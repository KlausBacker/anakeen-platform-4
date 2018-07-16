import JSONEditor from "jsoneditor";
import "jsoneditor/dist/jsoneditor.min.css";

export default {
  name: "admin-center-parameter-editor",

  props: {
    // Current edited item
    editedItem: {
      type: Object,
      default: {}
    },

    // Route url to modify the current edited parameter
    editRoute: {
      type: String,
      default: ""
    }
  },

  data() {
    return {
      // Json editor values
      jsonEditor: {},
      jsonValue: {},

      // Saved value sent by the server in response
      responseValue: ""
    };
  },

  methods: {
    // Open the parameter editor with corresponding fields
    openEditor() {
      if (this.editedItem) {
        let kendoDropdown = null;
        // Init kendoDropDown if edited item is an enum
        if (this.parameterInputType === "enum") {
          kendoDropdown = this.$(".enum-drop-down", this.$el)
            .kendoDropDownList()
            .data("kendoDropDownList");
        }
        // Init kendoButtons of the parameter editor
        this.$(".modify-btn", this.$el)
          .kendoButton({
            icon: "check"
          })
          .data("kendoButton");

        this.$(".cancel-btn", this.$el)
          .kendoButton({
            icon: "close"
          })
          .data("kendoButton");

        // Init Json editor if edited item is a json
        if (
          this.parameterInputType === "json" &&
          this.isJson(this.editedItem.value)
        ) {
          this.jsonValue = JSON.parse(this.editedItem.value);
          let divContainer = this.$(".json-editor", this.$el)[0]; // [0] to get DOM element
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

        const kendoWindow = this.$(".edition-window")
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
                this.$(".parameter-new-value", this.$el).focus();
              }
              this.$(".edition-window")
                .data("kendoWindow")
                .title(this.editedItem.name);
            },

            close: () => {
              if (
                this.parameterInputType === "json" &&
                this.isJson(this.editedItem.value)
              ) {
                this.jsonEditor.destroy();
              }

              this.$emit("closeEditor", this.responseValue);
            }
          })
          .data("kendoWindow");

        // Reset border color of fields
        this.$(".parameter-new-value", this.$el).css("border-color", "");

        kendoWindow.center().open();
      }
    },

    // Close the parameter editor
    closeEditor() {
      this.$(".edition-window")
        .data("kendoWindow")
        .close();
    },

    // Send request to modify parameter in server
    modifyParameter() {
      // Get new value to save depending on the parameter type
      let newValue;
      if (
        this.parameterInputType === "json" &&
        this.isJson(this.editedItem.value)
      ) {
        newValue = JSON.stringify(this.jsonEditor.get());
      } else if (
        this.parameterInputType === "json" &&
        !this.isJson(this.$(".parameter-new-value", this.$el).val())
      ) {
        this.$(".parameter-new-value", this.$el).css("border-color", "red");
      } else if (this.parameterInputType === "enum") {
        newValue = this.$("select.enum-drop-down", this.$el).val();
      } else {
        this.$(".parameter-new-value", this.$el).css("border-color", "");
        newValue = this.$(".parameter-new-value", this.$el).val();
      }

      if (newValue) {
        // Send the request at edition route passed as a prop of the component
        this.$ankApi
          .put(this.editRoute, {
            value: newValue
          })
          .then(response => {
            // Save the modified value sent by the server, and open a confirmation window
            this.responseValue = response.data.data.value;
            this.$(".confirmation-window")
              .kendoWindow({
                modal: true,
                draggable: false,
                resizable: false,
                title: "Parameter modified",
                width: "30%",
                visible: false,
                actions: []
              })
              .data("kendoWindow")
              .center()
              .open();

            // Init confirmation window close kendoButton
            this.$(".close-confirmation-btn").kendoButton({
              icon: "arrow-chevron-left"
            });
          })
          .catch(() => {
            // Open an error window to notify the user
            this.$(".error-window")
              .kendoWindow({
                modal: true,
                draggable: false,
                resizable: false,
                title: "Error",
                width: "30%",
                visible: false,
                actions: []
              })
              .data("kendoWindow")
              .center()
              .open();

            // Init error window close kendoButton
            this.$(".close-error-btn").kendoButton({
              icon: "arrow-chevron-left"
            });
          });
      }
    },

    // Close both confirmation and editor windows
    closeConfirmationAndEditor() {
      this.$(".confirmation-window")
        .data("kendoWindow")
        .close();
      this.$(".edition-window")
        .data("kendoWindow")
        .close();
    },

    // Close both error and editor windows
    closeErrorAndEditor() {
      this.$(".error-window")
        .data("kendoWindow")
        .close();
      this.$(".edition-window")
        .data("kendoWindow")
        .close();
    },

    // Check if a string is a correct Json
    isJson(stringValue) {
      try {
        JSON.parse(stringValue);
        return true;
      } catch (e) {
        return false;
      }
    }
  },

  computed: {
    // Input type to use in template
    parameterInputType() {
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
    },

    // Return the possible values of an enum parameter
    enumPossibleValues() {
      if (this.parameterInputType === "enum") {
        let rawEnum = this.editedItem.type;
        rawEnum = rawEnum.slice(5);
        rawEnum = rawEnum.slice(0, -1);
        return rawEnum.split("|");
      }
    },

    // Value to display in the editor. If the parameter has no value, display initial system value (if possible)
    inputSelectedValue() {
      if (this.editedItem.value) {
        return this.editedItem.value;
      } else if (this.editedItem.initialValue) {
        return this.editedItem.initialValue;
      } else {
        return "";
      }
    }
  },

  updated() {
    // When updated (editedItem and editionRoute modified), open editor
    this.openEditor();
  },

  mounted() {
    this.openEditor();

    // When resizing the browser window, resize and center the edition window
    window.addEventListener("resize", () => {
      let editionWindow = this.$(".edition-window").data("kendoWindow");
      if (editionWindow) {
        editionWindow.setOptions({
          width: "60%"
        });
        editionWindow.center();
      }
    });
  }
};

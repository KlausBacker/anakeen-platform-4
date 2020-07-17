import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm.esm";

export default {
  name: "element-logical-name",
  components: {
    "ank-smart-form": () => {
      return AnkSmartForm;
    }
  },
  props: {
    properties: {}
  },
  data: () => ({
    newLogicalName: "",
    title: "",
    actualValue: ""
  }),
  watch: {
    properties() {
      this.newLogicalName = "";
      this.title = this.properties.title;
      this.actualValue = this.properties.name;
    }
  },
  computed: {
    smartFormData() {
      return {
        renderOptions: {
          types: {
            menu: {
              labelPosition: "right"
            }
          }
        },
        menu: [
          {
            beforeContent: '<div class="fa fa-save" />',
            iconUrl: "",
            id: "submit",
            important: false,
            label: "Save value",
            target: "_self",
            type: "itemMenu",
            url: "#action/param.save"
          },
          {
            beforeContent: '<div class="fa fa-trash" />',
            iconUrl: "",
            id: "delete",
            important: false,
            label: "Delete logical name value",
            target: "_self",
            type: "itemMenu",
            url: "#action/param.delete"
          }
        ],
        structure: [
          {
            label: "Logical name",
            name: "my_fr_ident",
            type: "frame",
            icon: "/api/v2/images/assets/sizes/24x24c/se-iuser.png",
            url: "#action/param.cancel",
            content: [
              {
                label: "Value",
                name: "logical_name",
                type: "text",
                display: "write"
              }
            ]
          }
        ],
        title: this.title,
        type: "",
        force: true,
        values: {
          logical_name: this.actualValue
        }
      };
    }
  },
  created() {
    this.smartFormData.values.logical_name = this.properties.name;
    this.smartFormData.title = this.properties.title;
  },
  methods: {
    onChangeLogicalName: function(event, smartElement, smartField, values) {
      this.newLogicalName = values.current.values;
    },

    menuClick: function(event, smartElement, params) {
      const smartForm = this.$refs.formLogicalName;
      if (params.eventId === "param.save") {
        let modifications = smartForm.getValue("logical_name").value;
        this.$http
          .put(`/api/v2/devel/smart-elements/logical-name/${this.properties.initid}`, { newLogicalName: modifications })
          .then(() => {
            this.$emit("refresh");
          })
          .catch(err => {
            console.error(err);
          });
      }
      if (params.eventId === "param.delete") {
        this.$http
          .delete(`/api/v2/devel/smart-elements/logical-name/${this.properties.initid}`)
          .then(() => {
            this.actualValue = "";
            this.$refs.formLogicalName.setValue("logical_name", {
              value: ""
            });
            this.$emit("refresh");
          })
          .catch(err => {
            console.error(err);
          });
      }
    }
  }
};

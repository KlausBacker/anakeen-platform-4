import "@progress/kendo-ui/js/kendo.treelist";
import "@progress/kendo-ui/js/kendo.columnmenu";
import { checksum, convertAclToKendoStyle } from "./utils/group";

export default {
  name: "ank-dev-profile",
  props: {
    profileId: Number,
    defaultColumns: {
      type: Array,
      default: () => ["create", "icreate", "view", "edit", "delete"]
    }
  },
  data: () => ({
    title: "",
    id: "",
    name: ""
  }),
  created() {
    this.privateScope = {
      initTreeView: () => {
        this.$http
          .get(
            `/api/v2/devel/security/profile/${
              this.profileId
            }/accesses/?group=all`
          )
          .then(content => {
            if (!content.data.success) {
              return this.$emit("error", content.data.messages.join(" "));
            }
            const data = content.data.data;
            const accesses = convertAclToKendoStyle(data.accesses);
            this.title = data.properties.title;
            this.name = data.properties.name;
            this.id = data.properties.id;
            //Recreate tree

            const lineRender = (column, callback) => {
              return currentLine => {
                return callback(column, currentLine);
              };
            };
            const columns = data.properties.acl.map(currentElement => {
              return {
                field: currentElement,
                hidden: !this.defaultColumns.reduce(
                  (accumulator, currentColumn) => {
                    if (accumulator) {
                      return true;
                    }
                    return currentColumn === currentElement;
                  },
                  false
                ),
                template: lineRender(currentElement, (column, currentLine) => {
                  switch (currentLine[column]) {
                    case "set":
                      return `<span class="k-icon k-i-kpi-status-open" style="color: greenyellow;"></span>`;
                    case "inherit":
                      return `<span class="k-icon k-i-kpi-status-open" style="color: grey;"></span>`;
                    default:
                      return "";
                  }
                })
              };
            });
            //Add standard data
            let dataSource = [
              {
                id: checksum("fields"),
                title: "Dynamic fields",
                parentId: null
              },
              { id: checksum("role"), title: "Roles", parentId: null },
              { id: checksum("group"), title: "Groupes", parentId: null },
              { id: checksum("users"), title: "Users", parentId: null }
            ];
            dataSource = [
              ...dataSource,
              ...accesses.map(currentElement => {
                return {
                  ...currentElement,
                  ...{
                    title: currentElement.account.reference,
                    parentId:
                      currentElement.parentId ||
                      checksum(currentElement.account.type)
                  }
                };
              })
            ];
            columns.unshift({ field: "title" });
            $(this.$refs.profileTreeList).kendoTreeList({
              toolbar: kendo.template(
                `<label for="enableChk"><input type="checkbox" />Show all elements</label>`
              ),
              columnMenu: true,
              columns,
              dataSource
            });
          })
          .catch(err => {
            console.error(err);
            this.$emit("error", err);
          });
      }
    };
  },
  mounted() {
    this.privateScope.initTreeView();
  }
};

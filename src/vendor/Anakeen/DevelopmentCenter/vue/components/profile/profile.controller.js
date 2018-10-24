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
    name: "",
    extendedView: false
  }),
  created() {
    this.privateScope = {
      initDataSource: () => {
        return new kendo.data.TreeListDataSource({
          transport: {
            read: options => {
              this.$http
                .get(this.privateScope.getCurrentUrl())
                .then(content => {
                  if (!content.data.success) {
                    this.$emit("error", content.data.messages.join(" "));
                    throw Error(content.data.messages.join(" "));
                  }
                  const data = content.data.data;
                  const accesses = convertAclToKendoStyle(data.accesses);
                  this.title = data.properties.title;
                  this.name = data.properties.name;
                  this.id = data.properties.id;
                  //Add standard data
                  const nbElementsByCat = data.accesses.reduce(
                    (acc, currentAccess) => {
                      acc[currentAccess.account.type] =
                        acc[currentAccess.account.type] + 1;
                      return acc;
                    },
                    {
                      field: 0,
                      role: 0,
                      group: 0,
                      user: 0
                    }
                  );
                  let dataSourceContent = [
                    {
                      id: checksum("field"),
                      title: `Fields : ${nbElementsByCat.field}`,
                      parentId: null,
                      expanded: true
                    },
                    {
                      id: checksum("role"),
                      title: `Roles : ${nbElementsByCat.role}`,
                      parentId: null,
                      expanded: true
                    },
                    {
                      id: checksum("group"),
                      title: `Groups : ${nbElementsByCat.group}`,
                      parentId: null
                    },
                    {
                      id: checksum("user"),
                      title: `Users : ${nbElementsByCat.user}`,
                      parentId: null
                    }
                  ];
                  options.success([
                    ...dataSourceContent,
                    ...accesses.map(currentElement => {
                      return {
                        ...currentElement,
                        ...{
                          parentId:
                            currentElement.parentId ||
                            checksum(currentElement.account.type)
                        }
                      };
                    })
                  ]);
                })
                .catch(err => {
                  console.error(err);
                  //this.emit("error", err);
                  options.fail(err);
                });
            }
          }
        });
      },
      getCurrentUrl: () => {
        if (this.extendedView) {
          return `/api/v2/devel/security/profile/${
            this.profileId
          }/accesses/?group=all&role=all`;
        }
        return `/api/v2/devel/security/profile/${this.profileId}/accesses/`;
      },
      updateDataSource: () => {
        this.dataSource.read();
      },
      initTreeView: () => {
        this.$http
          .get(this.privateScope.getCurrentUrl())
          .then(content => {
            if (!content.data.success) {
              return this.$emit("error", content.data.messages.join(" "));
            }
            const data = content.data.data;
            this.title = data.properties.title;
            this.name = data.properties.name;
            this.id = data.properties.id;

            const lineRender = (column, callback) => {
              return currentLine => {
                return callback(column, currentLine);
              };
            };
            const columns = data.properties.acl.map(currentElement => {
              return {
                field: currentElement,
                attributes: {
                  class: "rightColumn"
                },
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
                      return `<span class="k-icon k-i-kpi-status-open right-set"></span>`;
                    case "inherit":
                      return `<span class="k-icon k-i-kpi-status-open right-inherited"></span>`;
                    default:
                      return "";
                  }
                })
              };
            });
            this.dataSource.bind("change", () => {
              treeList.data("kendoTreeList").autoFitColumn("title");
            });
            const treeList = $(this.$refs.profileTreeList).kendoTreeList({
              resizable: true,
              columnMenu: true,
              columns: [
                {
                  field: "title",
                  title: "Refs",
                  template: currentElement => {
                    if (currentElement.title) {
                      return currentElement.title;
                    }
                    return `<span title="${currentElement.accountId}">${
                      currentElement.account.reference
                    }</span>`;
                  }
                },
                {
                  field: "Rights",
                  columns
                }
              ],
              expand: () => {
                treeList.data("kendoTreeList").autoFitColumn("title");
              },
              collapse: () => {
                treeList.data("kendoTreeList").autoFitColumn("title");
              },
              dataBound: () => {
                treeList.data("kendoTreeList").autoFitColumn("title");
              },
              dataSource: this.dataSource
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
    this.dataSource = this.privateScope.initDataSource();
    this.privateScope.initTreeView();
  },
  methods: {
    updateGrid: function() {
      this.privateScope.updateDataSource();
    }
  }
};

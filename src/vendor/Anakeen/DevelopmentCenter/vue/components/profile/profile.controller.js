import "@progress/kendo-ui/js/kendo.treelist";
import "@progress/kendo-ui/js/kendo.columnmenu";
import { checksum, convertAclToKendoStyle } from "./utils/group";

export default {
  name: "ank-dev-profile",
  props: {
    profileId: [Number, String],
    defaultColumns: {
      type: Array,
      default: () => ["create", "icreate", "view", "edit", "delete"]
    },
    onlyExtendedAcls: false,
    detachable: false,
    onRefProfileClickCallback: {
      type: Function,
      default: refProfile => {
        window.open(
          `/api/v2/devels/security/profile/${refProfile.name ||
            refProfile.id}.html`
        );
      }
    }
  },
  data: () => ({
    title: "",
    id: "",
    name: "",
    refProfile: null,
    displayAllElements: false
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
                  //Count entry by cat
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
                  //Init source data with standard cat
                  let dataSourceContent = [
                    {
                      id: checksum("field"),
                      title: `Fields : <span class="badge ${
                        nbElementsByCat.field === 0
                          ? "badge-light"
                          : "badge-primary"
                      }">${nbElementsByCat.field}</span>`,
                      parentId: null,
                      expanded: true
                    },
                    {
                      id: checksum("role"),
                      title: `Roles : <span class="badge ${
                        nbElementsByCat.role === 0
                          ? "badge-light"
                          : "badge-primary"
                      }">${nbElementsByCat.role}</span>`,
                      parentId: null,
                      expanded: true
                    },
                    {
                      id: checksum("group"),
                      title: `Groups : <span class="groupElement badge account-type-group ${
                        nbElementsByCat.group === 0
                          ? "badge-light"
                          : "badge-primary"
                      }">${nbElementsByCat.group}</span> 
<button class="k-button k-button-icon foldGroups"><span class="k-icon k-i-minus"></span></button> 
<button class="k-button k-button-icon unfoldGroups"><span class="k-icon k-i-plus"></span></button>`,
                      parentId: null
                    },
                    {
                      id: checksum("user"),
                      title: `Users : <span class="badge ${
                        nbElementsByCat.user === 0
                          ? "badge-light"
                          : "badge-primary"
                      }">${nbElementsByCat.user}</span>`,
                      parentId: null
                    }
                  ];
                  //Add other elements
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
                  options.error(err);
                });
            }
          }
        });
      },
      getCurrentUrl: () => {
        if (this.displayAllElements) {
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
          .get(
            `/api/v2/devel/security/profile/${
              this.profileId
            }/accesses/?acls=only`
          )
          .then(content => {
            if (!content.data.success) {
              return this.$emit("error", content.data.messages.join(" "));
            }
            const data = content.data.data;
            this.title = data.properties.title;
            this.name = data.properties.name;
            this.id = data.properties.id;
            this.refProfile = data.properties.reference;

            const lineRender = (column, callback) => {
              return currentLine => {
                return callback(column, currentLine);
              };
            };
            const columns = data.properties.acls.map(currentElement => {
              return {
                field: `acls.${currentElement.name}`,
                title: `${currentElement.name}`,
                attributes: {
                  class: "rightColumn"
                },
                width: "7rem",
                hidden: !this.defaultColumns.reduce(
                  (accumulator, currentColumn) => {
                    if (accumulator) {
                      return true;
                    }
                    if (this.onlyExtendedAcls && currentElement.extended) {
                      return true;
                    }
                    if (this.onlyExtendedAcls) {
                      return false;
                    }
                    return currentColumn === currentElement.name;
                  },
                  false
                ),
                template: lineRender(
                  currentElement.name,
                  (column, currentLine) => {
                    if (!currentLine.acls) {
                      return "";
                    }
                    switch (currentLine.acls[column]) {
                      case "set":
                        return `<span class="k-icon k-i-kpi-status-open right-set"></span>`;
                      case "inherit":
                        return `<span class="k-icon k-i-kpi-status-open right-inherited"></span>`;
                      default:
                        return "";
                    }
                  }
                )
              };
            });
            this.dataSource.bind("change", () => {
              treeList.data("kendoTreeList").autoFitColumn("title");
            });
            const treeList = $(this.$refs.profileTreeList).kendoTreeList({
              columnMenu: true,
              height: "100%",
              columns: [
                {
                  field: "title",
                  title: "Refs",
                  template: currentElement => {
                    if (currentElement.title) {
                      return currentElement.title;
                    }
                    return `<span title="${
                      currentElement.accountId
                    }" class="account-type-${currentElement.account.type}">${
                      currentElement.account.reference
                    }</span>`;
                  }
                },
                {
                  field: "Acls",
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
            treeList.on("click", ".foldGroups", () => {
              treeList
                .find(".account-type-group")
                .toArray()
                .forEach(currentElement => {
                  treeList
                    .data("kendoTreeList")
                    .collapse($(currentElement).closest(`[role="row"]`));
                });
              treeList.data("kendoTreeList").autoFitColumn("title");
            });
            treeList.on("click", ".unfoldGroups", () => {
              treeList
                .find(".account-type-group")
                .toArray()
                .forEach(currentElement => {
                  treeList
                    .data("kendoTreeList")
                    .expand($(currentElement).closest(`[role="row"]`));
                });
              treeList.data("kendoTreeList").autoFitColumn("title");
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
    },
    onClickRefProfile() {
      if (typeof this.onRefProfileClickCallback === "function") {
        this.onRefProfileClickCallback.call(null, this.refProfile);
      }
    },
    onDetachProfile() {
      if (window.open) {
        window.open(`/api/v2/devels/security/profile/${this.profileId}.html`);
      }
    }
  }
};

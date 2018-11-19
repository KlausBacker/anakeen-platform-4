import AnkTreeList from "devComponents/SSTreeList/SSTreeList.vue";
import { getTreeListData } from "./utils/treeUtils";

const escapeColor = color => {
  if (color) {
    return color.replace("#", "\\#");
  }
  return "transparent";
};

export default {
  props: {
    workflowContentUrl: {
      type: String,
      default: "/api/v2/devel/security/workflows/<workflow>"
    },
    workflowConfigUrl: {
      type: String,
      default: "/api/v2/devel/smart/workflows/<workflow>"
    },
    wid: {
      type: [Number, String],
      default: 0
    },
    defaultAcls: {
      type: Array,
      default: () => ["view", "edit", "delete"]
    },
    onlyExtendedAcls: false,
    detachable: false
  },
  components: {
    AnkTreeList
  },
  computed: {
    resolvedWorkflowConfig() {
      const baseUrl = this.workflowConfigUrl;
      if (baseUrl.indexOf("<workflow>") === -1) {
        return baseUrl;
      }
      return baseUrl.replace("<workflow>", this.wid);
    },
    resolvedWorkflowContent() {
      const baseUrl =
        this.workflowContentUrl + (this.completeList ? "?complete=true" : "");
      if (baseUrl.indexOf("<workflow>") === -1) {
        return baseUrl;
      }
      return baseUrl.replace("<workflow>", this.wid);
    },
    firstColumn() {
      return {
        name: "account",
        label: "Account",
        hidden: false
      };
    }
  },
  beforeCreate() {
    this.privateMethods = {
      getRightsTemplate: rights => {
        if (rights) {
          return (
            "<div class='btn-group'>" +
            Object.keys(this.viewedAcls)
              .map(acl => {
                let hide = "";
                if (!this.viewedAcls[acl]) {
                  hide = "style='display: none;'";
                }
                switch (rights[acl]) {
                  case "set":
                    return `<button class="btn btn-primary" ${hide} data-acl="${acl}">${acl
                      .charAt(0)
                      .toUpperCase()}</button>`;
                  case "inherit":
                  case undefined:
                    return `<button class="btn btn-secondary" ${hide} data-acl="${acl}">${acl
                      .charAt(0)
                      .toUpperCase()}</button>`;
                  default:
                    return "";
                }
              })
              .join("") +
            "</div>"
          );
        }
      },
      getHeaderTemplate: column => {
        if (column.name !== "account") {
          let stepColor = column.color;
          return `<div class="step-header step--${column.name}">
                    <span class="step-header-color" style="background: ${escapeColor(
                      stepColor
                    )}"></span>
                    <span class="step-header-label">${column.name}</span>
                </div>`;
        } else {
          return `<div class="account-header">${column.label ||
            column.name} <div class="show-all-switch switch-container">
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider round"></span>
                            </label>
                            <label class="switch-label" for="extendedView">
                                <span>Show all</span>
                            </label>
                        </div></div>`;
        }
      },
      getCellTemplate: columnId => {
        return dataItem => {
          const currentValue = dataItem[columnId];
          if (currentValue) {
            if (columnId === "account") {
              if (dataItem.accountType) {
                return currentValue.type;
              }
              return currentValue.reference;
            } else if (typeof currentValue === "object") {
              return this.privateMethods.getRightsTemplate(currentValue);
            }
          }
          return "";
        };
      },
      getColumns: steps => {
        if (Array.isArray(steps)) {
          const columns = [this.firstColumn];
          steps.forEach(step => {
            const column = {
              name: step.id,
              label: step.label,
              color: step.color,
              profil: step.profil,
              fall: step.fall,
              hidden: false
            };
            columns.push(column);
          });
          return columns;
        }
        return [];
      },
      createCheckboxHeaderRow: treeList => {
        treeList.thead.append(
          `<tr role="row">
              <th class="k-header" >
                <div class="filter-clearable" style="position:relative;">
                  <input class="k-textbox filter" placeholder="Filter..." type="text"/>
                  <i class="material-icons filter-clear">close</i>
                </div>
              </th>
              <th class="k-header">
                ${this.acls
                  .map(c => {
                    return `<input type="checkbox" id="${
                      this.wid
                    }-${c}" class="k-checkbox check-acl check-acl--${c}" data-acl="${c}" checked="${
                      this.viewedAcls[c]
                    }"/><label class="k-checkbox-label" for="${
                      this.wid
                    }-${c}">${c.charAt(0).toUpperCase()}${c.substring(
                      1
                    )}</label>`;
                  })
                  .join("")}

                <button class="k-button" title="View all rights">+</button>
              </th>
            </tr>`
        );
        this.$(treeList.thead).on("change", ".check-acl", event => {
          const checked = event.currentTarget.checked;
          const acl = event.currentTarget.dataset.acl;
          if (checked) {
            this.viewedAcls[acl] = true;
            this.$("[data-acl=" + acl + "]", treeList.table).show();
          } else {
            this.viewedAcls[acl] = false;
            this.$("[data-acl=" + acl + "]", treeList.table).hide();
          }
        });
        this.$(treeList.thead).on(
          "change",
          ".show-all-switch input[type=checkbox]",
          event => {
            const checked = event.currentTarget.checked;
            if (checked) {
              this.completeList = true;
            } else {
              this.completeList = false;
            }
          }
        );
        this.$(treeList.thead).on("change", "input.filter", event => {
          const value = event.currentTarget.value;
          const dataSource = treeList.dataSource;
          if (value) {
            dataSource.filter({
              logic: "or",
              filters: [
                {
                  field: "accountLabel",
                  operator: "contains",
                  value: value
                }
              ]
            });
          } else {
            dataSource.filter(null);
          }
        });
        this.$(treeList.thead).on("click", ".filter-clear", () => {
          const dataSource = treeList.dataSource;
          this.$(treeList.thead)
            .find("input.filter")
            .val("");
          dataSource.filter(null);
        });
      },
      createProfilesHeaderRow: treeList => {
        const columns = this.treeColumns.filter(c => c.name !== "account");
        treeList.thead.append(
          `<tr role="row">
              <th class="k-header">
                <b>Profile</b>
              </th>
              ${columns
                .map(c => {
                  return `<th class="k-header profile-header">
                        <a data-role="develRouterLink" href="/devel/security/profiles/${
                          c.profil
                        }" >${c.profil || ""}</a>
                        </th>`;
                })
                .join("")}
            </tr>`
        );
      },
      createFallHeaderRow: treeList => {
        const columns = this.treeColumns.filter(c => c.name !== "account");
        treeList.thead.append(
          `<tr role="row">
              <th class="k-header">
                <b>Field Access</b>
              </th>
              ${columns
                .map(c => {
                  return `<th class="k-header fall-header">
                        <a data-role="develRouterLink" href="/devel/security/workflows/${
                          this.wid
                        }/accesses/${c.fall}" >${c.fall || ""}</a>
                        </th>`;
                })
                .join("")}
            </tr>`
        );
      },
      customizeTree: () => {
        const treeList = this.$refs.ankTreeList.$refs.ssTreelist.kendoWidget();
        this.privateMethods.createProfilesHeaderRow(treeList);
        this.privateMethods.createFallHeaderRow(treeList);
        this.privateMethods.createCheckboxHeaderRow(treeList);
        treeList.refresh();
      },
      fetchColumns: () => {
        this.$http
          .get(this.resolvedWorkflowConfig)
          .then(response => {
            if (response && response.data && response.data.data) {
              const steps = response.data.data.steps;
              this.treeColumns = this.privateMethods.getColumns(steps);
              this.$emit("workflow-rights-config-ready");
            }
          })
          .catch(err => {
            console.error(err);
            throw err;
          });
      },
      parseData: data => {
        return getTreeListData(data.steps);
      }
    };
  },
  created() {
    this.$on("workflow-rights-config-ready", () => {
      this.treeConfigReady = true;
    });
    this.privateMethods.fetchColumns();
  },
  mounted() {
    const prepareTree = () => {
      this.$nextTick(() => {
        this.privateMethods.customizeTree();
      });
    };
    if (this.treeConfigReady) {
      prepareTree();
    } else {
      this.$once("workflow-rights-config-ready", prepareTree);
    }
  },
  data() {
    return {
      model: {
        id: "virtualId",
        parentId: "parentId",
        expanded: true
      },
      completeList: false,
      treeColumns: [],
      treeConfigReady: false,
      treeContentReady: false,
      acls: this.defaultAcls,
      viewedAcls: this.defaultAcls.reduce((acc, curr) => {
        acc[curr] = true;
        return acc;
      }, {})
    };
  }
};

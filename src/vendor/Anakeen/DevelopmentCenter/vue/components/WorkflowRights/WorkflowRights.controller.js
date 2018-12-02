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
    detachable: {
      type: Boolean,
      default: false
    },
    visualizeGraph: {
      type: Boolean,
      default: false
    },
    detachUrl: {
      type: String,
      default: "/api/v2/devels/security/workflow/<identifier>.html"
    },
    graphUrl: {
      type: String,
      default: "/api/v2/devel/ui/workflows/image/<identifier>.svg"
    },
    displayField: {
      type: String,
      default: "name"
    }
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
    resolveDetachUrl() {
      const baseUrl = this.detachUrl;
      if (baseUrl.indexOf("<identifier>") === -1) {
        return baseUrl;
      }
      return baseUrl.replace("<identifier>", this.wid);
    },
    resolveGraphUrl() {
      const baseUrl = this.graphUrl;
      if (baseUrl.indexOf("<identifier>") === -1) {
        return baseUrl;
      }
      return baseUrl.replace("<identifier>", this.wid);
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
          const primaryAcls = [];
          const secondaryAcls = [];
          Object.keys(this.acls).forEach(acl => {
            let hide = "";
            if (!this.acls[acl].visible) {
              hide = "style='display: none;'";
            }
            let template;
            switch (rights[acl]) {
              case "set":
                template = `<button class="btn acl-set" ${hide} data-acl="${acl}">${acl
                  .charAt(0)
                  .toUpperCase()}</button>`;
                break;
              case "inherit":
                template = `<button class="btn acl-inherited" ${hide} data-acl="${acl}">${acl
                  .charAt(0)
                  .toUpperCase()}</button>`;
                break;
              default:
                template = `<button class="btn acl-disabled" ${hide} data-acl="${acl}">${acl
                  .charAt(0)
                  .toUpperCase()}</button>`;
                break;
            }
            if (this.acls[acl].default) {
              primaryAcls.push(template);
            } else {
              secondaryAcls.push(template);
            }
          });

          return `<div class="wfl-acl-rights-cell">
                <div class="btn-group primary-acls">
                    ${primaryAcls.join("")}
                </div>
                <div class="btn-group secondary-acls">
                    ${secondaryAcls.join("")}
                </div>
            </div>`;
        }
      },
      getHeaderTemplate: column => {
        if (column.name !== "account") {
          let stepColor = column.color;
          return `<div class="step-header step--${column.name}">
                    <span class="step-header-color" style="background: ${escapeColor(
                      stepColor
                    )}"></span>
                    <span class="step-header-label">${this.getLabel(
                      column
                    )}</span>
                </div>`;
        } else {
          return `<div class="account-header">
                <div class="show-all-switch switch-container">
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider round"></span>
                            </label>
                            <label class="switch-label" for="extendedView">
                                <span>Show all</span>
                            </label>
                        </div>
                 </div>`;
        }
      },
      getCellTemplate: columnId => {
        return dataItem => {
          const currentValue = dataItem[columnId];
          if (currentValue) {
            if (columnId === "account") {
              return dataItem.accountLabel;
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
              <th class="k-header acls-checkbox-header" colspan="${treeList
                .columns.length - 1}">
                <span>Rights to display :</span>
                <div class="acls-checkbox">
                    <div class="primary-acls">
                      ${this.defaultAcls
                        .map(c => {
                          let checked = "";
                          if (this.acls[c].visible) {
                            checked = "checked='checked'";
                          }
                          return `<input type="checkbox" id="${
                            this.wid
                          }-${c}" class="k-checkbox check-acl check-acl--${c}" data-acl="${c}" ${checked}/><label class="k-checkbox-label" for="${
                            this.wid
                          }-${c}">${this.getLabel(
                            this.acls[c],
                            true,
                            true
                          )}</label>`;
                        })
                        .join("")}
                    </div>
                    <div class="secondary-acls">
                      ${Object.values(this.acls)
                        .filter(a => !a.default)
                        .map(c => {
                          let checked = "";
                          if (c.visible) {
                            checked = "checked='checked'";
                          }
                          return `<input type="checkbox" id="${this.wid}-${
                            c.name
                          }" class="k-checkbox check-acl check-acl--${
                            c.name
                          }" data-acl="${
                            c.name
                          }" ${checked}/><label class="k-checkbox-label" for="${
                            this.wid
                          }-${c.name}">${this.getLabel(c, true, true)}</label>`;
                        })
                        .join("")}
                    </div>
                  </div>
                 <button class="k-button k-button-icon view-all-acls" title="View all rights">
                    <i class="k-icon k-i-plus"></i>
                 </button>   
              </th>
            </tr>`
        );
        this.$(treeList.thead).on("change", ".check-acl", event => {
          const checked = event.currentTarget.checked;
          const acl = event.currentTarget.dataset.acl;
          if (checked) {
            this.acls[acl] = true;
            this.$("[data-acl=" + acl + "]", treeList.table).show();
          } else {
            this.acls[acl] = false;
            this.$("[data-acl=" + acl + "]", treeList.table).hide();
          }
        });
        this.$(treeList.thead).on("click", "button.view-all-acls", event => {
          const $button = this.$(event.currentTarget);
          $button.toggleClass("all-acls-visible");
          $button
            .prev()
            .find(".secondary-acls")
            .toggleClass("all-acls-visible");
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
                        <a data-role="develRouterLink" href="/devel/security/fieldAccess/${
                          c.fall
                        }/config" >${c.fall || ""}</a>
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
        treeList.bind("expand", () => {
          treeList.autoFitColumn("account");
        });
        treeList.bind("collapse", () => {
          treeList.autoFitColumn("account");
        });
        treeList.bind("dataBound", () => {
          treeList.autoFitColumn("account");
        });
        treeList.dataSource.sort({
          field: "accountLabel",
          dir: "asc",
          compare: (a, b) => {
            const labelA = a.accountLabel;
            const labelB = b.accountLabel;
            if (labelA === labelB) {
              return 0;
            }
            if (labelA === "Fields") return -1;
            if (labelB === "Fields") return 1;
            if (labelA === "Groups") return 1;
            if (labelB === "Groups") return -1;
            return 0;
          }
        });
      },
      fetchColumns: () => {
        this.$http
          .get(this.resolvedWorkflowConfig)
          .then(response => {
            if (response && response.data && response.data.data) {
              this.graphProperties = response.data.data.properties;
              const steps = response.data.data.steps;
              this.treeColumns = this.privateMethods.getColumns(steps);
              if (steps.length) {
                this.acls = steps[0].acls.reduce((acc, acl) => {
                  acc[acl.name] = acl;
                  if (this.defaultAcls.indexOf(acl.name) > -1) {
                    acc[acl.name].default = true;
                    acc[acl.name].visible = true;
                  } else {
                    acc[acl.name].default = false;
                    acc[acl.name].visible = false;
                  }
                  return acc;
                }, {});
              }
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
  methods: {
    onVisualizeGraph() {
      window.open(this.resolveGraphUrl);
    },
    onDetachComponent() {
      window.open(this.resolveDetachUrl);
    },
    getLabel(element, capitalize = false, firstBold = false) {
      let label = "";
      if (element) {
        if (typeof element === "object") {
          if (this.displayField) {
            label = element[this.displayField] || element.name;
          } else {
            label = element.name;
          }
        } else {
          label = element;
        }
      }
      if (label && capitalize) {
        label = `${label.charAt(0).toUpperCase()}${label.substring(1)}`;
      }
      if (label && firstBold) {
        label = `${label.charAt(0).bold()}${label.substring(1)}`;
      }
      return label;
    }
  },
  data() {
    return {
      model: {
        id: "virtualId",
        parentId: "parentId",
        expanded: true
      },
      profilAcls: {},
      completeList: false,
      treeColumns: [],
      graphProperties: null,
      treeConfigReady: false,
      treeContentReady: false,
      acls: {}
    };
  }
};

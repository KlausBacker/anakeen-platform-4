import { getTreeListData } from "./utils/treeUtils";

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
  watch: {
    completeList() {
      if (this.dataSource) {
        this.dataSource.read();
      }
    },
    workflowContentUrl() {
      if (this.dataSource) {
        this.dataSource.read();
      }
    },
    wid() {
      if (this.dataSource) {
        this.dataSource.read();
      }
    }
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
        field: "account",
        headerTemplate: `<div class="account-header">Account <div class="show-all-switch switch-container">
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider round"></span>
                            </label>
                            <label class="switch-label" for="extendedView">
                                <span>Show all</span>
                            </label>
                        </div></div>`,
        template: e => {
          if (e.accountType) {
            return e.account.type;
          }
          return e.account.reference;
        }
      };
    }
  },
  beforeCreate() {
    this.privateMethods = {
      getRightsTemplate: rights => {
        if (rights) {
          return (
            "<div class='btn-group'>" +
            this.acls
              .map(acl => {
                switch (rights[acl]) {
                  case "set":
                    return `<button class="btn btn-primary" data-acl="${acl}">${acl
                      .charAt(0)
                      .toUpperCase()}</button>`;
                  case "inherit":
                  case undefined:
                    return `<button class="btn btn-secondary" data-acl="${acl}">${acl
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
      getHeaderTemplate: step => {
        let stepColor = step.color;
        if (!stepColor) {
          stepColor = "#FFFFFF";
        }
        return `<div class="step-header step--${step.id}">
                    ${step.label}
                     <span class="step-header-color" style="background: \\${stepColor}"></span>
                </div>`;
      },
      getCellTemplate: step => {
        const fieldId = step.id;
        return dataItem => {
          const currentValue = dataItem[fieldId];
          let template = currentValue;
          if (template && typeof template === "object") {
            template = this.privateMethods.getRightsTemplate(template);
          }
          return template || "";
        };
      },
      getColumns: steps => {
        if (Array.isArray(steps)) {
          const columns = [this.firstColumn];
          steps.forEach(step => {
            const column = {
              field: step.id,
              title: step.label,
              headerTemplate: this.privateMethods.getHeaderTemplate(step),
              template: this.privateMethods.getCellTemplate(step)
            };
            columns.push(column);
          });
          return columns;
        }
        return [];
      },
      loadTree: () => {
        this.treeList = this.$(this.$refs.treeListEl)
          .kendoTreeList({
            height: "100%",
            dataSource: this.dataSource,
            columns: this.treeColumns
          })
          .data("kendoTreeList");
        this.treeList.thead.append(
          `<tr role="row">
              <th class="k-header">
                <input class="k-textbox filter" placeholder="Filter..." type="text"/>
              </th>
              <th class="k-header">
                ${this.acls
                  .map(c => {
                    return `<input type="checkbox" id="${
                      this.wid
                    }-${c}" class="k-checkbox check-acl check-acl--${c}" data-acl="${c}" checked="checked"/><label class="k-checkbox-label" for="${
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
        this.$(this.treeList.thead).on("change", ".check-acl", event => {
          const checked = event.currentTarget.checked;
          const acl = event.currentTarget.dataset.acl;
          if (checked) {
            this.$("[data-acl=" + acl + "]", this.treeList.table).show();
          } else {
            this.$("[data-acl=" + acl + "]", this.treeList.table).hide();
          }
        });
        this.$(this.treeList.thead).on(
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
        this.$(this.treeList.thead).on("change", "input.filter", event => {
          const value = event.currentTarget.value;
          if (value) {
            this.dataSource.filter({
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
            this.dataSource.filter(null);
          }
        });
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
      readData: options => {
        this.$http
          .get(this.resolvedWorkflowContent)
          .then(response => {
            options.success(response);
          })
          .catch(err => {
            console.error(err);
            options.error(err);
          });
      },
      parseData: response => {
        return getTreeListData(response.data.data.steps);
      },
      generateDataSource: () => {
        this.dataSource = new kendo.data.TreeListDataSource({
          transport: {
            read: this.privateMethods.readData
          },
          schema: {
            model: this.model,
            parse: this.privateMethods.parseData
          }
        });
      }
    };
  },
  created() {
    this.$on("workflow-rights-config-ready", () => {
      this.treeConfigReady = true;
    });
    this.$on("workflow-rights-content-ready", () => {
      this.treeContentReady = true;
    });
    this.privateMethods.fetchColumns();
    this.privateMethods.generateDataSource();
  },
  mounted() {
    if (this.treeConfigReady) {
      this.privateMethods.loadTree();
    } else {
      this.$on("workflow-rights-config-ready", this.privateMethods.loadTree);
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
      dataSource: null,
      treeList: null,
      treeColumns: [],
      treeConfigReady: false,
      treeContentReady: false,
      acls: this.defaultAcls
    };
  }
};

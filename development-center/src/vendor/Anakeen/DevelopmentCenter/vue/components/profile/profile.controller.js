import "@progress/kendo-ui/js/kendo.treelist";
import { checksum, convertAclToKendoStyle } from "./utils/group";

export default {
  name: "ank-dev-profile",
  props: {
    profileId: [Number, String],
    onlyExtendedAcls: false,
    detachable: false,
    onRefProfileClickCallback: {
      type: Function,
      default: refProfile => {
        window.open(`/api/v2/devels/security/profile/${refProfile.name || refProfile.id}.html`);
      }
    },
    labelRotate: {
      type: Boolean,
      default: true
    }
  },
  watch: {
    profileId() {
      this.profileTreeReady = false;
      this.privateScope.initProfileComponent();
    },
    showLabels(newValue, oldValue) {
      if (newValue !== oldValue) {
        this.profileTreeReady = false;
        this.privateScope.initProfileComponent();

        window.localStorage.setItem("profile.show.labels", newValue);
      }
    },
    displayAllElements(newValue) {
      window.localStorage.setItem("profile.display.all.elements", newValue);
    },
    showColumns(newValue) {
      if (newValue) {
        this.columns.forEach(item => {
          if (!this.defaultColumns.includes(item.field.split(".")[1])) {
            $(this.$refs.profileTreeList)
              .data("kendoTreeList")
              .showColumn(item.field);
          }
        });
      } else {
        this.columns.forEach(item => {
          if (!this.defaultColumns.includes(item.field.split(".")[1])) {
            $(this.$refs.profileTreeList)
              .data("kendoTreeList")
              .hideColumn(item.field);
          }
        });
      }
      window.localStorage.setItem("profile.show.columns", newValue);
      this.profileTreeReady = false;
      this.privateScope.initProfileComponent();
    }
  },
  data: () => ({
    hiddenCol: ["modify", "send", "unlock", "confidential"],
    defaultColumns: [],
    title: "",
    id: "",
    name: "",
    refProfile: null,
    displayAllElements: window.localStorage.getItem("profile.display.all.elements") === "true" ? true : false,
    labelRotation: 300,
    columnWidth: "3rem",
    profileTreeReady: false,
    showError: false,
    errorToDisplay: "",
    showLabels: window.localStorage.getItem("profile.show.labels") === "true" ? true : false,
    showColumns: window.localStorage.getItem("profile.show.columns") === "true" ? true : false,
    columns: []
  }),
  devCenterRefreshData() {
    this.profileTreeReady = false;
    this.displayAllElements = false;
    this.privateScope.initProfileComponent();
  },
  created() {
    this.privateScope = {
      onResizeContent: () => {
        const wrapper = $(this.$el);
        const wrapperHeight = wrapper.height();
        const headerHeight = wrapper.find(".profile-content").height();
        let contentSize = wrapperHeight - headerHeight;
        const content = $(this.$refs.profileTreeList);
        if (!contentSize) {
          content.height("100%");
        } else {
          content.height(contentSize);
        }

        contentSize = content.height();
        const treeHeaderHeight = content.find(".k-grid-header").height();
        const treeContent = content.find(".k-grid-content");
        treeContent.height(contentSize - treeHeaderHeight - 5);
      },
      computeTextWidth: (text, font = "12px Arial") => {
        const tempCanvas = document.createElement("canvas");
        const ctx = tempCanvas.getContext("2d");
        ctx.font = font;
        return ctx.measureText(text).width;
      },
      computeHeaderHeight: (longestStringWidth, labelRotation = 300) => {
        const result = Math.sin((labelRotation * Math.PI) / 180) * longestStringWidth;
        if (result < 0) {
          return -1 * result;
        }
        return result;
      },
      initDataSource: () => {
        return new kendo.data.TreeListDataSource({
          transport: {
            read: options => {
              $(this.$refs.profileTreeList)
                .data("kendoTreeList")
                .autoFitColumn("title");
              kendo.ui.progress($(".k-grid", this.$el), true);
              this.$http
                .get(this.privateScope.getCurrentUrl())
                .then(content => {
                  kendo.ui.progress($(".k-grid", this.$el), false);
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
                      acc[currentAccess.account.type] = acc[currentAccess.account.type] + 1;
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
                        nbElementsByCat.field === 0 ? "badge-light" : "badge-primary"
                      }">${nbElementsByCat.field}</span>`,
                      parentId: null,
                      expanded: true
                    },
                    {
                      id: checksum("role"),
                      title: `Roles : <span class="badge ${
                        nbElementsByCat.role === 0 ? "badge-light" : "badge-primary"
                      }">${nbElementsByCat.role}</span>`,
                      parentId: null,
                      expanded: true
                    },
                    {
                      id: checksum("group"),
                      title: `Groups : <span class="groupElement badge account-type-group ${
                        nbElementsByCat.group === 0 ? "badge-light" : "badge-primary"
                      }">${nbElementsByCat.group}</span> 
<button class="k-button k-button-icon foldGroups"><span class="k-icon k-i-minus"></span></button> 
<button class="k-button k-button-icon unfoldGroups"><span class="k-icon k-i-plus"></span></button>`,
                      parentId: null
                    },
                    {
                      id: checksum("user"),
                      title: `Users : <span class="badge ${
                        nbElementsByCat.user === 0 ? "badge-light" : "badge-primary"
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
                          parentId: currentElement.parentId || checksum(currentElement.account.type)
                        }
                      };
                    })
                  ]);
                })
                .catch(err => {
                  kendo.ui.progress($(".k-grid-content", this.$el), false);
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
          return `/api/v2/devel/security/profile/${this.profileId}/accesses/?group=all&role=all`;
        }
        return `/api/v2/devel/security/profile/${this.profileId}/accesses/`;
      },
      updateDataSource: () => {
        this.dataSource.read();
      },
      fetchTreeConfig: () => {
        if (this.profileId) {
          this.$http
            .get(`/api/v2/devel/security/profile/${this.profileId}/accesses/?acls=only`)
            .then(content => {
              if (!content.data.success) {
                return this.$emit("error", content.data.messages.join(" "));
              }
              const data = content.data.data;
              this.title = data.properties.title;
              this.name = data.properties.name;
              this.id = data.properties.id;
              this.refProfile = data.properties.reference;
              this.profileTreeReady = true;

              this.$nextTick(() => {
                this.privateScope.initTreeView(data);
              });
            })
            .catch(err => {
              console.error(err);
              this.$emit("error", err);
            });
        } else {
          this.errorToDisplay = "There is no profile";
          this.showError = true;
        }
      },
      initTreeView: data => {
        data.properties.acls.map(currentElement => {
          if (!this.hiddenCol.includes(currentElement.name)) {
            this.defaultColumns.push(currentElement.name);
          }
        });
        const lineRender = (column, callback) => {
          return currentLine => {
            return callback(column, currentLine);
          };
        };
        let maxLabelSize = 0;
        const columns = data.properties.acls.map(currentElement => {
          const label = this.showLabels ? currentElement.label : currentElement.name;
          const textWidth = this.privateScope.computeTextWidth(label, $(this.$el).css("font"));
          if (textWidth > maxLabelSize) {
            maxLabelSize = textWidth;
          }
          let headerAttributes = {};

          if (!this.showColumns) {
            return {
              field: `acls.${currentElement.name}`,
              title: this.showLabels ? `${currentElement.label}` : `${currentElement.name}`,
              attributes: {
                class: "rightColumn"
              },
              headerAttributes,
              headerTemplate: `<div class="header-acl-label">
                       <span class="acl-label">${
                         this.showLabels ? currentElement.label : currentElement.name
                       }</span></div>`,
              width: this.columnWidth,
              hidden: !this.defaultColumns.reduce((accumulator, currentColumn) => {
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
              }, false),
              template: lineRender(currentElement.name, (column, currentLine) => {
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
              })
            };
          } else {
            return {
              field: `acls.${currentElement.name}`,
              title: this.showLabels ? `${currentElement.label}` : `${currentElement.name}`,
              attributes: {
                class: "rightColumn"
              },
              headerAttributes,
              headerTemplate: `<div class="header-acl-label">
                       <span class="acl-label">${
                         this.showLabels ? currentElement.label : currentElement.name
                       }</span></div>`,
              width: this.columnWidth,
              template: lineRender(currentElement.name, (column, currentLine) => {
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
              })
            };
          }
        });
        this.columns = columns;

        const treeList = $(this.$refs.profileTreeList).kendoTreeList({
          columns: [
            {
              field: "title",
              title: "Refs",
              template: currentElement => {
                if (currentElement.title) {
                  return currentElement.title;
                }
                return `<span title="${currentElement.accountId}" class="account-type-${currentElement.account.type}">${currentElement.account.reference}</span>`;
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
            $(window).trigger("resize");
          },
          dataSource: this.dataSource
        });
        this.dataSource.bind("change", () => {
          treeList.data("kendoTreeList").autoFitColumn("title");
        });

        $("table[role=grid]", this.$el).attr("style", this.previousWidth);

        treeList.on("click", ".foldGroups", () => {
          treeList
            .find(".account-type-group")
            .toArray()
            .forEach(currentElement => {
              treeList.data("kendoTreeList").collapse($(currentElement).closest(`[role="row"]`));
            });
          treeList.data("kendoTreeList").autoFitColumn("title");
        });
        treeList.on("click", ".unfoldGroups", () => {
          treeList
            .find(".account-type-group")
            .toArray()
            .forEach(currentElement => {
              treeList.data("kendoTreeList").expand($(currentElement).closest(`[role="row"]`));
            });
          treeList.data("kendoTreeList").autoFitColumn("title");
        });
        if (this.labelRotate) {
          $("thead", this.$el).addClass("rotated");
        }
      },
      initProfileComponent: () => {
        this.previousWidth = $("table[role=grid]", this.$el).attr("style");
        this.dataSource = this.privateScope.initDataSource();
        this.privateScope.fetchTreeConfig();
        this.privateScope.onResizeContent();
        $(window).resize(this.privateScope.onResizeContent);
      }
    };
  },
  computed: {
    detachPropOptions() {
      return {
        onlyExtendedAcls: !!this.onlyExtendedAcls
      };
    }
  },
  mounted() {
    this.privateScope.initProfileComponent();

    $(this.$el).on("mouseover", "td.rightColumn", event => {
      let $item = $(event.currentTarget);
      let index = $item.index();
      let $table = $item.closest(".k-treelist").find("table");
      $table.find(".right--over").removeClass("right--over");
      $table.find("tr td:nth-child(" + (index + 1) + "), tr th:nth-child(" + index + ")").addClass("right--over");
    });
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
        window.open(
          `/api/v2/devels/security/profile/${this.profileId}.html?${$.param({
            options: this.detachPropOptions
          })}`
        );
      }
    }
  }
};

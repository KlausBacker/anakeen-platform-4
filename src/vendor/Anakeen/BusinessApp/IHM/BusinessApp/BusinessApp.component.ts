import AnkSEList from "@anakeen/user-interfaces/components/lib/AnkSEList";
import AnkSETab from "@anakeen/user-interfaces/components/lib/AnkSETab";
import AnkTabs from "@anakeen/user-interfaces/components/lib/AnkSETabs";
import AnkTab from "@anakeen/user-interfaces/components/lib/AnkTab";
import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import Welcome from "../Welcome/Welcome.vue";

interface IBusinessAppCollectionProp {
  title: string;
  initid: string | number;
  id: string | number;
  name: string;
}

@Component({
  components: {
    "ank-se-list": AnkSEList,
    "ank-se-tab": AnkSETab,
    "ank-tab": AnkTab,
    "ank-tabs": AnkTabs,
    "ank-welcome": Welcome
  }
})
export default class BusinessApp extends Vue {
  @Prop({ default: () => [], type: Array })
  public collections!: IBusinessAppCollectionProp[];
  @Prop({ default: false, type: [Boolean, Object] }) public welcomeTab!:
    | boolean
    | object;

  @Prop({ default: "", type: String }) public selectedElement!: string;
  @Prop({ default: "", type: String }) public collection!: string;
  @Prop({ default: 1, type: Number }) public page!: number;
  @Prop({ default: "", type: String }) public filter!: string;

  public $refs!: {
    businessAppList: AnkSEList;
    businessWelcomeTab: Welcome;
    businessAppCollectionSelector: Element;
    seTab: AnkSETab;
  };

  public tabs: object[] = [];
  public selectedTab: string = this.selectedElement;
  public panes: object[] = [
    {
      collapsible: true,
      scrollable: false,
      size: "25%"
    },
    {
      collapsible: true,
      scrollable: false
    }
  ];
  public selectedCollection: string | number = "";
  public collectionDropDownList: kendo.ui.DropDownList = null;
  public currentListPage: number = 0;
  public currentListFilter: string = "";
  private creationCounter: number = 0;

  @Watch("selectedCollection")
  public onSelectedCollectionDataChange(newVal, oldVal) {
    if (newVal !== oldVal) {
      this.$emit("selectedCollection", newVal);
    }
  }

  @Watch("selectedTab")
  public onSelectedTabDataChange(newVal, oldVal) {
    if (newVal && newVal !== oldVal) {
      this.$emit("selectedElement", newVal);
    }
  }

  @Watch("currentListPage")
  public onCurrentListPageDataChange(newVal, oldVal) {
    if (newVal && newVal !== oldVal) {
      this.$emit("pageChanged", newVal);
    }
  }

  @Watch("currentListFilter")
  public onCurrentListFilterDataChange(newVal, oldVal) {
    if (newVal !== oldVal) {
      this.$emit("filterChanged", newVal);
    }
  }

  public mounted() {
    if (this.isMultiCollection) {
      this.initCollectionSelector();
      this.selectedCollection = this.collectionDropDownList.dataItem().initid;
    } else {
      if (this.collection) {
        this.selectedCollection = this.collection;
      } else if (this.collections && this.collections.length) {
        this.selectedCollection = this.collections[0].initid;
      }
    }
    this.initSEList();
    this.initSETabs();
  }

  public get isMultiCollection() {
    return this.collections && this.collections.length > 1;
  }

  public get hasWelcomeTab() {
    return this.welcomeTab;
  }

  protected addTab(tab) {
    if (tab.tabId === undefined) {
      tab.tabId = tab.name;
    }
    // @ts-ignore
    if (this.tabs.findIndex(t => t.tabId === tab.tabId) === -1) {
      this.tabs.push(tab);
    }
    this.selectedTab = tab.tabId;
  }

  protected onSelectListItem(event) {
    const seProps = event.detail[0];

    // @ts-ignore
    this.addTab({
      closable: true,
      icon: seProps.icon,
      label: seProps.title,
      name: seProps.id.toString(),
      title: seProps.title
    });
  }

  protected onTabRemove(tabRemoved) {
    const closeTab = () => {
      this.tabs.splice(index, 1);
      if (index !== 0) {
        if (this.selectedTab === tabRemoved) {
          // @ts-ignore
          this.selectedTab = this.tabs[index - 1].tabId;
        }
      } else {
        this.selectedTab = "welcome";
      }
    };
    // @ts-ignore
    const index = this.tabs.findIndex(t => t.tabId === tabRemoved);
    const vueTab = this.$refs.seTab[index];
    if (vueTab && vueTab.close) {
      vueTab.close().then(() => {
        closeTab();
      });
    }
  }

  protected onCreateElement(createInfo) {
    this.addTab({
      closable: true,
      icon: createInfo.icon,
      label: createInfo.title,
      name: createInfo.name,
      tabId: `CREATION_${createInfo.name}_${this.creationCounter++}`,
      title: `Creation ${createInfo.title}`,
      viewId: "!defaultCreation"
    });
  }

  protected onTabClick() {
    this.$refs.businessAppList.selectSe(this.selectedTab);
  }

  protected onActionClick(event, elementData, data) {
    event.preventDefault();
    if (data.eventId === "document.load") {
      this.addTab({
        closable: true,
        name: data.options[0],
        viewId: data.options[1]
      });
    }
  }

  protected onDisplayMessage(event, doc, message) {
    event.preventDefault();
    this.$emit("displayMessage", message);
  }

  protected onDisplayError(event, doc, message) {
    event.preventDefault();
    this.$emit("displayError", message);
  }

  protected onGridConsult(element) {
    this.addTab({
      closable: true,
      name: element.id.toString()
    });
  }

  protected onAfterSave() {
    this.$refs.businessAppList.refreshList();
    if (this.$refs.businessWelcomeTab) {
      // @ts-ignore
      this.$refs.businessWelcomeTab.refresh();
    }
  }

  protected onAfterDelete() {
    this.$refs.businessAppList.refreshList();
    if (this.$refs.businessWelcomeTab) {
      // @ts-ignore
      this.$refs.businessWelcomeTab.refresh();
    }
  }

  protected afterPageChange(event) {
    const page = event.detail && event.detail.length ? event.detail[0] : {};
    this.currentListPage = page.currentPage;
  }

  protected onListFilterChange(event) {
    const filter = event.detail && event.detail.length ? event.detail[0] : "";
    this.currentListFilter = filter.filterInput;
  }

  private initCollectionSelector() {
    this.collectionDropDownList = $(this.$refs.businessAppCollectionSelector)
      .kendoDropDownList({
        dataSource: this.collections,
        dataTextField: "title",
        dataValueField: "initid",
        select: (e: kendo.ui.DropDownListSelectEvent) => {
          this.selectedCollection = e.dataItem.initid;
          this.$refs.businessAppList.setCollection({
            initid: this.selectedCollection
          });
          this.currentListPage = 1;
        },
        template: `<span style="display: flex; align-items: center;"><img style="margin-right: 1rem;" src="#:displayIcon#"/> <span>#:title#</span></span>`,
        value: this.collection || "",
        valueTemplate: `<span style="display: flex; align-items: center;"><img style="margin-right: 1rem;" src="#:displayIcon#"/> <span>#:title#</span></span>`
      })
      .data("kendoDropDownList");
  }

  private initSEList() {
    if (this.$refs.businessAppList) {
      this.$refs.businessAppList.setCollection({
        initid: this.selectedCollection
      });
      this.$refs.businessAppList.$once("se-list-dataBound", () => {
        if (this.page) {
          this.currentListPage = this.page;
          this.$refs.businessAppList.dataSource.page(this.page);
        }
        if (this.filter) {
          this.currentListFilter = this.filter;
          this.$refs.businessAppList.filterList(this.filter);
        }
        this.$refs.businessAppList.refreshList().then(() => {
          if (this.selectedTab) {
            this.$refs.businessAppList.selectSe(this.selectedTab);
          }
        });
      });
    }
  }

  private initSETabs() {
    if (!this.selectedTab || this.selectedTab === "welcome") {
      this.selectedTab = "welcome";
    } else {
      const match = this.selectedTab.match(/CREATION_([A-Z0-9a-z]+)_\d+/);
      if (match && match.length > 1) {
        this.addTab({
          closable: true,
          name: match[1],
          tabId: `CREATION_${match[1]}_${this.creationCounter++}`,
          viewId: "!defaultCreation"
        });
      } else {
        this.addTab({
          closable: true,
          name: this.selectedTab
        });
      }
    }
  }
}

import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import AnkSplitter from "@anakeen/internal-components/lib/Splitter";
import AnkSEList from "@anakeen/user-interfaces/components/lib/AnkSEList";
import AnkSETabs from "@anakeen/user-interfaces/components/lib/AnkSETabs";
import AnkSETab from "@anakeen/user-interfaces/components/lib/AnkSETab";
import AnkTab from "@anakeen/user-interfaces/components/lib/AnkTab";
import Welcome from "../Welcome/Welcome.vue";

interface IBusinessAppCollectionProp {
  title: string;
  initid: string | number;
  id: string | number;
  name: string;
}

@Component({
  components: {
    AnkSplitter,
    "ank-se-list": AnkSEList,
    "ank-se-tabs": AnkSETabs,
    "ank-se-tab": AnkSETab,
    "ank-tab": AnkTab,
    "ank-welcome": Welcome
  }
})
export default class BusinessApp extends Vue {
  @Prop({ default: () => [], type: Array }) public collections!: Array<
    IBusinessAppCollectionProp
  >;
  @Prop({ default: false, type: [Boolean, Object] }) public welcomeTab!:
    | boolean
    | object;

  public $refs!: {
    businessAppList: AnkSEList;
    businessAppCollectionSelector: Element;
    businessAppSplitter: AnkSplitter;
    seTab: AnkSETab;
  };

  public tabs: object[] = [];
  public selectedTab: string = "";
  public panes: object[] = [
    {
      scrollable: false,
      collapsible: true,
      size: "25%"
    },
    {
      scrollable: false,
      collapsible: true
    }
  ];
  public selectedCollection: string | number = "";
  public collectionDropDownList: kendo.ui.DropDownList = null;

  @Watch("selectedCollection")
  onSelectedCollectionChange(newVal, oldVal) {
    if (newVal !== oldVal) {
      if (this.$refs.businessAppList) {
        this.$refs.businessAppList.setCollection({
          initid: newVal
        });
      }
    }
  }

  public mounted() {
    this.$refs.businessAppSplitter.disableEmptyContent();
    if (this.isMultiCollection) {
      this.collectionDropDownList = $(this.$refs.businessAppCollectionSelector)
        .kendoDropDownList({
          dataSource: this.collections,
          dataTextField: "title",
          dataValueField: "initid",
          template: `<span style="display: flex; align-items: center;"><img style="margin-right: 1rem;" src="#:displayIcon#"/> <span>#:title#</span></span>`,
          valueTemplate: `<span style="display: flex; align-items: center;"><img style="margin-right: 1rem;" src="#:displayIcon#"/> <span>#:title#</span></span>`,
          select: (e: kendo.ui.DropDownListSelectEvent) => {
            this.selectedCollection = e.dataItem.initid;
          }
        })
        .data("kendoDropDownList");
      this.selectedCollection = this.collectionDropDownList.dataItem().initid;
    } else {
      if (this.collections && this.collections.length) {
        this.selectedCollection = this.collections[0].initid;
      }
    }
    if (!this.selectedTab) {
      this.selectedTab = "ankWelcomePage";
    }
  }

  public get isMultiCollection() {
    return this.collections && this.collections.length > 1;
  }

  public get hasWelcomeTab() {
    return this.welcomeTab;
  }

  protected addTab(tab) {
    // @ts-ignore
    if (this.tabs.findIndex(t => t.name === tab.name) === -1) {
      this.tabs.push(tab);
    }
    this.selectedTab = tab.name;
  }

  protected onSelectListItem(event) {
    const seProps = event.detail[0];

    // @ts-ignore
    this.addTab({
      label: seProps.title,
      name: seProps.id.toString(),
      closable: true,
      icon: seProps.icon,
      title: seProps.title
    });
  }

  protected onTabRemove(tabRemoved) {
    const closeTab = () => {
      this.tabs.splice(index, 1);
      if (index !== 0) {
        if (this.selectedTab === tabRemoved) {
          // @ts-ignore
          this.selectedTab = this.tabs[index - 1].name;
        }
      } else {
        this.selectedTab = "ankWelcomePage";
      }
    };
    // @ts-ignore
    const index = this.tabs.findIndex(t => t.name === tabRemoved);
    const vueTab = this.$refs.seTab[index];
    if (vueTab && vueTab.close) {
      vueTab.close().then(() => {
        closeTab();
      });
    }
  }

  protected onCreateElement(createInfo) {
    this.addTab({
      label: createInfo.title,
      name: createInfo.name,
      viewId: "!defaultCreation",
      title: `Creation ${createInfo.title}`,
      icon: createInfo.icon,
      closable: true
    });
  }
}

import AnkSplitter from "@anakeen/internal-components/lib/Splitter";
import AnkSEList from "@anakeen/user-interfaces/components/lib/AnkSEList";
import AnkSETab from "@anakeen/user-interfaces/components/lib/AnkSETab";
import AnkSETabs from "@anakeen/user-interfaces/components/lib/AnkSETabs";
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
    AnkSplitter,
    "ank-se-list": AnkSEList,
    "ank-se-tab": AnkSETab,
    "ank-se-tabs": AnkSETabs,
    "ank-tab": AnkTab,
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

  public $refs!: {
    businessAppList: AnkSEList;
    businessAppCollectionSelector: Element;
    businessAppSplitter: AnkSplitter;
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

  @Watch("selectedCollection")
  public onSelectedCollectionChange(newVal, oldVal) {
    if (newVal !== oldVal) {
      if (this.$refs.businessAppList) {
        this.$refs.businessAppList.setCollection({
          initid: newVal
        });
      }
    }
  }

  @Watch("selectedTab")
  public onSelectedTabDataChange(newVal, oldVal) {
    if (newVal !== oldVal) {
      this.$emit("selectedElement", newVal);
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
          select: (e: kendo.ui.DropDownListSelectEvent) => {
            this.selectedCollection = e.dataItem.initid;
          },
          template: `<span style="display: flex; align-items: center;"><img style="margin-right: 1rem;" src="#:displayIcon#"/> <span>#:title#</span></span>`,
          valueTemplate: `<span style="display: flex; align-items: center;"><img style="margin-right: 1rem;" src="#:displayIcon#"/> <span>#:title#</span></span>`
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
    } else {
      this.addTab({
        closable: true,
        name: this.selectedTab
      });
      this.$refs.businessAppList.selectSe(this.selectedTab);
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
      closable: true,
      icon: createInfo.icon,
      label: createInfo.title,
      name: createInfo.name,
      title: `Creation ${createInfo.title}`,
      viewId: "!defaultCreation"
    });
  }

  protected onTabClick() {
    this.$refs.businessAppList.selectSe(this.selectedTab);
  }
}

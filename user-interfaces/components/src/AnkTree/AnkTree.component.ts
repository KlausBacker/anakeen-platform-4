/* eslint-disable no-console */
import "@progress/kendo-ui/js/kendo.splitter";
import { Component, Prop, Mixins, Watch } from "vue-property-decorator";
import axios, { CancelTokenSource } from "axios";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";

interface ITreeItemType {
  name: string;
  parentIndexes: string[];
  isSelected: boolean;
  isChildSelected: boolean;
  isOpened: boolean;
  index: string;
  login: string;
  accountId: number;
  id: string;
  level: number;
  match: boolean;
  loading: boolean;
  children: ITreeItemType[];
  positionIndex: number;
  directChildsCount: number;
  loadedChildrenCount: number;
  childrenCount: number;
  itemCount: number;
}

interface ITreeTranslationsType {
  headerLabel?: string;
  itemCount?: string;
  childrenCount?: string;
  selectItem?: string;
  reopenNode?: string;
  loading?: string;
  searching?: string;
  reload?: string;
  noItemToDisplay?: string;
}
@Component({
  name: "ank-tree"
})
export default class AnkTreeComponent extends Mixins(AnkI18NMixin) {
  public $refs!: {
    tree: Element;
  };

  @Prop({ type: String, default: "" }) public treeUrl;
  @Prop({ type: String, default: "" }) public treeOpenNodeUrl;
  @Prop({ type: Object }) public customTranslations: ITreeTranslationsType;
  @Prop({ type: Boolean, default: false }) public displayItemCount;
  @Prop({ type: Boolean, default: false }) public displayChildrenCount;
  @Prop({ type: Boolean, default: false }) public displaySelectedParent;
  @Prop({ type: Boolean, default: true }) public displayFilter;
  @Prop({ type: Number, default: 2 }) public itemHeight;
  @Prop({ type: Number, default: 2 }) public levelIndentationWidth;
  @Prop({ type: Boolean, default: false }) public multipleSelection;
  @Prop({ type: String, default: "" }) public filter;
  @Prop({ type: Number, default: 20 }) public scrollDebounce;

  public treeData: ITreeItemType[] = [];

  public error = "";
  public message = "";
  public lastItemHeight = 0;
  public firstItemHeight = 0;
  public requestFilter = "";
  public visibleTreeData: ITreeItemType[] = [];
  public selectedNodes: { [index: string]: ITreeItemType } = {};
  // Number of displayed branches (lines) : computed depends of height of visible tree div
  protected visibleItemSlice = 10;
  // Number of displayed branches to add more
  protected visibleItemDeltaSlice = 40;
  protected itemPxHeigth = 10;
  protected openedItemCount = 1;
  protected httpToken!: CancelTokenSource | null;
  protected scrollBarWidth = "10px";
  protected nTop = 0;
  protected timerId = 0;
  resizeObserver: ResizeObserver;
  @Watch("treeData")
  protected watchTreeData(): void {
    this.displayTree();
  }

  @Watch("selectedNodes")
  protected watchselectedNodes(): void {
    this.displaySelected();
  }

  @Watch("displaySelectedParent")
  protected watchDisplaySelectedParent(newDisplay): void {
    if (newDisplay === false) {
      if (Object.keys(this.selectedNodes).length > 0) {
        this.treeData.forEach(item => {
          this.$set(item, "isChildSelected", false);
        });
      }
    }
    this.displaySelected();
  }

  @Watch("displayItemCount")
  protected watchExpanded(): void {
    this.reloadTree();
  }
  @Watch("visibleItemSlice")
  protected watchVisibleItemSlice(): void {
    this.displayTree();
  }
  @Watch("filter")
  protected watchFilter(): void {
    this.reloadTree();
  }

  protected displaySelected(): void {
    let parentToSelect: string[] = [];
    const ids = Object.keys(this.selectedNodes);

    this.treeData.forEach(item => {
      if (ids.indexOf(item.id) === -1) {
        if (item.isSelected === true) this.$set(item, "isSelected", false);
      } else {
        this.$set(item, "isSelected", true);
      }
    });

    if (this.displaySelectedParent === true) {
      Object.values(this.selectedNodes).forEach(selectedNode => {
        if (selectedNode.parentIndexes) parentToSelect = parentToSelect.concat(selectedNode.parentIndexes);
      });

      // display parent selection
      this.treeData.forEach(item => {
        if (parentToSelect.indexOf(item.index) === -1) {
          this.$set(item, "isChildSelected", false);
        } else {
          this.$set(item, "isChildSelected", true);
        }
      });
    }
  }

  protected debounced(delay, fn): Function {
    return (...args): void => {
      if (this.timerId) {
        window.clearTimeout(this.timerId);
      }
      this.timerId = window.setTimeout(() => {
        fn(...args);
        this.timerId = null;
      }, delay);
    };
  }
  /**
   * Compute visible part of tree
   * The visibleTreeData is a sub-tree visible into the DOM
   * @protected
   */
  protected displayTree(): void {
    let nBottom = this.treeData.length - this.visibleItemSlice - this.nTop;
    this.visibleTreeData = this.treeData.slice(this.nTop, this.visibleItemSlice + this.nTop);

    this.firstItemHeight = this.nTop * this.itemHeight;

    if (nBottom < 0) {
      nBottom = 0;
    }
    this.lastItemHeight = nBottom * this.itemHeight;
    this.openedItemCount = this.treeData.length;

    // Need to next tick because tree div not in dom yet. It will be after v-if is set
    this.$nextTick(() => {
      if (this.$refs.tree) {
        this.resizeObserver.disconnect();
        this.resizeObserver.observe(this.$refs.tree);
      }
    });
  }

  /**
   * Add positionIndex to optimize compute position when insert new node childs
   * @param data
   * @protected
   */
  protected reindex(data): [] {
    return data.map((item, key) => {
      item.positionIndex = key;
      return item;
    });
  }

  /**
   * Get scrollbar width to align display of header
   * @protected
   */
  protected getScrollbarWidth(): number {
    const scrollDiv = document.createElement("div");

    Object.assign(scrollDiv.style, {
      width: "100px",
      height: "100px",
      overflow: "scroll",
      position: "absolute",
      top: "-9999px"
    });
    document.body.appendChild(scrollDiv);

    // Get the scrollbar width
    const scrollBarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth;
    // Delete the DIV
    document.body.removeChild(scrollDiv);
    return scrollBarWidth;
  }

  /**
   * Insert all children in item list
   * @param data
   * @protected
   */
  protected flatTree(data: ITreeItemType[]): ITreeItemType[] {
    return data.flatMap(item => {
      if (item.loadedChildrenCount > 0) {
        item.isOpened = true;
        const level1 = (item.level || 0) + 1;
        const childs = item.children.map(childItem => {
          childItem.level = level1;
          childItem.parentIndexes = [...(item.parentIndexes || []), item.index];
          return childItem;
        });
        item.children = undefined;
        return [item].concat(this.flatTree(childs));
      } else {
        return [item];
      }
    });
  }

  /**
   * Load new data from server
   * The tree will be redisplayed
   * @protected
   */
  protected reloadTree(): void {
    const url = this.treeUrl;

    this.treeData = [];
    this.nTop = 0;
    this.error = "";

    if (this.httpToken) {
      this.httpToken.cancel("Operation canceled because a new filter is requested.");
    }
    const CancelToken = axios.CancelToken;
    this.httpToken = CancelToken.source();

    this.$http
      .get(url, {
        cancelToken: this.httpToken.token,
        params: {
          getCounts: { item: this.displayItemCount, children: this.displayChildrenCount },
          filter: this.filter
        }
      })
      .then(response => {
        this.httpToken = null;
        this.error = "";
        if (response.data.data) {
          this.message = response.data.data.message;
          this.requestFilter = response.data.data.filter;
          if (response.data.data.hasChilds) {
            this.treeData = this.reindex(this.flatTree(response.data.data.treeData));
          } else {
            this.treeData = this.reindex(response.data.data.treeData);
          }
          if (!this.message && this.treeData.length === 0) {
            this.message = this.translations.noItemToDisplay;
          }
        } else {
          this.treeData = [];
          this.message = response.data;
        }
      })
      .catch(error => {
        if (!axios.isCancel(error)) {
          this.httpToken = null;
          this.treeData = [];
          this.displayError(error);
        }
      });
  }

  protected displayError(error): void {
    const event = new Event("error");
    this.$emit("onError", event, error);
    if (event.defaultPrevented) {
      return;
    }
    if (error.response && error.response.data) {
      const data = error.response.data;
      this.error = data.userMessage || data.message || data.exceptionMessage || data.error || "Unexpected error";
    } else {
      this.error = error.toString();
    }
  }
  /**
   * Collapse node : the child data are deleted
   * @param nodeInfo
   * @protected
   */
  protected closeNode(nodeInfo: ITreeItemType): void {
    this.treeData = this.reindex(
      this.treeData.filter(item => {
        return !item.parentIndexes || item.parentIndexes.indexOf(nodeInfo.index) === -1;
      })
    );

    nodeInfo.isOpened = false;
  }

  /**
   * @return translated texts
   */
  get translations(): ITreeTranslationsType {
    const defaultTranslation: ITreeTranslationsType = {
      headerLabel: this.$t("AnkTree.Label") as string,
      itemCount: this.$t("AnkTree.itemCount") as string,
      childrenCount: this.$t("AnkTree.childrenCount") as string,
      selectItem: this.$t("AnkTree.select") as string,
      reopenNode: this.$t("AnkTree.reopenNode") as string,
      loading: this.$t("AnkTree.loading") as string,
      searching: this.$t("AnkTree.searching") as string,
      reload: this.$t("AnkTree.reload") as string,
      noItemToDisplay: this.$t("AnkTree.noItemToDisplay") as string
    };

    return { ...defaultTranslation, ...this.customTranslations };
  }

  /**
   * Get initial tree data on created
   */
  public created(): void {
    this.reloadTree();
  }
  public destroyed(): void {
    this.resizeObserver.disconnect();
  }

  public mounted(): void {
    // Need to adapt margin of header to scrollvar width
    this.scrollBarWidth = this.getScrollbarWidth() + "px";
    this.resizeObserver = new window.ResizeObserver((entries: ResizeObserverEntry[]) => {
      for (const entry of entries) {
        const heigth = entry.contentRect.height;
        const remPx = parseFloat(getComputedStyle(document.documentElement).fontSize);
        const nbMin = Math.ceil(heigth / remPx);

        this.visibleItemSlice = Math.round(nbMin / this.itemHeight) + this.visibleItemDeltaSlice;
      }
    });
  }

  /**
   * Load child data and display under the node
   * @param event
   * @param nodeInfo
   * @param filterMatchOnly
   */
  public openNode(event: Event, nodeInfo: ITreeItemType, filterMatchOnly: boolean): void {
    // Prevent select event
    event.stopPropagation();
    const positionIndex = nodeInfo.positionIndex;
    if (!nodeInfo.isOpened) {
      const level = nodeInfo.level || 0;
      const level1 = level + 1;
      nodeInfo.loading = true;
      const url = this.treeOpenNodeUrl.replace("{nodeId}", nodeInfo.id);

      // Need to see loading effect
      this.visibleTreeData = { ...this.visibleTreeData };
      this.$http
        .get(url, {
          params: {
            getCounts: { item: this.displayItemCount, children: this.displayChildrenCount },
            filter: this.filter,
            filterMatchOnly
          }
        })
        .then(response => {
          const childNodes = response.data.data.treeData.map(item => {
            item.level = level1;
            item.parentIndexes = [...(nodeInfo.parentIndexes || []), nodeInfo.index];
            return item;
          });
          nodeInfo.loadedChildrenCount = childNodes.length;

          const newT = this.treeData
            .slice(0, positionIndex + 1)
            .concat(childNodes, this.treeData.slice(positionIndex + 1));
          nodeInfo.isOpened = true;
          this.treeData = this.reindex(newT);
          this.error = "";
          this.message = response.data.data.message;

          this.displaySelected();
          nodeInfo.loading = false;
        })
        .catch(error => {
          this.displayError(error);
          nodeInfo.loading = false;
        });
    } else {
      // close node => destroy childs
      this.closeNode(nodeInfo);

      nodeInfo.isOpened = false;
    }
  }

  public reopenNode(event: Event, nodeInfo: ITreeItemType): void {
    this.closeNode(nodeInfo);
    this.openNode(event, nodeInfo, true);
  }
  /**
   * Compute new position index where start visible tree part
   * @param e Event
   */
  public onScroll(e: Event): void {
    const target = e.target as Element;
    const top = target.scrollTop;
    const h1 = Math.round(target.scrollHeight / this.openedItemCount);
    // Compute new first row number to display
    this.nTop = Math.floor(top / h1);
    this.displayTree();
  }

  /**
   * Callback when a new node is selected
   * Emit onSelectItem event with selected node information
   * @param event
   * @param nodeInfo
   */
  public selectNode(event: Event, nodeInfo: ITreeItemType): void {
    if (this.multipleSelection === false) {
      event.preventDefault();

      this.selectedNodes = {};
      this.selectedNodes[nodeInfo.id] = nodeInfo;
      this.$emit("onSelectItem", event, nodeInfo);
    } else {
      // Copy to force vuejs reaction
      this.selectedNodes = { ...this.selectedNodes };
      if (nodeInfo.isSelected === true) {
        delete this.selectedNodes[nodeInfo.id];
      } else {
        this.selectedNodes[nodeInfo.id] = nodeInfo;
      }

      this.$emit("onSelectItem", event, Object.values(this.selectedNodes));
    }
  }
}

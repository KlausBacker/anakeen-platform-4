/* eslint-disable*/
import {DockPosition} from "../HubStation/HubStationsTypes";
import { HUB_DOCK_ENTRY_NAME, dockEntryEvents } from "./HubDockEntry/HubDockEntry.component";
import {Component, Prop, Vue, Watch } from "vue-property-decorator";

@Component({
    provide () {
        return {
            hubDock: this
        };
    }
})
export default class HubDock extends Vue {
    @Prop({ default: DockPosition.LEFT}) position!: DockPosition;
    @Prop({ default: true}) expandable!: boolean;
    @Prop({ default: false}) expanded!: boolean;
    @Prop({ default: "5rem", type: [String, Number]}) collapsedSize!: string | number;
    @Prop({ default: "10rem", type: [String, Number]}) size!: string | number;
    @Prop({ default: true}) collapseOnSelection!: boolean;
    @Prop({ default: false}) superposeDock!: boolean;
    @Prop({ default: false}) evenSpace!: boolean;
    @Prop({ default: false}) multiselection!: boolean;
    @Prop(Array) content!: [];

    collapsed: boolean = !this.expanded;
    currentSize: string|number = this.expanded ? this.size : this.collapsedSize;
    selectedItems: object[] = [];
    hubEntries: Vue[] = [];

    get faCollapseIcon(): string {
        let collapseIcon: string = "chevron";
        let expandIcon: string = "chevron";
        switch (this.position) {
            case DockPosition.LEFT:
                collapseIcon += "-left";
                expandIcon += "-right";
                break;
            case DockPosition.RIGHT:
                collapseIcon += "-right";
                expandIcon += "-left";
                break;
            case DockPosition.TOP:
                collapseIcon += "-up";
                expandIcon += "-down";
                break;
            case DockPosition.BOTTOM:
                collapseIcon += "-down";
                expandIcon += "-up";
                break;
        }
        return this.collapsed ? expandIcon : collapseIcon;
    }

    get sizeConverted(): string {
        if (this.currentSize in Number) {
            return `${this.currentSize}px`;
        } else {
            return <string>this.currentSize;
        }
    }

    get dockStyle(): object {
        switch (this.position) {
            case DockPosition.TOP:
            case DockPosition.BOTTOM:
                return {
                    height: this.sizeConverted
                };
            case DockPosition.LEFT:
            case DockPosition.RIGHT:
                return {
                    width: this.sizeConverted
                };
            default:
                return {}
        }
    }

    get entriesStyle(): object {
        if (this.evenSpace) {
            return {
                "justify-content": "space-evenly"
            };
        }
        return {};
    }

    @Watch("collapsed")
    onCollapsed(val: boolean) {
        if (val) {
            this.currentSize = this.collapsedSize;
        } else {
            this.currentSize = this.size;
        }
    }

    @Watch("selectedItems")
    onSelectedItems(val) {
        this.hubEntries.forEach((entry: any) => {
            entry.entrySelected = val.findIndex((i: any) => i.name === entry.name) > -1;
        });
        this.$emit("dockEntriesSelected", this.multiselection ? val: val[0]);
    }

    mounted() {
        this.hubEntries = HubDock.getHubEntriesInstance(this.$slots.default, this.$slots.header, this.$slots.footer);
        this.hubEntries.forEach(hubEntry => {
            if (hubEntry) {
                Object.keys(dockEntryEvents).forEach(key => {
                    const event = dockEntryEvents[key];
                    hubEntry.$on(event, this.onDockEntryEvent(event))
                })
            }
        })
    }

    expand() {
        this.collapsed = false;
        this.$emit("dockExpanded");
    }

    collapse() {
        this.collapsed = true;
        this.$emit("dockCollapsed");
    }

    toggleDock() {
        if (this.collapsed) {
            this.expand()
        } else {
            this.collapse();
        }
    }

    onDockEntryEvent(eventName) {
        return (eventOption) => {
            switch (eventName) {
                case dockEntryEvents.selected:
                    if (this.multiselection) {
                        if (this.selectedItems.findIndex((i: any) => i.name === eventOption.name) === -1) {
                            this.selectedItems.push(eventOption)
                        }
                    } else {
                        this.selectedItems = [eventOption];
                    }
                    break;
            }
        }
    }

    private static getHubEntriesInstance(...slots): Vue[] {
        let result: Vue[] = [];
        if (slots && slots.length) {
            for (let i = 0; i < slots.length; i++) {
                const nodeSlots = slots[i];
                if (nodeSlots && nodeSlots.length) {
                    result = result.concat(nodeSlots.filter(slot => {
                        if (slot.componentInstance && slot.componentInstance.$options) {
                            return slot.componentInstance.$options.name === HUB_DOCK_ENTRY_NAME;
                        }
                        return false;
                    }).map(slot => slot.componentInstance))
                }
            }
        }
        return result;
    }
}
